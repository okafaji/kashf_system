<x-app-layout>
    <x-slot name="header">
        <div class="fixed top-16 inset-x-0 z-40 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-4" dir="rtl">
                <div class="flex items-center justify-between gap-3 flex-wrap mb-0">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight whitespace-nowrap shrink-0 mb-0">
                        لوحة تحكم الأدمن
                    </h2>
                    <div class="flex gap-2 flex-wrap">
                        <a href="{{ route('dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold px-4 py-2 rounded">
                            رجوع للوحة الرئيسية
                        </a>
                        <a href="{{ route('users.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded">
                            إدارة المستخدمين
                        </a>
                        <a href="{{ route('roles.index') }}" class="bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded">
                            إدارة الأدوار
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg px-4 py-3 mb-6">
                <p class="text-sm">
                    تنبيه: عند إضافة صفحة أو ميزة جديدة، يرجى تحديث الصلاحيات في ملف
                    <span class="font-semibold">config/permissions.php</span>
                    وربط الراوت بصلاحية مناسبة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">إجمالي المستخدمين</p>
                    <p class="text-3xl font-bold text-blue-700">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">إجمالي الأدوار</p>
                    <p class="text-3xl font-bold text-amber-700">{{ $stats['total_roles'] }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm text-gray-500">إجمالي الصلاحيات</p>
                    <p class="text-3xl font-bold text-emerald-700">{{ $stats['total_permissions'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">الأدوار وعدد المستخدمين</h3>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="text-xs text-gray-500">
                                    <th class="pb-3">الدور</th>
                                    <th class="pb-3">عدد المستخدمين</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    <tr class="border-t">
                                        <td class="py-3 text-sm text-gray-800">{{ $role->name }}</td>
                                        <td class="py-3 text-sm text-gray-600">{{ $role->users_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">آخر المستخدمين</h3>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="text-xs text-gray-500">
                                    <th class="pb-3">الاسم</th>
                                    <th class="pb-3">البريد الإلكتروني</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentUsers as $user)
                                    <tr class="border-t">
                                        <td class="py-3 text-sm text-gray-800">{{ $user->name }}</td>
                                        <td class="py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
