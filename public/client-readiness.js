document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('client-form');
    const panel = document.getElementById('client-fasah-readiness');
    if (!form || !panel) return;
    const input = name => form.elements.namedItem(name)?.value?.trim() || '';
    const refresh = () => {
        const issues = [];
        if (!input('business_name')) issues.push('أدخل اسم العميل أو المنشأة.');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input('email'))) issues.push('أدخل بريدًا إلكترونيًا صحيحًا لاستلام فاتورة Fasah العامة.');
        const mobile = (input('mobile') || input('phone')).replace(/[\s().-]+/g, '').replace(/^(?:\+?966|00966)/, '0');
        if (!/^05\d{8}$/.test(mobile)) issues.push('أدخل جوالًا سعوديًا صحيحًا، مثل 05XXXXXXXX أو +9665XXXXXXXX.');
        if (input('tax_number') && !/^\d{15}$/.test(input('tax_number'))) issues.push('الرقم الضريبي اختياري؛ إذا أدخلته فيجب أن يتكون من 15 رقمًا.');
        panel.classList.toggle('error', issues.length > 0);
        panel.querySelector('strong').textContent = issues.length ? 'يمكن حفظ العميل الآن؛ يلزم استكمال البيانات التالية قبل إصدار فاتورة Fasah عامة:' : 'جاهز لفاتورة Fasah عامة';
        panel.querySelector('ul').replaceChildren(...issues.map(message => {
            const item = document.createElement('li');
            item.textContent = message;
            return item;
        }));
    };
    form.addEventListener('input', refresh);
    refresh();
});
