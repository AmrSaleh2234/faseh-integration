const formatMoney = value => '﷼ ' + Number(value || 0).toFixed(2);
const toNumber = value => Number.parseFloat(value) || 0;

export function initializeItems() {
    const rows = document.getElementById('item-rows');
    const template = document.getElementById('item-row-template');
    const subtotalField = document.getElementById('subtotal');
    const totalField = document.getElementById('total');
    const discountField = document.getElementById('invoice-discount');
    const discountKind = document.getElementById('discount-kind');
    const adjustmentField = document.getElementById('adjustment');

    function addItem(values = []) {
        const row = template.content.firstElementChild.cloneNode(true);

        row.querySelectorAll('input, select').forEach((field, index) => {
            field.value = values[index] ?? field.value;
        });

        rows.appendChild(row);
        calculate();
    }

    function calculate() {
        let subtotal = 0;

        rows.querySelectorAll('.item-row').forEach(row => {
            const base = toNumber(row.querySelector('.price').value)
                * toNumber(row.querySelector('.quantity').value);
            const discounted = Math.max(0, base - toNumber(row.querySelector('.item-discount').value));
            const taxRate = toNumber(row.querySelector('.tax-one').value)
                + toNumber(row.querySelector('.tax-two').value);
            const lineTotal = discounted * (1 + taxRate / 100);

            row.querySelector('.line-total').textContent = formatMoney(lineTotal);
            subtotal += lineTotal;
        });

        const discount = toNumber(discountField.value);
        const deduction = discountKind.value === 'percent' ? subtotal * discount / 100 : discount;
        const total = Math.max(0, subtotal - deduction + toNumber(adjustmentField.value) + toNumber(document.getElementById('insurance-expenses')?.value));

        subtotalField.textContent = formatMoney(subtotal);
        totalField.textContent = formatMoney(total);
    }

    function read() {
        return [...rows.querySelectorAll('.item-row')].map(row =>
            [...row.querySelectorAll('input, select')].map(field => field.value),
        );
    }

    function restore(savedRows) {
        if (!Array.isArray(savedRows) || savedRows.length === 0) return;

        rows.replaceChildren();
        savedRows.forEach(values => addItem(values));
        calculate();
    }

    document.getElementById('add-item').addEventListener('click', () => addItem());
    rows.addEventListener('input', calculate);
    rows.addEventListener('change', calculate);
    rows.addEventListener('click', event => {
        const removeButton = event.target.closest('.remove-item');
        if (!removeButton) return;

        removeButton.closest('.item-row').remove();
        if (!rows.children.length) addItem();
        calculate();
    });

    [discountField, discountKind, adjustmentField, document.getElementById('insurance-expenses')].filter(Boolean).forEach(field => {
        field.addEventListener('input', calculate);
        field.addEventListener('change', calculate);
    });

    calculate();

    return { rows, calculate, read, restore };
}
