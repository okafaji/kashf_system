<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $cityAccessPermissions = config('permissions.page_permissions.cities.access', ['access-governorates-page']);
        $cityManagePermissions = config('permissions.page_permissions.cities.manage', ['manage-settings', 'manage-governorates', 'manage-cities']);

        $this->middleware('permission:' . implode('|', $cityAccessPermissions))->only(['index', 'byGovernorate']);
        $this->middleware('permission:' . implode('|', $cityManagePermissions))->only(['store', 'update', 'destroy']);
    }

    /**
     * جلب قائمة المدن
     */
    public function index(Request $request)
    {
        if ($request->has('governorate_id')) {
            // نطلب جلب السعر مع الاسم
            return City::where('governorate_id', $request->governorate_id)
                       ->select('id', 'name', 'daily_allowance')
                       ->get();
        }
        return City::all();
    }

    /**
     * جلب مدن محافظة معينة
     */
    public function byGovernorate($governorateId)
    {
        try {
            $cities = City::where('governorate_id', $governorateId)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'cities' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل جلب المدن'
            ], 500);
        }
    }

    /**
     * إضافة مدينة جديدة
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'governorate_id' => 'required|exists:governorates,id',
                'daily_allowance' => 'required|numeric|min:0'
            ], [
                'name.required' => 'اسم المدينة مطلوب',
                'governorate_id.required' => 'المحافظة مطلوبة',
                'governorate_id.exists' => 'المحافظة غير موجودة',
                'daily_allowance.required' => 'بدل الإيفاد مطلوب',
                'daily_allowance.numeric' => 'بدل الإيفاد يجب أن يكون رقماً',
                'daily_allowance.min' => 'بدل الإيفاد يجب أن يكون موجباً'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // التحقق من عدم تكرار اسم المدينة في نفس المحافظة
            $exists = City::where('governorate_id', $request->governorate_id)
                ->where('name', trim($request->name))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'اسم المدينة موجود مسبقاً في هذه المحافظة'
                ], 422);
            }

            $city = City::create([
                'name' => trim($request->name),
                'governorate_id' => $request->governorate_id,
                'daily_allowance' => $request->daily_allowance
            ]);

            Log::info('City created', [
                'id' => $city->id,
                'name' => $city->name,
                'governorate_id' => $city->governorate_id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المدينة بنجاح',
                'city' => $city->load('governorate')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create city: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل إضافة المدينة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تعديل مدينة
     */
    public function update(Request $request, $id)
    {
        try {
            $city = City::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'governorate_id' => 'required|exists:governorates,id',
                'daily_allowance' => 'required|numeric|min:0'
            ], [
                'name.required' => 'اسم المدينة مطلوب',
                'governorate_id.required' => 'المحافظة مطلوبة',
                'governorate_id.exists' => 'المحافظة غير موجودة',
                'daily_allowance.required' => 'بدل الإيفاد مطلوب',
                'daily_allowance.numeric' => 'بدل الإيفاد يجب أن يكون رقماً',
                'daily_allowance.min' => 'بدل الإيفاد يجب أن يكون موجباً'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // التحقق من عدم تكرار اسم المدينة
            $exists = City::where('governorate_id', $request->governorate_id)
                ->where('name', trim($request->name))
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'اسم المدينة موجود مسبقاً في هذه المحافظة'
                ], 422);
            }

            $city->update([
                'name' => trim($request->name),
                'governorate_id' => $request->governorate_id,
                'daily_allowance' => $request->daily_allowance
            ]);

            Log::info('City updated', [
                'id' => $city->id,
                'name' => $city->name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل المدينة بنجاح',
                'city' => $city->load('governorate')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update city: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تعديل المدينة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف مدينة
     */
    public function destroy($id)
    {
        try {
            $city = City::findOrFail($id);

            $name = $city->name;
            $city->delete();

            Log::info('City deleted', [
                'id' => $id,
                'name' => $name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المدينة بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete city: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل حذف المدينة: ' . $e->getMessage()
            ], 500);
        }
    }
}

