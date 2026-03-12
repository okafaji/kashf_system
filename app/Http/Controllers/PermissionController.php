<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User|null $authUser */
            $authUser = Auth::user();

            // السماح لمن لديه admin أو manage-settings أو manage-users
            if (!$authUser || (
                !$authUser->hasRole('admin')
                && !$authUser->can('manage-settings')
                && !$authUser->can('manage-users')
            )) {
                abort(403, 'غير مصرح - صلاحيات Admin مطلوبة.');
            }
            return $next($request);
        });
    }

    /**
     * عرض صفحة إدارة صلاحيات المستخدمين
     */
    public function index()
    {
        $users = User::with('permissions', 'roles')->get();
        $permissions = Permission::all();
        $roles = Role::withCount('permissions')->get();

        return view('admin.permissions.index', compact('users', 'permissions', 'roles'));
    }

    /**
     * عرض صفحة تعديل صلاحيات مستخدم معين
     */
    public function edit($userId)
    {
        $user = User::with('permissions', 'roles', 'department')->findOrFail($userId);
        $allPermissions = Permission::all();
        $allRoles = Role::withCount('permissions')->get();

        return view('admin.permissions.edit', compact('user', 'allPermissions', 'allRoles'));
    }

    /**
     * تحديث صلاحيات المستخدم
     */
    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $guardName = $user->getDefaultGuardName();

        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => [
                'integer',
                Rule::exists('roles', 'id')->where(fn($query) => $query->where('guard_name', $guardName)),
            ],
            'permissions' => 'array',
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where(fn($query) => $query->where('guard_name', $guardName)),
            ],
        ]);

        $roleIds = collect($validated['roles'] ?? [])->map(fn($id) => (int) $id)->unique()->values();
        $permissionIds = collect($validated['permissions'] ?? [])->map(fn($id) => (int) $id)->unique()->values();

        $roles = Role::whereIn('id', $roleIds)->where('guard_name', $guardName)->get();
        $selectedPermissionNames = Permission::whereIn('id', $permissionIds)
            ->where('guard_name', $guardName)
            ->pluck('name')
            ->all();

        $normalizedPermissionNames = $this->normalizePermissionDependencies($selectedPermissionNames);
        $permissions = Permission::whereIn('name', $normalizedPermissionNames)
            ->where('guard_name', $guardName)
            ->get();

        // تحديث الأدوار والصلاحيات من المعرّفات المختارة بشكل آمن
        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        return redirect()
            ->route('admin.permissions.edit', $user->id)
            ->with('success', "تم تحديث صلاحيات المستخدم '{$user->name}' بنجاح ✅");
    }

    /**
     * إعطاء صلاحية معينة للمستخدم بشكل سريع
     */
    public function grantPermission(Request $request, $userId)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,id',
        ]);

        $user = User::findOrFail($userId);
        $permission = Permission::findOrFail($request->input('permission'));
        $guardName = $user->getDefaultGuardName();

        $permissionNamesToGrant = $this->normalizePermissionDependencies([$permission->name]);
        $permissionsToGrant = Permission::whereIn('name', $permissionNamesToGrant)
            ->where('guard_name', $guardName)
            ->get();

        if ($permissionsToGrant->isNotEmpty()) {
            $user->givePermissionTo($permissionsToGrant);
        }

        return response()->json([
            'success' => true,
            'message' => "تم إعطاء صلاحية '{$permission->name}' للمستخدم '{$user->name}' ✅",
        ]);
    }

    /**
     * إزالة صلاحية من المستخدم
     */
    public function revokePermission(Request $request, $userId)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,id',
        ]);

        $user = User::findOrFail($userId);
        $permission = Permission::findOrFail($request->input('permission'));
        $guardName = $user->getDefaultGuardName();

        $remainingDirectPermissionNames = $user->getDirectPermissions()
            ->pluck('name')
            ->reject(fn($name) => $name === $permission->name)
            ->values()
            ->all();

        $normalizedPermissionNames = $this->normalizePermissionDependencies($remainingDirectPermissionNames);
        $permissionsToSync = Permission::whereIn('name', $normalizedPermissionNames)
            ->where('guard_name', $guardName)
            ->get();

        $user->syncPermissions($permissionsToSync);

        return response()->json([
            'success' => true,
            'message' => "تم إزالة صلاحية '{$permission->name}' من المستخدم '{$user->name}' ✅",
        ]);
    }

    /**
     * إعطاء دور معين للمستخدم
     */
    public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->input('role'));

        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

        return response()->json([
            'success' => true,
            'message' => "تم تعيين دور '{$role->name}' للمستخدم '{$user->name}' ✅",
        ]);
    }

    /**
     * إزالة دور من المستخدم
     */
    public function removeRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->input('role'));

        if ($user->hasRole($role)) {
            $user->removeRole($role);
        }

        return response()->json([
            'success' => true,
            'message' => "تم إزالة دور '{$role->name}' من المستخدم '{$user->name}' ✅",
        ]);
    }

    /**
     * يفرض صلاحيات العرض اللازمة عند اختيار صلاحيات تشغيلية مرتبطة بها.
     */
    private function normalizePermissionDependencies(array $permissionNames): array
    {
        $permissions = collect($permissionNames)
            ->filter(fn($permission) => is_string($permission) && $permission !== '')
            ->unique()
            ->values();

        $requiresPayrollView = $permissions->intersect([
            'create-payrolls',
            'edit-payrolls',
            'delete-payrolls',
            'print-payrolls',
            'export-payrolls',
        ])->isNotEmpty();

        if ($requiresPayrollView) {
            $permissions->push('view-payrolls');
        }

        $requiresEmployeesView = $permissions->intersect([
            'create-employees',
            'edit-employees',
            'delete-employees',
            'import-employees',
        ])->isNotEmpty();

        if ($requiresEmployeesView) {
            $permissions->push('view-employees');
        }

        return $permissions->unique()->values()->all();
    }
}
