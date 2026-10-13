const panel = document.getElementById('fasah-sync');
if (panel && ['pending', 'sending'].includes(panel.dataset.status)) {
    const labels = { pending: 'قيد المزامنة', sending: 'جارٍ الإرسال', synced: 'تمت المزامنة', failed: 'فشلت المزامنة', uncertain: 'تحتاج مراجعة' };
    const refresh = async () => {
        try {
            const response = await fetch(panel.dataset.statusUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) return;
            const result = await response.json();
            panel.dataset.status = result.status;
            const badge = document.getElementById('fasah-sync-label');
            badge.className = `fasah-sync-badge ${result.status}`;
            badge.textContent = labels[result.status] || 'لم تبدأ';
            const number = document.getElementById('fasah-sync-number');
            number.hidden = !result.fasah_invoice_number;
            number.querySelector('strong').textContent = result.fasah_invoice_number || '';
            const message = document.getElementById('fasah-sync-message');
            message.hidden = !result.message;
            message.textContent = result.message || '';
            const retry = document.getElementById('fasah-retry-form');
            if (retry) retry.hidden = result.status !== 'failed';
            if (result.status === 'failed') message.textContent += ' يمكنك تعديل بيانات Fasah قبل إعادة المحاولة.';
            if (['pending', 'sending'].includes(result.status)) setTimeout(refresh, 5000);
        } catch (_) {
            setTimeout(refresh, 10000);
        }
    };
    setTimeout(refresh, 3000);
}
