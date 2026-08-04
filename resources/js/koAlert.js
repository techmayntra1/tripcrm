import Swal from 'sweetalert2';

const GOLD = '#f7b924';
const DARK = '#1a1a1a';
const DANGER = '#dc3545';
const SUCCESS = '#28a745';

const themedSwal = Swal.mixin({
    buttonsStyling: false,
    reverseButtons: true,
    customClass: {
        popup: 'ko-swal-popup',
        title: 'ko-swal-title',
        htmlContainer: 'ko-swal-body',
        confirmButton: 'btn ko-swal-confirm',
        cancelButton: 'btn ko-swal-cancel',
        icon: 'ko-swal-icon',
        actions: 'ko-swal-actions',
    },
});

const toaster = Swal.mixin({
    toast: true,
    position: 'top-end',
    backdrop: false,
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    customClass: {
        popup: 'ko-toast-popup',
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

const koAlert = {
    swal: themedSwal,
    raw: Swal,

    confirmDelete(message = 'Are you sure you want to delete this?', opts = {}) {
        return themedSwal.fire({
            title: opts.title || 'Are you sure?',
            html: message,
            icon: 'warning',
            iconColor: DANGER,
            showCancelButton: true,
            confirmButtonText: opts.confirmText || 'Yes, delete it',
            cancelButtonText: opts.cancelText || 'Cancel',
            focusCancel: true,
        }).then(r => r.isConfirmed);
    },

    confirmAction(message, opts = {}) {
        return themedSwal.fire({
            title: opts.title || 'Confirm',
            html: message,
            icon: opts.icon || 'question',
            iconColor: opts.iconColor || GOLD,
            showCancelButton: true,
            confirmButtonText: opts.confirmText || 'Yes, continue',
            cancelButtonText: opts.cancelText || 'Cancel',
        }).then(r => r.isConfirmed);
    },

    warning(message, opts = {}) {
        return themedSwal.fire({
            title: opts.title || 'Heads up',
            html: message,
            icon: 'warning',
            iconColor: GOLD,
            confirmButtonText: opts.confirmText || 'OK',
        });
    },

    error(message, opts = {}) {
        return themedSwal.fire({
            title: opts.title || 'Something went wrong',
            html: message,
            icon: 'error',
            iconColor: DANGER,
            confirmButtonText: opts.confirmText || 'OK',
        });
    },

    info(message, opts = {}) {
        return themedSwal.fire({
            title: opts.title || 'Info',
            html: message,
            icon: 'info',
            iconColor: GOLD,
            confirmButtonText: opts.confirmText || 'OK',
        });
    },

    success(message, opts = {}) {
        return themedSwal.fire({
            title: opts.title || 'Done',
            html: message,
            icon: 'success',
            iconColor: SUCCESS,
            confirmButtonText: opts.confirmText || 'OK',
        });
    },

    successToast(message) {
        return toaster.fire({ icon: 'success', title: message, iconColor: SUCCESS });
    },
    errorToast(message) {
        return toaster.fire({ icon: 'error', title: message, iconColor: DANGER });
    },
    warningToast(message) {
        return toaster.fire({ icon: 'warning', title: message, iconColor: GOLD });
    },
    infoToast(message) {
        return toaster.fire({ icon: 'info', title: message, iconColor: GOLD });
    },

    duplicateConfirm(message) {
        return themedSwal.fire({
            title: 'Possible duplicate',
            html: message,
            icon: 'warning',
            iconColor: GOLD,
            showCancelButton: true,
            confirmButtonText: 'Yes, add anyway',
            cancelButtonText: 'Cancel',
            focusCancel: true,
        }).then(r => r.isConfirmed);
    },
};

function bindConfirmForms() {
    document.querySelectorAll('form[onsubmit]').forEach(form => {
        if (form.dataset.koBound === '1') return;
        const handler = form.getAttribute('onsubmit') || '';
        const match = handler.match(/confirm\(\s*(['"`])([\s\S]*?)\1\s*\)/);
        if (!match) return;
        const message = match[2];
        const isDelete = /delete|deactivate|remove|trash/i.test(message);
        form.removeAttribute('onsubmit');
        form.dataset.koBound = '1';
        form.addEventListener('submit', function(e) {
            if (form.dataset.koConfirmed === '1') return;
            e.preventDefault();
            e.stopImmediatePropagation();
            const promptFn = isDelete ? koAlert.confirmDelete : koAlert.confirmAction;
            promptFn.call(koAlert, message).then(ok => {
                if (!ok) {
                    if (window.jQuery) {
                        window.jQuery(form).data('submitting', false);
                    }
                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
                        btn.disabled = false;
                        const orig = window.jQuery ? window.jQuery(btn).data('original-html') : null;
                        if (orig !== undefined && orig !== null) {
                            if (btn.tagName === 'BUTTON') btn.innerHTML = orig; else btn.value = orig;
                        }
                    });
                    return;
                }
                form.dataset.koConfirmed = '1';
                if (window.jQuery) {
                    window.jQuery(form).trigger('submit');
                } else {
                    form.submit();
                }
            });
        }, true);
    });

    document.querySelectorAll('a[onclick], button[onclick]').forEach(el => {
        if (el.dataset.koBound === '1') return;
        const handler = el.getAttribute('onclick') || '';
        const match = handler.match(/(?:return\s+confirm|if\s*\(\s*confirm)\(\s*(['"`])([\s\S]*?)\1\s*\)/);
        if (!match) return;
        const message = match[2];
        const isDelete = /delete|deactivate|remove|trash/i.test(message);
        el.removeAttribute('onclick');
        el.dataset.koBound = '1';
        el.addEventListener('click', function(e) {
            if (el.dataset.koConfirmed === '1') return;
            e.preventDefault();
            const promptFn = isDelete ? koAlert.confirmDelete : koAlert.confirmAction;
            promptFn.call(koAlert, message).then(ok => {
                if (ok) {
                    el.dataset.koConfirmed = '1';
                    if (el.tagName === 'A' && el.href) {
                        window.location.href = el.href;
                    } else {
                        el.click();
                    }
                }
            });
        });
    });
}

function flashFromSession() {
    const flash = window.__koFlash;
    if (!flash) return;
    if (flash.success) koAlert.successToast(flash.success);
    if (flash.error) koAlert.errorToast(flash.error);
    if (flash.warning) koAlert.warningToast(flash.warning);
    if (flash.info) koAlert.infoToast(flash.info);
}

function init() {
    bindConfirmForms();
    flashFromSession();
    document.querySelectorAll('.alert.alert-success, .alert.alert-danger, .alert.alert-warning, .alert.alert-info')
        .forEach(el => {
            if (el.dataset.koSkipFlash === '1') return;
            const message = (el.innerText || el.textContent || '').trim();
            if (!message) return;
            const cls = el.className;
            if (cls.includes('alert-success')) koAlert.successToast(message);
            else if (cls.includes('alert-danger')) koAlert.errorToast(message);
            else if (cls.includes('alert-warning')) koAlert.warningToast(message);
            else koAlert.infoToast(message);
            el.remove();
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

const mo = new MutationObserver(() => bindConfirmForms());
mo.observe(document.documentElement, { childList: true, subtree: true });

window.koAlert = koAlert;
window.Swal = Swal;

export default koAlert;
