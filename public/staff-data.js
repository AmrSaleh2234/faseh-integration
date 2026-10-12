const storageKey = 'faseh-staff-preview-v1';
const defaultStaff = {
    id: '1', first_name: 'محمد', middle_name: '', last_name: 'القرني',
    code: '1', type: 'مستخدم', role: 'Owner', job_title: '', department: '',
    email: 'info@titanicways.com', status: 'نشط',
};

export function loadStaff() {
    let stored = [];
    try { stored = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (_) { stored = []; }
    if (!Array.isArray(stored)) stored = [];
    const employees = [defaultStaff, ...stored.filter(employee => employee?.id !== '1')];
    const override = stored.find(employee => employee?.id === '1');
    if (override) employees[0] = { ...defaultStaff, ...override };
    return employees;
}

export function saveStaff(employee) {
    const saved = loadStaff().filter(item => item.id !== employee.id && item.id !== '1');
    if (employee.id === '1') saved.unshift(employee);
    else saved.push(employee);
    localStorage.setItem(storageKey, JSON.stringify(saved));
}

export function removeStaff(id) {
    if (id === '1') return;
    const saved = loadStaff().filter(employee => employee.id !== id && employee.id !== '1');
    localStorage.setItem(storageKey, JSON.stringify(saved));
}

export function nextStaffId() {
    return String(Math.max(...loadStaff().map(employee => Number(employee.id) || 0)) + 1);
}

export function staffLabel(employee) {
    return `${[employee.first_name, employee.middle_name, employee.last_name].filter(Boolean).join(' ')} #${employee.code}`;
}

export function populateSalesPeople(select) {
    if (!select) return;
    const previous = select.value;
    select.replaceChildren();
    loadStaff().filter(employee => employee.status !== 'غير نشط').forEach(employee => {
        select.add(new Option(staffLabel(employee), staffLabel(employee)));
    });
    if ([...select.options].some(option => option.value === previous)) select.value = previous;
}
