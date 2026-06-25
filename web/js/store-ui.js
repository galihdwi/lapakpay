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
            updateCheckoutSummary(scope);
        });
    });

    document.querySelectorAll('[data-checkout]').forEach((checkout) => {
        updateCheckoutSummary(checkout);

        const buyButton = checkout.closest('.game-detail-layout')?.querySelector('[data-buy-now]');
        if (!buyButton) {
            return;
        }

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

            if (!emailInput?.value.trim()) {
                showCheckoutMessage(message, 'Email wajib diisi.', true);
                emailInput?.focus();
                return;
            }

            const payload = new FormData();
            payload.append('product_id', selectedProduct.dataset.productId || '');
            payload.append('target', targetInput.value.trim());
            payload.append('zone', zoneInput?.value.trim() || '');
            payload.append('payment_method', 'Flip');
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

                showCheckoutMessage(message, `Invoice ${data.invoice_number} dibuat. Mengalihkan ke Flip...`, false);
                window.location.href = data.payment_url;
            } catch (error) {
                showCheckoutMessage(message, 'Gagal menghubungi server. Coba lagi.', true);
            } finally {
                buyButton.disabled = false;
                buyButton.textContent = 'Beli Sekarang';
            }
        });
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
