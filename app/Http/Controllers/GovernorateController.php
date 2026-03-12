<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GovernorateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $governorateAccessPermissions = config('permissions.page_permissions.governorates.access', ['access-governorates-page']);
        $governorateManagePermissions = config('permissions.page_permissions.governorates.manage', ['manage-settings', 'manage-governorates']);

        $this->middleware('permission:' . implode('|', $governorateAccessPermissions))->only(['index', 'list', 'all']);
        $this->middleware('permission:' . implode('|', $governorateManagePermissions))->only(['store', 'update', 'destroy']);
    }

    /**
     * عرض صفحة إدارة المحافظات والمدن
     */
    public function index()
    {
        return view('governorates.index');
    }

    /**
     * جلب قائمة المحافظات مع المدن
     */
    public function list()
    {
        try {
            $governorates = Governorate::with('cities')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'governorates' => $governorates
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch governorates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل جلب المحافظات'
            ], 500);
        }
    }

    /**
     * جلب جميع المحافظات (بدون مدن)
     */
    public function all()
    {
        try {
            $governorates = Governorate::orderBy('name')->get();

            return response()->json([
                'success' => true,
                'governorates' => $governorates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل جلب المحافظات'
            ], 500);
        }
    }

    /**
     * إضافة محافظة جديدة
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:governorates,name'
            ], [
                'name.required' => 'اسم المحافظة مطلوب',
                'name.unique' => 'اسم المحافظة موجود مسبقاً'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $governorate = Governorate::create([
                'name' => trim($request->name)
            ]);

            Log::info('Governorate created', [
                'id' => $governorate->id,
                'name' => $governorate->name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المحافظة بنجاح',
                'governorate' => $governorate->load('cities')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create governorate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إضافة المحافظة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تعديل محافظة
     */
    public function update(Request $request, $id)
    {
        try {
            $governorate = Governorate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:governorates,name,' . $id
            ], [
                'name.required' => 'اسم المحافظة مطلوب',
                'name.unique' => 'اسم المحافظة موجود مسبقاً'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $governorate->update([
                'name' => trim($request->name)
            ]);

            Log::info('Governorate updated', [
                'id' => $governorate->id,
                'name' => $governorate->name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل المحافظة بنجاح',
                'governorate' => $governorate->load('cities')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update governorate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تعديل المحافظة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف محافظة
     */
    public function destroy($id)
    {
        try {
            $governorate = Governorate::findOrFail($id);

            // التحقق من وجود مدن تابعة
            $citiesCount = $governorate->cities()->count();
            if ($citiesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "لا يمكن حذف المحافظة لوجود {$citiesCount} مدينة تابعة لها"
                ], 422);
            }

            $name = $governorate->name;
            $governorate->delete();

            Log::info('Governorate deleted', [
                'id' => $id,
                'name' => $name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المحافظة بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete governorate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف المحافظة: ' . $e->getMessage()
            ], 500);
        }
    }
}

