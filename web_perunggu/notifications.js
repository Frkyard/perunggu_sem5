(() => {
    const titles = {
        success: 'Berhasil',
        error: 'Terjadi kendala',
        warning: 'Perhatian',
        info: 'Informasi'
    };
    const icons = {
        success: '✓',
        error: '!',
        warning: '!',
        info: 'i'
    };
    let region;

    function getRegion() {
        if (!region) {
            region = document.createElement('div');
            region.className = 'site-notification-region';
            region.setAttribute('aria-label', 'Notifikasi');
            document.body.appendChild(region);
        }

        return region;
    }

    window.showSiteNotification = (message, options = {}) => {
        const allowedTypes = ['success', 'error', 'warning', 'info'];
        const type = allowedTypes.includes(options.type) ? options.type : 'info';
        const notification = document.createElement('section');
        notification.className = `site-notification site-notification--${type}`;
        notification.setAttribute('role', type === 'error' ? 'alert' : 'status');
        notification.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');

        const icon = document.createElement('span');
        icon.className = 'site-notification__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = icons[type];

        const content = document.createElement('div');
        content.className = 'site-notification__content';
        const title = document.createElement('strong');
        title.className = 'site-notification__title';
        title.textContent = options.title || titles[type];
        const description = document.createElement('p');
        description.className = 'site-notification__message';
        description.textContent = String(message);
        content.append(title, description);

        const closeButton = document.createElement('button');
        closeButton.className = 'site-notification__close';
        closeButton.type = 'button';
        closeButton.setAttribute('aria-label', 'Tutup notifikasi');
        closeButton.textContent = '×';

        notification.append(icon, content, closeButton);
        getRegion().appendChild(notification);

        const dismiss = () => {
            notification.classList.remove('is-visible');
            window.setTimeout(() => notification.remove(), 220);
        };
        closeButton.addEventListener('click', dismiss);
        requestAnimationFrame(() => notification.classList.add('is-visible'));

        const duration = options.duration === undefined ? 5000 : Number(options.duration);
        if (duration > 0) {
            window.setTimeout(dismiss, duration);
        }
    };
})();
