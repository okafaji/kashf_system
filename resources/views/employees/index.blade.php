<x-app-layout>
    <x-slot name="header">
        <x-floating-toolbar :title="'إدارة المنتسبين'">
            <div class="flex items-center gap-3 flex-1 overflow-x-auto">
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 min-w-[120px] shrink-0">
                    <p class="text-gray-600 text-xs font-medium">إجمالي المنتسبين</p>
                    <p class="text-lg font-bold text-blue-600"><span id="total-employees">{{ $employees->total() }}</span></p>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg px-4 py-2 min-w-[120px] shrink-0">
                    <p class="text-gray-600 text-xs font-medium">عدد الأقسام</p>
                    <p class="text-lg font-bold text-purple-600"><span id="departments-count">{{ $departmentsCount ?? 0 }}</span></p>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <div class="bg-white border border-gray-300 rounded-lg px-3 py-2 w-56 max-w-full">
                        <input type="text" id="employee-search" value="{{ request('q') }}" placeholder="🔍 ابحث بالاسم أو الرقم أو القسم..."
                               class="w-full border-0 p-0 focus:ring-0 text-sm">
                    </div>
                    <button type="button" onclick="clearEmployeeSearch()" id="clear-search-btn" class="text-xs text-gray-600 hover:text-gray-800 whitespace-nowrap {{ request('q') ? '' : 'hidden' }}">مسح</button>
                </div>
                <form action="{{ route('employees.sync') }}" method="POST" class="shrink-0">
                    @csrf
                    <x-button-info>
                        تحديث من ملف الإكسل
                    </x-button-info>
                </form>
                <button type="button" onclick="if(window.history.length > 1){window.history.back();}else{window.location='{{ route('dashboard') }}';}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold px-4 py-2 rounded">
                    ← رجوع
                </button>
            </div>
        </x-floating-toolbar>
    </x-slot>

    <div class="py-12 pt-40" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-bold">قائمة المنتسبين (<span id="list-count">{{ $employees->total() }}</span>)</h3>
                </div>

                <table class="min-w-full divide-y divide-gray-200 border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم البصمة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">القسم</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الراتب</th>
                        </tr>
                    </thead>
                    <tbody id="employees-tbody" class="bg-white divide-y divide-gray-200">
                        @foreach($employees as $emp)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $emp->employee_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $emp->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $emp->department }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($emp->salary) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div id="no-results" class="hidden text-center py-4 px-4 mt-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                    ⚠️ لم يتم العثور على نتائج مطابقة
                </div>

                <div class="mt-4" id="pagination-container">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        let searchTimer;

        document.getElementById('employee-search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            const query = this.value;

            searchTimer = setTimeout(() => {
                liveSearchEmployees(query);
            }, 350);

            document.getElementById('clear-search-btn').classList.toggle('hidden', query === '');
        });

        function clearEmployeeSearch() {
            document.getElementById('employee-search').value = '';
            document.getElementById('clear-search-btn').classList.add('hidden');
            liveSearchEmployees('');
        }

        async function liveSearchEmployees(query) {
            try {
                const response = await fetch(`{{ route('employees.search.live') }}?q=${encodeURIComponent(query)}`);
                const data = await response.json();

                document.getElementById('total-employees').textContent = data.total;
                document.getElementById('departments-count').textContent = data.departmentsCount;
                document.getElementById('list-count').textContent = data.total;

                const tbody = document.getElementById('employees-tbody');
                const noResults = document.getElementById('no-results');

                if (data.employees.length === 0) {
                    tbody.innerHTML = '';
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                    tbody.innerHTML = data.employees.map(emp => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${emp.employee_id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${emp.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${emp.department || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${Number(emp.salary).toLocaleString('en-US')}</td>
                        </tr>
                    `).join('');
                }

                document.getElementById('pagination-container').innerHTML = '';
            } catch (error) {
                console.error('خطأ في البحث:', error);
            }
        }
    </script>
</x-app-layout>
