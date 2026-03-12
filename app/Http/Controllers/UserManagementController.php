<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        $roles = Role::all();
        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $allowedRoles = $this->getAssignableRoles(Auth::user());
        if (empty($allowedRoles)) {
            abort(403, 'لا تملك صلاحية إضافة مستخدمين');
        }

        $roles = Role::whereIn('name', $allowedRoles)->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $allowedRoles = $this->getAssignableRoles(Auth::user());
        if (empty($allowedRoles)) {
            abort(403, 'لا تملك صلاحية إضافة مستخدمين');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $requestedRoles = array_values($request->roles);
            $invalidRoles = array_diff($requestedRoles, $allowedRoles);
            if (!empty($invalidRoles)) {
                abort(403, 'لا تملك صلاحية لإسناد هذا الدور');
            }

            $user->syncRoles($requestedRoles);
        }

        return redirect()->route('users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function edit(User $user)
    {
        if (!$this->canManageUser(Auth::user(), $user)) {
            abort(403, 'لا تملك صلاحية لإدارة هذا المستخدم');
        }

        $allowedRoles = $this->getAssignableRoles(Auth::user());
        $roles = Role::whereIn('name', $allowedRoles)->get();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        if (!$this->canManageUser(Auth::user(), $user)) {
            abort(403, 'لا تملك صلاحية لإدارة هذا المستخدم');
        }

        $allowedRoles = $this->getAssignableRoles(Auth::user());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->has('roles')) {
            $requestedRoles = array_values($request->roles);
            $invalidRoles = array_diff($requestedRoles, $allowedRoles);
            if (!empty($invalidRoles)) {
                abort(403, 'لا تملك صلاحية لإسناد هذا الدور');
            }

            $user->syncRoles($requestedRoles);
        }

        return redirect()->route('users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function updateRole(Request $request, User $user)
    {
        if (!$this->canManageUser(Auth::user(), $user)) {
            abort(403, 'لا تملك صلاحية لإدارة هذا المستخدم');
        }

        $allowedRoles = $this->getAssignableRoles(Auth::user());
        $request->validate([
            'role' => 'nullable|string|exists:roles,name',
        ]);

        if ($request->filled('role')) {
            if (!in_array($request->role, $allowedRoles, true)) {
                abort(403, 'لا تملك صلاحية لإسناد هذا الدور');
            }

            $user->syncRoles([$request->role]);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'تم تحديث دور المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        if (!$this->canManageUser(Auth::user(), $user)) {
            abort(403, 'لا تملك صلاحية لإدارة هذا المستخدم');
        }

        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }

    // إدارة الأدوار
    public function rolesIndex()
    {
        $this->ensureConfiguredPermissionsExist();
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function rolesEdit(Role $role)
    {
        $this->ensureConfiguredPermissionsExist();
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $lockedOwnRolesAccessPermissions = $this->getLockedOwnRolesAccessPermissions($role);

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'lockedOwnRolesAccessPermissions'));
    }

    public function rolesUpdate(Request $request, Role $role)
    {
        $this->ensureConfiguredPermissionsExist();
        $request->validate([
            'permissions' => 'array',
        ]);

        $submittedPermissions = $request->input('permissions', []);
        if (!is_array($submittedPermissions)) {
            $submittedPermissions = [];
        }

        $submittedPermissions = array_values(array_filter($submittedPermissions, static fn ($permission) => is_string($permission) && $permission !== ''));
        $submittedPermissions = $this->normalizePermissionDependencies($submittedPermissions);

        if ($this->wouldLoseRolesAreaAccess($role, $submittedPermissions)) {
            $message = 'لا يمكن إزالة جميع صلاحيات إدارة الأدوار/المستخدمين من دورك الحالي أثناء تسجيل الدخول.';

            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->withErrors(['permissions' => $message]);
        }

        $role->syncPermissions($submittedPermissions);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث صلاحيات الدور بنجاح',
                'permissions_count' => $role->permissions()->count(),
            ]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'تم تحديث صلاحيات الدور بنجاح');
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

    private function getAssignableRoles(User $user): array
    {
        if ($user->hasRole('admin')) {
            return [
                'payroll-manager',
                'employee-manager',
                'data-entry',
                'viewer',
            ];
        }

        if ($user->hasRole('payroll-manager')) {
            return [
                'employee-manager',
                'data-entry',
                'viewer',
            ];
        }

        if ($user->hasRole('employee-manager')) {
            return [
                'data-entry',
            ];
        }

        return [];
    }

    private function canManageUser(User $actor, User $target): bool
    {
        if ($actor->hasRole('admin')) {
            return true;
        }

        if ($target->hasRole('admin')) {
            return false;
        }

        $allowedRoles = $this->getAssignableRoles($actor);
        if (empty($allowedRoles)) {
            return false;
        }

        $targetRoles = $target->roles->pluck('name')->toArray();
        if (empty($targetRoles)) {
            return true;
        }

        $disallowed = array_diff($targetRoles, $allowedRoles);
        return empty($disallowed);
    }

    private function getRolesAreaAccessPermissions(): array
    {
        return ['manage-roles', 'manage-users'];
    }

    private function isEditingOwnRole(User $actor, Role $role): bool
    {
        return $actor->roles->contains('id', $role->id);
    }

    private function hasAlternativeRoleWithRolesAreaAccess(User $actor, Role $role): bool
    {
        $rolesAreaPermissions = $this->getRolesAreaAccessPermissions();

        return $actor->roles()
            ->where('roles.id', '!=', $role->id)
            ->whereHas('permissions', function ($query) use ($rolesAreaPermissions) {
                $query->whereIn('name', $rolesAreaPermissions);
            })
            ->exists();
    }

    private function wouldLoseRolesAreaAccess(Role $role, array $submittedPermissions): bool
    {
        $actor = Auth::user();
        if (!$actor instanceof User) {
            return false;
        }

        if (!$this->isEditingOwnRole($actor, $role)) {
            return false;
        }

        if ($this->hasAlternativeRoleWithRolesAreaAccess($actor, $role)) {
            return false;
        }

        $rolesAreaPermissions = $this->getRolesAreaAccessPermissions();
        $submittedHasRolesAreaAccess = count(array_intersect($submittedPermissions, $rolesAreaPermissions)) > 0;

        return !$submittedHasRolesAreaAccess;
    }

    private function getLockedOwnRolesAccessPermissions(Role $role): array
    {
        $actor = Auth::user();
        if (!$actor instanceof User) {
            return [];
        }

        if (!$this->isEditingOwnRole($actor, $role)) {
            return [];
        }

        if ($this->hasAlternativeRoleWithRolesAreaAccess($actor, $role)) {
            return [];
        }

        $rolesAreaPermissions = $this->getRolesAreaAccessPermissions();
        $currentRolePermissions = $role->permissions->pluck('name')->toArray();
        $presentRolesAreaPermissions = array_values(array_intersect($rolesAreaPermissions, $currentRolePermissions));

        // If this role has only one access permission for roles area, keep it locked
        // so the actor doesn't lose access immediately.
        return count($presentRolesAreaPermissions) <= 1 ? $presentRolesAreaPermissions : [];
    }

    private function ensureConfiguredPermissionsExist(): void
    {
        foreach (config('permissions.permissions', []) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }
    }
}
