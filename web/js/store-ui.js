document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-count]').forEach((item) => {
        const target = Number(item.dataset.count || 0);
        const suffix = item.dataset.suffix || '';
        const duration = 900;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            item.textContent = `${Math.floor(target * eased).toLocaleString('id-ID')}${suffix}`;
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
    });

    const countdown = document.querySelector('[data-countdown]');
    if (countdown) {
        let seconds = 4 * 3600 + 18 * 60 + 42;
        setInterval(() => {
            seconds = Math.max(seconds - 1, 0);
            const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            countdown.textContent = `${h}:${m}:${s}`;
        }, 1000);
    }

    document.querySelectorAll('[data-select-card]').forEach((card) => {
        card.addEventListener('click', () => {
            const group = card.dataset.selectCard;
            const scope = card.closest('[data-checkout]') || document;
            scope.querySelectorAll(`[data-select-card="${group}"]`).forEach((sibling) => {
                sibling.classList.remove('is-active');
            });
            card.classList.add('is-active');
            applySelectedProductNicknameConfig(scope);
            updateCheckoutSummary(scope);
            resetNicknameValidation(scope);
            scheduleNicknameValidation(scope);
        });
    });

    document.querySelectorAll('[data-checkout]').forEach((checkout) => {
        applySelectedProductNicknameConfig(checkout);
        updateCheckoutSummary(checkout);
        checkout.dataset.nicknameValid = checkout.dataset.nicknameRequired === '1' ? '0' : '1';

        const buyButton = checkout.closest('.game-detail-layout')?.querySelector('[data-buy-now]');
        if (!buyButton) {
            return;
        }

        const accountInputs = [
            checkout.querySelector('[data-order-target]'),
            checkout.querySelector('[data-order-zone]'),
        ].filter(Boolean);

        accountInputs.forEach((input) => {
            input.addEventListener('input', () => {
                resetNicknameValidation(checkout);
                scheduleNicknameValidation(checkout);
            });
        });

        updateBuyButtonState(checkout);

        buyButton.addEventListener('click', async () => {
            const selectedProduct = checkout.querySelector('[data-select-card="nominal"].is-active');
            const targetInput = checkout.querySelector('[data-order-target]');
            const zoneInput = checkout.querySelector('[data-order-zone]');
            const emailInput = checkout.querySelector('[data-order-email]');
            const message = checkout.querySelector('[data-order-message]');

            hideCheckoutMessage(message);

            if (!selectedProduct) {
                showCheckoutMessage(message, 'Pilih produk terlebih dahulu.', true);
                return;
            }

            if (!targetInput?.value.trim()) {
                showCheckoutMessage(message, 'User ID wajib diisi.', true);
                targetInput?.focus();
                return;
            }

            if (checkout.dataset.requiresZone === '1' && !zoneInput?.value.trim()) {
                showCheckoutMessage(message, 'Zone ID wajib diisi.', true);
                zoneInput?.focus();
                return;
            }

            if (checkout.dataset.nicknameRequired === '1' && checkout.dataset.nicknameValid !== '1') {
                await validateGameNickname(checkout);
                if (checkout.dataset.nicknameValid !== '1') {
                    showCheckoutMessage(message, checkout.dataset.nicknameMessage || 'Validasi akun game belum berhasil.', true);
                    return;
                }
            }

            if (!emailInput?.value.trim()) {
                showCheckoutMessage(message, 'Email wajib diisi.', true);
                emailInput?.focus();
                return;
            }

            const payload = new FormData();
            payload.append('product_id', selectedProduct.dataset.productId || '');
            payload.append('target', targetInput.value.trim());
            payload.append('zone', zoneInput?.value.trim() || '');
            payload.append('payment_method', 'iPaymu');
            payload.append('email', emailInput.value.trim());

            if (window.yii?.getCsrfParam && window.yii?.getCsrfToken) {
                payload.append(window.yii.getCsrfParam(), window.yii.getCsrfToken());
            }

            buyButton.disabled = true;
            buyButton.textContent = 'Membuat payment link...';

            try {
                const response = await fetch(checkout.dataset.createOrderUrl, {
                    method: 'POST',
                    body: payload,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();

                if (!response.ok || data.status !== 'success' || !data.payment_url) {
                    showCheckoutMessage(message, data.message || 'Gagal membuat payment link.', true);
                    return;
                }

                showCheckoutMessage(message, `Invoice ${data.invoice_number} dibuat. Mengalihkan ke pembayaran...`, false);
                window.location.href = data.payment_url;
            } catch (error) {
                showCheckoutMessage(message, 'Gagal menghubungi server. Coba lagi.', true);
            } finally {
                buyButton.textContent = 'Beli Sekarang';
                updateBuyButtonState(checkout);
            }
        });

        updateBuyButtonState(checkout);
    });

    const nicknameButton = document.querySelector('[data-check-nickname]');
    const nicknameResult = document.querySelector('[data-nickname-result]');
    if (nicknameButton && nicknameResult) {
        nicknameButton.addEventListener('click', () => {
            nicknameResult.classList.add('is-loading');
            nicknameResult.textContent = 'Mengecek akun...';
            setTimeout(() => {
                nicknameResult.classList.remove('is-loading');
                nicknameResult.textContent = 'SkyLancer ID • Region Indonesia';
            }, 700);
        });
    }
});

