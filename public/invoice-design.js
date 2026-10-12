import { initializeItems } from './invoice/scripts/items.js';
import { initializeCustomFields } from './invoice/scripts/fields.js';
import { initializeTabsAndEditor } from './invoice/scripts/tabs.js';
import { initializePreview } from './invoice/scripts/preview.js';

const items = initializeItems();
initializeCustomFields();

initializeTabsAndEditor();
initializePreview(items);
document.getElementById('sidebar-toggle')?.addEventListener('click', () => document.getElementById('sidebar')?.classList.toggle('open'));
document.getElementById('invoice-form')?.addEventListener('submit', event => {
    document.querySelectorAll('#item-rows .item-row').forEach((row, index) => {
        for (const [selector, field] of [['input[aria-label="البند"]', 'name'], ['input[aria-label="الوصف"]', 'description'], ['.price', 'unit_price'], ['.quantity', 'quantity'], ['.item-discount', 'discount'], ['.tax-one', 'tax_one'], ['.tax-two', 'tax_two']]) {
            row.querySelector(selector).name = `items[${index}][${field}]`;
        }
    });
    document.querySelectorAll('#custom-fields .custom-row').forEach((row, index) => {
        row.querySelector('.custom-name').name = `custom_fields[${index}][name]`;
        row.querySelector('.custom-value input').name = `custom_fields[${index}][value]`;
    });
    document.getElementById('notes-value').value = document.getElementById('notes').innerText;
    if (event.submitter?.value === 'issue' && !document.getElementById('client').value) {
        event.preventDefault();
        document.getElementById('client').focus();
        window.alert('اختر العميل أولًا');
    }
});
