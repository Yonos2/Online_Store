(() => {
    const printButton = document.querySelector('[data-print-page]');
    if (!printButton) {
        return;
    }

    printButton.hidden = false;
    printButton.addEventListener('click', () => {
        window.print();
    });
})();
