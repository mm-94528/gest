// public/assets/js/app.js - JavaScript principale
document.addEventListener('DOMContentLoaded', function() {
    
    // Conferma eliminazione
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
            e.preventDefault();
            
            if (confirm('Sei sicuro di voler eliminare questo elemento? Questa azione non può essere annullata.')) {
                const form = e.target.closest('form');
                if (form) {
                    form.submit();
                }
            }
        }
    });
    
    // Auto-hide alerts dopo 5 secondi
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Validazione form lato client
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Datatables inizializzazione
    if (typeof DataTable !== 'undefined') {
        const tables = document.querySelectorAll('.datatable');
        tables.forEach(function(table) {
            new DataTable(table, {
                language: {
                    url: '/assets/js/datatables-it.json'
                },
                pageLength: 25,
                responsive: true
            });
        });
    }
    
    // Select2 inizializzazione
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleziona...'
        });
    }
    
    // Date picker
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'd/m/Y',
            locale: 'it'
        });
        
        flatpickr('.datetimepicker', {
            enableTime: true,
            dateFormat: 'd/m/Y H:i',
            locale: 'it'
        });
    }
    
    // Gestione upload file
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const files = e.target.files;
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            for (let file of files) {
                if (file.size > maxSize) {
                    alert(`Il file ${file.name} è troppo grande. Dimensione massima: 10MB`);
                    e.target.value = '';
                    return;
                }
            }
        });
    });
    
    // Ricerca in tempo reale
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(function(input) {
        let timeout;
        input.addEventListener('input', function(e) {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                performSearch(e.target.value, e.target.dataset.searchUrl);
            }, 300);
        });
    });
    
    function performSearch(query, url) {
        if (query.length >= 2) {
            fetch(`${url}?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    updateSearchResults(data);
                })
                .catch(error => {
                    console.error('Errore nella ricerca:', error);
                });
        }
    }
    
    function updateSearchResults(data) {
        const container = document.querySelector('.search-results');
        if (container) {
            container.innerHTML = data.html || '';
        }
    }
    
    // Caricamento lazy delle tab
    const tabTriggers = document.querySelectorAll('[data-bs-toggle="tab"][data-lazy-url]');
    tabTriggers.forEach(function(trigger) {
        trigger.addEventListener('shown.bs.tab', function(e) {
            const url = e.target.dataset.lazyUrl;
            const targetPane = document.querySelector(e.target.dataset.bsTarget);
            
            if (targetPane && !targetPane.dataset.loaded) {
                targetPane.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Caricamento...</div>';
                
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        targetPane.innerHTML = html;
                        targetPane.dataset.loaded = 'true';
                    })
                    .catch(error => {
                        targetPane.innerHTML = '<div class="alert alert-danger">Errore nel caricamento dei dati</div>';
                    });
            }
        });
    });
});

// Funzioni utility globali
window.AppUtils = {
    
    // Formatta numeri come valuta
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('it-IT', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },
    
    // Formatta date
    formatDate: function(date) {
        return new Intl.DateTimeFormat('it-IT').format(new Date(date));
    },
    
    // Conferma azione
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    // Notifica toast
    toast: function(message, type = 'info') {
        // Implementazione toast personalizzata o Bootstrap toast
        console.log(`${type.toUpperCase()}: ${message}`);
    },
        // AJAX helper
    ajax: function(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const config = Object.assign(defaults, options);
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            });
    },
    
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};