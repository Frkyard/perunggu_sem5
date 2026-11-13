(() => {
    const modal = document.getElementById('delete-confirm-modal');
    if (!modal) return;

    const recordElement = document.getElementById('delete-confirm-record');
    const tableElement = document.getElementById('delete-confirm-table');
    const idElement = document.getElementById('delete-confirm-id');
    const confirmButton = document.getElementById('delete-confirm-submit');
    const cancelButtons = modal.querySelectorAll('[data-delete-cancel]');
    let pendingForm = null;
    let lastTrigger = null;

    function closeModal() {
        if (modal.hidden) return;
        modal.classList.remove('is-open');
        document.body.classList.remove('delete-confirm-open');
        pendingForm = null;
        window.setTimeout(() => {
            if (!modal.classList.contains('is-open')) modal.hidden = true;
        }, 170);
        if (lastTrigger) lastTrigger.focus();
    }

    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-delete-confirm]');
        if (!trigger) return;

        const form = trigger.closest('form');
        if (!form) return;
        event.preventDefault();

        pendingForm = form;
        lastTrigger = trigger;
        tableElement.textContent = trigger.dataset.tableLabel || 'data ini';
        recordElement.textContent = trigger.dataset.recordLabel || 'Data tanpa nama';
        idElement.textContent = `ID #${trigger.dataset.recordId || ''}`;
        confirmButton.disabled = false;
        confirmButton.removeAttribute('aria-busy');
        confirmButton.textContent = 'Ya, Hapus';
        modal.hidden = false;
        document.body.classList.add('delete-confirm-open');
        requestAnimationFrame(() => modal.classList.add('is-open'));
        modal.querySelector('[data-delete-cancel]').focus();
    });

    cancelButtons.forEach(button => button.addEventListener('click', closeModal));

    modal.addEventListener('click', event => {
        if (event.target === modal) closeModal();
    });

    modal.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.key !== 'Tab') return;
        const focusable = Array.from(modal.querySelectorAll('button:not(:disabled)'));
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    confirmButton.addEventListener('click', () => {
        if (!pendingForm) return;

        confirmButton.disabled = true;
        confirmButton.setAttribute('aria-busy', 'true');
        confirmButton.textContent = 'Menghapus...';
        if (typeof pendingForm.requestSubmit === 'function') {
            pendingForm.requestSubmit();
        } else {
            pendingForm.submit();
        }
    });
})();
