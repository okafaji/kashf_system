<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserStatsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MissionTypeController;
use App\Http\Controllers\PayrollAuditLogController;
use App\Http\Controllers\SystemHealthController;
use App\Http\Controllers\PermissionController;
use App\Models\City;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {

    // لوحة التحكم وإحصائياتها
    Route::middleware(['permission:access-dashboard'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // APIs للفيلترات الديناميكية
        Route::get('/api/dashboard/stats', [DashboardController::class, 'getStatsByFilter']);
        Route::get('/api/dashboard/years', [DashboardController::class, 'getAvailableYears']);
        Route::get('/api/dashboard/months', [DashboardController::class, 'getAvailableMonths']);
        Route::get('/api/dashboard/days', [DashboardController::class, 'getAvailableDays']);

        // APIs للإحصائيات الخاصة بالمستخدم الحالي (احصائيات الفريق)
        Route::get('/api/user-stats', [UserStatsController::class, 'getUserStats']);
        Route::get('/api/user-team-members', [UserStatsController::class, 'getTeamMembers']);
        Route::get('/api/user-years', [UserStatsController::class, 'getUserYears']);
        Route::get('/api/user-months', [UserStatsController::class, 'getUserMonths']);
        Route::get('/api/user-days', [UserStatsController::class, 'getUserDays']);
    });

    // الملف الشخصي
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- إدارة المنتسبين (Employees) ---
    Route::middleware(['permission:view-employees'])->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/search-live', [EmployeeController::class, 'searchLive'])->name('employees.search.live');
        Route::get('/api/employees/search', [EmployeeController::class, 'getEmployeesAjax'])->name('api.employees.search');
    });

    Route::middleware(['permission:import-employees'])->group(function () {
        Route::match(['get', 'post'], '/employees/sync', [EmployeeController::class, 'syncFromNetwork'])->name('employees.sync');
    });

    Route::get('/api/employees/{id}', [PayrollController::class, 'getEmployeeById'])->name('api.employees.show')
        ->middleware('permission:view-employees');

    // --- روابط الكشوفات (Payrolls) ---
    Route::middleware(['permission:view-payrolls'])->group(function () {
        Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::get('/payrolls/view/{kashf_no}', [PayrollController::class, 'show'])->name('payrolls.show');
        Route::get('/payrolls/name-suggest', [PayrollController::class, 'nameSuggestions'])->name('payrolls.name_suggest');
        Route::get('/payrolls/stats', [PayrollController::class, 'statsByName'])->name('payrolls.stats');
        Route::get('/payrolls/archive', [PayrollController::class, 'archive'])->name('payrolls.archive');
    });

    Route::middleware(['permission:view-payroll-audit-log'])->group(function () {
        Route::get('/payrolls/audit', [PayrollAuditLogController::class, 'index'])->name('payrolls.audit');
    });

    Route::middleware(['permission:create-payrolls'])->group(function () {
        Route::get('/payrolls/create', [PayrollController::class, 'create'])->name('payrolls.create');
        Route::post('/payrolls/store-multiple', [PayrollController::class, 'store_multiple'])->name('payrolls.store_multiple');
        Route::post('/payrolls', [PayrollController::class, 'store'])->name('payrolls.store');
        Route::post('/payrolls/import-preview', [PayrollController::class, 'importPreview']);
        Route::post('/api/check-duplicates', [PayrollController::class, 'checkDuplicates']);
        Route::get('/payrolls/download-template', [PayrollController::class, 'downloadTemplate'])->name('payrolls.download-template');
        Route::get('/api/mission-rate', [PayrollController::class, 'getMissionRate'])->name('payrolls.mission_rate');
    });

    Route::middleware(['permission:edit-payrolls'])->group(function () {
        Route::get('/payrolls/{id}/edit', [PayrollController::class, 'edit'])->name('payrolls.edit');
        Route::put('/payrolls/{id}', [PayrollController::class, 'update'])->name('payrolls.update');
        Route::post('/payrolls/{kashf_no}/add-employee', [PayrollController::class, 'addEmployee'])->name('payrolls.add_employee');
    });

    Route::middleware(['permission:delete-payrolls'])->group(function () {
        Route::delete('/payrolls/{id}', [PayrollController::class, 'destroy'])->name('payrolls.destroy');
    });

    Route::middleware(['permission:print-payrolls'])->group(function () {
        Route::get('/payrolls/print-multiple', [PayrollController::class, 'printMultiple'])->name('payrolls.print_multiple');
        Route::post('/payrolls/confirm-print', [PayrollController::class, 'confirmPrint'])->name('payrolls.confirm_print');
        Route::get('/payrolls/{id}/print', [PayrollController::class, 'print'])->name('payrolls.print');
    });

    Route::middleware(['permission:view-system-health-page'])->group(function () {
        Route::get('/system/health', [SystemHealthController::class, 'index'])->name('system.health');
    });

    Route::middleware(['permission:view-system-health-page', 'permission:manage-settings'])->group(function () {
        Route::post('/system/health/run-audit-backfill', [SystemHealthController::class, 'runAuditBackfill'])
            ->name('system.health.audit_backfill');
    });

    // --- التواقيع ---
    Route::middleware(['permission:manage-signatures'])->group(function () {
        Route::get('/settings/signatures', [PayrollController::class, 'editSignatures'])->name('signatures.edit_all');
        Route::post('/settings/signatures', [PayrollController::class, 'updateSignatures'])->name('signatures.update_all');
        Route::get('/signatures', [SignatureController::class, 'index'])->name('signatures.index');
        Route::get('/signatures/create', [SignatureController::class, 'create'])->name('signatures.create');
        Route::post('/signatures', [SignatureController::class, 'store'])->name('signatures.store');
        Route::get('/signatures/{id}/edit', [SignatureController::class, 'edit'])->name('signatures.edit');
        Route::put('/signatures/{id}', [SignatureController::class, 'update'])->name('signatures.update');
        Route::delete('/signatures/{id}', [SignatureController::class, 'destroy'])->name('signatures.destroy');
        Route::post('/signatures/{id}/toggle-active', [SignatureController::class, 'toggleActive'])->name('signatures.toggleActive');
    });

    // --- إدارة المستخدمين والصلاحيات ---
    Route::middleware(['permission:access-admin-dashboard'])->group(function () {
        Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.dashboard');
    });

    Route::middleware(['permission:manage-users'])->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update_role');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware(['permission:manage-roles|manage-users'])->group(function () {
        Route::get('/roles', [UserManagementController::class, 'rolesIndex'])->name('roles.index');
        Route::get('/roles/{role}/edit', [UserManagementController::class, 'rolesEdit'])->name('roles.edit');
        Route::put('/roles/{role}', [UserManagementController::class, 'rolesUpdate'])->name('roles.update');
    });

    // --- إدارة صلاحيات المستخدمين ---
    Route::middleware(['permission:manage-users|manage-settings'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/{user}', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('/permissions/{user}', [PermissionController::class, 'update'])->name('permissions.update');

        // AJAX endpoints للعمليات السريعة
        Route::post('/permissions/{user}/grant', [PermissionController::class, 'grantPermission'])->name('permissions.grant');
        Route::post('/permissions/{user}/revoke', [PermissionController::class, 'revokePermission'])->name('permissions.revoke');
        Route::post('/permissions/{user}/assign-role', [PermissionController::class, 'assignRole'])->name('permissions.assign-role');
        Route::post('/permissions/{user}/remove-role', [PermissionController::class, 'removeRole'])->name('permissions.remove-role');
    });

    Route::get('/api/cities', function (Request $request) {
        return City::where('governorate_id', $request->governorate_id)->get();
    })->name('api.cities')
        ->middleware('permission:create-payrolls|edit-payrolls|view-payrolls|manage-settings|manage-governorates|manage-cities');

    // --- النسخ الاحتياطية ---
    Route::middleware(['permission:manage-backups'])->group(function () {
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/create', [BackupController::class, 'createBackup'])->name('backups.create');
        Route::post('/backups/database', [BackupController::class, 'backupDatabaseOnly'])->name('backups.database');
        Route::post('/backups/code', [BackupController::class, 'backupCodeOnly'])->name('backups.code');
        Route::get('/backups/list', [BackupController::class, 'listBackups'])->name('backups.list');
        Route::get('/backups/download/{timestamp}', [BackupController::class, 'downloadBackup'])->name('backups.download');
        Route::post('/backups/open-folder/{timestamp}', [BackupController::class, 'openBackupFolder'])->name('backups.open-folder');
        Route::delete('/backups/delete/{timestamp}', [BackupController::class, 'deleteBackup'])->name('backups.delete');
    });

    // --- إدارة الأقسام ---
    Route::middleware(['permission:' . implode('|', config('permissions.page_permissions.departments.access', ['access-departments-page']))])->group(function () {
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/list', [DepartmentController::class, 'list'])->name('departments.list');
        Route::get('/departments/all', [DepartmentController::class, 'all'])->name('departments.all');
    });

    Route::middleware(['permission:' . implode('|', config('permissions.page_permissions.departments.manage', ['manage-settings', 'manage-departments']))])->group(function () {
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    });

    // --- إدارة أنواع الإيفادات (خارج البلد والمحافظات) ---
    Route::middleware(['permission:' . implode('|', config('permissions.page_permissions.mission_types.access', ['access-mission-types-page']))])->group(function () {
        Route::get('/settings/mission-types', [MissionTypeController::class, 'index'])->name('settings.mission-types');
        Route::get('/mission-types', [MissionTypeController::class, 'index'])->name('mission-types.index');
        Route::get('/mission-types/stats', [MissionTypeController::class, 'stats'])->name('mission-types.stats');
    });

    Route::middleware(['permission:' . implode('|', config('permissions.page_permissions.mission_types.manage', ['manage-settings', 'manage-mission-types']))])->group(function () {
        Route::get('/mission-types/create', [MissionTypeController::class, 'create'])->name('mission-types.create');
        Route::post('/mission-types', [MissionTypeController::class, 'store'])->name('mission-types.store');
        Route::get('/mission-types/{missionType}/edit', [MissionTypeController::class, 'edit'])->name('mission-types.edit');
        Route::put('/mission-types/{missionType}', [MissionTypeController::class, 'update'])->name('mission-types.update');
        Route::delete('/mission-types/{missionType}', [MissionTypeController::class, 'destroy'])->name('mission-types.destroy');
        Route::post('/mission-types/bulk-update', [MissionTypeController::class, 'bulkUpdate'])->name('mission-types.bulk-update');
    });

    // --- إدارة المحافظات والمدن ---
    Route::middleware(['permission:' . implode('|', config('permissions.page_permissions.governorates.access', ['access-governorates-page']))])->group(function () {
        Route::get('/governorates', [GovernorateController::class, 'index'])->name('governorates.index');
        Route::get('/governorates/list', [GovernorateController::class, 'list'])->name('governorates.list');
        Route::get('/governorates/all', [GovernorateController::class, 'all'])->name('governorates.all');
        Route::get('/cities/by-governorate/{id}', [CityController::class, 'byGovernorate'])->name('cities.by-governorate');
    });

    Route::middleware(['permission:' . implode('|', config('permissions.page_permissions.governorates.manage', ['manage-settings', 'manage-governorates']))])->group(function () {
        Route::post('/governorates', [GovernorateController::class, 'store'])->name('governorates.store');
        Route::put('/governorates/{id}', [GovernorateController::class, 'update'])->name('governorates.update');
        Route::delete('/governorates/{id}', [GovernorateController::class, 'destroy'])->name('governorates.destroy');
    });

    Route::middleware(['permission:' . implode('|', config('permissions.page_permissions.cities.manage', ['manage-settings', 'manage-governorates', 'manage-cities']))])->group(function () {
        Route::post('/cities', [CityController::class, 'store'])->name('cities.store');
        Route::put('/cities/{id}', [CityController::class, 'update'])->name('cities.update');
        Route::delete('/cities/{id}', [CityController::class, 'destroy'])->name('cities.destroy');
    });
});

require __DIR__.'/auth.php';
