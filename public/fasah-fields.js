const fasahType = document.getElementById('fasah-type');
if (fasahType) {
    const portSelect = document.getElementById('fasah-port-select');
    const portOther = document.getElementById('fasah-port-other');
    const refresh = () => {
        document.querySelectorAll('[data-fasah-types]').forEach(field => {
            field.hidden = !field.dataset.fasahTypes.split(',').includes(fasahType.value);
        });
        if (portSelect && portOther) {
            const custom = portSelect.value === '__other__';
            portOther.hidden = !custom;
            portOther.name = custom ? 'fasah_meta[port]' : '';
            portSelect.name = custom ? '' : 'fasah_meta[port]';
            const knownPort = Array.from(portSelect.options).some(option => option.value === portOther.value.trim());
            document.querySelector('[data-fasah-warning="port"]').hidden = !custom || !portOther.value.trim() || knownPort;
        }
        const declaration = document.querySelector('[name="fasah_meta[declaration_type]"]');
        document.querySelector('[data-fasah-warning="declaration_type"]').hidden = !['6', '8', '13'].includes(declaration?.value);
        const docRef = document.querySelector('[name="fasah_meta[doc_ref_no]"]');
        document.querySelector('[data-fasah-warning="doc_ref_no"]').hidden = !docRef?.value || /^\d{14}$/.test(docRef.value);
    };
    fasahType.addEventListener('change', refresh);
    document.querySelector('.fasah-fields').addEventListener('input', refresh);
    document.querySelector('.fasah-fields').addEventListener('change', refresh);
    refresh();
}
