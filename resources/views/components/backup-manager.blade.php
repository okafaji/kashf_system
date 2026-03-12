<!-- Backup Manager Modal and Button -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-900">النسخ الاحتياطية</h3>
        <button id="backup-create-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-semibold">
            💾 إنشاء نسخة احتياطية
        </button>
    </div>
    
    <div id="backups-list" class="bg-gray-50 rounded p-4 min-h-[200px]">
        <div class="text-center py-8 text-gray-500">
            جاري تحميل النسخ الاحتياطية...
        </div>
    </div>
</div>

<!-- Modal -->
<div id="backup-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">إنشاء نسخة احتياطية</h2>
            <button id="backup-modal-close" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <p class="text-gray-600 mb-6">
            سيتم إنشاء نسخة احتياطية كاملة من قاعدة البيانات والأكواد. قد يستغرق بعض الوقت حسب حجم البيانات.
        </p>

        <button id="backup-create-confirm" 
                onclick="createBackup()"
                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-semibold">
            إنشاء نسخة احتياطية
        </button>

        <div id="backup-status" class="text-sm text-gray-600 mt-4"></div>

        <button type="button" id="backup-modal-close-btn" 
                onclick="document.getElementById('backup-modal').classList.add('hidden')"
                class="w-full mt-4 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 rounded font-semibold">
            إغلاق
        </button>
    </div>
</div>
