<x-app-layout>
    @php
        $canManageMissionTypes = auth()->user()?->canAny(config('permissions.page_permissions.mission_types.manage', ['manage-settings', 'manage-mission-types'])) ?? false;
    @endphp

    <x-slot name="header">
        <div class="fixed top-16 inset-x-0 z-40 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-4" dir="rtl">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight whitespace-nowrap shrink-0">
                        ✈️ ايفاد خارج البلد
                    </h2>

                    <div class="flex items-center gap-3 flex-1 overflow-x-auto">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 min-w-[140px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">إجمالي الأنواع</p>
                            <p class="text-lg font-bold text-blue-600"><span id="total-types">0</span></p>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-lg px-4 py-2 min-w-[140px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">عدد الأنواع</p>
                            <p class="text-lg font-bold text-purple-600"><span id="total-names">0</span></p>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 min-w-[150px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">المستويات الفريدة</p>
                            <p class="text-lg font-bold text-green-600"><span id="total-levels">0</span></p>
                        </div>

                        <div class="bg-white border border-gray-300 rounded-lg px-3 py-2 w-56 max-w-full shrink-0">
                            <input type="text" id="search-input" placeholder="ابحث..."
                                   class="w-full border-0 p-0 focus:ring-0 text-sm"
                                   onkeyup="searchMissions()">
                        </div>

                        @if($canManageMissionTypes)
                            <button onclick="openAddModal()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow-sm flex items-center gap-2 shrink-0 whitespace-nowrap">
                                <span>+</span>
                                <span>إضافة</span>
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-200 text-sm">
                            <thead class="bg-gray-800 text-white sticky top-0">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-center w-12">#</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">نوع الإيفاد</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">المستوى</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">المبلغ (د)</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center w-24">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="missions-list">
                                <tr>
                                    <td colspan="5" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                        جاري التحميل...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination" class="mt-4 flex justify-center gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="mission-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4" dir="rtl">
            <div class="flex justify-between items-center mb-4">
                <h2 id="modal-title" class="text-xl font-bold">إضافة جديد</h2>
                <button onclick="closeModal()" class="text-2xl">x</button>
            </div>

            <form id="mission-form" class="space-y-4">
                <input type="hidden" id="mission-id">

                <div>
                    <label class="block text-sm font-medium mb-2">نوع الإيفاد <span class="text-red-500">*</span></label>
                    <input type="text" id="mission-name" placeholder="خارج القطر/1" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">المستوى <span class="text-red-500">*</span></label>
                    <input type="text" id="mission-level" placeholder="منتسب" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">المبلغ <span class="text-red-500">*</span></label>
                    <input type="number" id="mission-rate" step="0.01" min="0" placeholder="30000.00" class="w-full px-3 py-2 border rounded" required>
                </div>

                <div id="modal-message" class="hidden text-sm p-3 rounded"></div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">حفظ</button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-900 px-4 py-2 rounded">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let isEditMode = false;
        const canManageMissionTypes = @json($canManageMissionTypes);

        document.addEventListener('DOMContentLoaded', function () {
            loadMissions(1);
            document.getElementById('mission-form').addEventListener('submit', async function (e) {
                e.preventDefault();
                await saveMission();
            });
        });

        async function loadMissions(page) {
            const search = document.getElementById('search-input').value;
            try {
                const response = await fetch(`{{ route('mission-types.index') }}?ajax=true&page=${page}&search=${encodeURIComponent(search)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    renderMissions(result.data, result.page, result.per_page);
                    renderPagination(result.page, Math.ceil(result.total / result.per_page));
                    document.getElementById('total-types').textContent = result.total;
                    document.getElementById('total-names').textContent = Object.keys(result.count_by_name || {}).length;
                    document.getElementById('total-levels').textContent = result.total_levels;
                }
            } catch (error) {
                console.error(error);
            }
        }

        function renderMissions(missions, page, perPage) {
            const tbody = document.getElementById('missions-list');
            if (!missions.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="border px-4 py-8 text-center text-gray-500">لا توجد بيانات</td></tr>';
                return;
            }

            tbody.innerHTML = missions.map(function (m, i) {
                const index = ((page - 1) * perPage) + i + 1;
                const actionsHtml = canManageMissionTypes
                    ? `
                        <button onclick="editMission(${m.id})" class="px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded">تعديل</button>
                        <button onclick="deleteMission(${m.id})" class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">حذف</button>
                    `
                    : '<span class="text-xs text-gray-400">عرض فقط</span>';

                return `
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="border px-4 py-2 text-center">${index}</td>
                        <td class="border px-4 py-2 text-center"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">${m.name}</span></td>
                        <td class="border px-4 py-2 text-center">${m.responsibility_level}</td>
                        <td class="border px-4 py-2 text-center font-bold text-green-700">${Number(m.daily_rate).toLocaleString('ar-IQ', { minimumFractionDigits: 2 })}</td>
                        <td class="border px-4 py-2 text-center">
                            <div class="flex gap-1 justify-center">
                                ${actionsHtml}
                            </div>
                        </td>
                    </tr>`;
            }).join('');
        }

        function renderPagination(currentPage, totalPages) {
            const container = document.getElementById('pagination');
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            if (currentPage > 1) {
                html += `<button onclick="loadMissions(${currentPage - 1})" class="px-3 py-1 border rounded">السابق</button>`;
            }

            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                if (i === currentPage) {
                    html += `<button class="px-3 py-1 bg-blue-600 text-white rounded">${i}</button>`;
                } else {
                    html += `<button onclick="loadMissions(${i})" class="px-3 py-1 border rounded">${i}</button>`;
                }
            }

            if (currentPage < totalPages) {
                html += `<button onclick="loadMissions(${currentPage + 1})" class="px-3 py-1 border rounded">التالي</button>`;
            }

            container.innerHTML = html;
        }

        function searchMissions() {
            loadMissions(1);
        }

        function openAddModal() {
            if (!canManageMissionTypes) {
                showMessage('ليس لديك صلاحية لإدارة أنواع الإيفاد', 'error');
                return;
            }

            isEditMode = false;
            document.getElementById('mission-id').value = '';
            document.getElementById('modal-title').textContent = 'إضافة إيفاد جديد';
            document.getElementById('mission-form').reset();
            document.getElementById('modal-message').classList.add('hidden');
            document.getElementById('mission-modal').classList.remove('hidden');
        }

        async function editMission(id) {
            if (!canManageMissionTypes) {
                showMessage('ليس لديك صلاحية لتعديل أنواع الإيفاد', 'error');
                return;
            }

            try {
                const response = await fetch(`{{ route('mission-types.index') }}/${id}/edit?ajax=true`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    isEditMode = true;
                    const mission = result.data;
                    document.getElementById('mission-id').value = mission.id;
                    document.getElementById('modal-title').textContent = 'تعديل الإيفاد';
                    document.getElementById('mission-name').value = mission.name;
                    document.getElementById('mission-level').value = mission.responsibility_level;
                    document.getElementById('mission-rate').value = mission.daily_rate;
                    document.getElementById('modal-message').classList.add('hidden');
                    document.getElementById('mission-modal').classList.remove('hidden');
                }
            } catch (error) {
                console.error(error);
            }
        }

        async function saveMission() {
            if (!canManageMissionTypes) {
                showMessage('ليس لديك صلاحية لحفظ أنواع الإيفاد', 'error');
                return;
            }

            const payload = {
                name: document.getElementById('mission-name').value,
                responsibility_level: document.getElementById('mission-level').value,
                daily_rate: document.getElementById('mission-rate').value
            };

            const missionId = document.getElementById('mission-id').value;
            const url = isEditMode ? `{{ route('mission-types.index') }}/${missionId}` : `{{ route('mission-types.index') }}`;
            const method = isEditMode ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                const modalMsg = document.getElementById('modal-message');

                if (result.success) {
                    modalMsg.textContent = result.message;
                    modalMsg.className = 'text-sm p-3 rounded bg-green-100 text-green-800';
                    modalMsg.classList.remove('hidden');
                    setTimeout(function () {
                        closeModal();
                        loadMissions(1);
                    }, 700);
                } else {
                    modalMsg.textContent = result.message || 'حدث خطأ';
                    modalMsg.className = 'text-sm p-3 rounded bg-red-100 text-red-800';
                    modalMsg.classList.remove('hidden');
                }
            } catch (error) {
                console.error(error);
            }
        }

        async function deleteMission(id) {
            if (!canManageMissionTypes) {
                showMessage('ليس لديك صلاحية لحذف أنواع الإيفاد', 'error');
                return;
            }

            if (!confirm('متأكد من الحذف؟')) {
                return;
            }

            try {
                const response = await fetch(`{{ route('mission-types.index') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                showMessage(result.message || 'تم التنفيذ', result.success ? 'success' : 'error');
                if (result.success) {
                    loadMissions(1);
                }
            } catch (error) {
                console.error(error);
            }
        }

        function closeModal() {
            document.getElementById('mission-modal').classList.add('hidden');
        }

        function showMessage(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-20 right-4 p-4 rounded border z-50 ' + (type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
            alertDiv.textContent = message;
            document.body.appendChild(alertDiv);
            setTimeout(function () {
                alertDiv.remove();
            }, 2500);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</x-app-layout>
