const fasahType = document.getElementById('fasah-type');
if (fasahType) {
    const refresh = () => {
        document.querySelectorAll('[data-fasah-types]').forEach(field => {
            field.hidden = !field.dataset.fasahTypes.split(',').includes(fasahType.value);
        });
    };
    fasahType.addEventListener('change', refresh);
    refresh();
}
