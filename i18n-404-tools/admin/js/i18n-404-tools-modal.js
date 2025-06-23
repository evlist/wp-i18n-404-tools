// admin/js/i18n-404-tools-modal.js

document.addEventListener('DOMContentLoaded', function() {
    // Open the modal when a trigger button is clicked
    document.body.addEventListener('click', function(e) {
        var target = e.target.closest('.i18n-404-tools-generate-pot');
        if (target) {
            e.preventDefault();
            var plugin = target.getAttribute('data-plugin');
            var command = target.getAttribute('data-command') || 'generate_pot';
            showModal({ plugin, command, step: 'check' });
        }
    });

    // Handle modal internal button actions
    document.body.addEventListener('click', function(e) {
        var modal = document.querySelector('.i18n-modal');
        if (!modal || !modal.contains(e.target)) return;
        var t = e.target;

        // Confirm button: trigger next step via AJAX
        if (t.classList.contains('i18n-confirm')) {
            var step = t.getAttribute('data-step');
            var overwrite = t.getAttribute('data-overwrite');
            var data = Object.assign({}, modal._modalData || {});
            data.step = step;
            if (overwrite) data.overwrite = 1;
            fetchStep(data);
        }

        // Cancel or close buttons
        if (t.classList.contains('i18n-cancel') || t.classList.contains('i18n-close')) {
            closeModal();
        }

        // Copy to clipboard button
        if (t.classList.contains('i18n-copy-btn')) {
            var pre = modal.querySelector('.i18n-modal-output');
            if (pre) {
                navigator.clipboard.writeText(pre.textContent).then(function() {
                    t.classList.add('copied');
                    setTimeout(function() { t.classList.remove('copied'); }, 1000);
                });
            }
        }
    });

    function showModal(data) {
        var modal = document.querySelector('.i18n-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'i18n-modal';
            // Basic modal styles (customize as needed)
            modal.style.position = 'fixed';
            modal.style.top = '0'; modal.style.left = '0';
            modal.style.width = '100vw'; modal.style.height = '100vh';
            modal.style.background = 'rgba(0,0,0,0.5)';
            modal.style.zIndex = '9999';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            document.body.appendChild(modal);
        }
        modal.innerHTML = '<div class="i18n-modal-content"><p>Loading...</p></div>';
        modal._modalData = data;
        fetchStep(data);
        modal.style.display = 'flex';
    }

    function closeModal() {
        var modal = document.querySelector('.i18n-modal');
        if (modal) modal.style.display = 'none';
    }

    function fetchStep(data) {
        var modal = document.querySelector('.i18n-modal');
        if (!modal) return;
        modal._modalData = data;
        var formData = new FormData();
        for (var k in data) formData.append(k, data[k]);
        formData.append('action', 'i18n_404_tools_command');
        fetch(window.ajaxurl || (window.I18n404PotGen && I18n404PotGen.ajax_url), {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(resp) {
            if (resp.success && resp.data && resp.data.html) {
                modal.innerHTML = resp.data.html;
            } else if (resp.data && resp.data.html) {
                modal.innerHTML = resp.data.html;
            } else {
                modal.innerHTML = '<div class="i18n-modal-content"><p>' +
                    (resp.data && resp.data.message ? resp.data.message : 'Error') +
                    '</p><button type="button" class="button i18n-close">Close</button></div>';
            }
        })
        .catch(function() {
            modal.innerHTML = '<div class="i18n-modal-content"><p>Error</p><button type="button" class="button i18n-close">Close</button></div>';
        });
    }
});
