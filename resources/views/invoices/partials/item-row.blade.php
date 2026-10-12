<tr class="item-row">
    <td><input placeholder="البند" aria-label="البند" value="{{ $item['name'] ?? '' }}"></td>
    <td><input placeholder="الوصف" aria-label="الوصف" value="{{ $item['description'] ?? '' }}"></td>
    <td><input type="number" min="0" step="0.01" class="price" placeholder="سعر الوحدة" aria-label="سعر الوحدة" value="{{ $item['unit_price'] ?? '' }}"></td>
    <td><input type="number" min="0" step="0.01" class="quantity" placeholder="الكمية" aria-label="الكمية" value="{{ $item['quantity'] ?? '' }}"></td>
    <td><input type="number" min="0" step="0.01" class="item-discount" placeholder="الخصم" aria-label="الخصم" value="{{ $item['discount'] ?? '' }}"></td>
    <td>
        <select class="tax-one" aria-label="الضريبة الأولى">
            <option value="0" @selected(($item['tax_one'] ?? 0) == 0)>بدون ضريبة</option>
            <option value="15" @selected(($item['tax_one'] ?? 0) == 15)>15%</option>
        </select>
    </td>
    <td>
        <select class="tax-two" aria-label="الضريبة الثانية">
            <option value="0" @selected(($item['tax_two'] ?? 0) == 0)>بدون ضريبة</option>
            <option value="15" @selected(($item['tax_two'] ?? 0) == 15)>15%</option>
        </select>
    </td>
    <td class="line-total">﷼ 0.00</td>
    <td><button type="button" class="remove-item" aria-label="حذف البند">⊖</button></td>
</tr>
