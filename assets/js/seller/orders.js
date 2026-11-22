(() => {
    const filterPanel = document.querySelector('[data-order-filters]');
    const searchInput = document.querySelector('[data-order-search]');
    const statusFilter = document.querySelector('[data-order-status-filter]');
    const resultCount = document.querySelector('[data-order-count]');
    const noMatchesMessage = document.querySelector('[data-no-order-matches]');
    const rows = Array.from(document.querySelectorAll('[data-order-row]'));

    if (
        !filterPanel ||
        !searchInput ||
        !statusFilter ||
        !resultCount ||
        !noMatchesMessage ||
        rows.length === 0
    ) {
        return;
    }

    const searchableText = new Map(
        rows.map((row) => {
            const cells = Array.from(row.cells);
            const text = cells
                .map((cell, index) => (
                    index === 4 ? row.dataset.orderStatus : cell.textContent
                ))
                .join(' ')
                .toLocaleLowerCase();

            return [row, text];
        })
    );

    const updateVisibleOrders = () => {
        const query = searchInput.value.trim().toLocaleLowerCase();
        const selectedStatus = statusFilter.value;
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesSearch = searchableText.get(row).includes(query);
            const matchesStatus = (
                selectedStatus === '' ||
                row.dataset.orderStatus === selectedStatus
            );
            const isVisible = matchesSearch && matchesStatus;

            row.hidden = !isVisible;
            if (isVisible) {
                visibleCount += 1;
            }
        });

        noMatchesMessage.hidden = visibleCount > 0;
        resultCount.textContent = `Showing ${visibleCount} of ${rows.length} orders.`;
    };

    filterPanel.hidden = false;
    updateVisibleOrders();
    searchInput.addEventListener('input', updateVisibleOrders);
    statusFilter.addEventListener('change', updateVisibleOrders);
})();
