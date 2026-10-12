<section class="items panel" aria-label="بنود الفاتورة">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>الخصم</th>
                    <th>الضريبة 1</th>
                    <th>الضريبة 2</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="item-rows">
                @foreach(old('items',$invoice?->items?->toArray() ?: [[]]) as $item)
                    @include('invoices.partials.item-row', ['item' => $item])
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="items-footer">
        <button class="add-item" id="add-item" type="button">⊕ &nbsp; إضافة</button>

        <div class="totals">
            <div><b>الإجمالي</b><span id="subtotal">﷼ 0.00</span></div>
            <div><b>الإجمالي</b><span id="total">﷼ 0.00</span></div>
        </div>
    </div>
</section>

<template id="item-row-template">
    @include('invoices.partials.item-row', ['item' => []])
</template>