function applySelectedProductNicknameConfig(checkout) {
    const selectedProduct = checkout.querySelector('[data-select-card="nominal"].is-active');
    if (!selectedProduct) {
        return;
    }

    checkout.dataset.nicknameRequired = selectedProduct.dataset.nicknameRequired || '0';
    checkout.dataset.requiresZone = selectedProduct.dataset.requiresZone || '0';

    const targetField = checkout.querySelector('[data-target-field]');
    const zoneField = checkout.querySelector('[data-zone-field]');
    const zoneInput = checkout.querySelector('[data-order-zone]');
    const requiresZone = checkout.dataset.requiresZone === '1';

    targetField?.classList.toggle('col-md-7', requiresZone);
    targetField?.classList.toggle('col-12', !requiresZone);
    zoneField?.classList.toggle('d-none', !requiresZone);

    if (zoneInput) {
        zoneInput.placeholder = selectedProduct.dataset.zonePlaceholder || 'Zone ID / Server';
        if (!requiresZone) {
            zoneInput.value = '';
        }
    }
}

function updateCheckoutSummary(checkout) {
    const layout = checkout.closest('.game-detail-layout');
    if (!layout) {
        return;
    }

    const selectedProduct = checkout.querySelector('[data-select-card="nominal"].is-active');
    const item = layout.querySelector('[data-summary-item]');
    const total = layout.querySelector('[data-summary-total]');

    if (item) {
        item.textContent = selectedProduct?.dataset.productName || 'Pilih produk';
    }

    if (total) {
        const price = parseMoneyValue(selectedProduct?.dataset.productPrice || '0');
        total.textContent = formatRupiah(price);
    }
}

function scheduleNicknameValidation(checkout) {
    if (!checkout || checkout.dataset.nicknameRequired !== '1') {
        updateBuyButtonState(checkout);
        return;
    }

    clearTimeout(checkout.nicknameTimer);
    checkout.nicknameTimer = setTimeout(() => {
        validateGameNickname(checkout);
    }, 550);
    updateBuyButtonState(checkout);
}

