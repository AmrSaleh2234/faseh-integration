const storageKey = 'invoice-design-draft';

export function initializeDraft(items, customFields) {
    function save() {
        const state = {};

        document.querySelectorAll('input[id]:not([type="file"]), select[id]').forEach(field => {
            state[field.id] = field.type === 'checkbox' ? field.checked : field.value;
        });

        state.currency = document.getElementById('currency').textContent;
        state.notes = document.getElementById('notes').innerText;
        state.items = items.read();
        state.customFields = customFields.read();

        localStorage.setItem(storageKey, JSON.stringify(state));
        window.alert('تم حفظ نسخة محلية في هذا المتصفح فقط');
    }

    function restore() {
        let state;

        try {
            state = JSON.parse(localStorage.getItem(storageKey) || 'null');
        } catch (_) {
            return;
        }

        if (!state) return;

        Object.entries(state).forEach(([id, value]) => {
            const field = document.getElementById(id);
            if (!field || !('value' in field)) return;

            if (field.type === 'checkbox') {
                field.checked = value;
                return;
            }

            if (id === 'client' && value && ![...field.options].some(option => option.value === value)) {
                field.add(new Option(value, value));
            }

            field.value = value;
        });

        if (state.currency) document.getElementById('currency').textContent = state.currency;
        if (state.notes) document.getElementById('notes').innerText = state.notes;

        customFields.restore(state.customFields);
        items.restore(state.items);
        items.calculate();
    }

    document.getElementById('save-draft').addEventListener('click', save);
    document.getElementById('save-invoice').addEventListener('click', () => {
        const client = document.getElementById('client');

        if (!client.value) {
            window.alert('اختر العميل أولاً');
            client.focus();
            return;
        }

        save();
    });

    restore();
}
