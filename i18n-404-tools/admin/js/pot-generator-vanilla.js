(function () {
    // Load Dashicons if not present
    if (!document.getElementById('dashicons-css')) {
        const link = document.createElement('link');
        link.id = 'dashicons-css';
        link.rel = 'stylesheet';
        link.href = 'https://s.w.org/wp-includes/css/dashicons.css';
        document.head.appendChild(link);
    }

    function showModal(title, html, isLoading = false) {
        let modal = document.getElementById('i18n404-potgen-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'i18n404-potgen-modal';
            modal.style = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:100000;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;';
            modal.innerHTML =
                '<div style="background:#fff;max-width:600px;width:90vw;max-height:90vh;overflow:auto;padding:2em;position:relative;border-radius:4px;box-shadow:0 2px 20px #0002;">' +
                '<button id="i18n404-potgen-close" style="position:absolute;top:1em;right:1em;font-size:1.5em;background:none;border:none;cursor:pointer;">×</button>' +
                '<h3 id="i18n404-potgen-title"></h3>' +
                '<div id="i18n404-potgen-content"></div>' +
                '</div>';
            document.body.appendChild(modal);
            document.getElementById('i18n404-potgen-close').onclick = function () {
                modal.remove();
            };
        }
        document.getElementById('i18n404-potgen-title').textContent = title;
        document.getElementById('i18n404-potgen-content').innerHTML = html || '';
        if (isLoading) {
            document.getElementById('i18n404-potgen-content').innerHTML = '<div style="padding:1em;text-align:center;"><span class="spinner is-active" style="vertical-align:middle;"></span> ' + I18n404PotGen.generating + '</div>';
        }
    }

    document.addEventListener('click', function (e) {
        const link = e.target.closest('.i18n-404-tools-generate-pot');
        if (!link) return;
        e.preventDefault();

        const plugin = link.getAttribute('data-plugin');
        const nonce = link.getAttribute('data-nonce');
        let overwrite = false;

        // Step 1: Check if file exists
        showModal(I18n404PotGen.modal_title, '', true);
        fetch(I18n404PotGen.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=' + encodeURIComponent('i18n_404_tools_generate_pot') +
                '&plugin=' + encodeURIComponent(plugin) +
                '&nonce=' + encodeURIComponent(nonce) +
                '&op=check'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.exists) {
                    showModal(
                        I18n404PotGen.modal_title,
                        '<p>' + I18n404PotGen.overwrite_confirm + '</p>' +
                        '<button id="i18n404-potgen-yes" class="button button-primary">' + I18n404PotGen.btn_yes + '</button> ' +
                        '<button id="i18n404-potgen-no" class="button">' + I18n404PotGen.btn_no + '</button>'
                    );
                    document.getElementById('i18n404-potgen-yes').onclick = function () {
                        overwrite = true;
                        generatePot();
                    };
                    document.getElementById('i18n404-potgen-no').onclick = function () {
                        document.getElementById('i18n404-potgen-modal').remove();
                    };
                } else {
                    generatePot();
                }
            });

        // Step 2: Generate .pot file (and show output)
        function generatePot() {
            showModal(I18n404PotGen.modal_title, '', true);
            fetch(I18n404PotGen.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=' + encodeURIComponent('i18n_404_tools_generate_pot') +
                    '&plugin=' + encodeURIComponent(plugin) +
                    '&nonce=' + encodeURIComponent(nonce) +
                    '&op=generate' +
                    (overwrite ? '&overwrite=1' : '')
            })
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.success) {
                        html += '<div style="margin-bottom:1em;">' + data.data.message + '</div>';
                    } else {
                        html += '<div style="margin-bottom:1em;color:red;">' + (data.data && data.data.message ? data.data.message : 'Error') + '</div>';
                    }
                    if (data.data && data.data.cli_output) {
                        html += '<div style="margin:0 0 1em 0;position:relative;">' +
                            '<button id="i18n404-cli-copy" style="position:absolute;top:0;right:0;background:transparent;border:none;cursor:pointer;" title="Copy to clipboard">' +
                            '<span class="dashicons dashicons-clipboard" id="i18n404-cli-copy-icon" style="font-size:20px;vertical-align:middle;"></span>' +
                            '</button>' +
                            '<pre id="i18n404-cli-output" style="max-height:200px;overflow:auto;background:#f6f6f6;border:1px solid #ccc;padding:1em;font-size:12px;white-space:pre-wrap;">' +
                            escapeHtml(data.data.cli_output) +
                            '</pre>' +
                            '</div>';
                    }
                    showModal(I18n404PotGen.modal_title, html);

                    // Clipboard copy functionality
                    const copyBtn = document.getElementById('i18n404-cli-copy');
                    const copyIcon = document.getElementById('i18n404-cli-copy-icon');
                    if (copyBtn && copyIcon) {
                        copyBtn.onclick = function () {
                            const text = document.getElementById('i18n404-cli-output').innerText;
                            navigator.clipboard.writeText(text);
                            copyIcon.classList.remove('dashicons-clipboard');
                            copyIcon.classList.add('dashicons-yes');
                            setTimeout(() => {
                                copyIcon.classList.remove('dashicons-yes');
                                copyIcon.classList.add('dashicons-clipboard');
                            }, 1200);
                        };
                    }
                });
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }
})();
