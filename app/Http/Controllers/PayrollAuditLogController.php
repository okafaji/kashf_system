<?php

namespace App\Http\Controllers;

use App\Models\PayrollAuditLog;
use App\Models\Payroll;
use App\Models\City;
use App\Models\Department;
use App\Models\MissionType;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'action' => trim((string) $request->query('action', '')),
            'user_id' => trim((string) $request->query('user_id', '')),
            'from_date' => trim((string) $request->query('from_date', '')),
            'to_date' => trim((string) $request->query('to_date', '')),
            'kashf_no' => trim((string) $request->query('kashf_no', '')),
        ];

        $query = PayrollAuditLog::query()->orderByDesc('id');

        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }

        if ($filters['user_id'] !== '' && ctype_digit($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if ($filters['kashf_no'] !== '') {
            $query->where('kashf_no', 'LIKE', '%' . $filters['kashf_no'] . '%');
        }

        if ($filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if ($filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $logs = $query->paginate(25)->appends($request->query());

        $actionOptions = PayrollAuditLog::query()->distinct()->orderBy('action')->pluck('action');
        $userOptions = User::query()->whereIn('id', PayrollAuditLog::query()->whereNotNull('user_id')->select('user_id')->distinct())->orderBy('name')->get(['id', 'name']);
        $cityMap = City::query()->pluck('name', 'id')->toArray();
        $missionTypeMap = MissionType::query()->pluck('name', 'id')->toArray();
        $departmentMap = Department::query()->pluck('name', 'id')->toArray();
        $statusLabels = Payroll::statusLabels();

        $actionLabels = [
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            'print_confirmed' => 'تأكيد الطباعة',
        ];

        return view('payrolls.audit', [
            'logs' => $logs,
            'filters' => $filters,
            'actionOptions' => $actionOptions,
            'userOptions' => $userOptions,
            'cityMap' => $cityMap,
            'missionTypeMap' => $missionTypeMap,
            'departmentMap' => $departmentMap,
            'statusLabels' => $statusLabels,
            'actionLabels' => $actionLabels,
        ]);
    }
}
