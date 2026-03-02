/**
 * DataTables Initialization Helper
 * Provides common functionality for DataTables across the application
 */

class DataTablesManager {
    constructor(tableId, apiUrl, columns, options = {}) {
        this.tableId = tableId;
        this.apiUrl = apiUrl;
        this.columns = columns;
        this.options = {
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
            },
            ...options
        };
        this.table = null;
    }

    init() {
        this.table = $(`#${this.tableId}`).DataTable({
            ...this.options,
            ajax: {
                url: this.apiUrl,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: this.columns
        });

        return this.table;
    }

    reload() {
        if (this.table) {
            this.table.ajax.reload();
        }
    }

    search(term) {
        if (this.table) {
            this.table.search(term).draw();
        }
    }

    destroy() {
        if (this.table) {
            this.table.destroy();
        }
    }
}

/**
 * Form Handler Helper
 * Provides common form functionality
 */
class FormHandler {
    constructor(formId, submitUrl, method = 'POST') {
        this.formId = formId;
        this.submitUrl = submitUrl;
        this.method = method;
        this.form = $(`#${formId}`);
    }

    init(successCallback, errorCallback) {
        this.form.on('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(this.form[0]);
            const isUpdate = this.form.find('[name="id"]').val() || this.form.find('[name="user_id"]').val() || this.form.find('[name="tenant_id"]').val();

            axios({
                method: this.method,
                url: this.submitUrl,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                this.clearErrors();
                if (successCallback) {
                    successCallback(response, isUpdate);
                }
            })
            .catch(error => {
                if (error.response?.data?.errors) {
                    this.displayErrors(error.response.data.errors);
                }
                if (errorCallback) {
                    errorCallback(error);
                }
            });
        });
    }

    displayErrors(errors) {
        this.clearErrors();

        Object.keys(errors).forEach(field => {
            const input = this.form.find(`[name="${field}"]`);
            if (input.length) {
                input.addClass('is-invalid');
                const feedback = input.closest('.form-group').find('.invalid-feedback');
                if (feedback.length) {
                    feedback.text(errors[field][0]).show();
                }
            }
        });
    }

    clearErrors() {
        this.form.find('.is-invalid').removeClass('is-invalid');
        this.form.find('.invalid-feedback').text('').hide();
    }

    reset() {
        this.form[0].reset();
        this.clearErrors();
    }

    getData() {
        return new FormData(this.form[0]);
    }

    setFieldValue(fieldName, value) {
        this.form.find(`[name="${fieldName}"]`).val(value);
    }

    getFieldValue(fieldName) {
        return this.form.find(`[name="${fieldName}"]`).val();
    }
}

/**
 * Modal Helper
 * Provides common modal functionality
 */
class ModalManager {
    constructor(modalId) {
        this.modalId = modalId;
        this.modal = $(`#${modalId}`);
        this.bootstrapModal = null;
    }

    init() {
        this.bootstrapModal = new bootstrap.Modal(this.modal[0]);
        return this;
    }

    show() {
        this.init().bootstrapModal.show();
    }

    hide() {
        if (this.bootstrapModal) {
            this.bootstrapModal.hide();
        }
    }

    setTitle(title) {
        this.modal.find('.modal-title').html(title);
    }

    setContent(content) {
        this.modal.find('.modal-body').html(content);
    }

    onHidden(callback) {
        this.modal.on('hidden.bs.modal', callback);
    }

    onShown(callback) {
        this.modal.on('shown.bs.modal', callback);
    }
}

/**
 * API Helper
 * Provides common API functionality
 */
class ApiManager {
    static getHeaders() {
        return {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type': 'application/json'
        };
    }

    static get(url) {
        return axios.get(url, {
            headers: this.getHeaders()
        });
    }

    static post(url, data) {
        return axios.post(url, data, {
            headers: this.getHeaders()
        });
    }

    static put(url, data) {
        return axios.put(url, data, {
            headers: this.getHeaders()
        });
    }

    static delete(url) {
        return axios.delete(url, {
            headers: this.getHeaders()
        });
    }

    static handleError(error) {
        const message = error.response?.data?.message || 'حدث خطأ غير متوقع';
        toastr.error(message);
        console.error('API Error:', error);
    }

    static handleSuccess(message = 'تمت العملية بنجاح') {
        toastr.success(message);
    }
}

/**
 * Status Badge Helper
 */
const StatusBadges = {
    tenant: {
        active: '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>نشطة</span>',
        inactive: '<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>معطلة</span>',
        pending: '<span class="badge badge-warning"><i class="fas fa-exclamation-circle me-1"></i>معلقة</span>'
    },
    user: {
        active: '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>نشط</span>',
        inactive: '<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>معطل</span>',
        pending: '<span class="badge badge-warning"><i class="fas fa-hourglass-half me-1"></i>قيد الانتظار</span>'
    }
};

/**
 * Utility Helper
 */
const Utils = {
    formatDate(date) {
        return new Date(date).toLocaleDateString('ar-SA');
    },

    formatDateTime(date) {
        return new Date(date).toLocaleString('ar-SA');
    },

    formatNumber(number) {
        return new Intl.NumberFormat('ar-SA').format(number);
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('ar-SA', {
            style: 'currency',
            currency: 'SAR'
        }).format(amount);
    },

    confirmDelete(message = 'هل أنت متأكد من حذف هذا العنصر؟') {
        return confirm(message);
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    generateId() {
        return Math.random().toString(36).substr(2, 9);
    }
};

/**
 * Toast Notifications Configuration
 */
function initializeToastr() {
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-left",
        "timeOut": "3000",
        "showDuration": "300",
        "hideDuration": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
}

// Initialize on document ready
$(document).ready(function() {
    initializeToastr();

    // CSRF token setup for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
