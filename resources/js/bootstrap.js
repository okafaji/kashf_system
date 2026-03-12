import Alpine from 'alpinejs';
import $ from 'jquery';
import 'select2/dist/css/select2.min.css';
import 'bootstrap';
import './backup-manager.js';

window.$ = $;
window.jQuery = $;
window.Alpine = Alpine;

// استيراد Select2 وتطبيقه على jQuery بشكل ديناميكي
(async () => {
    try {
        const select2Module = await import('select2');
        select2Module.default($);
        console.log('✅ Select2 loaded successfully');
        if (window.initPayrollNameSearch) {
            window.initPayrollNameSearch();
        }
    } catch (error) {
        console.error('❌ Failed to load Select2:', error);
    }
})();

Alpine.start();
