document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-mobile-toggle]');
    const links = document.querySelector('.nav-links');

    if (toggle && links) {
        toggle.addEventListener('click', () => links.classList.toggle('open'));
    }

    document.querySelectorAll('[data-qty]').forEach((control) => {
        const input = control.querySelector('input');
        const minus = control.querySelector('[data-qty-minus]');
        const plus = control.querySelector('[data-qty-plus]');
        const max = parseInt(input?.getAttribute('max') || '99', 10);

        if (minus) {
            minus.addEventListener('click', () => {
                const value = Math.max(1, parseInt(input.value || '1', 10) - 1);
                input.value = value;
                input.dispatchEvent(new Event('change'));
            });
        }

        if (plus) {
            plus.addEventListener('click', () => {
                const value = Math.min(max, parseInt(input.value || '1', 10) + 1);
                input.value = value;
                input.dispatchEvent(new Event('change'));
            });
        }
    });

    document.querySelectorAll('[data-auto-submit]').forEach((input) => {
        input.addEventListener('change', () => input.form?.submit());
    });

    const gallery = document.querySelector('[data-gallery-main]');
    document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (!gallery) return;
            gallery.src = thumb.dataset.src;
            document.querySelectorAll('[data-gallery-thumb]').forEach((item) => item.classList.remove('active'));
            thumb.classList.add('active');
        });
    });

    document.querySelectorAll('.alert').forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s, transform .4s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';
            setTimeout(() => alert.remove(), 450);
        }, 6000);
    });

    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            if (!window.confirm(element.dataset.confirm || 'Yakin?')) {
                event.preventDefault();
            }
        });
    });
});
