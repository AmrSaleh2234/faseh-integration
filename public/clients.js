(() => {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    sidebarToggle?.addEventListener('click', () => document.getElementById('sidebar')?.classList.toggle('open'));

    const form = document.getElementById('client-form');
    if (form) {
        document.getElementById('second-address-toggle')?.addEventListener('change', event => {
            document.getElementById('second-address').hidden = !event.target.checked;
        });
        document.getElementById('add-contact')?.addEventListener('click', () => {
            const row = document.getElementById('contact-template').content.firstElementChild.cloneNode(true);
            document.getElementById('contacts').append(row);
        });
        document.getElementById('contacts')?.addEventListener('click', event => {
            if (event.target.classList.contains('remove-contact')) event.target.closest('.contact-row').remove();
        });
        form.addEventListener('submit', event => {
            event.preventDefault();
            const notice = document.getElementById('client-notice');
            notice.textContent = 'تم التحقق من النموذج. هذه معاينة تصميم محلية، ولم تُرسل البيانات إلى الخادم.';
            notice.hidden = false;
            notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    const search = document.getElementById('client-search');
    if (search) {
        const rows = [...document.querySelectorAll('.client-list-row')];
        const apply = () => {
            const name = document.getElementById('filter-name').value.trim().toLocaleLowerCase('ar');
            const type = document.getElementById('filter-client').value;
            const status = document.getElementById('filter-status').value;
            const classification = document.getElementById('filter-class').value;
            const visible = rows.filter(row => (!name || row.dataset.name.includes(name)) && (!type || row.dataset.type === type) && (!status || row.dataset.status === status) && (!classification || (classification === 'شركة' ? row.dataset.type === 'business' : true)));
            rows.forEach(row => { row.hidden = !visible.includes(row); });
            document.getElementById('result-count').textContent = visible.length ? `1 - ${visible.length} من ${visible.length}` : '0 من 0';
            document.getElementById('client-empty').hidden = visible.length !== 0;
        };
        search.addEventListener('submit', event => { event.preventDefault(); apply(); });
        search.addEventListener('reset', () => setTimeout(apply, 0));
        document.getElementById('toggle-filters')?.addEventListener('click', event => {
            search.hidden = !search.hidden;
            event.target.textContent = search.hidden ? 'إظهار ⌄' : 'إخفاء ⌃';
        });
        document.getElementById('select-all')?.addEventListener('change', event => document.querySelectorAll('.row-check').forEach(input => { input.checked = event.target.checked; }));
        document.querySelectorAll('.row-menu').forEach(button => button.addEventListener('click', () => {
            const panel = button.nextElementSibling;
            document.querySelectorAll('.row-menu-panel').forEach(other => { if (other !== panel) other.hidden = true; });
            panel.hidden = !panel.hidden;
        }));
    }

    document.querySelectorAll('.client-tabs [data-tab]').forEach(tab => tab.addEventListener('click', () => {
        document.querySelectorAll('.client-tabs [data-tab]').forEach(button => { button.classList.toggle('active', button === tab); button.setAttribute('aria-selected', button === tab ? 'true' : 'false'); });
        document.querySelectorAll('.client-tab-panel').forEach(panel => { panel.hidden = panel.id !== `tab-${tab.dataset.tab}`; });
    }));
    document.querySelectorAll('[data-local-action]').forEach(button => button.addEventListener('click', () => {
        const notice = document.getElementById('client-notice');
        notice.textContent = `${button.dataset.localAction} غير متاح في معاينة التصميم المحلية.`;
        notice.hidden = false;
    }));
})();
