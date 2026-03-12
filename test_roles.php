<?php

use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Payroll;

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ALL ROLES ===\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- " . $role->name . "\n";
}

echo "\n=== USERS WITH THEIR ROLES ===\n";
$users = User::with('roles')->limit(5)->get();
foreach ($users as $user) {
    echo "\nUser: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "Roles: " . json_encode($user->roles->pluck('name')->toArray()) . "\n";
    echo "Department: " . ($user->department_id ?: "NONE") . "\n";

    // Check payroll count for this user
    $count = Payroll::where('user_id', $user->id)->count();
    echo "Payrolls by user: " . $count . "\n";
}

echo "\n=== CHECKING PAYROLL TABLE ===\n";
$payroll = Payroll::first();
if ($payroll) {
    echo "Sample Payroll:\n";
    echo "- user_id: " . $payroll->user_id . "\n";
    echo "- created_by_department_id: " . $payroll->created_by_department_id . "\n";
    echo "- created_at: " . $payroll->created_at . "\n";
}
