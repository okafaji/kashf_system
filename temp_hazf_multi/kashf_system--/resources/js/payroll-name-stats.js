function initPayrollNameSearch() {
    const $ = window.$;
    if (!$ || !$.fn || !$.fn.select2) {
        setTimeout(initPayrollNameSearch, 300);
        return;
    }

    const $select = $('#payroll_name_search');
    if ($select.length === 0) {
        return;
    }

    if ($select.data('select2')) {
        return;
    }

    $select.select2({
        placeholder: 'ابحث بالاسم الكامل...',
        allowClear: true,
        width: '100%',
        dir: 'rtl',
        dropdownParent: $('body'),
        minimumResultsForSearch: 0,
        minimumInputLength: 2,
        ajax: {
            url: '/payrolls/name-suggest',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    term: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            }
        }
    }).on('select2:select', function(e) {
        const name = e.params.data ? (e.params.data.id || e.params.data.text) : '';
        if (name) {
            window.location.href = `/payrolls/stats?name=${encodeURIComponent(name)}`;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPayrollNameSearch);
} else {
    initPayrollNameSearch();
}

window.initPayrollNameSearch = initPayrollNameSearch;
