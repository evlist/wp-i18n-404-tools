// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

document.addEventListener('DOMContentLoaded', function () {
    if (typeof I18n404ToolsConfig === 'undefined' || !I18n404ToolsConfig.ui) {
        console.error('I18n404ToolsConfig.ui is not defined!');
        return;
    }
    const cfg = I18n404ToolsConfig.ui;

    // Generic delegated click handler for all action links and modal buttons
    document.body.addEventListener('click', function (e) {
        const action = e.target.closest('.' + cfg.action_class);
        if (action) {
            e.preventDefault();
            showI18n404ToolsModal(I18n404ToolsConfig.i18n.loading);
            const plugin = action.getAttribute(cfg.data_plugin);
            const command = action.getAttribute(cfg.data_command);
            const step = action.getAttribute(cfg.data_step) || 'start';
            fetchI18n404ToolsModalContent(plugin, command, step);
            return;
        }
        // Close modal on overlay or close icon
        if (e.target.classList.contains(cfg.overlay_class) || e.target.classList.contains(cfg.close_class)) {
            closeI18n404ToolsModal();
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeI18n404ToolsModal();
        }
    });

    // Show modal with content
    function showI18n404ToolsModal(content) {
        closeI18n404ToolsModal();
        const overlay = document.createElement('div');
        overlay.className = cfg.overlay_class;
        overlay.innerHTML = `
            <div class="${cfg.content_class}">
                <span class="dashicons dashicons-no-alt ${cfg.close_class}" title="${I18n404ToolsConfig.i18n.close}"></span>
                <div class="${cfg.body_class}">${content}</div>
            </div>
        `;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    }

    // Close modal
    function closeI18n404ToolsModal() {
        const overlay = document.querySelector('.' + cfg.overlay_class);
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    }

    // AJAX to fetch modal content
    function fetchI18n404ToolsModalContent(plugin, command, step = 'start', data = {}) {
        if (!command) {
            showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">' + I18n404ToolsConfig.i18n.error_no_command + '</div>');
            return;
        }
        const postData = Object.assign({}, data, {
            action: 'i18n_404_tools_command',
            plugin: plugin,
            command: command,
            step: step,
            _ajax_nonce: (typeof I18n404ToolsConfig.nonce !== 'undefined') ? I18n404ToolsConfig.nonce : undefined
        });

        fetch(I18n404ToolsConfig.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: Object.keys(postData)
                .filter(key => postData[key] !== undefined)
                .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(postData[key]))
                .join('&')
        })
            .then(resp => resp.json())
            .then(json => {
                if (json.success && json.data && json.data.html) {
                    showI18n404ToolsModal(json.data.html);
                } else if (json.data && json.data.message) {
                    showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">' + json.data.message + '</div>');
                } else {
                    showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">' + I18n404ToolsConfig.i18n.error_unexpected + '</div>');
                }
            })
            .catch(() => {
                showI18n404ToolsModal('<div class="i18n-404-tools-modal-error">' + I18n404ToolsConfig.i18n.error_ajax_failed + '</div>');
            });
    }
});
