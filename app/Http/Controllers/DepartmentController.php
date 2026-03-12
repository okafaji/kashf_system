<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $departmentAccessPermissions = config('permissions.page_permissions.departments.access', ['access-departments-page']);
        $departmentManagePermissions = config('permissions.page_permissions.departments.manage', ['manage-settings', 'manage-departments']);

        $this->middleware('permission:' . implode('|', $departmentAccessPermissions))->only(['index', 'list', 'all']);
        $this->middleware('permission:' . implode('|', $departmentManagePermissions))->only(['store', 'update', 'destroy']);
    }

    /**
     * عرض صفحة إدارة الأقسام
     */
    public function index()
    {
        return view('departments.index');
    }

    /**
     * جلب قائمة الأقسام مع التسلسل الهرمي
     */
    public function list()
    {
        try {
            $departments = Department::with('children', 'parent')
                ->whereNull('parent_id') // جلب الأقسام الرئيسية فقط
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'departments' => $departments
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل جلب الأقسام'
            ], 500);
        }
    }

    /**
     * جلب جميع الأقسام (مسطح)
     */
    public function all()
    {
        try {
            $departments = Department::with('parent')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'departments' => $departments
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch all departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل جلب الأقسام'
            ], 500);
        }
    }

    /**
     * إضافة قسم جديد
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments,name',
                'parent_id' => 'nullable|exists:departments,id'
            ], [
                'name.required' => 'اسم القسم مطلوب',
                'name.unique' => 'اسم القسم موجود مسبقاً',
                'parent_id.exists' => 'القسم الأب غير موجود'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $department = Department::create([
                'name' => trim($request->name),
                'parent_id' => $request->parent_id
            ]);

            Log::info('Department created', [
                'id' => $department->id,
                'name' => $department->name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة القسم بنجاح',
                'department' => $department->load('parent')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create department: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إضافة القسم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تعديل قسم
     */
    public function update(Request $request, $id)
    {
        try {
            $department = Department::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments,name,' . $id,
                'parent_id' => [
                    'nullable',
                    'exists:departments,id',
                    function ($attribute, $value, $fail) use ($id) {
                        // منع القسم من أن يكون أب لنفسه
                        if ($value == $id) {
                            $fail('لا يمكن جعل القسم تابعاً لنفسه');
                        }
                        // منع الحلقات الدائرية
                        if ($value && $this->wouldCreateCircularReference($id, $value)) {
                            $fail('لا يمكن إنشاء تسلسل دائري');
                        }
                    }
                ]
            ], [
                'name.required' => 'اسم القسم مطلوب',
                'name.unique' => 'اسم القسم موجود مسبقاً',
                'parent_id.exists' => 'القسم الأب غير موجود'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $department->update([
                'name' => trim($request->name),
                'parent_id' => $request->parent_id
            ]);

            Log::info('Department updated', [
                'id' => $department->id,
                'name' => $department->name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل القسم بنجاح',
                'department' => $department->load('parent', 'children')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update department: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تعديل القسم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف قسم
     */
    public function destroy($id)
    {
        try {
            $department = Department::findOrFail($id);
            $name = $department->name;

            $childrenMoved = 0;
            $usersUnlinked = 0;
            $payrollsUnlinked = 0;

            DB::transaction(function () use ($department, &$childrenMoved, &$usersUnlinked, &$payrollsUnlinked) {
                $childrenMoved = Department::where('parent_id', $department->id)
                    ->update(['parent_id' => $department->parent_id]);

                $usersUnlinked = User::where('department_id', $department->id)
                    ->update(['department_id' => null]);

                $payrollsUnlinked = Payroll::where('created_by_department_id', $department->id)
                    ->update(['created_by_department_id' => null]);

                $department->delete();
            });

            Log::info('Department deleted', [
                'id' => $id,
                'name' => $name,
                'children_moved' => $childrenMoved,
                'users_unlinked' => $usersUnlinked,
                'payrolls_unlinked' => $payrollsUnlinked,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف القسم بنجاح',
                'meta' => [
                    'children_moved' => $childrenMoved,
                    'users_unlinked' => $usersUnlinked,
                    'payrolls_unlinked' => $payrollsUnlinked,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete department: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف القسم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من إنشاء مرجع دائري
     */
    private function wouldCreateCircularReference($deptId, $newParentId)
    {
        $current = Department::find($newParentId);

        while ($current) {
            if ($current->id == $deptId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }
}
