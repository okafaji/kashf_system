<?php

namespace App\Http\Controllers;

use App\Models\MissionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getMissionTypesJson($request);
        }

        return view('mission-types.index');
    }

    private function getMissionTypesJson(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $query = MissionType::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('responsibility_level', 'like', "%{$search}%");
            });
        }

        $total = $query->count();

        $data = $query->orderBy('name')
            ->orderBy('responsibility_level')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $countByName = MissionType::selectRaw('name, COUNT(*) as count')
            ->groupBy('name')
            ->pluck('count', 'name');

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'count_by_name' => $countByName,
            'total_names' => MissionType::distinct('name')->count('name'),
            'total_levels' => MissionType::distinct('responsibility_level')->count('responsibility_level'),
        ]);
    }

    public function create()
    {
        $missionNames = MissionType::query()->distinct()->orderBy('name')->pluck('name');
        $responsibilityLevels = MissionType::query()->distinct()->orderBy('responsibility_level')->pluck('responsibility_level');

        return view('mission-types.create', compact('missionNames', 'responsibilityLevels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'responsibility_level' => 'required|string|max:255',
            'daily_rate' => 'required|numeric|min:0|max:999999.99',
        ], [
            'name.required' => 'نوع الإيفاد مطلوب',
            'responsibility_level.required' => 'مستوى المسؤولية مطلوب',
            'daily_rate.required' => 'المبلغ اليومي مطلوب',
            'daily_rate.numeric' => 'يجب أن يكون المبلغ رقما',
        ]);

        $exists = MissionType::where('name', $validated['name'])
            ->where('responsibility_level', $validated['responsibility_level'])
            ->exists();

        if ($exists) {
            $message = 'هذا المجموع (نوع الإيفاد + مستوى المسؤولية) موجود بالفعل';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withInput()->withErrors(['name' => $message]);
        }

        $missionType = MissionType::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة نوع الإيفاد بنجاح',
                'data' => $missionType,
            ]);
        }

        return redirect()->route('mission-types.index')->with('success', 'تم إضافة نوع الإيفاد بنجاح');
    }

    public function edit(Request $request, MissionType $missionType)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $missionType,
            ]);
        }

        return view('mission-types.edit', compact('missionType'));
    }

    public function update(Request $request, MissionType $missionType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'responsibility_level' => 'required|string|max:255',
            'daily_rate' => 'required|numeric|min:0|max:999999.99',
        ], [
            'name.required' => 'نوع الإيفاد مطلوب',
            'responsibility_level.required' => 'مستوى المسؤولية مطلوب',
            'daily_rate.required' => 'المبلغ اليومي مطلوب',
        ]);

        $exists = MissionType::where('name', $validated['name'])
            ->where('responsibility_level', $validated['responsibility_level'])
            ->where('id', '!=', $missionType->id)
            ->exists();

        if ($exists) {
            $message = 'هذا المجموع موجود بالفعل';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withInput()->withErrors(['name' => $message]);
        }

        $missionType->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث نوع الإيفاد بنجاح',
                'data' => $missionType,
            ]);
        }

        return redirect()->route('mission-types.index')->with('success', 'تم تحديث نوع الإيفاد بنجاح');
    }

    public function destroy(Request $request, MissionType $missionType)
    {
        $payrolls = DB::table('payrolls')
            ->where('mission_type_id', $missionType->id)
            ->count();

        if ($payrolls > 0) {
            $message = "لا يمكن حذف هذا النوع - مستخدم في {$payrolls} كشف";
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $missionType->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف نوع الإيفاد بنجاح',
            ]);
        }

        return redirect()->route('mission-types.index')->with('success', 'تم حذف نوع الإيفاد بنجاح');
    }

    public function stats()
    {
        $stats = DB::table('mission_types as mt')
            ->leftJoin('payrolls as p', 'mt.id', '=', 'p.mission_type_id')
            ->selectRaw('
                mt.name,
                mt.responsibility_level,
                COUNT(DISTINCT p.id) as usage_count,
                COALESCE(SUM(p.daily_allowance), 0) as total_amount
            ')
            ->groupBy('mt.name', 'mt.responsibility_level')
            ->orderBy('mt.name')
            ->orderBy('mt.responsibility_level')
            ->paginate(50);

        return view('mission-types.stats', compact('stats'));
    }

    public function bulkUpdate(Request $request)
    {
        $updates = $request->input('updates', []);

        if (!is_array($updates) || empty($updates)) {
            $message = 'لا توجد بيانات للتحديث';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        DB::transaction(function () use ($updates) {
            foreach ($updates as $row) {
                if (!isset($row['id'], $row['daily_rate'])) {
                    continue;
                }

                MissionType::where('id', $row['id'])->update([
                    'daily_rate' => (float) $row['daily_rate'],
                ]);
            }
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الأسعار بنجاح',
            ]);
        }

        return redirect()->route('mission-types.index')->with('success', 'تم تحديث الأسعار بنجاح');
    }
}
