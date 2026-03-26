## 📡 API Reference - نظام النسخ الاحتياطية

**الإصدار:** 1.0.0  
**الحالة:** ✅ جاهز

---

## 🔐 المصادقة والصلاحيات

### Required Headers
```http
Authorization: Bearer YOUR_TOKEN
X-CSRF-TOKEN: YOUR_CSRF_TOKEN
Content-Type: application/json
```

### Required Permissions
```
- auth          (تسجيل الدخول)
- manage-backups (إدارة النسخ)
```

### Required Role
```
- Admin (أو أي دور له permission: manage-backups)
```

---

## 📋 Endpoints

### 1. إنشاء نسخة احتياطية جديدة

#### Request
```http
POST /backups/create HTTP/1.1
Host: localhost:8000
X-CSRF-TOKEN: YOUR_TOKEN
Content-Type: application/json
```

#### cURL
```bash
curl -X POST http://localhost:8000/backups/create \
  -H "X-CSRF-TOKEN: YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

#### JavaScript (jQuery)
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$.post('/backups/create', function(response) {
    console.log('Backup created:', response);
});
```

#### JavaScript (Fetch)
```javascript
fetch('/backups/create', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

#### Response - Success (200)
```json
{
  "success": true,
  "message": "تم إنشاء النسخة الاحتياطية بنجاح",
  "timestamp": "2024_01_15_14_30_45",
  "backup_folder": "/path/to/backup_folder"
}
```

#### Response - Error (500)
```json
{
  "success": false,
  "message": "فشل إنشاء النسخة الاحتياطية: mysqldump command failed"
}
```

---

### 2. عرض قائمة النسخ المتاحة

#### Request
```http
GET /backups/list HTTP/1.1
Host: localhost:8000
```

#### cURL
```bash
curl http://localhost:8000/backups/list
```

#### JavaScript (Fetch)
```javascript
fetch('/backups/list')
  .then(response => response.json())
  .then(data => console.log('Backups:', data.backups))
  .catch(error => console.error('Error:', error));
```

#### Response - Success (200)
```json
{
  "success": true,
  "backups": [
    {
      "folder": "backup_2024_01_15_14_30_45",
      "timestamp": "2024_01_15_14_30_45",
      "date": "2024/01/15 14:30:45",
      "size": "150.45 MB",
      "files": 2
    },
    {
      "folder": "backup_2024_01_14_10_15_20",
      "timestamp": "2024_01_14_10_15_20",
      "date": "2024/01/14 10:15:20",
      "size": "148.30 MB",
      "files": 2
    }
  ]
}
```

#### Response - No Backups (200)
```json
{
  "success": true,
  "backups": []
}
```

#### Response - Error (500)
```json
{
  "success": false,
  "message": "فشل جلب قائمة النسخ: permission denied"
}
```

---

### 3. تحميل نسخة احتياطية

#### Request
```http
GET /backups/download/2024_01_15_14_30_45 HTTP/1.1
Host: localhost:8000
```

#### URL Parameters
```
{timestamp}    - معرّف النسخة (مثال: 2024_01_15_14_30_45)
```

#### cURL
```bash
curl http://localhost:8000/backups/download/2024_01_15_14_30_45 \
  -o backup.zip
```

#### JavaScript (Browser)
```javascript
// طريقة 1: Direct link
window.location.href = `/backups/download/${timestamp}`;

// طريقة 2: Download attribute
const link = document.createElement('a');
link.href = `/backups/download/${timestamp}`;
link.download = `kashf_backup_${timestamp}.zip`;
link.click();
```

#### Response - Success (200)
```
Binary File: kashf_backup_2024_01_15_14_30_45.zip
Content-Type: application/zip
Content-Length: 150MB
```

#### Response - Error (404)
```json
{
  "success": false,
  "message": "النسخة الاحتياطية غير موجودة"
}
```

#### Response - Error (500)
```json
{
  "success": false,
  "message": "فشل تحميل النسخة: disk space error"
}
```

---

### 4. حذف نسخة احتياطية

#### Request
```http
DELETE /backups/delete/2024_01_15_14_30_45 HTTP/1.1
Host: localhost:8000
X-CSRF-TOKEN: YOUR_TOKEN
```

#### URL Parameters
```
{timestamp}    - معرّف النسخة (مثال: 2024_01_15_14_30_45)
```

#### cURL
```bash
curl -X DELETE http://localhost:8000/backups/delete/2024_01_15_14_30_45 \
  -H "X-CSRF-TOKEN: YOUR_TOKEN"
