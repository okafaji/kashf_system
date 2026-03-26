<!-- ===== Sidebar Navigation ===== -->
@php
    $departmentsPagePermissions = config('permissions.page_permissions.departments.access', ['access-departments-page']);
    $governoratesPagePermissions = config('permissions.page_permissions.governorates.access', ['access-governorates-page']);
    $missionTypesPagePermissions = config('permissions.page_permissions.mission_types.access', ['access-mission-types-page']);
@endphp

<aside id="sidebar" class="fixed right-0 top-0 h-screen w-72 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-xl z-40 transition-all duration-300 rtl">
    <!-- Logo & Branding -->
    <div class="p-6 border-b border-blue-700">
        <div class="flex items-center justify-center">
            <h1 class="text-2xl font-bold text-center text-white">لوحة التحكم</h1>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto p-4">
        <div class="space-y-0.5">
            <!-- Dashboard -->
            @can('access-dashboard')
                <a href="{{ route('dashboard') }}" class="sidebar-link group flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-2h2v16h-2z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                    <span class="font-medium text-emerald-100">لوحة التحكم</span>
                </a>
            @endcan

            <!-- Divider -->
            <div class="my-2 border-t border-blue-700"></div>

            <!-- Main Modules -->
            <p class="px-4 py-1.5 text-xs font-bold text-blue-300 uppercase tracking-wider">الوحدات الرئيسية</p>

            <!-- Create Payroll -->
            @can('create-payrolls')
                <a href="{{ route('payrolls.create') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('payrolls.create') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-sky-300"></span>
                    <span class="text-sky-100">إضافة كشف إيفاد</span>
                </a>
            @endcan

            <!-- Payrolls Registry -->
            @can('view-payrolls')
                <a href="{{ route('payrolls.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('payrolls.*') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-indigo-300"></span>
                    <span class="text-indigo-100">سجل الكشوفات</span>
                </a>
            @endcan

            <!-- Employees -->
            @can('view-employees')
                <a href="{{ route('employees.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('employees.*') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm6-6a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-amber-300"></span>
                    <span class="text-amber-100">إدارة المنتسبين</span>
                </a>
            @endcan

            <!-- Divider -->
            <div class="my-2 border-t border-blue-700"></div>

            <!-- Settings -->
            <p class="px-4 py-1.5 text-xs font-bold text-blue-300 uppercase tracking-wider">الإعدادات</p>

            <!-- Signatures -->
            @can('manage-signatures')
                <a href="{{ route('signatures.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('signatures.*') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-fuchsia-300"></span>
                    <span class="text-fuchsia-100">إدارة التواقيع</span>
                </a>
            @endcan

            @canany($departmentsPagePermissions)
                <a href="{{ route('departments.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('departments.*') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M6 7V5h12v2m-1 0v12a2 2 0 01-2 2H9a2 2 0 01-2-2V7m3 4h4m-4 4h4"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-violet-300"></span>
                    <span class="text-violet-100">الأقسام</span>
                </a>
            @endcanany

            @canany($governoratesPagePermissions)
                <a href="{{ route('governorates.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('governorates.*') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 01.553-.894L9 2l6 3 6-3v14l-6 3-6-3z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-teal-300"></span>
                    <span class="text-teal-100">المحافظات</span>
                </a>
            @endcanany

            <!-- Mission Types Management -->
            @canany($missionTypesPagePermissions)
                <a href="{{ route('settings.mission-types') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('mission-types.*') || request()->routeIs('settings.mission-types') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-orange-300"></span>
                    <span class="text-orange-100">ايفاد خارج البلد</span>
                </a>
            @endcanany

            @can('access-admin-dashboard')
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3a.75.75 0 01.75.75V5h3V3.75a.75.75 0 011.5 0V5h1.25A2.75 2.75 0 0118.5 7.75V10H5.5V7.75A2.75 2.75 0 017.75 5H9V3.75A.75.75 0 019.75 3zM5.5 11.5h13v4.75A2.75 2.75 0 0115.75 19H8.25A2.75 2.75 0 015.5 16.25v-4.75z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-red-300"></span>
                    <span class="text-red-100">لوحة تحكم الأدمن</span>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('users.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('users.*') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H3v-2a4 4 0 014-4h6m2-4a4 4 0 11-8 0 4 4 0 018 0zm6 4a3 3 0 10-6 0 3 3 0 006 0z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-cyan-300"></span>
                    <span class="text-cyan-100">إدارة المستخدمين</span>
                </a>
            @endcan

            @canany(['manage-roles', 'manage-users'])
                <a href="{{ route('roles.index') }}" class="sidebar-link flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('roles.*') ? 'bg-blue-700 border-r-4 border-blue-400' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a4 4 0 118 0H6zm12-6a4 4 0 01-4 4H8a4 4 0 01-4-4V9a4 4 0 014-4h6a4 4 0 014 4v3z"/>
                    </svg>
                    <span class="w-2 h-2 rounded-full bg-lime-300"></span>
                    <span class="text-lime-100">إدارة الأدوار</span>
                </a>
            @endcanany
        </div>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-blue-700">
        <div class="text-xs text-blue-200 text-center">
            <p>{{ Auth::user()->name }}</p>
            <p class="text-blue-300 font-semibold">مسؤول النظام</p>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full text-sm bg-blue-700 hover:bg-blue-600 text-white font-semibold py-2 px-3 rounded">
                تسجيل الخروج
            </button>
        </form>
    </div>
</aside>

<!-- Overlay for Mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

<script>
    // Toggle Sidebar on Mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
        });
    }

    overlay?.addEventListener('click', () => {
        sidebar.classList.add('translate-x-full');
        overlay.classList.add('hidden');
    });
</script>