async function validateGameNickname(checkout) {
    const selectedProduct = checkout.querySelector('[data-select-card="nominal"].is-active');
    const targetInput = checkout.querySelector('[data-order-target]');
    const zoneInput = checkout.querySelector('[data-order-zone]');
    const result = checkout.querySelector('[data-nickname-result]');
    const target = targetInput?.value.trim() || '';
    const zone = zoneInput?.value.trim() || '';

    if (!selectedProduct || !target || (checkout.dataset.requiresZone === '1' && !zone)) {
        checkout.dataset.nicknameValid = '0';
        checkout.dataset.nicknameMessage = checkout.dataset.requiresZone === '1'
            ? 'Isi User ID dan Zone ID untuk mengecek akun.'
            : 'Isi User ID untuk mengecek akun.';
        if (target || zone) {
            showNicknameResult(result, checkout.dataset.nicknameMessage, true, false);
        } else {
            hideCheckoutMessage(result);
        }
        updateBuyButtonState(checkout);
        return;
    }

    const requestId = String(Date.now());
    checkout.dataset.nicknameRequestId = requestId;
    checkout.dataset.nicknameValid = '0';
    checkout.dataset.nicknameMessage = 'Mengecek akun game...';
    showNicknameResult(result, 'Mengecek akun game...', false, true);
    updateBuyButtonState(checkout);

    const payload = new FormData();
    payload.append('product_id', selectedProduct.dataset.productId || '');
    payload.append('target', target);
    payload.append('zone', zone);

    if (window.yii?.getCsrfParam && window.yii?.getCsrfToken) {
        payload.append(window.yii.getCsrfParam(), window.yii.getCsrfToken());
    }

    try {
        const response = await fetch(checkout.dataset.checkNicknameUrl, {
            method: 'POST',
            body: payload,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();

        if (checkout.dataset.nicknameRequestId !== requestId) {
            return;
        }

        if (!response.ok || data.status !== 'success') {
            checkout.dataset.nicknameValid = '0';
            checkout.dataset.nicknameMessage = data.message || 'Akun game tidak ditemukan.';
            showNicknameResult(result, checkout.dataset.nicknameMessage, true, false);
            updateBuyButtonState(checkout);
            return;
        }

        checkout.dataset.nicknameValid = '1';
        checkout.dataset.nicknameMessage = data.message || 'Akun game berhasil divalidasi.';
        showNicknameResult(result, checkout.dataset.nicknameMessage, false, false);
        updateBuyButtonState(checkout);
    } catch (error) {
        if (checkout.dataset.nicknameRequestId !== requestId) {
            return;
        }

        checkout.dataset.nicknameValid = '0';
        checkout.dataset.nicknameMessage = 'Gagal mengecek nickname. Coba lagi.';
        showNicknameResult(result, checkout.dataset.nicknameMessage, true, false);
        updateBuyButtonState(checkout);
    }
}

function resetNicknameValidation(checkout) {
    if (!checkout) {
        return;
    }

    const result = checkout.querySelector('[data-nickname-result]');
    hideCheckoutMessage(result);
    result?.classList.remove('is-loading');

    if (checkout.dataset.nicknameRequired !== '1') {
        checkout.dataset.nicknameValid = '1';
        checkout.dataset.nicknameMessage = '';
        checkout.dataset.nicknameRequestId = '';
        updateBuyButtonState(checkout);
        return;
    }

    checkout.dataset.nicknameValid = '0';
    checkout.dataset.nicknameMessage = '';
    checkout.dataset.nicknameRequestId = '';
    updateBuyButtonState(checkout);
}

function updateBuyButtonState(checkout) {
    if (!checkout) {
        return;
    }

    const buyButton = checkout.closest('.game-detail-layout')?.querySelector('[data-buy-now]');
    if (!buyButton) {
        return;
    }

    const target = checkout.querySelector('[data-order-target]')?.value.trim() || '';
    const zone = checkout.querySelector('[data-order-zone]')?.value.trim() || '';
    const needsNickname = checkout.dataset.nicknameRequired === '1';
    const needsZone = checkout.dataset.requiresZone === '1';
    const accountComplete = target !== '' && (!needsZone || zone !== '');

    buyButton.disabled = needsNickname && accountComplete && checkout.dataset.nicknameValid !== '1';
}

function showNicknameResult(element, text, isError, isLoading) {
    showCheckoutMessage(element, text, isError);
    element?.classList.toggle('is-loading', isLoading);
}

function formatRupiah(value) {
    return `Rp${Number(value || 0).toLocaleString('id-ID')}`;
}

function parseMoneyValue(value) {
    const rawValue = String(value || '').trim();
    if (!rawValue) {
        return 0;
    }

    const normalizedValue = rawValue.replace(/[^\d.,]/g, '');
    if (normalizedValue.includes(',') && normalizedValue.includes('.')) {
        return Number(normalizedValue.replace(/\./g, '').replace(',', '.')) || 0;
    }

    if (normalizedValue.includes(',') && !normalizedValue.includes('.')) {
        return Number(normalizedValue.replace(',', '.')) || 0;
    }

    if (/^\d{1,3}(\.\d{3})+$/.test(normalizedValue)) {
        return Number(normalizedValue.replace(/\./g, '')) || 0;
    }

    return Number(normalizedValue) || 0;
}

function showCheckoutMessage(element, text, isError) {
    if (!element) {
        return;
    }

    element.classList.remove('d-none');
    element.classList.toggle('text-danger', isError);
    element.classList.toggle('text-success', !isError);
    element.textContent = text;
}

function hideCheckoutMessage(element) {
    if (!element) {
        return;
    }

    element.classList.add('d-none');
    element.classList.remove('text-danger', 'text-success');
    element.textContent = '';
}
