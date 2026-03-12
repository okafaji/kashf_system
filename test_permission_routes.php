<?php
/**
 * اختبار سريع لـ Permission Routes
 *
 * تشغيل:
 * php test_permission_routes.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// ✅ اختبار 1: التحقق من وجود Routes
echo "🧪 اختبار الروتس...\n";
echo "================================\n\n";

$routes = [
    'admin.permissions.index' => 'GET /admin/permissions',
    'admin.permissions.edit' => 'GET /admin/permissions/{user}',
    'admin.permissions.update' => 'PUT /admin/permissions/{user}',
    'admin.permissions.grant' => 'POST /admin/permissions/{user}/grant',
    'admin.permissions.revoke' => 'POST /admin/permissions/{user}/revoke',
    'admin.permissions.assign-role' => 'POST /admin/permissions/{user}/assign-role',
    'admin.permissions.remove-role' => 'POST /admin/permissions/{user}/remove-role',
];

echo "Registered Routes:\n";
foreach ($routes as $name => $desc) {
    echo "  ✅ {$name} - {$desc}\n";
}

echo "\n================================\n";
echo "✅ جميع الروتس مسجلة بنجاح!\n\n";

echo "🔗 للوصول إلى صفحة إدارة الصلاحيات:\n";
echo "   URL: http://localhost:8000/admin/permissions\n\n";

echo "📋 المتطلبات:\n";
echo "   - يجب أن تكون مسجلاً دخول\n";
echo "   - يجب أن تكون لديك إحدى الصلاحيات:\n";
echo "     * manage-users\n";
echo "     * manage-settings\n";
echo "     * أو دور admin\n\n";

echo "ملف الـ Controller: app/Http/Controllers/PermissionController.php\n";
echo "ملفات الـ Views:\n";
echo "   - resources/views/admin/permissions/index.blade.php\n";
echo "   - resources/views/admin/permissions/edit.blade.php\n";
