<x-app-layout>
    @php
        $permissionLabels = collect(config('permissions.permission_groups', []))
            ->flatMap(fn($group) => $group['permissions'] ?? [])
            ->all();

        $roleLabels = [
            'admin' => 'مدير النظام',
            'payroll-manager' => 'مدير الكشوفات',
            'employee-manager' => 'مدير المنتسبين',
            'data-entry' => 'مدخل بيانات',
            'viewer' => 'مُطّلع',
        ];
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right">إدارة صلاحيات المستخدمين</h2>
    </x-slot>

    <div class="py-8" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold mb-4">قائمة المستخدمين وصلاحياتهم</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="p-3 text-right">#</th>
                                <th class="p-3 text-right">اسم المستخدم</th>
                                <th class="p-3 text-right">البريد الإلكتروني</th>
                                <th class="p-3 text-right">الأدوار</th>
                                <th class="p-3 text-right">الصلاحيات</th>
                                <th class="p-3 text-right">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($users as $index => $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3">{{ $index + 1 }}</td>
                                    <td class="p-3 font-bold">{{ $user->name }}</td>
                                    <td class="p-3 text-gray-600">{{ $user->email }}</td>
                                    <td class="p-3">
                                        @if($user->roles->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($user->roles as $role)
                                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                        🔵 {{ $roleLabels[$role->name] ?? $role->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-500 text-xs">لا يوجد أدوار</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($user->permissions->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($user->permissions->take(3) as $permission)
                                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                        ✓ {{ $permissionLabels[$permission->name] ?? $permission->name }}
                                                    </span>
                                                @endforeach
                                                @if($user->permissions->count() > 3)
                                                    <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                                        +{{ $user->permissions->count() - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-500 text-xs">لا يوجد صلاحيات</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <a href="{{ route('admin.permissions.edit', $user->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded transition">
                                            ⚙️ تحرير
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-500">لا يوجد مستخدمون</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- إحصائيات الصلاحيات -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <p class="text-gray-600 text-sm">إجمالي المستخدمين</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $users->count() }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <p class="text-gray-600 text-sm">إجمالي الصلاحيات</p>
                    <p class="text-3xl font-bold text-green-600">{{ $permissions->count() }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <p class="text-gray-600 text-sm">إجمالي الأدوار</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $roles->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
