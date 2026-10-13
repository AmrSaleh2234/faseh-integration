<tr class="item-row">
    <td><input name="items[{{ $index ?? 0 }}][name]" placeholder="البند" aria-label="البند" value="{{ $item['name'] ?? '' }}">@error('items.'.($index ?? 0).'.name')<small class="field-error">{{ $message }}</small>@enderror</td>
    <td><input name="items[{{ $index ?? 0 }}][description]" placeholder="الوصف" aria-label="الوصف" value="{{ $item['description'] ?? '' }}"></td>
    <td><input name="items[{{ $index ?? 0 }}][unit_price]" type="number" min="0" step="0.01" class="price" placeholder="سعر الوحدة" aria-label="سعر الوحدة" value="{{ $item['unit_price'] ?? '' }}">@error('items.'.($index ?? 0).'.unit_price')<small class="field-error">{{ $message }}</small>@enderror</td>
    <td><input name="items[{{ $index ?? 0 }}][quantity]" type="number" min="0" step="0.01" class="quantity" placeholder="الكمية" aria-label="الكمية" value="{{ $item['quantity'] ?? '' }}">@error('items.'.($index ?? 0).'.quantity')<small class="field-error">{{ $message }}</small>@enderror</td>
    <td><input name="items[{{ $index ?? 0 }}][discount]" type="number" min="0" step="0.01" class="item-discount" placeholder="الخصم" aria-label="الخصم" value="{{ $item['discount'] ?? '' }}">@error('items.'.($index ?? 0).'.discount')<small class="field-error">{{ $message }}</small>@enderror</td>
    <td>
        <select name="items[{{ $index ?? 0 }}][tax_one]" class="tax-one" aria-label="الضريبة الأولى">
            <option value="0" @selected(($item['tax_one'] ?? 0) == 0)>بدون ضريبة</option>
            <option value="15" @selected(($item['tax_one'] ?? 0) == 15)>15%</option>
        </select>@error('items.'.($index ?? 0).'.tax_one')<small class="field-error">{{ $message }}</small>@enderror
    </td>
    <td>
        <select name="items[{{ $index ?? 0 }}][tax_two]" class="tax-two" aria-label="الضريبة الثانية">
            <option value="0" @selected(($item['tax_two'] ?? 0) == 0)>بدون ضريبة</option>
            <option value="15" @selected(($item['tax_two'] ?? 0) == 15)>15%</option>
        </select>@error('items.'.($index ?? 0).'.tax_two')<small class="field-error">{{ $message }}</small>@enderror
    </td>
    <td class="line-total">﷼ 0.00</td>
    <td><button type="button" class="remove-item" aria-label="حذف البند">⊖</button></td>
</tr>
