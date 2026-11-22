(() => {
    const filterPanel = document.querySelector('[data-product-filters]');
    const searchInput = document.querySelector('[data-product-search]');
    const resultCount = document.querySelector('[data-product-count]');
    const noMatchesMessage = document.querySelector('[data-no-product-matches]');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));

    if (
        !filterPanel ||
        !searchInput ||
        !resultCount ||
        !noMatchesMessage ||
        rows.length === 0
    ) {
        return;
    }

    const updateVisibleProducts = () => {
        const query = searchInput.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const matches = row.dataset.searchText
                .toLocaleLowerCase()
                .includes(query);

            row.hidden = !matches;
            if (matches) {
                visibleCount += 1;
            }
        });

        noMatchesMessage.hidden = visibleCount > 0;
        resultCount.textContent = `Showing ${visibleCount} of ${rows.length} products.`;
    };

    filterPanel.hidden = false;
    updateVisibleProducts();
    searchInput.addEventListener('input', updateVisibleProducts);
})();