```

#### JavaScript (Fetch)
```javascript
fetch(`/backups/delete/${timestamp}`, {
  method: 'DELETE',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  }
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        alert('تم حذف النسخة بنجاح');
    }
})
.catch(error => console.error('Error:', error));
```

#### Response - Success (200)
```json
{
  "success": true,
  "message": "تم حذف النسخة الاحتياطية بنجاح"
}
```

#### Response - Error (404)
```json
{
  "success": false,
  "message": "النسخة الاحتياطية غير موجودة"
}
```

#### Response - Error (500)
```json
{
  "success": false,
  "message": "فشل حذف النسخة: permission denied"
}
```

---

## 📊 Status Codes

| Code | المعنى | الإجراء |
|------|--------|--------|
| 200 | OK - نجح الطلب | تقدّم للخطوة التالية |
| 404 | Not Found - النسخة غير موجودة | تحقق من الـ timestamp |
| 401 | Unauthorized - غير مصرح | سجّل الدخول |
| 403 | Forbidden - لا توجد صلاحيات | اطلب من Admin |
| 500 | Server Error - خطأ في السيرفر | راجع اللوجات |

---

## 🔄 Response Format

### الإجابة الناجحة
```json
{
  "success": true,
  "message": "رسالة نجاح بالعربية",
  "data": {
    // بيانات إضافية حسب الـ endpoint
  }
}
```

### الإجابة الخاطئة
```json
{
  "success": false,
  "message": "رسالة خطأ بالعربية",
  "error": "detailed error message"
}
```

---

## 🧪 أمثلة عملية

### مثال 1: إنشاء وتحميل نسخة

```bash
#!/bin/bash

# Step 1: إنشاء نسخة
echo "إنشاء نسخة احتياطية..."
RESPONSE=$(curl -X POST http://localhost:8000/backups/create \
  -H "X-CSRF-TOKEN: YOUR_TOKEN" \
  -s)

TIMESTAMP=$(echo $RESPONSE | grep -o '"timestamp":"[^"]*' | cut -d'"' -f4)

echo "النسخة: $TIMESTAMP"

# Step 2: انتظر قليلا
sleep 5

# Step 3: تحميل النسخة
echo "تحميل النسخة..."
curl http://localhost:8000/backups/download/$TIMESTAMP \
  -o kashf_backup_$TIMESTAMP.zip

echo "تم التحميل: kashf_backup_$TIMESTAMP.zip"
```

### مثال 2: جدولة النسخ الدورية (Cron Job)

```bash
#!/bin/bash
# اسم الملف: backup-daily.sh
# الوقت: ليلاً الساعة 2 صباحاً

BACKUP_URL="http://localhost:8000/backups/create"
CSRF_TOKEN="your_token_here"

# إنشاء نسخة
curl -X POST "$BACKUP_URL" \
  -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
  -H "Content-Type: application/json"

# إرسال تنويه بالبريد (اختياري)
echo "تم إنشاء نسخة احتياطية" | mail -s "Backup Done" admin@example.com
```

**إضافة إلى crontab:**
```bash
# كل يوم الساعة 2 صباحاً
0 2 * * * /path/to/backup-daily.sh
```

### مثال 3: تطبيق Node.js للنسخ

```javascript
const axios = require('axios');

const backupAPI = axios.create({
  baseURL: 'http://localhost:8000',
  headers: {
    'X-CSRF-TOKEN': process.env.CSRF_TOKEN
  }
});

async function createBackup() {
  try {
    const response = await backupAPI.post('/backups/create');
    console.log('✅ Backup created:', response.data.timestamp);
    return response.data.timestamp;
  } catch (error) {
    console.error('❌ Error:', error.response.data.message);
  }
}

async function listBackups() {
  try {
    const response = await backupAPI.get('/backups/list');
    console.log('📦 Available backups:');
    response.data.backups.forEach(backup => {
      console.log(`  - ${backup.date} (${backup.size})`);
    });
  } catch (error) {
    console.error('❌ Error:', error.response.data.message);
  }
}

async function downloadBackup(timestamp) {
  try {
    const response = await backupAPI.get(`/backups/download/${timestamp}`, {
      responseType: 'arraybuffer'
    });
    
    const fs = require('fs');
    fs.writeFileSync(`kashf_backup_${timestamp}.zip`, response.data);
    console.log('✅ Backup downloaded');
  } catch (error) {
    console.error('❌ Error:', error.response.data.message);
  }
}

async function deleteBackup(timestamp) {
  try {
    const response = await backupAPI.delete(`/backups/delete/${timestamp}`);
    console.log('✅', response.data.message);
  } catch (error) {
    console.error('❌ Error:', error.response.data.message);
  }
}

// الاستخدام:
(async () => {
  const timestamp = await createBackup();
  await listBackups();
  await downloadBackup(timestamp);
})();
```

### مثال 4: Python Script للنسخ

```python
import requests
import json

