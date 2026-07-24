import './stimulus_bootstrap.js';
import './styles/app.css';

function initDataTable() {
    document.querySelectorAll('table:not(.dataTable)').forEach(function(table) {
        if (table.rows && table.rows.length > 1) {
            var config = {
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50],
                order: [],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_ per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: {
                        previous: '&laquo;',
                        next: '&raquo;'
                    },
                    emptyTable: 'No data available'
                },
                dom: '<"top"f>rt<"bottom"lip>',
                columnDefs: []
            };

            var actionColIdx = -1;
            var headers = table.querySelectorAll('thead th');
            headers.forEach(function(th, idx) {
                if (th.textContent.trim().toLowerCase() === 'actions') {
                    actionColIdx = idx;
                }
            });

            if (actionColIdx >= 0) {
                config.columnDefs.push({
                    orderable: false,
                    targets: actionColIdx
                });
            }

            $(table).DataTable(config);
        }
    });
}

function initDeleteConfirmations() {
    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
        if (form._swalBound) return;
        form._swalBound = true;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var f = this;
            Swal.fire({
                title: f.dataset.confirmTitle || 'Are you sure?',
                text: f.dataset.confirm || 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    f.submit();
                }
            });
        });
    });
}

function initFlashMessages() {
    var flashMessages = window.flashMessages;
    if (flashMessages) {
        Object.keys(flashMessages).forEach(function(type) {
            flashMessages[type].forEach(function(message) {
                var iconMap = {
                    'success': 'success',
                    'danger': 'error',
                    'error': 'error',
                    'warning': 'warning',
                    'info': 'info'
                };
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: iconMap[type] || 'info',
                    title: message,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: function(toast) {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            });
        });
        window.flashMessages = null;
    }
}

function initAll() {
    initDataTable();
    initDeleteConfirmations();
    initFlashMessages();
}

document.addEventListener('DOMContentLoaded', initAll);
document.addEventListener('turbo:load', initAll);
