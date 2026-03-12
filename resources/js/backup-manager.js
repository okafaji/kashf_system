// ============================================
// Backup Management Script
// ============================================

function initBackupManager() {
    const createDatabaseBtn = document.getElementById('backup-create-database-btn');
    const createCodeBtn = document.getElementById('backup-create-code-btn');
    const modal = document.getElementById('backup-modal');
    const closeBtn = document.getElementById('backup-modal-close');
    const closeBtnBottom = document.getElementById('backup-modal-close-btn');
    const createConfirmBtn = document.getElementById('backup-create-confirm');
    const backupsList = document.getElementById('backups-list');
    const statusMessage = document.getElementById('backup-status');

    if (!createDatabaseBtn && !createCodeBtn) return;

    // فتح Modal لـ نسخ قاعدة البيانات
    if (createDatabaseBtn) {
        createDatabaseBtn.addEventListener('click', () => {
            document.getElementById('backup-type-label').textContent = 'نسخ قاعدة البيانات';
            document.getElementById('backup-type-input').value = 'database';
            modal.classList.remove('hidden');
            loadBackupsList();
        });
    }

    // فتح Modal لـ نسخ الأكواد
    if (createCodeBtn) {
        createCodeBtn.addEventListener('click', () => {
            document.getElementById('backup-type-label').textContent = 'نسخ الأكواد';
            document.getElementById('backup-type-input').value = 'code';
            modal.classList.remove('hidden');
            loadBackupsList();
        });
    }

    // إغلاق Modal
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

    if (closeBtnBottom) {
        closeBtnBottom.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

    // إغلاق Modal عند الضغط خارجه
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }

    // ربط زر الإنشاء
    if (createConfirmBtn) {
        createConfirmBtn.addEventListener('click', createBackup);
    }
}

async function createBackup() {
    const btn = document.getElementById('backup-create-confirm');
    const statusMessage = document.getElementById('backup-status');
    const backupType = document.getElementById('backup-type-input').value;

    const url = backupType === 'database' ? '/backups/database' : '/backups/code';

    btn.disabled = true;
    btn.innerHTML = 'جاري الإنشاء...';
    statusMessage.className = 'text-sm text-blue-600 mt-2';
    statusMessage.textContent = 'جاري إنشاء النسخة...';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        if (data.success) {
            statusMessage.className = 'text-sm text-green-600 mt-2';
            statusMessage.textContent = `✅ ${data.message}`;
            setTimeout(() => loadBackupsList(), 500);
        } else {
            statusMessage.className = 'text-sm text-red-600 mt-2';
            statusMessage.textContent = `❌ ${data.message}`;
        }
    } catch (error) {
        statusMessage.className = 'text-sm text-red-600 mt-2';
        statusMessage.textContent = `❌ خطأ: ${error.message}`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'إنشاء نسخة';
    }
}

async function loadBackupsList() {
    const backupsList = document.getElementById('backups-list');
    if (!backupsList) return; // Check if element exists

    backupsList.innerHTML = '<div class="text-center py-4">جاري تحميل النسخ...</div>';

    try {
        const response = await fetch('/backups/list');
        const data = await response.json();

        if (!data.success || data.backups.length === 0) {
            backupsList.innerHTML = '<div class="text-center py-4 text-gray-500">لا توجد نسخ احتياطية</div>';
            return;
        }

        backupsList.innerHTML = data.backups.map((backup) => `
            <div class="bg-gray-50 p-4 rounded border border-gray-200 mb-3">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-bold text-gray-900">${backup.date}</p>
                        <p class="text-xs text-gray-600 mt-1">❯ ${backup.files} ملف - حجم: ${backup.size} / ${backup.type_label || 'غير محدد'}</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openBackupFolder('${backup.timestamp}')"
                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded"
                                title="فتح المجلد">
                            📁
                        </button>
                        <button onclick="downloadBackup('${backup.timestamp}')"
                                class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded">
                            تحميل
                        </button>
                        <button onclick="deleteBackup('${backup.timestamp}')"
                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                            حذف
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

    } catch (error) {
        backupsList.innerHTML = `<div class="text-center py-4 text-red-600">خطأ في تحميل النسخ: ${error.message}</div>`;
    }
}

async function openBackupFolder(timestamp) {
    try {
        const response = await fetch(`/backups/open-folder/${timestamp}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            // تم فتح المجلد بنجاح
        } else {
            alert(data.message || 'فشل فتح المجلد');
        }
    } catch (error) {
        alert(`خطأ في فتح المجلد: ${error.message}`);
    }
}

async function downloadBackup(timestamp) {
    try {
        window.location.href = `/backups/download/${timestamp}`;
    } catch (error) {
        alert(`خطأ في التحميل: ${error.message}`);
    }
}

async function deleteBackup(timestamp) {
    if (!confirm('هل أنت متأكد من حذف هذه النسخة الاحتياطية؟')) {
        return;
    }

    try {
        const response = await fetch(`/backups/delete/${timestamp}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            loadBackupsList();
        } else {
            alert(`خطأ: ${data.message}`);
        }
    } catch (error) {
        alert(`خطأ: ${error.message}`);
    }
}

// تهيئة عند تحميل الصفحة
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initBackupManager();
        loadBackupsList();
    });
} else {
    initBackupManager();
    loadBackupsList();
}

window.initBackupManager = initBackupManager;
window.createBackup = createBackup;
window.loadBackupsList = loadBackupsList;
window.openBackupFolder = openBackupFolder;
window.downloadBackup = downloadBackup;
window.deleteBackup = deleteBackup;
