<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" dir="rtl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">إدارة النسخ الاحتياطية</h2>
                <a href="{{ route('dashboard') }}"
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded font-semibold text-sm">
                    الرجوع للوحة الرئيسية
                </a>
            </div>

            <div class="mb-6 bg-white rounded-lg shadow p-6" id="backups">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">النسخ الاحتياطية</h3>
                    <div class="flex gap-2">
                        <button id="backup-create-database-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold text-sm">
                            💾 نسخ قاعدة البيانات
                        </button>
                        <button id="backup-create-code-btn" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded font-semibold text-sm">
                            📁 نسخ الأكواد
                        </button>
                    </div>
                </div>

                <div id="backups-list" class="bg-gray-50 rounded p-4 min-h-[200px]">
                    <div class="text-center py-8 text-gray-500">
                        جاري تحميل النسخ الاحتياطية...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="backup-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4" dir="rtl">
            <div class="flex justify-between items-center mb-4">
                <h2 id="backup-type-label" class="text-xl font-bold text-gray-900">إنشاء نسخة احتياطية</h2>
                <button id="backup-modal-close" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
            </div>

            <p class="text-gray-600 mb-6">
                سيتم إنشاء نسخة احتياطية. قد يستغرق بعض الوقت حسب حجم البيانات.
            </p>

            <input type="hidden" id="backup-type-input" value="">

            <button id="backup-create-confirm"
                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-semibold">
                إنشاء النسخة الاحتياطية
            </button>

            <div id="backup-status" class="text-sm text-gray-600 mt-4"></div>

            <button type="button" id="backup-modal-close-btn"
                    class="w-full mt-4 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 rounded font-semibold">
                إغلاق
            </button>
        </div>
    </div>
</x-app-layout>
