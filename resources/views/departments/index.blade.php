<x-app-layout>
    @php
        $canManageDepartments = auth()->user()?->canAny(config('permissions.page_permissions.departments.manage', ['manage-settings', 'manage-departments'])) ?? false;
    @endphp

    <x-slot name="header">
        <div class="fixed top-16 inset-x-0 z-40 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-4" dir="rtl">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight whitespace-nowrap shrink-0">
                        {{ __('📋 إدارة الأقسام') }}
                    </h2>

                    <div class="flex items-center gap-3 flex-1 overflow-x-auto">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 min-w-[120px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">إجمالي الأقسام</p>
                            <p class="text-lg font-bold text-blue-600"><span id="total-depts">0</span></p>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-lg px-4 py-2 min-w-[120px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">أقسام رئيسية</p>
                            <p class="text-lg font-bold text-purple-600"><span id="main-depts">0</span></p>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 min-w-[120px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">أقسام فرعية</p>
                            <p class="text-lg font-bold text-green-600"><span id="sub-depts">0</span></p>
                        </div>

                        <div class="bg-white border border-gray-300 rounded-lg px-3 py-2 w-56 max-w-full shrink-0">
                            <input type="text" id="search-input" placeholder="🔍 ابحث عن قسم..."
                                   class="w-full border-0 p-0 focus:ring-0 text-sm"
                                   onkeyup="filterDepartments()">
                        </div>

                        @if($canManageDepartments)
                            <button onclick="openAddDepartmentModal()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow-sm flex items-center gap-2 shrink-0">
                                <span>➕</span>
                                <span>إضافة قسم</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="py-8 pt-40" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- الأقسام -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="departments-list" class="space-y-4">
                        <div class="text-center py-8 text-gray-500">
                            جاري تحميل الأقسام...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة/تعديل قسم -->
    <div id="department-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4" dir="rtl">
            <div class="flex justify-between items-center mb-4">
                <h2 id="modal-title" class="text-xl font-bold text-gray-900">إضافة قسم جديد</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
            </div>

            <form id="department-form" class="space-y-4">
                <input type="hidden" id="department-id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        اسم القسم <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="department-name"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           placeholder="مثال: قسم الموارد البشرية" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        القسم الرئيسي (اختياري)
                    </label>
                    <select id="department-parent"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <option value="">بدون قسم رئيسي</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">اترك فارغاً لإنشاء قسم رئيسي</p>
                </div>

                <div id="modal-message" class="hidden text-sm p-3 rounded"></div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" id="submit-btn"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                        حفظ
                    </button>
                    <button type="button" onclick="closeModal()"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 px-4 py-2 rounded font-semibold">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let allDepartments = [];
        const canManageDepartments = @json($canManageDepartments);

        // تحميل الأقسام عند بدء الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            loadDepartments();
        });

        async function loadDepartments() {
            try {
                const response = await fetch('/departments/list');
                const data = await response.json();

                if (data.success) {
                    allDepartments = flattenDepartments(data.departments);
                    renderDepartments(data.departments);
                    updateStats();
                } else {
                    showError('فشل تحميل الأقسام');
                }
            } catch (error) {
                showError('خطأ في الاتصال بالخادم');
            }
        }

        function flattenDepartments(departments, result = []) {
            departments.forEach(dept => {
                result.push(dept);
                if (dept.children && dept.children.length > 0) {
                    flattenDepartments(dept.children, result);
                }
            });
            return result;
        }

        function renderDepartments(departments) {
            const container = document.getElementById('departments-list');

            if (departments.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">لا توجد أقسام</div>';
                return;
            }

            container.innerHTML = departments.map(dept => `
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50" data-dept-id="${dept.id}" data-dept-name="${dept.name}">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">${dept.name}</h3>
                            ${dept.parent ? `<p class="text-sm text-gray-600">تابع لـ: ${dept.parent.name}</p>` : '<p class="text-sm text-blue-600">قسم رئيسي</p>'}
                        </div>
                        ${canManageDepartments ? `
                            <div class="flex gap-2">
                                <button onclick="editDepartment(${dept.id})"
                                        class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded">
                                    تعديل
                                </button>
                                <button onclick="deleteDepartment(${dept.id}, '${dept.name}')"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded">
                                    حذف
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    ${dept.children && dept.children.length > 0 ? `
                        <div class="mt-3 mr-6 space-y-2">
                            <p class="text-sm font-semibold text-gray-700">الأقسام الفرعية:</p>
                            ${renderSubDepartments(dept.children)}
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        function renderSubDepartments(children) {
            return children.map(child => `
                <div class="border-r-4 border-blue-400 pr-3 py-2 bg-white rounded">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-semibold text-gray-800">${child.name}</span>
                        </div>
                        ${canManageDepartments ? `
                            <div class="flex gap-2">
                                <button onclick="editDepartment(${child.id})"
                                        class="px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded">
                                    تعديل
                                </button>
                                <button onclick="deleteDepartment(${child.id}, '${child.name}')"
                                        class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                    حذف
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    ${child.children && child.children.length > 0 ? renderSubDepartments(child.children) : ''}
                </div>
            `).join('');
        }

        function openAddDepartmentModal() {
            if (!canManageDepartments) {
                alert('ليس لديك صلاحية لإدارة الأقسام');
                return;
            }

            document.getElementById('modal-title').textContent = 'إضافة قسم جديد';
            document.getElementById('department-id').value = '';
            document.getElementById('department-name').value = '';
            document.getElementById('department-parent').value = '';

            populateParentSelect();

            document.getElementById('department-modal').classList.remove('hidden');
        }

        function editDepartment(id) {
            if (!canManageDepartments) {
                alert('ليس لديك صلاحية لتعديل الأقسام');
                return;
            }

            const dept = allDepartments.find(d => d.id === id);
            if (!dept) return;

            document.getElementById('modal-title').textContent = 'تعديل قسم';
            document.getElementById('department-id').value = dept.id;
            document.getElementById('department-name').value = dept.name;
            document.getElementById('department-parent').value = dept.parent_id || '';

            populateParentSelect(id);

            document.getElementById('department-modal').classList.remove('hidden');
        }

        function populateParentSelect(excludeId = null) {
            const select = document.getElementById('department-parent');
            select.innerHTML = '<option value="">بدون قسم رئيسي</option>';

            allDepartments
                .filter(d => d.id !== excludeId && !d.parent_id) // فقط الأقسام الرئيسية
                .forEach(dept => {
                    select.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
                });
        }

        function closeModal() {
            document.getElementById('department-modal').classList.add('hidden');
            document.getElementById('modal-message').classList.add('hidden');
        }

        document.getElementById('department-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('department-id').value;
            const name = document.getElementById('department-name').value.trim();
            const parent_id = document.getElementById('department-parent').value || null;

            if (!name) {
                showModalMessage('الرجاء إدخال اسم القسم', 'error');
                return;
            }

            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'جاري الحفظ...';

            try {
                const url = id ? `/departments/${id}` : '/departments';
                const method = id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ name, parent_id })
                });

                const data = await response.json();

                if (data.success) {
                    showModalMessage(data.message, 'success');
                    setTimeout(() => {
                        closeModal();
                        loadDepartments();
                    }, 1000);
                } else {
                    showModalMessage(data.message, 'error');
                }
            } catch (error) {
                showModalMessage('خطأ في الاتصال بالخادم', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'حفظ';
            }
        });

        async function deleteDepartment(id, name) {
            if (!canManageDepartments) {
                alert('ليس لديك صلاحية لحذف الأقسام');
                return;
            }

            if (!confirm(`هل أنت متأكد من حذف القسم "${name}"؟`)) return;

            try {
                const response = await fetch(`/departments/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    loadDepartments();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('خطأ في الاتصال بالخادم');
            }
        }

        function showError(message) {
            document.getElementById('departments-list').innerHTML =
                `<div class="text-center py-8 text-red-600">${message}</div>`;
        }

        function showModalMessage(message, type) {
            const msg = document.getElementById('modal-message');
            msg.textContent = message;
            msg.className = `text-sm p-3 rounded ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            msg.classList.remove('hidden');
        }

        // تحديث الإحصائيات
        function updateStats() {
            let totalDepts = allDepartments.length;
            let mainDepts = allDepartments.filter(d => !d.parent_id).length;
            let subDepts = totalDepts - mainDepts;

            document.getElementById('total-depts').textContent = totalDepts;
            document.getElementById('main-depts').textContent = mainDepts;
            document.getElementById('sub-depts').textContent = subDepts;
        }

        // تطبيع النص العربي للبحث الصحيح
        function normalizeArabic(text) {
            if (!text) return '';
            return text
                .toLowerCase()
                .replace(/[ً-ْ]/g, '') // إزالة التشكيل
                .replace(/[أإآء]/g, 'ا') // توحيد الهمزات
                .replace(/ة/g, 'ه') // تاء مربوطة = هاء
                .replace(/ى/g, 'ي') // ألف مقصورة = ياء
                .trim();
        }

        // البحث الفوري في الأقسام
        function filterDepartments() {
            const searchInput = document.getElementById('search-input').value;
            const query = normalizeArabic(searchInput);
            const items = document.querySelectorAll('[data-dept-id]');
            const container = document.getElementById('departments-list');
            let visibleCount = 0;

            items.forEach(item => {
                const name = normalizeArabic(item.getAttribute('data-dept-name'));
                const shouldShow = query === '' || name.includes(query);
                item.style.display = shouldShow ? 'block' : 'none';
                if (shouldShow) visibleCount++;
            });

            // إظهار رسالة فقط إذا لم تكن هناك نتائج ولكن دون حذف البيانات
            let existingMessage = document.getElementById('no-results-msg');
            if (visibleCount === 0 && query !== '') {
                if (!existingMessage) {
                    existingMessage = document.createElement('div');
                    existingMessage.id = 'no-results-msg';
                    existingMessage.className = 'text-center py-4 px-4 mb-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-800';
                    container.insertBefore(existingMessage, container.firstChild);
                }
                existingMessage.textContent = `⚠️ لم يتم العثور على نتائج لـ "${searchInput}"`;
                existingMessage.style.display = 'block';
            } else if (existingMessage) {
                existingMessage.style.display = 'none';
            }
        }
    </script>
</x-app-layout>
