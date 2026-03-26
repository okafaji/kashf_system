<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                إدارة الأدوار والصلاحيات
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold px-4 py-2 rounded">
                رجوع
            </a>
        </div>
    </x-slot>

    @php
        $roleTranslations = [
            'admin' => 'مدير عام',
            'payroll-manager' => 'مسؤول الشعبة',
            'data-entry' => 'المنظم',
            'viewer' => 'المدقق',
            'employee-manager' => 'مسؤول الوحدة',
        ];

        $pageVisibilityPermissions = [
            'access-departments-page' => 'الأقسام',
            'access-governorates-page' => 'المحافظات',
            'access-mission-types-page' => 'إيفاد خارج البلد',
        ];

        $roleHelp = config('permissions.role_help', []);

        $currentUserRoleIds = auth()->user()?->roles?->pluck('id')->toArray() ?? [];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                        <span class="font-semibold">ملاحظة:</span>
                        هذا الملخص يوضح صلاحية إظهار الصفحات فقط. صلاحيات الإدارة (إضافة/تعديل/حذف) تُضبط من مجموعات الإدارة داخل شاشة تعديل الدور.
                    </div>

                    <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        <span class="font-semibold">تنبيه:</span>
                        عند تعديل دور مرتبط بحسابك الحالي، النظام يحميك من فقدان الوصول إلى إدارة الأدوار/المستخدمين. إذا كان التغيير يؤدي لفقدان الوصول سيتم رفض الحفظ وإرجاع الحالة الأخيرة المحفوظة تلقائيا.
                    </div>

                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <label for="page-visibility-filter" class="text-sm font-semibold text-gray-700">فلتر إظهار الصفحات:</label>
                                <select id="page-visibility-filter" class="rounded border-gray-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                    <option value="all">كل الأدوار</option>
                                    @foreach($pageVisibilityPermissions as $permissionName => $label)
                                        <option value="{{ $permissionName }}">يعرض صفحة {{ $label }}</option>
                                    @endforeach
                                </select>

                                <label for="page-visibility-state-filter" class="text-sm font-semibold text-gray-700">الحالة:</label>
                                <select id="page-visibility-state-filter" class="rounded border-gray-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                                    <option value="all">الكل</option>
                                    <option value="visible">ظاهر</option>
                                    <option value="hidden">مخفي</option>
                                </select>

                                <button type="button" onclick="resetPageVisibilityFilter()" class="rounded bg-gray-200 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                                    إعادة تعيين
                                </button>
                            </div>

                            <div class="text-sm text-gray-600">
                                عدد النتائج: <span id="roles-filter-count" class="font-bold text-sky-700">{{ $roles->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    الدور
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    عدد الصلاحيات
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    إظهار الصفحات
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    الإجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody id="roles-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach($roles as $role)
                                @php
                                    $rolePermissionNames = $role->permissions->pluck('name')->toArray();
                                    $isCurrentUserRole = in_array($role->id, $currentUserRoleIds, true);
                                @endphp
                                <tr
                                    class="role-row {{ $isCurrentUserRole ? 'bg-amber-50/40' : '' }}"
                                    data-perm-access-departments-page="{{ in_array('access-departments-page', $rolePermissionNames, true) ? '1' : '0' }}"
                                    data-perm-access-governorates-page="{{ in_array('access-governorates-page', $rolePermissionNames, true) ? '1' : '0' }}"
                                    data-perm-access-mission-types-page="{{ in_array('access-mission-types-page', $rolePermissionNames, true) ? '1' : '0' }}"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $roleTranslations[$role->name] ?? $role->name }}</span>
                                            @if($isCurrentUserRole)
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                                                    دورك الحالي
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($roleHelp[$role->name] ?? null))
                                            <div class="mt-1 text-xs text-gray-600">{{ $roleHelp[$role->name] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $role->permissions->count() }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($pageVisibilityPermissions as $permissionName => $label)
                                                @php
                                                    $hasPagePermission = in_array($permissionName, $rolePermissionNames, true);
                                                @endphp

                                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $hasPagePermission ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                                    <span>{{ $hasPagePermission ? 'ظاهر' : 'مخفي' }}</span>
                                                    <span>•</span>
                                                    <span>{{ $label }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a
                                            href="{{ route('roles.edit', $role) }}"
                                            data-sensitive-edit="{{ $isCurrentUserRole ? '1' : '0' }}"
                                            data-role-name="{{ $roleTranslations[$role->name] ?? $role->name }}"
                                            class="inline-flex items-center gap-1 rounded px-2 py-1 {{ $isCurrentUserRole ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'text-indigo-600 hover:text-indigo-900' }}"
                                        >
                                            @if($isCurrentUserRole)
                                                <span aria-hidden="true">⚠️</span>
                                            @endif
                                            <span>تعديل الصلاحيات</span>
                                        </a>

                                        @if($isCurrentUserRole)
                                            <div class="mt-1 text-xs text-amber-700">تعديل حساس: هذا الدور مرتبط بحسابك الحالي</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="roles-filter-empty" class="hidden mt-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        لا توجد أدوار مطابقة للفلتر المحدد.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="current-role-edit-modal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" data-close-current-role-modal="1"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-xl border border-amber-300 bg-white p-5 shadow-lg" dir="rtl">
                <h3 class="text-base font-bold text-amber-800">
                    تأكيد تعديل دور: <span id="current-role-edit-title-name">-</span>
                </h3>
                <p class="mt-2 text-sm text-gray-700">
                    أنت على وشك فتح تعديل الصلاحيات لدور مرتبط بحسابك الحالي. أي تعديل غير مناسب قد يسبب رفض حفظ أو تقييد الوصول.
                </p>
                <p class="mt-2 text-sm text-amber-900">
                    الدور المستهدف: <span id="current-role-edit-name" class="font-bold">-</span>
                </p>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" id="cancel-current-role-edit" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                        إلغاء
                    </button>
                    <button type="button" id="confirm-current-role-edit" class="rounded bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                        متابعة التعديل
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pendingSensitiveRoleEditHref = null;
        let pendingSensitiveRoleName = null;

        function openCurrentRoleEditModal(href, roleName) {
            pendingSensitiveRoleEditHref = href;
            pendingSensitiveRoleName = roleName || '-';

            const modal = document.getElementById('current-role-edit-modal');
            const roleNameElement = document.getElementById('current-role-edit-name');
            const roleTitleNameElement = document.getElementById('current-role-edit-title-name');

            if (roleNameElement) {
                roleNameElement.textContent = pendingSensitiveRoleName;
            }

            if (roleTitleNameElement) {
                roleTitleNameElement.textContent = pendingSensitiveRoleName;
            }

            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeCurrentRoleEditModal() {
            pendingSensitiveRoleEditHref = null;
            pendingSensitiveRoleName = null;
            const modal = document.getElementById('current-role-edit-modal');
            const roleNameElement = document.getElementById('current-role-edit-name');
            const roleTitleNameElement = document.getElementById('current-role-edit-title-name');

            if (roleNameElement) {
                roleNameElement.textContent = '-';
            }

            if (roleTitleNameElement) {
                roleTitleNameElement.textContent = '-';
            }

            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function applyPageVisibilityFilter() {
            const permissionFilterSelect = document.getElementById('page-visibility-filter');
            const stateFilterSelect = document.getElementById('page-visibility-state-filter');
            const selectedPermission = permissionFilterSelect ? permissionFilterSelect.value : 'all';
            const selectedState = stateFilterSelect ? stateFilterSelect.value : 'all';
            const rows = document.querySelectorAll('.role-row');
            const emptyState = document.getElementById('roles-filter-empty');
            const countElement = document.getElementById('roles-filter-count');

            let visibleCount = 0;

            rows.forEach(function (row) {
                let shouldShow = true;

                if (selectedPermission !== 'all') {
                    const hasPermission = row.getAttribute(`data-perm-${selectedPermission}`) === '1';

                    if (selectedState === 'visible') {
                        shouldShow = hasPermission;
                    } else if (selectedState === 'hidden') {
                        shouldShow = !hasPermission;
                    } else {
                        shouldShow = true;
                    }
                }

                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) {
                    visibleCount++;
                }
            });

            if (countElement) {
                countElement.textContent = String(visibleCount);
            }

            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleCount > 0);
            }
        }

        function resetPageVisibilityFilter() {
            const permissionFilterSelect = document.getElementById('page-visibility-filter');
            const stateFilterSelect = document.getElementById('page-visibility-state-filter');

            if (permissionFilterSelect) {
                permissionFilterSelect.value = 'all';
            }

            if (stateFilterSelect) {
                stateFilterSelect.value = 'all';
            }

            applyPageVisibilityFilter();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const permissionFilterSelect = document.getElementById('page-visibility-filter');
            const stateFilterSelect = document.getElementById('page-visibility-state-filter');
            const sensitiveEditLinks = document.querySelectorAll('a[data-sensitive-edit="1"]');
            const cancelCurrentRoleEditButton = document.getElementById('cancel-current-role-edit');
            const confirmCurrentRoleEditButton = document.getElementById('confirm-current-role-edit');
            const closeModalElements = document.querySelectorAll('[data-close-current-role-modal="1"]');

            if (permissionFilterSelect) {
                permissionFilterSelect.addEventListener('change', applyPageVisibilityFilter);
            }

            if (stateFilterSelect) {
                stateFilterSelect.addEventListener('change', applyPageVisibilityFilter);
            }

            sensitiveEditLinks.forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    openCurrentRoleEditModal(link.getAttribute('href'), link.getAttribute('data-role-name'));
                });
            });

            if (cancelCurrentRoleEditButton) {
                cancelCurrentRoleEditButton.addEventListener('click', closeCurrentRoleEditModal);
            }

            if (confirmCurrentRoleEditButton) {
                confirmCurrentRoleEditButton.addEventListener('click', function () {
                    if (pendingSensitiveRoleEditHref) {
                        window.location.href = pendingSensitiveRoleEditHref;
                    }
                });
            }

            closeModalElements.forEach(function (element) {
                element.addEventListener('click', closeCurrentRoleEditModal);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeCurrentRoleEditModal();
                }
            });

            applyPageVisibilityFilter();
        });
    </script>

</x-app-layout>
