document.addEventListener('DOMContentLoaded', function () {
    // Listen for clicks on any action link with our class
    document.body.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('i18n-404-tools-action')) {
            e.preventDefault();
            showI18n404ToolsModal('Loading…');
            const plugin = e.target.getAttribute('data-plugin');
            const command = e.target.getAttribute('data-command');
            const step = e.target.getAttribute('data-step') || 'start';
            fetchI18n404ToolsModalContent(plugin, command, step);
        }
    });

    // Close modal on ESC or background click or close icon
    document.body.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('i18n-404-tools-modal-overlay')) {
            closeI18n404ToolsModal();
        }
        if (e.target && e.target.classList.contains('i18n-404-tools-modal-close')) {
            closeI18n404ToolsModal();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeI18n404ToolsModal();
        }
    });

    // Helper: Show modal with given HTML content
    function showI18n404ToolsModal(content) {
        closeI18n404ToolsModal();
        const overlay = document.createElement('div');
        overlay.className = 'i18n-404-tools-modal-overlay';
        overlay.innerHTML = `
            <div class="i18n-404-tools-modal-content">
                <span class="dashicons dashicons-no-alt i18n-404-tools-modal-close" title="Close"></span>
                <div class="i18n-404-tools-modal-body">${content}</div>
            </div>
        `;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    }

    // Helper: Close modal
    function closeI18n404ToolsModal() {
        const overlay = document.querySelector('.i18n-404-tools-modal-overlay');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    }

    // Helper: AJAX to fetch modal content
    function fetchI18n404ToolsModalContent(plugin, command, step = 'start', data = {}) {
        if (!command) {
            showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">Error: No command specified.</div>');
            return;
        }
        const postData = Object.assign({}, data, {
            action: 'i18n_404_tools_command',
            plugin: plugin,
            command: command,
            step: step,
            _ajax_nonce: typeof I18n404PotGen !== 'undefined' && I18n404PotGen.nonce ? I18n404PotGen.nonce : undefined
        });

        fetch(I18n404PotGen.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: Object.keys(postData).map(key => encodeURIComponent(key) + '=' + encodeURIComponent(postData[key])).join('&')
        })
            .then(resp => resp.json())
            .then(json => {
                if (json.success && json.data && json.data.html) {
                    showI18n404ToolsModal(json.data.html);
                    attachI18n404ToolsModalActionHandlers(plugin, command);
                } else if (json.data && json.data.message) {
                    showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">' + json.data.message + '</div>');
                } else {
                    showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">Error: Unexpected response.</div>');
                }
            })
            .catch(() => {
                showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">Error: AJAX request failed.</div>');
            });
    }

    // Helper: Attach handlers for buttons in modal, e.g., Next, Retry, etc.
    function attachI18n404ToolsModalActionHandlers(plugin, command) {
        const modal = document.querySelector('.i18n-404-tools-modal-content');
        if (!modal) return;
        modal.querySelectorAll('[data-i18n-404-tools-step]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const step = btn.getAttribute('data-i18n-404-tools-step');
                // Collect user input fields if needed
                const data = {};
                modal.querySelectorAll('[name]').forEach(input => {
                    data[input.name] = input.value;
                });
                showI18n404ToolsModal('Loading…');
                fetchI18n404ToolsModalContent(plugin, command, step, data);
            });
        });
    }
});
