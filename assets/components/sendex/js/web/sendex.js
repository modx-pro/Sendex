(function (window, document) {
    'use strict';

    var SendexForm = {
        init: function (root) {
            var scope = root || document;
            var forms = scope.querySelectorAll('[data-sendex-form]');
            var i;

            for (i = 0; i < forms.length; i++) {
                SendexForm.bind(forms[i]);
            }
        },

        bind: function (form) {
            if (form.getAttribute('data-sendex-bound') === '1') {
                return;
            }
            form.setAttribute('data-sendex-bound', '1');
            form.addEventListener('submit', SendexForm.onSubmit);
        },

        onSubmit: function (event) {
            var form = event.target;
            var widget;
            var body;
            var action;

            if (!form || !form.getAttribute('data-sendex-form')) {
                return;
            }

            event.preventDefault();
            widget = form.closest('[data-sendex-widget]');
            body = new FormData(form);
            body.append('ajax', '1');
            action = form.getAttribute('action') || window.location.href;

            fetch(action, {
                method: (form.getAttribute('method') || 'POST').toUpperCase(),
                body: body,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    SendexForm.applyResponse(widget, data);
                })
                .catch(function () {
                    SendexForm.applyResponse(widget, {
                        success: false,
                        message: 'Request failed',
                        html: ''
                    });
                });
        },

        applyResponse: function (widget, data) {
            var html;
            var messageNode;

            if (!data) {
                return;
            }

            html = data.html || '';
            if (html && widget && widget.parentNode) {
                widget.outerHTML = html;
                SendexForm.init(document);
                return;
            }

            if (!widget) {
                return;
            }

            messageNode = widget.querySelector('[data-sendex-message]');
            if (messageNode && data.message) {
                messageNode.innerHTML = '<b>' + SendexForm.escapeHtml(data.message) + '</b>';
                messageNode.className = 'sendex-message ' + (data.success ? 'active' : 'sendex-error');
            }
        },

        escapeHtml: function (value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            SendexForm.init(document);
        });
    } else {
        SendexForm.init(document);
    }

    window.SendexForm = SendexForm;
}(window, document));
