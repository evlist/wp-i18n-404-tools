// i18n-404-tools/admin/js/pot-generator-vanilla.js

(function(){
 // Utility to create a modal dialog
 function createModal(title, content, buttons=[]) {
 // Remove existing modal if present
 let existing = document.getElementById('i18n404-pot-modal');
 if (existing) existing.remove();

 let overlay = document.createElement('div');
 overlay.id = 'i18n404-pot-modal';
 overlay.style.position = 'fixed';
 overlay.style.zIndex = 100000;
 overlay.style.left = 0;
 overlay.style.top = 0;
 overlay.style.width = '100vw';
 overlay.style.height = '100vh';
 overlay.style.background = 'rgba(30,30,30,0.25)';
 overlay.style.display = 'flex';
 overlay.style.alignItems = 'center';
 overlay.style.justifyContent = 'center';

 let modal = document.createElement('div');
 modal.style.background = '#fff';
 modal.style.boxShadow = '0 4px 16px rgba(0,0,0,0.25)';
 modal.style.maxWidth = '370px';
 modal.style.width = '90vw';
 modal.style.borderRadius = '6px';
 modal.style.padding = '0';
 modal.style.fontFamily = 'inherit';

 let header = document.createElement('div');
 header.textContent = title;
 header.style.fontWeight = 'bold';
 header.style.background = '#f6f7f7';
 header.style.padding = '12px 18px';
 header.style.borderBottom = '1px solid #eee';
 modal.appendChild(header);

 let body = document.createElement('div');
 body.style.padding = '18px';
 body.innerHTML = content;
 modal.appendChild(body);

 let footer = document.createElement('div');
 footer.style.textAlign = 'right';
 footer.style.background = '#f6f7f7';
 footer.style.padding = '12px 18px';
 footer.style.borderTop = '1px solid #eee';

 buttons.forEach(btnObj => {
         let btn = document.createElement('button');
         btn.textContent = btnObj.text;
         btn.className = btnObj.class || 'button';
         btn.style.marginLeft = '8px';
         btn.type = 'button';
         btn.onclick = function(){
         btnObj.onClick && btnObj.onClick();
         };
         footer.appendChild(btn);
         });

 modal.appendChild(footer);
 overlay.appendChild(modal);
 document.body.appendChild(overlay);

 // ESC to close
 overlay.addEventListener('keydown', function(e){
         if (e.key === 'Escape') overlay.remove();
         });
 overlay.tabIndex = -1;
 overlay.focus();
 return {
overlay: overlay,
             setBody: function(html) { body.innerHTML = html; },
             setButtons: function(btns) {
                 footer.innerHTML = '';
                 btns.forEach(btnObj => {
                         let btn = document.createElement('button');
                         btn.textContent = btnObj.text;
                         btn.className = btnObj.class || 'button';
                         btn.style.marginLeft = '8px';
                         btn.type = 'button';
                         btn.onclick = function(){
                         btnObj.onClick && btnObj.onClick();
                         };
                         footer.appendChild(btn);
                         });
             },
close: function() { overlay.remove(); }
 }
 }

 function ajax(data, callback) {
     let xhr = new XMLHttpRequest();
     xhr.open('POST', I18n404PotGen.ajax_url);
     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
     xhr.onload = function() {
         let resp = {};
         if (xhr.status === 200) {
             try { resp = JSON.parse(xhr.responseText); } catch(e){}
             callback(resp);
         } else {
             callback({
success: false,
data: {
message: 'AJAX error: HTTP ' + xhr.status + ' ' + xhr.statusText
}
});
}
};
xhr.onerror = function() {
    callback({
success: false,
data: { message: 'AJAX request failed (network error).' }
});
};
let params = [];
for (let k in data) {
    params.push(encodeURIComponent(k) + '=' + encodeURIComponent(data[k]));
}
xhr.send(params.join('&'));
}

function showPotModal(plugin, nonce) {
    let modal = createModal(I18n404PotGen.modal_title, I18n404PotGen.generating, [
            {
text: I18n404PotGen.btn_no,
class: 'button',
onClick: function() { modal.close(); }
}
    ]);
    // Step 1: Check if .pot exists
    ajax({
action: 'i18n_404_tools_generate_pot',
nonce: nonce,
plugin: plugin,
op: 'check'
}, function(resp){
if (resp.success && resp.data.exists) {
modal.setBody('<div>' + I18n404PotGen.overwrite_confirm + '</div>');
modal.setButtons([
        {
text: I18n404PotGen.btn_yes,
class: 'button button-primary',
onClick: function(){ generatePot(plugin, nonce, true, modal); }
},
{
text: I18n404PotGen.btn_no,
class: 'button',
onClick: function(){ modal.close(); }
}
]);
} else {
    generatePot(plugin, nonce, false, modal);
}
});
}

function generatePot(plugin, nonce, overwrite, modal) {
    modal.setBody('<div>' + I18n404PotGen.generating + '</div>');
    modal.setButtons([]);
    ajax({
action: 'i18n_404_tools_generate_pot',
nonce: nonce,
plugin: plugin,
op: 'generate',
overwrite: overwrite ? 1 : 0
}, function(resp){
if (resp.success) {
modal.setBody('<div class="notice notice-success"><p>' + resp.data.message + '</p></div>');
} else {
modal.setBody('<div class="notice notice-error"><p>' + resp.data.message + '</p></div>');
}
modal.setButtons([
    {
text: 'OK',
class: 'button button-primary',
onClick: function(){ modal.close(); }
}
]);
});
}

document.addEventListener('click', function(e){
        if (e.target.classList.contains('i18n-404-tools-generate-pot')) {
        e.preventDefault();
        let plugin = e.target.getAttribute('data-plugin');
        let nonce = e.target.getAttribute('data-nonce');
        showPotModal(plugin, nonce);
        }
        });
})();
