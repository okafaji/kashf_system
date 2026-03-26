<x-app-layout>
    <x-slot name="header">
        <div class="fixed top-16 inset-x-0 z-40 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-4" dir="rtl">
                <div class="flex items-center justify-between gap-3 flex-wrap mb-0">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight whitespace-nowrap shrink-0 mb-0">
                        {{ __('إدارة التواقيع') }}
                    </h2>
                    <x-button-success>
                        <a href="{{ route('signatures.create') }}" class="text-white">
                            إضافة توقيع جديد +
                        </a>
                    </x-button-success>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-4 shadow-sm text-right" dir="rtl">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse" dir="rtl">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr class="text-gray-600 text-sm font-bold">
                                <th class="p-4">نوع المسؤولية</th>
                                <th class="p-4">الاسم</th>
                                <th class="p-4">الرتبة/المنصب</th>
                                <th class="p-4 text-center">حالة التفعيل</th>
                                <th class="p-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($signatures as $signature)
                                <tr class="hover:bg-blue-50/50 transition duration-150">
                                    <td class="p-4 text-sm">
                                        @php
                                            $roleLabels = [
                                                1 => 'مسؤول وحدة',
                                                2 => 'مسؤول الشعبة',
                                                3 => 'التدقيق',
                                                4 => 'رئيس قسم الشؤون المالية',
                                            ];
                                        @endphp
                                        {{ $roleLabels[$signature->responsibility_code] ?? '-' }}
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">{{ $signature->name }}</div>
                                    </td>
                                    <td class="p-4 text-sm">{{ $signature->title }}</td>
                                    <td class="p-4 text-center">
                                        @if($signature->is_active)
                                            <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                ✅ نشط
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                                ❌ غير نشط
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center gap-1">
                                            <a href="{{ route('signatures.edit', $signature->id) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition ease-in-out duration-150">
                                                تعديل
                                            </a>

                                            <form action="{{ route('signatures.toggleActive', $signature->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                                    {{ $signature->is_active ? '⛔ إلغاء تفعيل' : '✅ تفعيل' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('signatures.destroy', $signature->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500 italic">لا توجد توقيعات حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
