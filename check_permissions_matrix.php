<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

/**
 * Extracts the permission expression from route middleware.
 * Supports both alias style (permission:foo|bar) and class style.
 */
function extractPermissionExpression(array $middlewares): ?string
{
    foreach ($middlewares as $middleware) {
        if (str_starts_with($middleware, 'permission:')) {
            return substr($middleware, strlen('permission:'));
        }

        $needle = 'PermissionMiddleware:';
        $pos = strpos($middleware, $needle);
        if ($pos !== false) {
            return substr($middleware, $pos + strlen($needle));
        }
    }

    return null;
}

function routeHasAuth(array $middlewares): bool
{
    foreach ($middlewares as $middleware) {
        if ($middleware === 'auth' || str_contains($middleware, 'Authenticate')) {
            return true;
        }
    }

    return false;
}

$criticalRouteNames = [
    'dashboard',
    'payrolls.create',
    'payrolls.index',
    'employees.index',
    'signatures.index',
    'departments.index',
    'governorates.index',
    'settings.mission-types',
    'backups.index',
    'users.index',
    'roles.index',
    'admin.dashboard',
];

$allRoutes = collect(Route::getRoutes()->getRoutes());

$criticalRoutes = [];
foreach ($criticalRouteNames as $name) {
    $route = Route::getRoutes()->getByName($name);
    if (!$route) {
        continue;
    }

    $middlewares = $route->gatherMiddleware();
    $criticalRoutes[] = [
        'name' => $name,
        'uri' => $route->uri(),
        'permission_expression' => extractPermissionExpression($middlewares),
        'middlewares' => $middlewares,
    ];
}

$protectedRoutes = $allRoutes
    ->map(function ($route) {
        $middlewares = $route->gatherMiddleware();
        return [
            'name' => $route->getName() ?: '-',
            'uri' => $route->uri(),
            'methods' => implode('|', $route->methods()),
            'auth' => routeHasAuth($middlewares),
            'permission_expression' => extractPermissionExpression($middlewares),
        ];
    })
    ->filter(fn ($r) => $r['auth'] && !empty($r['permission_expression']))
    ->values();

$roles = Role::with('permissions')->orderBy('name')->get();

$reportLines = [];
$reportLines[] = '# Permission Matrix Report';
$reportLines[] = '';
$reportLines[] = 'Generated at: ' . now()->format('Y-m-d H:i:s');
$reportLines[] = '';
$reportLines[] = '## Roles And Permissions';
$reportLines[] = '';

foreach ($roles as $role) {
    $permNames = $role->permissions->pluck('name')->sort()->values()->all();
    $reportLines[] = '- **' . $role->name . '**: ' . (empty($permNames) ? '(no permissions)' : implode(', ', $permNames));
}

$reportLines[] = '';
$reportLines[] = '## Critical Routes Matrix';
$reportLines[] = '';
$reportLines[] = '| Role | ' . implode(' | ', array_map(fn ($r) => $r['name'], $criticalRoutes)) . ' |';
$reportLines[] = '|---|' . str_repeat('---|', count($criticalRoutes));

foreach ($roles as $role) {
    $cells = [];
    foreach ($criticalRoutes as $routeInfo) {
        $expr = $routeInfo['permission_expression'];

        if ($expr === null || $expr === '') {
            $cells[] = 'AUTH';
            continue;
        }

        $required = array_values(array_filter(array_map('trim', explode('|', $expr))));
        $allowed = false;
        foreach ($required as $perm) {
            if ($role->hasPermissionTo($perm)) {
                $allowed = true;
                break;
            }
        }

        $cells[] = $allowed ? 'YES' : 'NO';
    }

    $reportLines[] = '| ' . $role->name . ' | ' . implode(' | ', $cells) . ' |';
}

$reportLines[] = '';
$reportLines[] = '## Coverage Summary';
$reportLines[] = '';
$reportLines[] = 'Total protected routes (auth + permission): **' . $protectedRoutes->count() . '**';
$reportLines[] = '';

foreach ($roles as $role) {
    $allowedCount = 0;
    $denied = [];

    foreach ($protectedRoutes as $routeInfo) {
        $required = array_values(array_filter(array_map('trim', explode('|', (string) $routeInfo['permission_expression']))));
        $allowed = false;

        foreach ($required as $perm) {
            if ($role->hasPermissionTo($perm)) {
                $allowed = true;
                break;
            }
        }

        if ($allowed) {
            $allowedCount++;
        } else {
            $denied[] = $routeInfo['name'] . ' (`' . $routeInfo['uri'] . '`)';
        }
    }

    $reportLines[] = '### ' . $role->name;
    $reportLines[] = '- Allowed routes: **' . $allowedCount . '**';
    $reportLines[] = '- Denied routes: **' . count($denied) . '**';

    if (!empty($denied)) {
        $sample = array_slice($denied, 0, 12);
        $reportLines[] = '- Sample denied routes: ' . implode(', ', $sample);
    }

    $reportLines[] = '';
}

$reportPath = __DIR__ . '/PERMISSIONS_MATRIX_REPORT.md';
file_put_contents($reportPath, implode(PHP_EOL, $reportLines));

echo 'Report generated: ' . $reportPath . PHP_EOL;
echo 'Roles audited: ' . $roles->count() . PHP_EOL;
echo 'Protected routes audited: ' . $protectedRoutes->count() . PHP_EOL;
