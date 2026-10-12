export function initializeTabsAndEditor() {
    const extras = document.querySelector('.extras');

    extras?.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            extras.querySelectorAll('.tab, .tab-body').forEach(node => {
                node.classList.remove('active');
            });

            tab.classList.add('active');
            extras.querySelector('[data-panel="' + tab.dataset.tab + '"]').classList.add('active');
        });
    });

    extras?.querySelectorAll('[data-document-tab]').forEach(tab => {
        tab.addEventListener('click', () => {
            extras.querySelectorAll('[data-document-tab]').forEach(button => {
                const active = button === tab;
                button.classList.toggle('active', active);
                button.setAttribute('aria-selected', String(active));
            });

            extras.querySelectorAll('[data-document-panel]').forEach(panel => {
                panel.hidden = panel.dataset.documentPanel !== tab.dataset.documentTab;
            });
        });
    });

    const attachments = document.getElementById('invoice-attachments');
    const selectedAttachments = document.getElementById('selected-attachments');
    const uploadZone = extras?.querySelector('.upload-zone');

    function showSelectedFiles(files) {
        selectedAttachments.replaceChildren();
        for (const file of files) {
            const item = document.createElement('li');
            item.textContent = file.name;
            selectedAttachments.append(item);
        }
    }

    attachments?.addEventListener('change', () => showSelectedFiles(attachments.files));
    uploadZone?.addEventListener('dragover', event => {
        event.preventDefault();
        uploadZone.classList.add('dragging');
    });
    uploadZone?.addEventListener('dragleave', () => uploadZone.classList.remove('dragging'));
    uploadZone?.addEventListener('drop', event => {
        event.preventDefault();
        uploadZone.classList.remove('dragging');
        showSelectedFiles(event.dataTransfer.files);
    });

    const editor = document.getElementById('notes');

    document.querySelectorAll('[data-command]').forEach(button => {
        button.addEventListener('click', () => {
            editor.focus();
            document.execCommand(button.dataset.command);
        });
    });
}
