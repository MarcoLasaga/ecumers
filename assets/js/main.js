(function () {
    'use strict';

    function qs(selector, scope) {
        return (scope || document).querySelector(selector);
    }

    function qsa(selector, scope) {
        return (scope || document).querySelectorAll(selector);
    }

    function getCsrfToken() {
        var meta = qs('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showToast(message, type) {
        var toast = qs('.toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.remove('toast-success', 'toast-error', 'show');
        if (type === 'success') {
            toast.classList.add('toast-success');
        } else if (type === 'error') {
            toast.classList.add('toast-error');
        }
        void toast.offsetWidth;
        toast.classList.add('show');
        setTimeout(function () {
            toast.classList.remove('show');
        }, 2500);
    }

    function updateCartCount(count) {
        var el = qs('#cart-count');
        if (el) {
            el.textContent = count;
            el.classList.remove('bump');
            void el.offsetWidth;
            el.classList.add('bump');
        }
    }

    function ajax(method, url, data, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                var resp;
                try {
                    resp = JSON.parse(xhr.responseText);
                } catch (e) {
                    resp = { success: false, message: 'Unexpected response.' };
                }
                callback(resp, xhr.status);
            }
        };
        xhr.send(data);
    }

    function handleAddToCartClick(e) {
        var btn = e.currentTarget;
        var productId = parseInt(btn.getAttribute('data-product-id'), 10);
        if (!productId) return;

        var qty = 1;
        var qtyInputId = btn.getAttribute('data-qty-input-id');
        if (qtyInputId) {
            var input = qs('#' + qtyInputId);
            if (input) {
                qty = parseInt(input.value, 10) || 1;
            }
        }

        var csrf = encodeURIComponent(getCsrfToken());
        var payload = 'action=add&product_id=' + encodeURIComponent(productId) +
            '&quantity=' + encodeURIComponent(qty) +
            '&csrf_token=' + csrf;

        ajax('POST', 'cart.php', payload, function (resp) {
            if (resp.success) {
                updateCartCount(resp.cartCount);
                showToast(resp.message || 'Added to cart.', 'success');
            } else {
                showToast(resp.message || 'Failed to add to cart.', 'error');
            }
        });
    }

    function initAddToCartButtons() {
        qsa('.add-to-cart-btn').forEach(function (btn) {
            btn.addEventListener('click', handleAddToCartClick);
        });
    }

    function initCartPage() {
        var table = qs('.cart-table');
        if (!table) return;

        table.addEventListener('change', function (e) {
            if (e.target.classList.contains('cart-qty-input')) {
                var row = e.target.closest('tr');
                var productId = parseInt(row.getAttribute('data-product-id'), 10);
                var qty = parseInt(e.target.value, 10) || 0;
                var csrf = encodeURIComponent(getCsrfToken());
                var payload = 'action=update&product_id=' + encodeURIComponent(productId) +
                    '&quantity=' + encodeURIComponent(qty) +
                    '&csrf_token=' + csrf;

                ajax('POST', 'cart.php', payload, function (resp) {
                    if (resp.success) {
                        updateCartCount(resp.cartCount);
                        if (typeof resp.total !== 'undefined') {
                            var totalEl = qs('#cart-total');
                            if (totalEl) totalEl.textContent = parseFloat(resp.total).toFixed(2);
                            location.reload();
                        }
                    } else {
                        showToast(resp.message || 'Failed to update cart.', 'error');
                    }
                });
            }
        });

        table.addEventListener('click', function (e) {
            if (e.target.classList.contains('cart-remove-btn')) {
                var row = e.target.closest('tr');
                var productId = parseInt(row.getAttribute('data-product-id'), 10);
                var csrf = encodeURIComponent(getCsrfToken());
                var payload = 'action=remove&product_id=' + encodeURIComponent(productId) +
                    '&csrf_token=' + csrf;

                ajax('POST', 'cart.php', payload, function (resp) {
                    if (resp.success) {
                        updateCartCount(resp.cartCount);
                        row.remove();
                        var totalEl = qs('#cart-total');
                        if (typeof resp.total !== 'undefined' && totalEl) {
                            totalEl.textContent = parseFloat(resp.total).toFixed(2);
                        }
                        showToast(resp.message || 'Item removed.', 'success');
                        if (!qs('.cart-table tbody tr')) {
                            location.reload();
                        }
                    } else {
                        showToast(resp.message || 'Failed to remove item.', 'error');
                    }
                });
            }
        });
    }

    function initForms() {
        var forms = qsa('form[novalidate]');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var valid = true;
                var requiredFields = qsa('[required]', form);
                requiredFields.forEach(function (field) {
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('field-error');
                    } else {
                        field.classList.remove('field-error');
                    }
                });
                if (!valid) {
                    e.preventDefault();
                    showToast('Please fill out required fields.', 'error');
                }
            });
        });
    }

    function initNavToggle() {
        var toggle = qs('#nav-toggle');
        var navMain = qs('#nav-main');
        var navUser = qs('#nav-user');
        var overlay = qs('#nav-overlay');

        if (!toggle || !navMain || !navUser || !overlay) return;

        function openNav() {
            navMain.classList.add('nav-open');
            navUser.classList.add('nav-open');
            overlay.classList.add('nav-overlay-open');
            toggle.classList.add('nav-toggle-open');
            document.body.style.overflow = 'hidden';
        }

        function closeNav() {
            navMain.classList.remove('nav-open');
            navUser.classList.remove('nav-open');
            overlay.classList.remove('nav-overlay-open');
            toggle.classList.remove('nav-toggle-open');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', function () {
            if (navMain.classList.contains('nav-open')) {
                closeNav();
            } else {
                openNav();
            }
        });

        overlay.addEventListener('click', function () {
            closeNav();
        });

        qsa('#nav-main a, #nav-user a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeNav();
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                closeNav();
            }
        });
    }

    function initHeaderScroll() {
        var header = qs('#site-header');
        if (!header) return;

        window.addEventListener('scroll', function () {
            if (window.scrollY > 10) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });
    }

    function initCategoriesDropdown() {
        var container = qs('#nav-categories');
        var menu = qs('#nav-categories-menu');
        if (!container || !menu) return;

        var toggle = container.querySelector('.nav-categories-toggle');

        function closeMenu() {
            menu.classList.remove('open');
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('open');
        });

        document.addEventListener('click', function () {
            closeMenu();
        });
    }

    function setupSearch(inputSelector, suggestionsSelector, formSelector) {
        var input = qs(inputSelector);
        var suggestions = qs(suggestionsSelector);
        var form = qs(formSelector);
        if (!input || !suggestions || !form) return;

        var debounceTimer = null;

        function clearSuggestions() {
            suggestions.innerHTML = '';
            suggestions.classList.remove('show');
        }

        input.addEventListener('input', function () {
            var query = input.value.trim();
            if (debounceTimer) clearTimeout(debounceTimer);

            if (!query) {
                clearSuggestions();
                return;
            }

            debounceTimer = setTimeout(function () {
                var payload = 'q=' + encodeURIComponent(query) +
                    '&csrf_token=' + encodeURIComponent(getCsrfToken());
                ajax('POST', 'search_suggestions.php', payload, function (resp) {
                    if (!resp.success || !Array.isArray(resp.results) || !resp.results.length) {
                        clearSuggestions();
                        return;
                    }
                    suggestions.innerHTML = '';
                    resp.results.forEach(function (item) {
                        var el = document.createElement('div');
                        el.className = 'nav-search-suggestion';
                        el.innerHTML = '<span>' + item.name + '</span><span>$' +
                            parseFloat(item.price).toFixed(2) + '</span>';
                        el.addEventListener('click', function () {
                            window.location.href = 'product.php?id=' + encodeURIComponent(item.id);
                        });
                        suggestions.appendChild(el);
                    });
                    suggestions.classList.add('show');
                });
            }, 200);
        });

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) {
                clearSuggestions();
            }
        });

        form.addEventListener('submit', function () {
            clearSuggestions();
        });
    }

    function initNavSearch() {
        setupSearch('#nav-search-input', '#nav-search-suggestions', '#nav-search-form');
        setupSearch('#nav-search-input-mobile', '#nav-search-suggestions-mobile', '#nav-search-form-mobile');
    }

    function initImageModal() {
        var overlay = qs('#image-modal');
        var imgEl = qs('#image-modal-img');
        var closeBtn = qs('#image-modal-close');
        if (!overlay || !imgEl || !closeBtn) return;

        function openModal(src) {
            imgEl.src = src;
            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            overlay.classList.remove('open');
            overlay.setAttribute('aria-hidden', 'true');
            imgEl.src = '';
        }

        qsa('.js-product-image img').forEach(function (img) {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', function (e) {
                e.preventDefault();
                openModal(img.src);
            });
        });

        closeBtn.addEventListener('click', function () {
            closeModal();
        });

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('open')) {
                closeModal();
            }
        });
    }

    function initCouponApply() {
        var btn = qs('#apply-coupon-btn');
        var input = qs('#coupon_code');
        var discountEl = qs('#discount-amount');
        var totalEl = qs('#grand-total');
        var messageEl = qs('#coupon-message');
        if (!btn || !input || !discountEl || !totalEl || !messageEl) return;

        btn.addEventListener('click', function () {
            var code = input.value.trim();
            if (!code) {
                showToast('Enter a coupon code.', 'error');
                messageEl.textContent = 'Enter a coupon code.';
                messageEl.classList.add('error');
                return;
            }
            var payload = 'code=' + encodeURIComponent(code) +
                '&csrf_token=' + encodeURIComponent(getCsrfToken());
            ajax('POST', 'coupon_apply.php', payload, function (resp) {
                if (resp.success) {
                    discountEl.textContent = '-$' + parseFloat(resp.discount_amount).toFixed(2);
                    totalEl.textContent = '$' + parseFloat(resp.grand_total).toFixed(2);
                    messageEl.textContent = resp.message || 'Coupon applied.';
                    messageEl.classList.remove('error');
                    showToast('Coupon applied.', 'success');
                } else {
                    messageEl.textContent = resp.message || 'Invalid coupon.';
                    messageEl.classList.add('error');
                    discountEl.textContent = '-$0.00';
                    if (typeof resp.grand_total !== 'undefined') {
                        totalEl.textContent = '$' + parseFloat(resp.grand_total).toFixed(2);
                    }
                    showToast(resp.message || 'Coupon failed.', 'error');
                }
            });
        });
    }

    function initRevealOnScroll() {
        var elements = qsa('[data-reveal]');
        if (!elements.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15
        });

        elements.forEach(function (el) {
            observer.observe(el);
        });
    }

    function initSmoothAnchors() {
        qsa('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var targetId = link.getAttribute('href').slice(1);
                var target = qs('#' + targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    function initFlashToasts() {
        qsa('.js-flash').forEach(function (el) {
            var type = el.getAttribute('data-flash-type') || 'success';
            var msg = el.textContent.trim();
            if (msg) {
                showToast(msg, type === 'error' ? 'error' : 'success');
            }
            el.parentNode.removeChild(el);
        });
    }

    function initContactForm() {
        var form = qs('#contact-form');
        if (!form) return;

        var nameEl = qs('#contact-name');
        var emailEl = qs('#contact-email');
        var subjectEl = qs('#contact-subject');
        var messageEl = qs('#contact-message');
        var resultEl = qs('#contact-message-result');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!nameEl.value.trim() || !emailEl.value.trim() || !subjectEl.value.trim() || !messageEl.value.trim()) {
                showToast('Please fill all contact fields.', 'error');
                if (resultEl) resultEl.textContent = 'Please fill all required fields.';
                return;
            }
            var email = emailEl.value.trim();
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                showToast('Enter a valid email address.', 'error');
                if (resultEl) resultEl.textContent = 'Enter a valid email address.';
                return;
            }
            if (resultEl) resultEl.textContent = 'Message sent. We will respond shortly.';
            showToast('Message sent. We will respond shortly.', 'success');
            form.reset();
        });
    }

    function initNewsletterForm() {
        var form = qs('#newsletter-form');
        if (!form) return;

        var emailEl = qs('#newsletter-email');
        var msgEl = qs('#newsletter-message');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var email = emailEl.value.trim();
            if (!email) {
                showToast('Enter your email.', 'error');
                if (msgEl) msgEl.textContent = 'Enter your email.';
                return;
            }
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                showToast('Enter a valid email address.', 'error');
                if (msgEl) msgEl.textContent = 'Enter a valid email address.';
                return;
            }
            if (msgEl) msgEl.textContent = 'Subscribed. Watch your inbox.';
            showToast('Subscribed. Watch your inbox.', 'success');
            emailEl.value = '';
        });
    }

    function initAddressAutocompleteInternal() {
        var input = qs('#address_autocomplete');
        if (!input || !window.google || !google.maps || !google.maps.places) return;

        var streetField = qs('#address');
        var cityField = qs('#city');
        var postalField = qs('#postal_code');
        var countryField = qs('#country');
        var formattedField = qs('#formatted_address');
        var latField = qs('#address_lat');
        var lngField = qs('#address_lng');

        var autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['geocode']
        });

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            if (!place || !place.address_components) return;

            var components = place.address_components;
            var street = '';
            var city = '';
            var postal = '';
            var country = '';

            components.forEach(function (c) {
                if (c.types.indexOf('street_number') !== -1) {
                    street = c.long_name + ' ' + street;
                }
                if (c.types.indexOf('route') !== -1) {
                    street += c.long_name;
                }
                if (c.types.indexOf('locality') !== -1) {
                    city = c.long_name;
                }
                if (c.types.indexOf('postal_code') !== -1) {
                    postal = c.long_name;
                }
                if (c.types.indexOf('country') !== -1) {
                    country = c.long_name;
                }
            });

            if (streetField) streetField.value = street;
            if (cityField) cityField.value = city;
            if (postalField) postalField.value = postal;
            if (countryField) countryField.value = country;

            if (formattedField) {
                formattedField.value = place.formatted_address || '';
            }
            if (place.geometry && place.geometry.location) {
                if (latField) latField.value = place.geometry.location.lat();
                if (lngField) lngField.value = place.geometry.location.lng();
            }
        });
    }

    window.initSalsaAddressAutocomplete = function () {
        initAddressAutocompleteInternal();
    };

    document.addEventListener('DOMContentLoaded', function () {
        initAddToCartButtons();
        initCartPage();
        initForms();
        initNavToggle();
        initHeaderScroll();
        initCategoriesDropdown();
        initNavSearch();
        initImageModal();
        initCouponApply();
        initRevealOnScroll();
        initSmoothAnchors();
        initFlashToasts();
        initContactForm();
        initNewsletterForm();
    });
})();