<x-app-layout>
    @php
        $permissionGroups = config('permissions.permission_groups', []);
        $permissionLabels = collect($permissionGroups)
            ->flatMap(fn($group) => $group['permissions'] ?? [])
            ->all();

        $roleLabels = [
            'admin' => 'مدير النظام',
            'payroll-manager' => 'مدير الكشوفات',
            'employee-manager' => 'مدير المنتسبين',
            'data-entry' => 'مدخل بيانات',
            'viewer' => 'مُطّلع',
        ];

        $groupedPermissionNames = collect($permissionGroups)
            ->flatMap(fn($group) => array_keys($group['permissions'] ?? []))
            ->values()
            ->all();
    @endphp

    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center; direction: rtl;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                تحرير صلاحيات: {{ $user->name }}
            </h2>
            <a href="{{ route('admin.permissions.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded transition">
                ← رجوع
            </a>
        </div>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <!-- معلومات المستخدم -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">اسم المستخدم</p>
                        <p class="text-lg font-bold">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">البريد الإلكتروني</p>
                        <p class="text-lg font-bold">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">القسم</p>
                        <p class="text-lg font-bold">
                            {{ $user->department ? $user->department->name : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.permissions.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- الأدوار -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">الأدوار</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($allRoles as $role)
                            <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                                <input type="checkbox"
                                       name="roles[]"
                                       value="{{ $role->id }}"
                                       @checked($user->hasRole($role))
                                       class="w-4 h-4 cursor-pointer">
                                <div>
                                    <p class="font-semibold">🔵 {{ $roleLabels[$role->name] ?? $role->name }}</p>
                                    <p class="text-xs text-gray-600">
                                        {{ $role->permissions_count }} صلاحية
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- الصلاحيات المباشرة -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">الصلاحيات المباشرة</h3>

                    @foreach($permissionGroups as $group)
                        @php
                            $groupPermissionsMap = $group['permissions'] ?? [];
                            $groupPermissions = $allPermissions->filter(fn($permission) => array_key_exists($permission->name, $groupPermissionsMap));
                        @endphp

                        @continue($groupPermissions->isEmpty())

                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-700 mb-3 bg-gray-100 p-2 rounded">
                                📋 {{ $group['label'] ?? 'صلاحيات أخرى' }}
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($groupPermissions as $permission)
                                    <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->id }}"
                                               @checked($user->hasPermissionTo($permission))
                                               class="w-4 h-4 cursor-pointer">
                                        <span class="font-semibold text-sm">✓ {{ $permissionLabels[$permission->name] ?? $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @php
                        $otherPermissions = $allPermissions->filter(fn($permission) => !in_array($permission->name, $groupedPermissionNames, true));
                    @endphp

                    @if($otherPermissions->isNotEmpty())
                        <div class="mb-2">
                            <h4 class="font-semibold text-gray-700 mb-3 bg-gray-100 p-2 rounded">📋 صلاحيات أخرى</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($otherPermissions as $permission)
                                    <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->id }}"
                                               @checked($user->hasPermissionTo($permission))
                                               class="w-4 h-4 cursor-pointer">
                                        <span class="font-semibold text-sm">✓ {{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- أزرار الإجراء -->
                <div class="flex gap-2 justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded transition shadow">
                        💾 حفظ التغييرات
                    </button>
                    <a href="{{ route('admin.permissions.index') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded transition">
                        ✕ إلغاء
                    </a>
                </div>
            </form>

            <!-- الملخص السريع -->
            <div class="bg-blue-50 rounded-lg shadow-sm p-6 mt-6 border-r-4 border-blue-500">
                <h4 class="font-bold mb-3">📋 ملخص الصلاحيات الحالية</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-700 mb-2"><strong>الأدوار:</strong></p>
                        @if($user->roles->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->roles as $role)
                                    <span class="bg-blue-200 text-blue-800 text-xs px-2 py-1 rounded">
                                        🔵 {{ $roleLabels[$role->name] ?? $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500">بدون أدوار</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-700 mb-2"><strong>الصلاحيات المباشرة:</strong></p>
                        @if($user->permissions->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->permissions as $permission)
                                    <span class="bg-green-200 text-green-800 text-xs px-2 py-1 rounded">
                                        ✓ {{ $permissionLabels[$permission->name] ?? $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500">بدون صلاحيات مباشرة</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
