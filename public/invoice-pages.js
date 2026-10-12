import { populateSalesPeople } from './staff-data.js';

(() => {
    populateSalesPeople(document.getElementById('issued-sales'));
    const sidebarToggle = document.getElementById('sidebar-toggle');
    sidebarToggle?.addEventListener('click', () => document.getElementById('sidebar')?.classList.toggle('open'));

    const search = document.getElementById('invoice-search');
    if (search) {
        const rows = [...document.querySelectorAll('.invoice-result-row')];
        let activeStatus = '';
        const applyFilters = () => {
            const client = document.getElementById('invoice-client-search').value.trim().toLocaleLowerCase('ar');
            const number = document.getElementById('invoice-number-search').value.trim();
            const status = document.getElementById('invoice-status-search').value || activeStatus;
            const from = document.getElementById('invoice-date-from').value;
            const to = document.getElementById('invoice-date-to').value;
            const visible = rows.filter(row =>
                (!client || row.dataset.client.includes(client)) &&
                (!number || row.dataset.number.includes(number)) &&
                (!status || row.dataset.status === status) &&
                (!from || row.dataset.date >= from) &&
                (!to || row.dataset.date <= to)
            );
            rows.forEach(row => { row.hidden = !visible.includes(row); });
            document.getElementById('invoice-count').textContent = visible.length ? `1 - ${visible.length} من ${visible.length}` : '0 من 0';
            document.getElementById('invoice-empty').hidden = visible.length > 0;
        };
        search.addEventListener('submit', event => { event.preventDefault(); applyFilters(); });
        search.addEventListener('reset', () => {
            activeStatus = '';
            document.querySelectorAll('.invoice-status-tabs button').forEach(button => button.classList.toggle('active', button.dataset.status === ''));
            setTimeout(applyFilters, 0);
        });
        document.querySelectorAll('.invoice-status-tabs button').forEach(button => button.addEventListener('click', () => {
            activeStatus = button.dataset.status;
            document.getElementById('invoice-status-search').value = '';
            document.querySelectorAll('.invoice-status-tabs button').forEach(tab => tab.classList.toggle('active', tab === button));
            applyFilters();
        }));
        document.getElementById('invoice-status-search').addEventListener('change', () => {
            activeStatus = '';
            document.querySelectorAll('.invoice-status-tabs button').forEach(tab => tab.classList.toggle('active', tab.dataset.status === ''));
        });
        document.getElementById('invoice-advanced-toggle').addEventListener('click', event => {
            const advanced = document.getElementById('invoice-advanced');
            advanced.hidden = !advanced.hidden;
            event.target.setAttribute('aria-expanded', String(!advanced.hidden));
        });
        document.getElementById('invoice-select-all').addEventListener('change', event => {
            document.querySelectorAll('.invoice-result-row:not([hidden]) .invoice-row-check').forEach(input => { input.checked = event.target.checked; });
        });
        document.querySelectorAll('.invoice-row-menu').forEach(button => button.addEventListener('click', () => {
            const menu = button.nextElementSibling;
            menu.hidden = !menu.hidden;
            button.setAttribute('aria-expanded', String(!menu.hidden));
        }));
    }

    document.querySelectorAll('[data-invoice-tab]').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('[data-invoice-tab]').forEach(tab => {
            tab.classList.toggle('active', tab === button);
            tab.setAttribute('aria-selected', String(tab === button));
        });
        document.querySelectorAll('.invoice-view-panel').forEach(panel => { panel.hidden = panel.id !== `invoice-tab-${button.dataset.invoiceTab}`; });
    }));
    document.getElementById('invoice-print')?.addEventListener('click', () => window.print());
    document.querySelectorAll('[data-invoice-action]').forEach(button => button.addEventListener('click', () => {
        const notice = document.getElementById('invoice-action-notice');
        notice.textContent = `${button.dataset.invoiceAction} غير متاحة في المعاينة المحلية.`;
        notice.hidden = false;
    }));

    const storageKey = 'issued-invoice-159-local';
    let saved = null;
    try { saved = JSON.parse(localStorage.getItem(storageKey) || 'null'); } catch (_) { saved = null; }
    const applyToPreview = data => {
        if (!data) return;
        document.querySelectorAll('[data-issued-field]').forEach(element => {
            if (Object.hasOwn(data.fields || {}, element.dataset.issuedField)) element.textContent = data.fields[element.dataset.issuedField];
        });
        document.querySelectorAll('.issued-extra-field').forEach(element => element.remove());
        document.querySelectorAll('.issued-document-reference dl').forEach(list => {
            (data.extraFields || []).forEach(field => {
                if (!field.name.trim()) return;
                const label = document.createElement('dt');
                const value = document.createElement('dd');
                label.className = 'issued-extra-field';
                value.className = 'issued-extra-field';
                label.textContent = field.name;
                value.textContent = field.value;
                list.append(label, value);
            });
        });
        document.querySelectorAll('#issued-note').forEach(element => {
            if (data.notes) element.textContent = data.notes;
        });
    };
    applyToPreview(saved);

    const form = document.getElementById('issued-invoice-form');
    if (form) {
        const fields = [...document.querySelectorAll('[data-field-name]')];
        if (saved) {
            fields.forEach(input => { if (Object.hasOwn(saved.fields || {}, input.dataset.fieldName)) input.value = saved.fields[input.dataset.fieldName]; });
            (saved.extraFields || []).forEach(field => {
                const row = document.getElementById('issued-custom-template').content.firstElementChild.cloneNode(true);
                row.querySelector('[aria-label="اسم الحقل"]').value = field.name;
                row.querySelector('[aria-label="قيمة الحقل"]').value = field.value;
                document.getElementById('issued-custom-fields').append(row);
            });
            if (saved.sales) document.getElementById('issued-sales').value = saved.sales;
            if (saved.terms !== undefined) document.getElementById('issued-terms').value = saved.terms;
            if (saved.insurance) document.getElementById('issued-insurance').value = saved.insurance;
            if (saved.notes) document.getElementById('notes').innerText = saved.notes;
        }
        const readState = () => ({
            fields: Object.fromEntries(fields.map(input => [input.dataset.fieldName, input.value])),
            extraFields: [...document.querySelectorAll('#issued-custom-fields .issued-custom-row:not(:has([data-field-name]))')].map(row => ({
                name: row.querySelector('[aria-label="اسم الحقل"]').value,
                value: row.querySelector('[aria-label="قيمة الحقل"]').value,
            })),
            sales: document.getElementById('issued-sales').value,
            terms: document.getElementById('issued-terms').value,
            insurance: document.getElementById('issued-insurance').value,
            notes: document.getElementById('notes').innerText,
        });
        form.addEventListener('submit', event => {
            event.preventDefault();
            localStorage.setItem(storageKey, JSON.stringify(readState()));
            const notice = document.getElementById('invoice-edit-notice');
            notice.textContent = 'تم حفظ التعديلات غير المالية محليًا في هذا المتصفح.';
            notice.hidden = false;
            notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
        document.getElementById('edit-preview').addEventListener('click', () => {
            applyToPreview(readState());
            document.getElementById('issued-preview-dialog').hidden = false;
        });
        document.getElementById('issued-preview-close').addEventListener('click', () => { document.getElementById('issued-preview-dialog').hidden = true; });
        document.querySelectorAll('[data-edit-tab]').forEach(button => button.addEventListener('click', () => {
            document.querySelectorAll('[data-edit-tab]').forEach(tab => tab.classList.toggle('active', tab === button));
            document.querySelectorAll('[data-edit-panel]').forEach(panel => panel.classList.toggle('active', panel.dataset.editPanel === button.dataset.editTab));
        }));
        document.querySelectorAll('.editor-toolbar [data-command]').forEach(button => button.addEventListener('click', () => {
            document.getElementById('notes').focus();
            document.execCommand(button.dataset.command, false);
        }));
        document.getElementById('issued-add-field').addEventListener('click', () => {
            document.getElementById('issued-custom-fields').append(document.getElementById('issued-custom-template').content.firstElementChild.cloneNode(true));
        });
    }
})();
