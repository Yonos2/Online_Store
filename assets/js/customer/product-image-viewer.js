(() => {
    const dialog = document.querySelector('.product-image-dialog');
    const dialogImage = document.querySelector('[data-product-image-dialog-img]');
    const caption = document.querySelector('[data-product-image-caption]');

    if (
        !(dialog instanceof HTMLDialogElement) ||
        !(dialogImage instanceof HTMLImageElement) ||
        !(caption instanceof HTMLElement) ||
        typeof dialog.showModal !== 'function'
    ) {
        return;
    }

    document.addEventListener('click', (event) => {
        const link = event.target instanceof Element
            ? event.target.closest('[data-product-image-view]')
            : null;

        if (!(link instanceof HTMLAnchorElement) || event.defaultPrevented) {
            return;
        }

        if (
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        event.preventDefault();
        dialogImage.src = link.href;
        dialogImage.alt = link.dataset.productName ?? '';
        caption.textContent = link.dataset.productName ?? '';
        dialog.showModal();
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    dialog.addEventListener('close', () => {
        dialogImage.removeAttribute('src');
        dialogImage.alt = '';
        caption.textContent = '';
    });
})();
