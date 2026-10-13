export function initializePreview(items) {
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');

    document.getElementById('preview').addEventListener('click', () => {
        const client = document.getElementById('client');
        const lines = [
            ['رقم الفاتورة', document.getElementById('invoice-number').value],
            ['تاريخ الفاتورة', document.getElementById('invoice-date').value],
            ['العميل', client.value ? client.selectedOptions[0].textContent.trim() : 'لم يتم اختيار عميل'],
            ['عدد البنود', String([...items.rows.children].filter(row => row.querySelector('input[aria-label="البند"]')?.value.trim()).length)],
            ['الإجمالي', document.getElementById('total').textContent],
        ];

        content.replaceChildren();

        lines.forEach(([label, value]) => {
            const line = document.createElement('div');
            line.className = 'preview-row';
            line.textContent = label + ': ' + value;
            content.appendChild(line);
        });

        modal.classList.add('open');
    });

    document.getElementById('close-preview').addEventListener('click', () => {
        modal.classList.remove('open');
    });

    modal.addEventListener('click', event => {
        if (event.target === modal) modal.classList.remove('open');
    });
}
