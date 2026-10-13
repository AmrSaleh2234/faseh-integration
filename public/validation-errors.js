document.addEventListener('DOMContentLoaded', () => {
    const fieldName = key => key.replace(/\.([^.]*)/g, '[$1]');
    const findField = key => {
        const name = fieldName(key);
        return Array.from(document.querySelectorAll('[name]')).find(field => field.name === name);
    };

    document.querySelectorAll('[data-validation-field]').forEach(link => {
        const field = findField(link.dataset.validationField)
            || (link.dataset.validationField === 'items' ? document.querySelector('#item-rows .item-row input') : null)
            || (link.dataset.validationField === 'client_id' ? document.querySelector('[data-client-edit-link]') : null);
        if (!field) return;
        field.classList.add('has-error');
        field.setAttribute('aria-invalid', 'true');
        link.addEventListener('click', event => {
            event.preventDefault();
            if (field.matches('[data-client-edit-link]')) {
                field.click();
                return;
            }
            const group = field.closest('[data-fasah-types][hidden]');
            if (group) group.hidden = false;
            const panel = field.closest('.tab-body:not(.active)');
            if (panel) document.querySelector(`.tab[data-tab="${panel.dataset.panel}"]`)?.click();
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            field.focus({ preventScroll: true });
        });
    });
});
