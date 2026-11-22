(() => {
    const popup = document.querySelector('[data-logout-popup]');
    if (!(popup instanceof HTMLDialogElement)) {
        return;
    }

    const closeButton = popup.querySelector('[data-close-popup]');
    closeButton?.addEventListener('click', () => popup.close());

    if (typeof popup.showModal === 'function') {
        popup.showModal();
    }
})();
