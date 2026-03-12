<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        $departmentsPagePermissions = config('permissions.page_permissions.departments.access', ['access-departments-page']);
        $governoratesPagePermissions = config('permissions.page_permissions.governorates.access', ['access-governorates-page']);
        $missionTypesPagePermissions = config('permissions.page_permissions.mission_types.access', ['access-mission-types-page']);
    @endphp

    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{
                        auth()->user()?->can('access-dashboard') ? route('dashboard') :
                        (auth()->user()?->can('view-payrolls') ? route('payrolls.index') :
                        (auth()->user()?->can('view-employees') ? route('employees.index') : route('profile.edit')))
                    }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden gap-1 sm:-my-px sm:ms-10 sm:flex">
                    @can('access-dashboard')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1 rounded-md">
                            {{ __('اللوحة الرئيسية') }}
                        </x-nav-link>
                    @endcan

                    @can('create-payrolls')
                        <x-nav-link :href="route('payrolls.create')" :active="request()->routeIs('payrolls.create')" class="text-sky-700 bg-sky-50 hover:bg-sky-100 px-3 py-1 rounded-md">
                            {{ __('إضافة كشف إيفاد') }}
                        </x-nav-link>
                    @endcan

                    @can('view-payrolls')
                        <x-nav-link :href="route('payrolls.index')" :active="request()->routeIs('payrolls.index')" class="text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md">
                            {{ __('سجل الكشوفات') }}
                        </x-nav-link>
                    @endcan

                    @can('view-employees')
                        <x-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')" class="text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-1 rounded-md">
                            {{ __('إدارة المنتسبين') }}
                        </x-nav-link>
                    @endcan

                    @can('manage-signatures')
                        <x-nav-link :href="route('signatures.index')" :active="request()->routeIs('signatures.*')" class="text-fuchsia-700 bg-fuchsia-50 hover:bg-fuchsia-100 px-3 py-1 rounded-md">
                            {{ __('إدارة التواقيع') }}
                        </x-nav-link>
                    @endcan

                    @canany($departmentsPagePermissions)
                        <x-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')" class="text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1 rounded-md">
                            {{ __('📋 الأقسام') }}
                        </x-nav-link>
                    @endcanany

                    @canany($governoratesPagePermissions)
                        <x-nav-link :href="route('governorates.index')" :active="request()->routeIs('governorates.*')" class="text-teal-700 bg-teal-50 hover:bg-teal-100 px-3 py-1 rounded-md">
                            {{ __('🗺️ المحافظات') }}
                        </x-nav-link>
                    @endcanany

                    @canany($missionTypesPagePermissions)
                        <x-nav-link :href="route('settings.mission-types')" :active="request()->routeIs('mission-types.*') || request()->routeIs('settings.mission-types')" class="text-orange-700 bg-orange-50 hover:bg-orange-100 px-3 py-1 rounded-md">
                            {{ __('✈️ ايفاد خارج البلد') }}
                        </x-nav-link>
                    @endcanany

                    @can('manage-backups')
                        <x-nav-link :href="route('backups.index')" :active="request()->routeIs('backups.*')" class="text-green-700 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md">
                            {{ __('💾 النسخ الاحتياطية') }}
                        </x-nav-link>
                    @endcan

                    @can('view-payroll-audit-log')
                        <x-nav-link :href="route('payrolls.audit')" :active="request()->routeIs('payrolls.audit')" class="text-violet-700 bg-violet-50 hover:bg-violet-100 px-3 py-1 rounded-md">
                            {{ __('🧾 سجل التدقيق') }}
                        </x-nav-link>
                    @endcan

                    @can('view-system-health-page')
                        <x-nav-link :href="route('system.health')" :active="request()->routeIs('system.health')" class="text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1 rounded-md">
                            {{ __('🩺 صحة النظام') }}
                        </x-nav-link>
                    @endcan

                    @can('access-admin-dashboard')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md">
                            {{ __('لوحة الأدمن') }}
                        </x-nav-link>
                    @endcan

                    @canany(['manage-users', 'manage-settings'])
                        <x-nav-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.*')" class="text-cyan-700 bg-cyan-50 hover:bg-cyan-100 px-3 py-1 rounded-md">
                            {{ __('🔐 إدارة الصلاحيات') }}
                        </x-nav-link>
                    @endcanany
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('الملف الشخصي') }}
                        </x-dropdown-link>

                        @can('access-admin-dashboard')
                            <x-dropdown-link :href="route('admin.dashboard')">
                                {{ __('لوحة الأدمن') }}
                            </x-dropdown-link>
                        @endcan

                        @canany(['manage-users', 'manage-settings'])
                            <x-dropdown-link :href="route('admin.permissions.index')">
                                {{ __('🔐 إدارة الصلاحيات') }}
                            </x-dropdown-link>
                        @endcanany

                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out border-none bg-transparent cursor-pointer">
                                {{ __('تسجيل الخروج') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>
