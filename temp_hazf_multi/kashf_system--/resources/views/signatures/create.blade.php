<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('إضافة توقيع جديد') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('signatures.store') }}" method="POST" dir="rtl">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">رقم المسؤولية <span class="text-red-500">*</span></label>
                        <select name="responsibility_code" class="form-input w-full" required>
                            <option value="">اختر نوع المسؤولية</option>
                            <option value="1" {{ old('responsibility_code') == 1 ? 'selected' : '' }}>مسؤول وحدة</option>
                            <option value="2" {{ old('responsibility_code') == 2 ? 'selected' : '' }}>مسؤول الشعبة</option>
                            <option value="3" {{ old('responsibility_code') == 3 ? 'selected' : '' }}>قسم التدقيق</option>
                            <option value="4" {{ old('responsibility_code') == 4 ? 'selected' : '' }}>رئيس قسم الشؤون المالية</option>
                        </select>
                        @error('responsibility_code')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">الاسم الكامل <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input w-full" placeholder="أدخل اسم المسؤول" required value="{{ old('name') }}">
                        @error('name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex gap-1">
                        <button type="submit" class="inline-flex items-center px-5 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">حفظ التوقيع</button>
                        <a href="{{ route('signatures.index') }}" class="inline-flex items-center px-5 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">إلغاء والعودة</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
