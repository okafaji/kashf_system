$dest = "public/js/xlsx.full.min.js"
$url = "https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"
Write-Host "🔄 جاري تحميل مكتبة SheetJS من: $url"
try {
    Invoke-WebRequest -Uri $url -OutFile $dest -UseBasicParsing
    Write-Host "✅ تم تحميل المكتبة بنجاح إلى: $dest"
}
catch {
    Write-Host "❌ حدث خطأ أثناء التحميل: $($_.Exception.Message)"
    exit 1
}
if ((Get-Item $dest).Length -lt 100000) {
    Write-Host "❌ الملف الذي تم تحميله صغير جداً أو غير صحيح!"
    exit 2
}
Write-Host "📦 مكتبة SheetJS جاهزة للاستخدام محلياً."
