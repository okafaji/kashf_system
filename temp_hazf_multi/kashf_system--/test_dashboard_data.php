<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = User::first();
Auth::setUser($user);

// Test the index method
$controller = new DashboardController();

// We need to pass a view into this, so let's just check what data it prepares
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('index');

// Call the method and capture its output
ob_start();
$view = $controller->index();
ob_end_clean();

// The index() method returns a view, so we need to check the data that's passed to it
// Let's check by calling buildQueryByRole directly

$queryMethod = $reflection->getMethod('buildQueryByRole');
$queryMethod->setAccessible(true);

$query = $queryMethod->invoke($controller, $user);

echo "=== DASHBOARD DATA ===\n";
echo "User: " . $user->name . "\n";
echo "User Roles: " . json_encode($user->roles->pluck('name')->toArray()) . "\n\n";

echo "Payroll Counts:\n";
echo "- Total (all): " . $query->count() . "\n";
echo "- Year 2026: " . (clone $query)->whereYear('created_at', 2026)->count() . "\n";
echo "- Year 2025: " . (clone $query)->whereYear('created_at', 2025)->count() . "\n";

echo "\nPayroll Amounts:\n";
echo "- Total: " . (clone $query)->sum('total_amount') . "\n";
echo "- Year 2026: " . (clone $query)->whereYear('created_at', 2026)->sum('total_amount') . "\n";

echo "\nRecent Payrolls:\n";
$recent = (clone $query)->orderBy('created_at', 'desc')->take(3)->get();
foreach ($recent as $p) {
    echo "- " . $p->name . " | Amount: " . $p->total_amount . " | Date: " . $p->created_at . "\n";
}
