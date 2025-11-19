import * as Turbo from '@hotwired/turbo';
import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

window.Alpine = Alpine;
window.flatpickr = flatpickr;

// Set Flatpickr global Indonesian locale
flatpickr.localize(Indonesian);

/**
 * Initialize Flatpickr on date inputs
 */
export function initDatepickers(container = document) {
    if (!container || !container.querySelectorAll) return;

    const dateInputs = container.querySelectorAll('input[type="date"], input.datepicker, [data-datepicker]');
    dateInputs.forEach((el) => {
        if (el._flatpickr) return;

        // Extract pre-filled value
        const initialVal = el.value || el.getAttribute('value') || '';

        // Configure flatpickr
        flatpickr(el, {
            altInput: true,
            altFormat: 'd/m/Y',
            dateFormat: 'Y-m-d',
            allowInput: true,
            locale: Indonesian,
            defaultDate: initialVal || null,
            altInputClass: el.className || 'w-full rounded-xl border-slate-300 p-2.5 text-xs bg-white',
            onChange: (selectedDates, dateStr) => {
                el.value = dateStr;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            },
        });
    });
}

window.initDatepickers = initDatepickers;

/**
 * Format string or number to Indonesian number format (thousands separator with '.')
 */
export function formatNumber(val) {
    if (val === null || val === undefined || val === '') return '';
    let str = val.toString().trim();
    if (/^\d+\.\d{1,2}$/.test(str)) {
        str = str.split('.')[0];
    }
    const clean = str.replace(/\D/g, '');
    if (!clean) return '';
    return clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/**
 * Clean formatted number string to pure integer digits
 */
export function unformatNumber(val) {
    if (!val) return 0;
    let str = val.toString().trim();
    if (/^\d+\.\d{1,2}$/.test(str)) {
        str = str.split('.')[0];
    }
    return parseInt(str.replace(/\D/g, ''), 10) || 0;
}

/**
 * Formats an input while maintaining the user's cursor position smoothly.
 */
export function handleNumberInput(input) {
    if (!input) return;
    const oldVal = input.value || '';
    const oldCursor = input.selectionStart ?? oldVal.length;

    const digitsBeforeCursor = oldVal.slice(0, oldCursor).replace(/\D/g, '').length;

    const cleanDigits = oldVal.replace(/\D/g, '');
    const formatted = cleanDigits ? cleanDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    if (input.value === formatted) return;
    input.value = formatted;

    let newCursor = 0;
    let digitCount = 0;
    for (let i = 0; i < formatted.length; i++) {
        if (/\d/.test(formatted[i])) {
            digitCount++;
        }
        if (digitCount === digitsBeforeCursor) {
            newCursor = i + 1;
            break;
        }
    }
    if (digitsBeforeCursor === 0) newCursor = 0;

    try {
        input.setSelectionRange(newCursor, newCursor);
    } catch (e) {}
}

// Make helpers globally accessible
window.formatNumber = formatNumber;
window.formatRupiah = formatNumber;
window.unformatNumber = unformatNumber;
window.handleNumberInput = handleNumberInput;

window.fillLogin = function (em, pw) {
    const emailInput = document.getElementById('email');
    const passInput = document.getElementById('password');
    if (emailInput) {
        emailInput.value = em;
        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (passInput) {
        passInput.value = pw;
        passInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
};

// Global In-Memory Cache for Wilayah (Persists across components and Turbo navigation)
window._wilayahCache = window._wilayahCache || {
    provinsis: null,
    kabupatens: {},
    kecamatans: {},
    kelurahans: {},
};

/**
 * Alpine Wilayah Cascading Component Data Factory (Hybrid Smart-Cache + Self-Bootstrapping)
 */
window.wilayahSelector = function (config = {}) {
    if (config.provinsis && Array.isArray(config.provinsis) && config.provinsis.length > 0) {
        window._wilayahCache.provinsis = config.provinsis;
    }

    const instance = {
        provinsis: config.provinsis || window._wilayahCache.provinsis || [],
        kabupatens: [],
        kecamatans: [],
        kelurahans: [],
        selectedProvinsi: config.provinsi || '',
        selectedKabupaten: config.kabupaten || '',
        selectedKecamatan: config.kecamatan || '',
        selectedKelurahan: config.kelurahan || '',
        kodePos: config.kode_pos || '',
        alamat: config.alamat || '',
        loading: {
            provinsi: false,
            kabupaten: false,
            kecamatan: false,
            kelurahan: false,
        },
        _initialized: false,

        async init() {
            if (this._initialized && this.provinsis.length > 0) {
                return;
            }
            this._initialized = true;

            await this.loadProvinsis();
            if (this.selectedProvinsi) {
                await this.loadKabupatens(this.selectedProvinsi);
            }
            if (this.selectedKabupaten) {
                await this.loadKecamatans(this.selectedKabupaten);
            }
            if (this.selectedKecamatan) {
                await this.loadKelurahans(this.selectedKecamatan);
            }
        },

        setValues(data = {}) {
            this.selectedProvinsi = data.id_provinsi || '';
            this.selectedKabupaten = data.id_kabupaten || '';
            this.selectedKecamatan = data.id_kecamatan || '';
            this.selectedKelurahan = data.id_kelurahan || '';
            this.kodePos = data.kode_pos || '';
            this.alamat = data.alamat || '';

            if (this.selectedProvinsi) {
                this.loadKabupatens(this.selectedProvinsi);
            } else {
                this.kabupatens = [];
            }

            if (this.selectedKabupaten) {
                this.loadKecamatans(this.selectedKabupaten);
            } else {
                this.kecamatans = [];
            }

            if (this.selectedKecamatan) {
                this.loadKelurahans(this.selectedKecamatan);
            } else {
                this.kelurahans = [];
            }
        },

        async loadProvinsis() {
            if (window._wilayahCache.provinsis && window._wilayahCache.provinsis.length > 0) {
                this.provinsis = window._wilayahCache.provinsis;
                return;
            }

            try {
                this.loading.provinsi = true;
                const res = await fetch('/api/wilayah/provinsi');
                if (res.ok) {
                    const data = await res.json();
                    this.provinsis = data;
                    window._wilayahCache.provinsis = data;
                }
            } catch (e) {
                console.error('Failed to load provinsis', e);
            } finally {
                this.loading.provinsi = false;
            }
        },

        async onProvinsiChange() {
            this.kabupatens = [];
            this.kecamatans = [];
            this.kelurahans = [];
            this.selectedKabupaten = '';
            this.selectedKecamatan = '';
            this.selectedKelurahan = '';
            this.kodePos = '';

            if (this.selectedProvinsi) {
                await this.loadKabupatens(this.selectedProvinsi);
            }
        },

        async loadKabupatens(idProvinsi) {
            if (!idProvinsi) return;

            if (window._wilayahCache.kabupatens[idProvinsi]) {
                this.kabupatens = window._wilayahCache.kabupatens[idProvinsi];
                return;
            }

            try {
                this.loading.kabupaten = true;
                const res = await fetch(`/api/wilayah/kabupaten/${idProvinsi}`);
                if (res.ok) {
                    const data = await res.json();
                    this.kabupatens = data;
                    window._wilayahCache.kabupatens[idProvinsi] = data;
                }
            } catch (e) {
                console.error('Failed to load kabupatens', e);
            } finally {
                this.loading.kabupaten = false;
            }
        },

        async onKabupatenChange() {
            this.kecamatans = [];
            this.kelurahans = [];
            this.selectedKecamatan = '';
            this.selectedKelurahan = '';
            this.kodePos = '';

            if (this.selectedKabupaten) {
                await this.loadKecamatans(this.selectedKabupaten);
            }
        },

        async loadKecamatans(idKabupaten) {
            if (!idKabupaten) return;

            if (window._wilayahCache.kecamatans[idKabupaten]) {
                this.kecamatans = window._wilayahCache.kecamatans[idKabupaten];
                return;
            }

            try {
                this.loading.kecamatan = true;
                const res = await fetch(`/api/wilayah/kecamatan/${idKabupaten}`);
                if (res.ok) {
                    const data = await res.json();
                    this.kecamatans = data;
                    window._wilayahCache.kecamatans[idKabupaten] = data;
                }
            } catch (e) {
                console.error('Failed to load kecamatans', e);
            } finally {
                this.loading.kecamatan = false;
            }
        },

        async onKecamatanChange() {
            this.kelurahans = [];
            this.selectedKelurahan = '';
            this.kodePos = '';

            if (this.selectedKecamatan) {
                await this.loadKelurahans(this.selectedKecamatan);
            }
        },

        async loadKelurahans(idKecamatan) {
            if (!idKecamatan) return;

            if (window._wilayahCache.kelurahans[idKecamatan]) {
                this.kelurahans = window._wilayahCache.kelurahans[idKecamatan];
                return;
            }

            try {
                this.loading.kelurahan = true;
                const res = await fetch(`/api/wilayah/kelurahan/${idKecamatan}`);
                if (res.ok) {
                    const data = await res.json();
                    this.kelurahans = data;
                    window._wilayahCache.kelurahans[idKecamatan] = data;
                }
            } catch (e) {
                console.error('Failed to load kelurahans', e);
            } finally {
                this.loading.kelurahan = false;
            }
        },

        onKelurahanChange() {
            if (this.selectedKelurahan && this.kelurahans.length > 0) {
                const found = this.kelurahans.find((k) => k.id_wilayah === this.selectedKelurahan);
                if (found && found.kode_pos) {
                    this.kodePos = found.kode_pos;
                }
            }
        },

        // Build address object from current selected IDs + loaded names
        buildFullAddress() {
            const result = {};

            if (this.selectedProvinsi) {
                const p = this.provinsis.find(x => x.id_wilayah === this.selectedProvinsi);
                if (p) result.provinsi_nama = p.nama;
            }
            if (this.selectedKabupaten) {
                const k = this.kabupatens.find(x => x.id_wilayah === this.selectedKabupaten);
                if (k) result.kabupaten_nama = k.nama;
            }
            if (this.selectedKecamatan) {
                const c = this.kecamatans.find(x => x.id_wilayah === this.selectedKecamatan);
                if (c) result.kecamatan_nama = c.nama;
            }
            if (this.selectedKelurahan) {
                const l = this.kelurahans.find(x => x.id_wilayah === this.selectedKelurahan);
                if (l) result.kelurahan_nama = l.nama;
            }

            return result;
        },
    };

    // Self-bootstrapping: langsung inisialisasi di background event tick
    // sehingga tetap berjalan normal meskipun parent Alpine tidak memanggil .init()
    setTimeout(() => {
        instance.init();
    }, 0);

    return instance;
};

// Register Alpine directives
document.addEventListener('alpine:init', () => {
    // Global Confirm & Alert Dialog store
    Alpine.store('confirmDialog', {
        open: false,
        title: '',
        message: '',
        confirmText: 'Lanjutkan',
        cancelText: 'Batal',
        isDanger: true,
        type: 'confirm',
        _resolve: null,

        openDialog(options = {}) {
            this.title = options.title || (options.type === 'alert' ? 'Pemberitahuan' : 'Konfirmasi');
            this.message = options.message || '';
            this.confirmText = options.confirmText || (options.type === 'alert' ? 'Mengerti' : 'Lanjutkan');
            this.cancelText = options.type === 'alert' ? '' : (options.cancelText || 'Batal');
            this.isDanger = options.isDanger ?? (options.type === 'danger');
            this.type = options.type || (this.isDanger ? 'danger' : 'confirm');
            this._resolve = options.resolve || null;
            this.open = true;
        },

        confirm() {
            this.open = false;
            if (this._resolve) {
                this._resolve(true);
                this._resolve = null;
            }
        },

        cancel() {
            this.open = false;
            if (this._resolve) {
                this._resolve(false);
                this._resolve = null;
            }
        },
    });

    // x-number & x-separator directives
    const numberDirectiveFn = (el, { modifiers }, { cleanup }) => {
        const onInput = () => {
            handleNumberInput(el);
        };
        el.addEventListener('input', onInput);
        el.addEventListener('change', onInput);
        if (el.value) {
            el.value = formatNumber(el.value);
        }
        cleanup(() => {
            el.removeEventListener('input', onInput);
            el.removeEventListener('change', onInput);
        });
    };

    Alpine.directive('number', numberDirectiveFn);
    Alpine.directive('separator', numberDirectiveFn);

    // x-datepicker directive for Alpine elements
    Alpine.directive('datepicker', (el, { modifiers }, { cleanup }) => {
        const initialVal = el.value || '';
        const fp = flatpickr(el, {
            altInput: true,
            altFormat: 'd/m/Y',
            dateFormat: 'Y-m-d',
            allowInput: true,
            locale: Indonesian,
            defaultDate: initialVal || null,
            altInputClass: el.className || 'w-full rounded-xl border-slate-300 p-2.5 text-xs bg-white',
            onChange: (selectedDates, dateStr) => {
                el.value = dateStr;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            },
        });

        cleanup(() => {
            if (fp) fp.destroy();
        });
    });
});

// Start Alpine
Alpine.start();

// Auto-initialize datepickers on load, Turbo events, and DOM mutations
document.addEventListener('DOMContentLoaded', () => initDatepickers());
document.addEventListener('turbo:load', () => initDatepickers());
document.addEventListener('turbo:render', () => initDatepickers());

// Global delegation for number formatting
['input', 'change'].forEach((evtType) => {
    document.addEventListener(
        evtType,
        (e) => {
            const el = e.target;
            if (
                el &&
                el.matches?.(
                    '[data-number], [data-format-number], [data-separator], .format-number, [x-number], [x-separator]'
                )
            ) {
                handleNumberInput(el);
            }
        },
        true
    );
});

/**
 * Modern Confirm Dialog helper returning a Promise<boolean>
 */
window.$confirm = function (optionsOrMessage) {
    let opts = {};
    if (typeof optionsOrMessage === 'string') {
        opts = { message: optionsOrMessage };
    } else if (optionsOrMessage && typeof optionsOrMessage === 'object') {
        opts = { ...optionsOrMessage };
    }
    return new Promise((resolve) => {
        const store = Alpine.store('confirmDialog');
        if (store) {
            store.openDialog({
                title: opts.title || 'Konfirmasi',
                message: opts.message || 'Apakah Anda yakin ingin melanjutkan?',
                confirmText: opts.confirmText || 'Lanjutkan',
                cancelText: opts.cancelText || 'Batal',
                isDanger: opts.isDanger !== undefined ? opts.isDanger : true,
                type: opts.type || (opts.isDanger !== false ? 'danger' : 'confirm'),
                resolve,
            });
        } else {
            resolve(window.confirm ? window.confirm(opts.message) : true);
        }
    });
};

/**
 * Modern Alert Dialog helper returning a Promise<void>
 */
window.$alert = function (optionsOrMessage) {
    let opts = {};
    if (typeof optionsOrMessage === 'string') {
        opts = { message: optionsOrMessage };
    } else if (optionsOrMessage && typeof optionsOrMessage === 'object') {
        opts = { ...optionsOrMessage };
    }
    return new Promise((resolve) => {
        const store = Alpine.store('confirmDialog');
        if (store) {
            store.openDialog({
                title: opts.title || 'Pemberitahuan',
                message: opts.message || '',
                confirmText: opts.confirmText || 'Tutup',
                cancelText: '',
                isDanger: false,
                type: opts.type || 'alert',
                resolve,
            });
        } else {
            resolve();
        }
    });
};

// Safe override for native window.alert to proxy into modern modal
window.alert = function (message) {
    window.$alert(message);
};

// Intercept form submissions having data-confirm attribute
document.addEventListener(
    'submit',
    async (e) => {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;

        if (form.__submittingConfirmed) {
            delete form.__submittingConfirmed;
            return;
        }

        const submitter = e.submitter;
        const confirmMsg =
            (submitter && submitter.getAttribute('data-confirm')) ||
            form.getAttribute('data-confirm');

        if (!confirmMsg) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const title =
            (submitter && submitter.getAttribute('data-confirm-title')) ||
            form.getAttribute('data-confirm-title') ||
            'Konfirmasi Tindakan';
        const isDanger =
            (submitter && submitter.getAttribute('data-confirm-danger') === 'false') ||
            form.getAttribute('data-confirm-danger') === 'false'
                ? false
                : true;
        const confirmText =
            (submitter && submitter.getAttribute('data-confirm-ok')) ||
            form.getAttribute('data-confirm-ok') ||
            'Lanjutkan';

        const ok = await window.$confirm({
            title,
            message: confirmMsg,
            confirmText,
            cancelText: 'Batal',
            isDanger,
        });

        if (ok) {
            form.__submittingConfirmed = true;
            if (submitter && submitter.name) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
        }
    },
    true
);