class BackupManager:
    def __init__(self, base_url, csrf_token):
        self.base_url = base_url
        self.headers = {
            'X-CSRF-TOKEN': csrf_token,
            'Content-Type': 'application/json'
        }
    
    def create_backup(self):
        """إنشاء نسخة احتياطية جديدة"""
        response = requests.post(
            f'{self.base_url}/backups/create',
            headers=self.headers
        )
        data = response.json()
        if data['success']:
            print(f"✅ النسخة: {data['timestamp']}")
            return data['timestamp']
        else:
            print(f"❌ خطأ: {data['message']}")
            return None
    
    def list_backups(self):
        """عرض قائمة النسخ"""
        response = requests.get(f'{self.base_url}/backups/list')
        data = response.json()
        if data['success']:
            print("📦 النسخ المتاحة:")
            for backup in data['backups']:
                print(f"  - {backup['date']} ({backup['size']})")
        else:
            print(f"❌ خطأ: {data['message']}")
    
    def download_backup(self, timestamp):
        """تحميل نسخة"""
        response = requests.get(
            f'{self.base_url}/backups/download/{timestamp}'
        )
        filename = f'kashf_backup_{timestamp}.zip'
        with open(filename, 'wb') as f:
            f.write(response.content)
        print(f"✅ تم التحميل: {filename}")
    
    def delete_backup(self, timestamp):
        """حذف نسخة"""
        response = requests.delete(
            f'{self.base_url}/backups/delete/{timestamp}',
            headers=self.headers
        )
        data = response.json()
        if data['success']:
            print(f"✅ {data['message']}")
        else:
            print(f"❌ {data['message']}")

# الاستخدام:
manager = BackupManager('http://localhost:8000', 'YOUR_CSRF_TOKEN')
timestamp = manager.create_backup()
manager.list_backups()
manager.download_backup(timestamp)
manager.delete_backup(timestamp)
```

---

## 📱 Postman Collection

```json
{
  "info": {
    "name": "Backup API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Create Backup",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "X-CSRF-TOKEN",
            "value": "{{csrf_token}}"
          }
        ],
        "url": {
          "raw": "{{base_url}}/backups/create",
          "host": ["{{base_url}}"],
          "path": ["backups", "create"]
        }
      }
    },
    {
      "name": "List Backups",
      "request": {
        "method": "GET",
        "url": {
          "raw": "{{base_url}}/backups/list",
          "host": ["{{base_url}}"],
          "path": ["backups", "list"]
        }
      }
    },
    {
      "name": "Download Backup",
      "request": {
        "method": "GET",
        "url": {
          "raw": "{{base_url}}/backups/download/{{timestamp}}",
          "host": ["{{base_url}}"],
          "path": ["backups", "download", "{{timestamp}}"]
        }
      }
    },
    {
      "name": "Delete Backup",
      "request": {
        "method": "DELETE",
        "header": [
          {
            "key": "X-CSRF-TOKEN",
            "value": "{{csrf_token}}"
          }
        ],
        "url": {
          "raw": "{{base_url}}/backups/delete/{{timestamp}}",
          "host": ["{{base_url}}"],
          "path": ["backups", "delete", "{{timestamp}}"]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    },
    {
      "key": "csrf_token",
      "value": "YOUR_CSRF_TOKEN"
    },
    {
      "key": "timestamp",
      "value": "2024_01_15_14_30_45"
    }
  ]
}
```

---

## ⏱️ Response Times (متوقع)

| Operation | Time |
|-----------|------|
| Create Backup (50 MB) | 30-45 سثانية |
| List Backups | < 1 ثانية |
| Download Backup (150 MB) | 5-15 ثانية |
| Delete Backup | < 1 ثانية |

---

## 🔗 محتويات الـ Response

### Backup Object
```json
{
  "folder": "backup_2024_01_15_14_30_45",
  "timestamp": "2024_01_15_14_30_45",
  "date": "2024/01/15 14:30:45",
  "size": "150.45 MB",
  "files": 2
}
```

### Files in Backup
```
├── database_2024_01_15_14_30_45.sql   (100-200 MB)
└── code_2024_01_15_14_30_45.zip       (50-150 MB)
```

---

## 📚 المراجع الإضافية

- [API Security Best Practices](./BACKUP_SYSTEM_USAGE.md#أمان-البيانات)
- [Error Troubleshooting](./BACKUP_SYSTEM_USAGE.md#استكشاف-الأخطاء)
- [Full Documentation](./BACKUP_GUIDE.md)

---

**تم الإعداد:** يناير 2025  
**الحالة:** جاهز للاستخدام ✅
