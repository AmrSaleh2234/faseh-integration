export function initializeTabsAndEditor() {
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab, .tab-body').forEach(node => {
                node.classList.remove('active');
            });

            tab.classList.add('active');
            document.querySelector('[data-panel="' + tab.dataset.tab + '"]').classList.add('active');
        });
    });

    const editor = document.getElementById('notes');

    document.querySelectorAll('[data-command]').forEach(button => {
        button.addEventListener('click', () => {
            editor.focus();
            document.execCommand(button.dataset.command);
        });
    });
}
