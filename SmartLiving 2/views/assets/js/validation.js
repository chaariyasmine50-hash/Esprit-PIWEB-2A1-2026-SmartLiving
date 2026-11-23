/**
 * views/assets/js/validation.js
 * Client-side input validation (first barrier, for user comfort).
 * The SAME rules are checked again on the server in PHP (AuthController / UserController):
 * JavaScript can be disabled, so the server validation is the one that really protects.
 *
 * Usage in a form:  <form ... novalidate data-validate>
 *                     <input name="email" data-rules="required|email">
 *                     <small class="err"></small>
 * Available rules: required, email, name, username, password, int, min:N, max:N, match:fieldName
 */
(function () {
    'use strict';

    var RULES = {
        required: function (v) {
            return v.trim() !== '' || 'This field is required.';
        },
        email: function (v) {
            return v === '' || /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || 'Please enter a valid email address.';
        },
        name: function (v) {
            return v === '' || /^[A-Za-zÀ-ÖØ-öø-ÿ][A-Za-zÀ-ÖØ-öø-ÿ\s'\-]{1,49}$/.test(v.trim()) || 'Only letters, 2 to 50 characters.';
        },
        username: function (v) {
            return v === '' || /^[a-zA-Z0-9_\-]{3,50}$/.test(v.trim()) || '3 to 50 characters: letters, digits, _ or -.';
        },
        password: function (v) {
            if (v === '') { return true; }
            if (v.length < 8) { return 'At least 8 characters.'; }
            return (/[A-Za-z]/.test(v) && /[0-9]/.test(v)) || 'Use at least one letter and one digit.';
        },
        int: function (v) {
            return v === '' || /^\d{1,7}$/.test(v.trim()) || 'Please enter a positive whole number.';
        },
        min: function (v, n) {
            return v === '' || v.trim().length >= parseInt(n, 10) || 'At least ' + n + ' characters.';
        },
        max: function (v, n) {
            return v.trim().length <= parseInt(n, 10) || 'At most ' + n + ' characters.';
        },
        match: function (v, other, form) {
            var o = form.querySelector('[name="' + other + '"]');
            return !o || v === o.value || 'The two values do not match.';
        }
    };

    function errorBox(input) {
        var field = input.closest('.field');
        return field ? field.querySelector('.err') : null;
    }

    function validateInput(input, form) {
        if (input.disabled) { return true; }
        var rules = (input.getAttribute('data-rules') || '').split('|').filter(Boolean);
        var value = input.value || '';
        var message = '';

        for (var i = 0; i < rules.length; i++) {
            var parts = rules[i].split(':');
            var fn = RULES[parts[0]];
            if (!fn) { continue; }
            var result = fn(value, parts[1], form);
            if (result !== true) { message = result; break; }
        }

        var box = errorBox(input);
        if (message) {
            input.classList.add('invalid');
            input.classList.remove('valid');
            if (box) { box.textContent = message; }
            return false;
        }
        input.classList.remove('invalid');
        if (value !== '') { input.classList.add('valid'); }
        if (box) { box.textContent = ''; }
        return true;
    }

    function setupForm(form) {
        var inputs = form.querySelectorAll('[data-rules]');

        inputs.forEach(function (input) {
            // Validate when the user leaves a field, then live while he corrects it
            input.addEventListener('blur', function () { validateInput(input, form); });
            input.addEventListener('input', function () {
                if (input.classList.contains('invalid')) { validateInput(input, form); }
            });
        });

        form.addEventListener('submit', function (event) {
            var ok = true;
            var first = null;
            inputs.forEach(function (input) {
                if (!validateInput(input, form)) {
                    ok = false;
                    if (!first) { first = input; }
                }
            });
            if (!ok) {
                event.preventDefault();
                if (first) { first.focus(); }
            }
        });
    }

    // Confirmation before dangerous actions (delete, block...).
    // No inline onclick: the Content-Security-Policy forbids inline JavaScript.
    function setupConfirm(form) {
        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented) { return; }
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-validate]').forEach(setupForm);
        document.querySelectorAll('form[data-confirm]').forEach(setupConfirm);
    });
})();
