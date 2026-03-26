<x-app-layout>
    @php
        $roleTranslations = [
            'admin' => 'مدير عام',
            'payroll-manager' => 'مدير الكشوف',
            'data-entry' => 'مدخل بيانات',
            'viewer' => 'مشاهد',
            'employee-manager' => 'مدير الموظفين',
        ];

        $permissionGroups = config('permissions.permission_groups', []);
        $permissionHelp = config('permissions.permission_help', []);
        $roleHelp = config('permissions.role_help', []);
        $currentRoleHelp = $roleHelp[$role->name] ?? null;
        $groupOrder = ['page_access', 'access', 'payrolls', 'employees', 'signatures', 'settings', 'locations'];
        $orderedPermissionGroups = [];

        foreach ($groupOrder as $groupKey) {
            if (isset($permissionGroups[$groupKey])) {
                $orderedPermissionGroups[$groupKey] = $permissionGroups[$groupKey];
            }
        }

        foreach ($permissionGroups as $groupKey => $groupConfig) {
            if (!isset($orderedPermissionGroups[$groupKey])) {
                $orderedPermissionGroups[$groupKey] = $groupConfig;
            }
        }
    @endphp
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                تعديل صلاحيات: {{ $roleTranslations[$role->name] ?? $role->name }}
            </h2>
            @if($currentRoleHelp)
                <p class="text-sm text-amber-700 dark:text-amber-300">
                    وصف الدور: {{ $currentRoleHelp }}
                </p>
            @endif
            <p class="text-sm text-gray-600 dark:text-gray-300">
                تم فصل صلاحيات إظهار الصفحات عن صلاحيات الإدارة حتى يمكن عرض الصفحة بدون السماح بالإضافة أو التعديل أو الحذف.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form id="role-permissions-form" method="POST" action="{{ route('roles.update', $role) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-6 rounded-xl border border-sky-200 bg-gradient-to-r from-sky-50 to-white p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-bold text-sky-800">توضيح مهم</h3>
                                    <p class="mt-1 text-sm text-sky-900">
                                        صلاحيات "إظهار الصفحات" تتحكم فقط بظهور الرابط وفتح الصفحة، أما أزرار الإضافة والتعديل والحذف فتبقى مرتبطة بصلاحيات الإدارة داخل كل قسم.
                                    </p>
                                </div>
                                <div class="shrink-0 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                                    فصل العرض عن الإدارة
                                </div>
                            </div>
                        </div>

                        @if(!empty($lockedOwnRolesAccessPermissions ?? []))
                            <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-4">
                                <h3 class="text-sm font-bold text-amber-800">تنبيه حماية الوصول الذاتي</h3>
                                <p class="mt-1 text-sm text-amber-900">
                                    هذا الدور مرتبط بحسابك الحالي، لذلك صلاحيات الوصول التالية محمية مؤقتًا حتى لا تفقد صلاحية الدخول إلى إدارة الأدوار/المستخدمين أثناء الجلسة الحالية.
                                </p>
                                <p class="mt-2 text-sm text-amber-900">
                                    عند رفض الحفظ، سيتم إرجاع checkbox إلى آخر حالة محفوظة تلقائيًا.
                                </p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($orderedPermissionGroups as $groupKey => $group)
                                @php
                                    $columns = max(1, (int)($group['columns'] ?? 1));
                                    $isWide = $columns > 1;
                                    $isPageAccessGroup = $groupKey === 'page_access';
                                    $containerClasses = $isPageAccessGroup
                                        ? 'border border-sky-300 bg-sky-50/70 rounded-xl p-5 md:col-span-2 shadow-sm'
                                        : 'border border-gray-300 rounded-lg p-4' . ($isWide ? ' md:col-span-2' : '');
                                    $innerGridClass = $isWide ? ('grid grid-cols-1 md:grid-cols-' . $columns . ' gap-4') : '';
                                    $groupPermissions = $group['permissions'] ?? [];
                                    $selectedCount = count(array_intersect(array_keys($groupPermissions), $rolePermissions));
                                @endphp

                                <div class="{{ $containerClasses }}">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-bold text-lg {{ $group['color'] ?? 'text-gray-700' }}">
                                                {{ $group['label'] ?? 'صلاحيات' }}
                                            </h3>

                                            @if($isPageAccessGroup)
                                                <p class="mt-1 text-sm text-sky-800">
                                                    فعّل الصفحة هنا إذا كنت تريد ظهورها في القوائم، ثم امنح صلاحية الإدارة من المجموعات الأخرى فقط عند الحاجة.
                                                </p>
                                            @endif
                                        </div>

                                        <span data-group-counter="{{ $groupKey }}" data-group-total="{{ count($groupPermissions) }}" class="group-counter shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $isPageAccessGroup ? 'bg-sky-100 text-sky-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $selectedCount }}/{{ count($groupPermissions) }} محدد
                                        </span>
                                    </div>

                                    @if($isWide)
                                        <div class="{{ $innerGridClass }}">
                                            @foreach($groupPermissions as $perm => $label)
                                                @php
                                                    $lockedPermissions = $lockedOwnRolesAccessPermissions ?? [];
                                                    $isProtectedRolesAccessPermission = in_array($perm, $lockedPermissions, true);
                                                    $permissionHelpItem = $permissionHelp[$perm] ?? null;
                                                    $permissionEnableHelp = is_array($permissionHelpItem) ? ($permissionHelpItem['enable'] ?? null) : null;
                                                    $permissionDisableHelp = is_array($permissionHelpItem) ? ($permissionHelpItem['disable'] ?? null) : null;
                                                @endphp

                                                <label for="perm_{{ $perm }}" class="flex items-start gap-3 rounded-lg border {{ $isPageAccessGroup ? 'border-sky-200 bg-white/80' : 'border-transparent' }} px-3 py-2 mb-2 cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $perm }}"
                                                        id="perm_{{ $perm }}"
                                                        data-group="{{ $groupKey }}"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                                        {{ in_array($perm, $rolePermissions, true) ? 'checked' : '' }}
                                                    >
                                                    <span class="text-sm leading-6">
                                                        {{ $label }}
                                                        @if($permissionEnableHelp)
                                                            <span class="block text-xs text-gray-600">عند التفعيل: {{ $permissionEnableHelp }}</span>
                                                        @endif
                                                        @if($permissionDisableHelp)
                                                            <span class="block text-xs text-gray-500">عند الإلغاء: {{ $permissionDisableHelp }}</span>
                                                        @endif
                                                        @if($isProtectedRolesAccessPermission)
                                                            <span class="block text-xs text-amber-700">محمية لحماية وصولك الحالي (عند إزالتها يتم تفعيل الصلاحية البديلة تلقائيا)</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        @foreach($groupPermissions as $perm => $label)
                                            @php
                                                $lockedPermissions = $lockedOwnRolesAccessPermissions ?? [];
                                                $isProtectedRolesAccessPermission = in_array($perm, $lockedPermissions, true);
                                                $permissionHelpItem = $permissionHelp[$perm] ?? null;
                                                $permissionEnableHelp = is_array($permissionHelpItem) ? ($permissionHelpItem['enable'] ?? null) : null;
                                                $permissionDisableHelp = is_array($permissionHelpItem) ? ($permissionHelpItem['disable'] ?? null) : null;
                                            @endphp

                                            <label for="perm_{{ $perm }}" class="flex items-start gap-3 rounded-lg border {{ $isPageAccessGroup ? 'border-sky-200 bg-white/80' : 'border-transparent' }} px-3 py-2 mb-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $perm }}"
                                                    id="perm_{{ $perm }}"
                                                    data-group="{{ $groupKey }}"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                                    {{ in_array($perm, $rolePermissions, true) ? 'checked' : '' }}
                                                >
                                                <span class="text-sm leading-6">
                                                    {{ $label }}
                                                    @if($permissionEnableHelp)
                                                        <span class="block text-xs text-gray-600">عند التفعيل: {{ $permissionEnableHelp }}</span>
                                                    @endif
                                                    @if($permissionDisableHelp)
                                                        <span class="block text-xs text-gray-500">عند الإلغاء: {{ $permissionDisableHelp }}</span>
                                                    @endif
                                                    @if($isProtectedRolesAccessPermission)
                                                        <span class="block text-xs text-amber-700">محمية لحماية وصولك الحالي (عند إزالتها يتم تفعيل الصلاحية البديلة تلقائيا)</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between mt-8">
                            <div class="space-y-1">
                                <div id="selected-permissions-summary" class="text-sm font-semibold text-sky-700">
                                    عدد الصلاحيات المحددة: <span id="selected-permissions-count">{{ count($rolePermissions) }}</span>
                                </div>
                                <div id="autosave-status" class="text-xs text-gray-500">الحفظ التلقائي مفعل</div>
                            </div>

                            <a href="{{ route('roles.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                إلغاء
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('role-permissions-form');
            if (!form) {
                return;
            }

            const checkboxes = Array.from(document.querySelectorAll('input[name="permissions[]"]'));
            const groupCounters = Array.from(document.querySelectorAll('[data-group-counter]'));
            const selectedCountElement = document.getElementById('selected-permissions-count');
            const autosaveStatusElement = document.getElementById('autosave-status');
            const csrfInput = form.querySelector('input[name="_token"]');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const lockedOwnRolesAccessPermissions = @json($lockedOwnRolesAccessPermissions ?? []);
            const rolesAreaAccessPermissions = ['manage-users', 'manage-roles'];
            const rolesPermissionLabels = {
                'manage-users': 'إدارة المستخدمين',
                'manage-roles': 'إدارة الأدوار'
            };

            let saveTimer = null;
            let isSaving = false;
            let pendingSave = false;
            let lastSavedCount = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
            let lastSavedPermissions = new Set(checkboxes.filter(function (checkbox) { return checkbox.checked; }).map(function (checkbox) { return checkbox.value; }));

            function getSelectedPermissions() {
                return checkboxes
                    .filter(function (checkbox) { return checkbox.checked; })
                    .map(function (checkbox) { return checkbox.value; });
            }

            function updateSelectedCounters() {
                const selectedPermissions = getSelectedPermissions();
                const selectedSet = new Set(selectedPermissions);

                if (selectedCountElement) {
                    selectedCountElement.textContent = String(selectedPermissions.length);
                }

                groupCounters.forEach(function (counterElement) {
                    const groupKey = counterElement.getAttribute('data-group-counter');
                    const groupTotal = Number(counterElement.getAttribute('data-group-total') || 0);
                    const selectedInGroup = checkboxes.filter(function (checkbox) {
                        return checkbox.getAttribute('data-group') === groupKey && selectedSet.has(checkbox.value);
                    }).length;

                    counterElement.textContent = `${selectedInGroup}/${groupTotal} محدد`;
                });
            }

            function applySavedPermissionsState(savedPermissions) {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = savedPermissions.has(checkbox.value);
                });
                updateSelectedCounters();
            }

            function setAutosaveStatus(message, type) {
                if (!autosaveStatusElement) {
                    return;
                }

                autosaveStatusElement.textContent = message;
                autosaveStatusElement.className = 'text-xs';

                if (type === 'success') {
                    autosaveStatusElement.classList.add('text-green-600');
                } else if (type === 'error') {
                    autosaveStatusElement.classList.add('text-red-600');
                } else {
                    autosaveStatusElement.classList.add('text-gray-500');
                }
            }

            async function savePermissionsNow() {
                if (isSaving) {
                    pendingSave = true;
                    return;
                }

                isSaving = true;
                setAutosaveStatus('جاري الحفظ التلقائي...', 'info');

                const selectedPermissions = getSelectedPermissions();
                const payload = new URLSearchParams();
                payload.append('_token', csrfInput ? csrfInput.value : '');
                payload.append('_method', 'PUT');
                selectedPermissions.forEach(function (permissionName) {
                    payload.append('permissions[]', permissionName);
                });

                const csrfToken = (csrfInput && csrfInput.value) || (csrfMeta && csrfMeta.getAttribute('content')) || '';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: payload.toString()
                    });

                    const responseType = response.headers.get('content-type') || '';
                    let result = null;

                    if (responseType.includes('application/json')) {
                        result = await response.json();
                    } else {
                        const responseText = await response.text();
                        result = { message: responseText };
                    }

                    if (!response.ok) {
                        const backendMessage = result && typeof result.message === 'string' ? result.message : '';
                        if (response.status === 419) {
                            throw new Error('انتهت الجلسة. يرجى تحديث الصفحة ثم المحاولة.');
                        }
                        if (response.status === 403) {
                            throw new Error('لا تملك صلاحية كافية لتعديل هذا الدور.');
                        }
                        throw new Error(backendMessage || `فشل الحفظ (HTTP ${response.status})`);
                    }

                    lastSavedCount = typeof result.permissions_count === 'number' ? result.permissions_count : selectedPermissions.length;
                    lastSavedPermissions = new Set(selectedPermissions);
                    setAutosaveStatus('تم الحفظ تلقائيا', 'success');
                } catch (error) {
                    applySavedPermissionsState(lastSavedPermissions);
                    const errorMessage = error && error.message ? error.message : 'فشل الحفظ التلقائي. حاول مجددا.';
                    setAutosaveStatus(errorMessage, 'error');
                } finally {
                    isSaving = false;
                    if (pendingSave) {
                        pendingSave = false;
                        savePermissionsNow();
                    }
                }
            }

            function scheduleAutoSave() {
                if (saveTimer) {
                    clearTimeout(saveTimer);
                }

                saveTimer = setTimeout(function () {
                    savePermissionsNow();
                }, 250);
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const isProtectedOwnAccessPermission =
                        lockedOwnRolesAccessPermissions.includes(checkbox.value) && checkbox.checked === false;

                    if (isProtectedOwnAccessPermission) {
                        const alternativePermission = rolesAreaAccessPermissions.find(function (permissionName) {
                            return permissionName !== checkbox.value;
                        });

                        const alternativeCheckbox = checkboxes.find(function (item) {
                            return item.value === alternativePermission;
                        });

                        if (alternativeCheckbox && !alternativeCheckbox.checked) {
                            alternativeCheckbox.checked = true;
                            const alternativeLabel = rolesPermissionLabels[alternativePermission] || alternativePermission;
                            setAutosaveStatus(`تم تفعيل "${alternativeLabel}" تلقائيا للحفاظ على وصولك`, 'info');
                        }
                    }

                    updateSelectedCounters();
                    setAutosaveStatus('يوجد تغييرات غير محفوظة...', 'info');
                    scheduleAutoSave();
                });
            });

            form.addEventListener('submit', function () {
                setAutosaveStatus('جاري الحفظ...', 'info');
            });

            updateSelectedCounters();
            if (selectedCountElement) {
                selectedCountElement.textContent = String(lastSavedCount);
            }
        })();
    </script>

</x-app-layout>
