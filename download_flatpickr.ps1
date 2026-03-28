# سكربت PowerShell لتنزيل flatpickr تلقائياً
# شغّل هذا الملف من مجلد المشروع الرئيسي (أو من public/js)

$dest = "public/js"
if (!(Test-Path $dest)) { New-Item -ItemType Directory -Path $dest }

Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js" -OutFile "$dest/flatpickr.min.js"
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" -OutFile "$dest/flatpickr.min.css"
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js" -OutFile "$dest/ar.js"

Write-Host "تم تنزيل جميع ملفات flatpickr بنجاح في $dest" -ForegroundColor Green
