import { loadStaff, saveStaff, removeStaff, nextStaffId, staffLabel } from './staff-data.js';

document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('open');
});

const list = document.getElementById('staff-rows');
if (list) {
    const form = document.getElementById('staff-search-form');
    let employees = loadStaff();

    function render() {
        const query = document.getElementById('staff-query').value.trim().toLocaleLowerCase('ar');
        const status = document.getElementById('staff-status-filter').value;
        const type = document.getElementById('staff-type-filter').value;
        const role = document.getElementById('staff-role-filter').value.trim().toLocaleLowerCase('ar');
        const filtered = employees.filter(employee => {
            const searchable = [staffLabel(employee), employee.email, employee.department].join(' ').toLocaleLowerCase('ar');
            return (!query || searchable.includes(query)) && (!status || employee.status === status) &&
                (!type || employee.type === type) && (!role || employee.role.toLocaleLowerCase('ar').includes(role));
        });
        list.replaceChildren();
        filtered.forEach(employee => {
            const row = document.createElement('article');
            row.className = 'staff-row';
            const identity = document.createElement('div');
            identity.className = 'staff-identity';
            const avatar = document.createElement('span');
            avatar.className = 'staff-avatar';
            avatar.textContent = employee.first_name?.charAt(0) || 'م';
            const nameWrap = document.createElement('div');
            const name = document.createElement('a');
            name.href = `${list.dataset.editBase}/${encodeURIComponent(employee.id)}/edit`;
            name.textContent = [employee.first_name, employee.middle_name, employee.last_name].filter(Boolean).join(' ');
            const code = document.createElement('small');
            code.textContent = `#${employee.code}`;
            nameWrap.append(name, code);
            identity.append(avatar, nameWrap);
            const roleCell = document.createElement('span');
            roleCell.textContent = `${employee.role} (${employee.type})`;
            const title = document.createElement('span');
            title.textContent = employee.job_title || '-';
            const department = document.createElement('span');
            department.textContent = employee.department || '-';
            const state = document.createElement('span');
            state.className = `staff-status${employee.status === 'غير نشط' ? ' inactive' : ''}`;
            state.textContent = employee.status;
            const menuWrap = document.createElement('div');
            const menuButton = document.createElement('button');
            menuButton.type = 'button';
            menuButton.className = 'staff-row-menu';
            menuButton.setAttribute('aria-label', `إجراءات ${name.textContent}`);
            menuButton.textContent = '•••';
            const options = document.createElement('div');
            options.className = 'staff-row-options';
            options.hidden = true;
            const edit = document.createElement('a');
            edit.href = name.href;
            edit.textContent = 'تعديل';
            options.append(edit);
            if (employee.id !== '1') {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'staff-text-button';
                remove.textContent = 'حذف محلي';
                remove.addEventListener('click', () => {
                    if (!window.confirm(`حذف ${name.textContent} من المعاينة المحلية؟`)) return;
                    removeStaff(employee.id);
                    employees = loadStaff();
                    render();
                });
                options.append(remove);
            }
            menuButton.addEventListener('click', () => { options.hidden = !options.hidden; });
            menuWrap.append(menuButton, options);
            row.append(identity, roleCell, title, department, state, menuWrap);
            list.append(row);
        });
        document.getElementById('staff-count').textContent = filtered.length ? `1 - ${filtered.length} من ${filtered.length}` : '0 من 0';
        document.getElementById('staff-empty').hidden = filtered.length > 0;
    }

    form.addEventListener('submit', event => { event.preventDefault(); render(); });
    form.addEventListener('reset', () => setTimeout(render, 0));
    document.getElementById('staff-toggle-search').addEventListener('click', event => {
        form.hidden = !form.hidden;
        event.target.textContent = form.hidden ? 'إظهار ⌄' : 'إخفاء ⌃';
    });
    render();
}

const staffForm = document.getElementById('staff-form');
if (staffForm) {
    const id = staffForm.dataset.staffId;
    const existing = id ? loadStaff().find(employee => employee.id === id) : null;
    if (existing) {
        for (const [name, value] of Object.entries(existing)) {
            const field = staffForm.elements.namedItem(name);
            if (field && typeof value === 'string') field.value = value;
        }
    } else if (!id) {
        staffForm.elements.namedItem('type').value = 'موظف';
    }
    staffForm.addEventListener('submit', event => {
        event.preventDefault();
        if (!staffForm.reportValidity()) return;
        const data = Object.fromEntries(new FormData(staffForm).entries());
        const employee = {
            id: id || nextStaffId(),
            first_name: data.first_name.trim(),
            middle_name: data.middle_name.trim(),
            last_name: data.last_name.trim(),
            code: data.code.trim(),
            type: data.type,
            role: data.role,
            job_title: data.job_title.trim(),
            department: data.department.trim(),
            email: data.email.trim(),
            status: 'نشط',
        };
        saveStaff(employee);
        const notice = document.getElementById('staff-notice');
        notice.textContent = 'تم حفظ بيانات الموظف الأساسية محليًا. حقول الهوية والاتصال الإضافية للمعاينة فقط.';
        notice.hidden = false;
        window.setTimeout(() => { window.location.href = staffForm.dataset.listUrl; }, 700);
    });
}
