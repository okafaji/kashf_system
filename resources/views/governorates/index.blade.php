<x-app-layout>
    @php
        $canManageGovernorates = auth()->user()?->canAny(config('permissions.page_permissions.governorates.manage', ['manage-settings', 'manage-governorates'])) ?? false;
    @endphp

    <x-slot name="header">
        <div class="fixed top-16 inset-x-0 z-40 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-4" dir="rtl">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight whitespace-nowrap shrink-0">
                        {{ __('🗺️ إدارة المحافظات') }}
                    </h2>

                    <div class="flex items-center gap-3 flex-1 overflow-x-auto">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 min-w-[120px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">إجمالي المحافظات</p>
                            <p class="text-lg font-bold text-blue-600"><span id="total-govs">0</span></p>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2 min-w-[120px] shrink-0">
                            <p class="text-gray-600 text-xs font-medium">إجمالي المدن</p>
                            <p class="text-lg font-bold text-green-600"><span id="total-cities">0</span></p>
                        </div>

                        <div class="bg-white border border-gray-300 rounded-lg px-3 py-2 w-56 max-w-full shrink-0">
                            <input type="text" id="search-input" placeholder="🔍 ابحث عن محافظة..."
                                   class="w-full border-0 p-0 focus:ring-0 text-sm"
                                   onkeyup="filterGovernorates()">
                        </div>

                        @if($canManageGovernorates)
                            <button onclick="openAddGovernorateModal()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow-sm flex items-center gap-2 shrink-0">
                                <span>➕</span>
                                <span>إضافة محافظة</span>
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
            <!-- المحافظات والمدن -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="governorates-list" class="space-y-4">
                        <div class="text-center py-8 text-gray-500">
                            جاري تحميل المحافظات...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة/تعديل محافظة -->
    <div id="governorate-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4" dir="rtl">
            <div class="flex justify-between items-center mb-4">
                <h2 id="gov-modal-title" class="text-xl font-bold text-gray-900">إضافة محافظة جديدة</h2>
                <button onclick="closeGovernorateModal()" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
            </div>

            <form id="governorate-form" class="space-y-4">
                <input type="hidden" id="governorate-id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        اسم المحافظة <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="governorate-name"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           placeholder="مثال: بغداد" required>
                </div>

                <div id="gov-modal-message" class="hidden text-sm p-3 rounded"></div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" id="gov-submit-btn"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                        حفظ
                    </button>
                    <button type="button" onclick="closeGovernorateModal()"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 px-4 py-2 rounded font-semibold">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal إضافة/تعديل مدينة -->
    <div id="city-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4" dir="rtl">
            <div class="flex justify-between items-center mb-4">
                <h2 id="city-modal-title" class="text-xl font-bold text-gray-900">إضافة مدينة جديدة</h2>
                <button onclick="closeCityModal()" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
            </div>

            <form id="city-form" class="space-y-4">
                <input type="hidden" id="city-id">
                <input type="hidden" id="city-governorate-id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        اسم المدينة <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="city-name"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           placeholder="مثال: الكرخ" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        بدل الإيفاد اليومي (دينار) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="city-daily-allowance" step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                           placeholder="مثال: 15000" required>
                </div>

                <div id="city-modal-message" class="hidden text-sm p-3 rounded"></div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" id="city-submit-btn"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
                        حفظ
                    </button>
                    <button type="button" onclick="closeCityModal()"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 px-4 py-2 rounded font-semibold">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let governoratesData = [];
        const canManageGovernorates = @json($canManageGovernorates);

        // تحميل المحافظات عند بدء الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            loadGovernorates();
        });

        async function loadGovernorates() {
            try {
                console.log('🔄 بدء تحميل المحافظات...');
                const response = await fetch('/governorates/list');
                console.log('📡 Response status:', response.status);
                console.log('📡 Response OK:', response.ok);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('✅ Data received:', data);

                if (data.success) {
                    governoratesData = data.governorates;
                    renderGovernorates(data.governorates);
                    updateGovernorateStats();
                } else {
                    showError('فشل تحميل المحافظات: ' + (data.message || 'خطأ غير معروف'));
                }
            } catch (error) {
                console.error('❌ Error in loadGovernorates:', error);
                showError('خطأ في الاتصال بالخادم: ' + error.message);
            }
        }

        function renderGovernorates(governorates) {
            const container = document.getElementById('governorates-list');

            if (governorates.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">لا توجد محافظات</div>';
                return;
            }

            container.innerHTML = governorates.map(gov => `
                <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-r from-blue-50 to-white" data-gov-id="${gov.id}" data-gov-name="${gov.name}">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">${gov.name}</h3>
                            <p class="text-sm text-gray-600">${gov.cities.length} مدينة</p>
                        </div>
                        ${canManageGovernorates ? `
                            <div class="flex gap-2">
                                <button onclick="openAddCityModal(${gov.id}, '${gov.name}')"
                                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded flex items-center gap-1">
                                    <span>➕</span>
                                    <span>مدينة</span>
                                </button>
                                <button onclick="editGovernorate(${gov.id})"
                                        class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded">
                                    تعديل
                                </button>
                                <button onclick="deleteGovernorate(${gov.id}, '${gov.name}')"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded">
                                    حذف
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    <!-- قائمة المدن -->
                    ${gov.cities.length > 0 ? `
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                            ${gov.cities.map(city => `
                                <div class="bg-white border border-gray-200 rounded p-3 flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold text-gray-800">${city.name}</p>
                                        <p class="text-xs text-gray-600">بدل الإيفاد: ${parseFloat(city.daily_allowance).toLocaleString('ar-IQ')} دينار</p>
                                    </div>
                                    ${canManageGovernorates ? `
                                        <div class="flex gap-1">
                                            <button onclick='editCity(${JSON.stringify(city)})'
                                                    class="px-2 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded">
                                                تعديل
                                            </button>
                                            <button onclick="deleteCity(${city.id}, '${city.name}')"
                                                    class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                                حذف
                                            </button>
                                        </div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    ` : '<p class="text-sm text-gray-500 text-center py-2">لا توجد مدن</p>'}
                </div>
            `).join('');
        }

        // ==================== إدارة المحافظات ====================

        function openAddGovernorateModal() {
            if (!canManageGovernorates) {
                alert('ليس لديك صلاحية لإدارة المحافظات');
                return;
            }

            document.getElementById('gov-modal-title').textContent = 'إضافة محافظة جديدة';
            document.getElementById('governorate-id').value = '';
            document.getElementById('governorate-name').value = '';
            document.getElementById('governorate-modal').classList.remove('hidden');
        }

        function editGovernorate(id) {
            if (!canManageGovernorates) {
                alert('ليس لديك صلاحية لتعديل المحافظات');
                return;
            }

            const gov = governoratesData.find(g => g.id === id);
            if (!gov) return;

            document.getElementById('gov-modal-title').textContent = 'تعديل محافظة';
            document.getElementById('governorate-id').value = gov.id;
            document.getElementById('governorate-name').value = gov.name;
            document.getElementById('governorate-modal').classList.remove('hidden');
        }

        function closeGovernorateModal() {
            document.getElementById('governorate-modal').classList.add('hidden');
            document.getElementById('gov-modal-message').classList.add('hidden');
        }

        document.getElementById('governorate-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('governorate-id').value;
            const name = document.getElementById('governorate-name').value.trim();

            if (!name) {
                showGovModalMessage('الرجاء إدخال اسم المحافظة', 'error');
                return;
            }

            const btn = document.getElementById('gov-submit-btn');
            btn.disabled = true;
            btn.textContent = 'جاري الحفظ...';

            try {
                const url = id ? `/governorates/${id}` : '/governorates';
                const method = id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ name })
                });

                const data = await response.json();

                if (data.success) {
                    showGovModalMessage(data.message, 'success');
                    setTimeout(() => {
                        closeGovernorateModal();
                        loadGovernorates();
                    }, 1000);
                } else {
                    showGovModalMessage(data.message, 'error');
                }
            } catch (error) {
                showGovModalMessage('خطأ في الاتصال بالخادم', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'حفظ';
            }
        });

        async function deleteGovernorate(id, name) {
            if (!canManageGovernorates) {
                alert('ليس لديك صلاحية لحذف المحافظات');
                return;
            }

            if (!confirm(`هل أنت متأكد من حذف المحافظة "${name}"؟\nسيتم حذف جميع المدن التابعة لها أيضاً.`)) return;

            try {
                const response = await fetch(`/governorates/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    loadGovernorates();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('خطأ في الاتصال بالخادم');
            }
        }

        // ==================== إدارة المدن ====================

        function openAddCityModal(governorateId, governorateName) {
            if (!canManageGovernorates) {
                alert('ليس لديك صلاحية لإدارة المدن');
                return;
            }

            document.getElementById('city-modal-title').textContent = `إضافة مدينة لـ ${governorateName}`;
            document.getElementById('city-id').value = '';
            document.getElementById('city-governorate-id').value = governorateId;
            document.getElementById('city-name').value = '';
            document.getElementById('city-daily-allowance').value = '';
            document.getElementById('city-modal').classList.remove('hidden');
        }

        function editCity(city) {
            if (!canManageGovernorates) {
                alert('ليس لديك صلاحية لتعديل المدن');
                return;
            }

            const gov = governoratesData.find(g => g.id === city.governorate_id);
            document.getElementById('city-modal-title').textContent = `تعديل مدينة في ${gov ? gov.name : 'المحافظة'}`;
            document.getElementById('city-id').value = city.id;
            document.getElementById('city-governorate-id').value = city.governorate_id;
            document.getElementById('city-name').value = city.name;
            document.getElementById('city-daily-allowance').value = city.daily_allowance;
            document.getElementById('city-modal').classList.remove('hidden');
        }

        function closeCityModal() {
            document.getElementById('city-modal').classList.add('hidden');
            document.getElementById('city-modal-message').classList.add('hidden');
        }

        document.getElementById('city-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('city-id').value;
            const name = document.getElementById('city-name').value.trim();
            const governorate_id = document.getElementById('city-governorate-id').value;
            const daily_allowance = document.getElementById('city-daily-allowance').value;

            if (!name || !daily_allowance) {
                showCityModalMessage('الرجاء إكمال جميع الحقول', 'error');
                return;
            }

            const btn = document.getElementById('city-submit-btn');
            btn.disabled = true;
            btn.textContent = 'جاري الحفظ...';

            try {
                const url = id ? `/cities/${id}` : '/cities';
                const method = id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ name, governorate_id, daily_allowance })
                });

                const data = await response.json();

                if (data.success) {
                    showCityModalMessage(data.message, 'success');
                    setTimeout(() => {
                        closeCityModal();
                        loadGovernorates();
                    }, 1000);
                } else {
                    showCityModalMessage(data.message, 'error');
                }
            } catch (error) {
                showCityModalMessage('خطأ في الاتصال بالخادم', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'حفظ';
            }
        });

        async function deleteCity(id, name) {
            if (!canManageGovernorates) {
                alert('ليس لديك صلاحية لحذف المدن');
                return;
            }

            if (!confirm(`هل أنت متأكد من حذف المدينة "${name}"؟`)) return;

            try {
                const response = await fetch(`/cities/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    loadGovernorates();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('خطأ في الاتصال بالخادم');
            }
        }

        // ==================== Helper Functions ====================

        function showError(message) {
            document.getElementById('governorates-list').innerHTML =
                `<div class="text-center py-8 text-red-600">${message}</div>`;
        }

        function showGovModalMessage(message, type) {
            const msg = document.getElementById('gov-modal-message');
            msg.textContent = message;
            msg.className = `text-sm p-3 rounded ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            msg.classList.remove('hidden');
        }

        function showCityModalMessage(message, type) {
            const msg = document.getElementById('city-modal-message');
            msg.textContent = message;
            msg.className = `text-sm p-3 rounded ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            msg.classList.remove('hidden');
        }

        // تحديث الإحصائيات
        function updateGovernorateStats() {
            let totalGovs = (governoratesData || []).length;
            let totalCities = (governoratesData || []).reduce((sum, g) => sum + (g.cities?.length || 0), 0);

            document.getElementById('total-govs').textContent = totalGovs;
            document.getElementById('total-cities').textContent = totalCities;
        }

        // تطبيع النص العربي للبحتالصحيح
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

        // البحث الفوري في المحافظات
        function filterGovernorates() {
            const searchInput = document.getElementById('search-input').value;
            const query = normalizeArabic(searchInput);
            const items = document.querySelectorAll('[data-gov-id]');
            const container = document.getElementById('governorates-list');
            let visibleCount = 0;

            items.forEach(item => {
                const name = normalizeArabic(item.getAttribute('data-gov-name'));
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
