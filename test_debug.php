<?php

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Set up a fake request with a user
$user = User::first();

echo "=== DEBUG INFO ===\n";
echo "Total Users: " . User::count() . "\n";
echo "Total Payrolls: " . Payroll::count() . "\n";

if ($user) {
    echo "\nFirst User: " . $user->name . "\n";
    echo "User ID: " . $user->id . "\n";
    echo "Department ID: " . $user->department_id . "\n";
    echo "User Roles: " . json_encode($user->roles->pluck('name')->toArray()) . "\n";

    // Test the query
    if ($user->hasRole('رئيس قسم')) {
        echo "User is رئيس قسم\n";
        $count = Payroll::count();
    } elseif ($user->hasRole('مسؤول شعبة')) {
        echo "User is مسؤول شعبة\n";
        $count = Payroll::where('created_by_department_id', $user->department_id)
            ->orWhereIn('created_by_department_id', function($q) use ($user) {
                $q->select('id')->from('departments')->where('parent_id', $user->department_id);
            })->count();
    } elseif ($user->hasRole('مسؤول وحدة')) {
        echo "User is مسؤول وحدة\n";
        $count = Payroll::where('created_by_department_id', $user->department_id)->count();
    } else {
        echo "User is regular employee\n";
        $count = Payroll::where('user_id', $user->id)->count();
    }

    echo "Filtered Payroll Count: " . $count . "\n";
} else {
    echo "No user found!\n";
}
