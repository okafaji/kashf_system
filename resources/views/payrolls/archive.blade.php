<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right">
            {{ __('أرشيف الإيفادات المطبوعة') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="mb-6 flex justify-between items-center">
                    <form action="{{ route('payrolls.archive') }}" method="GET" class="flex gap-2 w-full max-w-md">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم أو رقم الأمر..." class="w-full border-gray-300 rounded-lg">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">بحث</button>
                    </form>
                    <a href="{{ route('payrolls.index') }}" class="text-indigo-600 hover:underline">العودة للسجل النشط ←</a>
                </div>

                <table class="min-w-full border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border p-3 text-right">الاسم</th>
                            <th class="border p-3 text-right">رقم الأمر</th>
                            <th class="border p-3 text-right">الوجهة</th>
                            <th class="border p-3 text-right">الفترة</th>
                            <th class="border p-3 text-right">المجموع</th>
                            <th class="border p-3 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedPayrolls as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-3">{{ $item->name }}</td>
                                <td class="border p-3 text-blue-700 font-bold">{{ $item->admin_order_no }}</td>
                                <td class="border p-3">{{ $item->destination }}</td>
                                <td class="border p-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($item->start_date)->format('Y/m/d') }} إلى {{ \Carbon\Carbon::parse($item->end_date)->format('Y/m/d') }}</td>
                                <td class="border p-3 font-bold">{{ number_format($item->total_amount) }} د.ع</td>
                                <td class="border p-3 text-center">
                                    <a href="{{ route('payrolls.print_multiple', ['group_no' => ($item->kashf_no ?? $item->receipt_no), 'ids' => $item->id]) }}" target="_blank" class="text-green-600 hover:text-green-800 font-medium">إعادة طباعة</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-gray-400 italic">لا توجد بيانات مؤرشفة تطابق بحثك..</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $archivedPayrolls->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
