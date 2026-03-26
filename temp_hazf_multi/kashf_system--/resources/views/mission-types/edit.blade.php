<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">✎ تعديل ايفاد خارج البلد</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow p-6" dir="rtl">
                <!-- الأخطاء -->
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <h3 class="font-bold mb-2">خطأ في المدخلات:</h3>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('mission-types.update', $missionType->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- نوع الإيفاد -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            نوع الإيفاد <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $missionType->name) }}"
                               placeholder="مثال: خارج القطر/1"
                               class="w-full px-3 py-2 border border-gray-300 rounded text-sm @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- مستوى المسؤولية -->
                    <div>
                        <label for="responsibility_level" class="block text-sm font-medium text-gray-700 mb-2">
                            مستوى المسؤولية <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="responsibility_level" name="responsibility_level"
                               value="{{ old('responsibility_level', $missionType->responsibility_level) }}"
                               placeholder="مثال: منتسب"
                               class="w-full px-3 py-2 border border-gray-300 rounded text-sm @error('responsibility_level') border-red-500 @enderror"
                               required>
                        @error('responsibility_level')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- المبلغ اليومي -->
                    <div>
                        <label for="daily_rate" class="block text-sm font-medium text-gray-700 mb-2">
                            المبلغ اليومي (دينار) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="daily_rate" name="daily_rate"
                               value="{{ old('daily_rate', $missionType->daily_rate) }}"
                               step="0.01" min="0" max="999999.99"
                               placeholder="30000.00"
                               class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-bold text-green-700 @error('daily_rate') border-red-500 @enderror"
                               required>
                        @error('daily_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- معلومات إضافية -->
                    <div class="bg-gray-100 p-3 rounded text-sm text-gray-700">
                        <p><strong>تم الإنشاء:</strong> {{ $missionType->created_at->format('Y/m/d H:i') }}</p>
                        <p><strong>آخر تحديث:</strong> {{ $missionType->updated_at->format('Y/m/d H:i') }}</p>
                    </div>

                    <!-- الأزرار -->
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
                            ✓ حفظ التغييرات
                        </button>
                        <a href="{{ route('mission-types.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded font-semibold">
                            ✕ إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
