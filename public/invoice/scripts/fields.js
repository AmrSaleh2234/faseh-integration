export function initializeCustomFields() {
    const container = document.getElementById('custom-fields');
    const template = document.getElementById('custom-field-template');

    function addField(name = '', value = '') {
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelector('.custom-name').value = name;
        row.querySelector('.custom-value input').value = value;
        container.appendChild(row);
    }

    function read() {
        return [...container.querySelectorAll('.custom-row')].map(row => ({
            name: row.querySelector('.custom-name').value,
            value: row.querySelector('.custom-value input').value,
        }));
    }

    function restore(savedFields) {
        if (!Array.isArray(savedFields)) return;

        container.replaceChildren();
        savedFields.forEach(field => addField(field.name ?? '', field.value ?? ''));
    }

    container.addEventListener('click', event => {
        const removeButton = event.target.closest('.remove-custom');
        if (removeButton) removeButton.closest('.custom-row').remove();
    });

    document.getElementById('add-field').addEventListener('click', () => addField());

    return { read, restore };
}

export function initializeCustomerControls() {
    const client = document.getElementById('client');
    const currency = document.getElementById('currency');

    document.getElementById('sidebar-toggle').addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('open');
    });

    document.getElementById('new-client').addEventListener('click', () => {
        const name = window.prompt('اسم العميل الجديد');
        if (name?.trim()) client.add(new Option(name.trim(), name.trim(), true, true));
    });

    currency.addEventListener('click', () => {
        const code = window.prompt('العملة', currency.textContent);
        if (code?.trim()) currency.textContent = code.trim().toUpperCase();
    });
}
