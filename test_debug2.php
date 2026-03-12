<?php

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a fake admin user manually
$user = User::first();

echo "=== DEBUG INFO ===\n";
echo "Total Users: " . User::count() . "\n";
echo "Total Payrolls: " . Payroll::count() . "\n";

if ($user) {
    echo "\nFirst User: " . $user->name . "\n";
    echo "User ID: " . $user->id . "\n";
    echo "Department ID: " . ($user->department_id ?: "NONE") . "\n";
    echo "User Roles: " . json_encode($user->roles->pluck('name')->toArray()) . "\n";

    // Test the buildQueryByRole method
    $controller = new DashboardController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('buildQueryByRole');
    $method->setAccessible(true);

    $query = $method->invoke($controller, $user);
    $count = $query->count();

    echo "\nFiltered Payroll Count (via buildQueryByRole): " . $count . "\n";

    // Check role detection
    echo "\nRole Detection:\n";
    echo "- hasRole('admin'): " . ($user->hasRole('admin') ? "YES" : "NO") . "\n";
    echo "- hasRole('payroll-manager'): " . ($user->hasRole('payroll-manager') ? "YES" : "NO") . "\n";
    echo "- hasRole(['admin', 'رئيس قسم']): " . ($user->hasRole(['admin', 'رئيس قسم']) ? "YES" : "NO") . "\n";
} else {
    echo "No user found!\n";
}
