(() => {
    const toggles = document.querySelectorAll('.dashboard-section-toggle');

    toggles.forEach((toggle) => {
        const dialogId = toggle.getAttribute('data-dialog-target');
        const dialog = dialogId
            ? document.getElementById(dialogId)
            : null;

        if (
            !(toggle instanceof HTMLButtonElement) ||
            !(dialog instanceof HTMLDialogElement) ||
            typeof dialog.showModal !== 'function'
        ) {
            return;
        }

        toggle.addEventListener('click', () => {
            if (!dialog.open) {
                dialog.showModal();
            }
        });
    });

    const updatesButton = document.querySelector('[data-order-updates-button]');
    const updatesList = document.querySelector('[data-live-order-updates]');
    const updatesCount = document.querySelector('[data-order-updates-count]');
    const updatesBadge = document.querySelector('[data-order-updates-badge]');
    const emptyMessage = document.querySelector('[data-no-live-order-updates]');
    const moreMessage = document.querySelector('[data-live-more-order-updates]');

    if (
        !(updatesButton instanceof HTMLButtonElement) ||
        !(updatesList instanceof HTMLUListElement) ||
        !(updatesCount instanceof HTMLElement) ||
        !(updatesBadge instanceof HTMLElement) ||
        !(emptyMessage instanceof HTMLElement) ||
        !(moreMessage instanceof HTMLElement)
    ) {
        return;
    }

    const initialUpdates = Array.from(
        updatesList.querySelectorAll('[data-order-update-id]')
    ).map((item) => ({
        id: Number(item.dataset.orderUpdateId),
        marker: `${item.dataset.orderStatus ?? ''}|${item.dataset.orderUpdatedAt ?? ''}`
    })).filter((update) => Number.isInteger(update.id) && update.id > 0);
    const knownMarkers = new Map(
        initialUpdates.map((update) => [Number(update.id), update.marker])
    );
    const productUnreadCount = Number(updatesBadge.dataset.productUnreadCount) || 0;
    let toastTimer;
    let requestInFlight = false;

    const orderUpdatesDialog = document.getElementById('order-updates-dialog');

    const showLiveToast = (update) => {
        let toast = document.querySelector('[data-order-live-toast]');
        if (!(toast instanceof HTMLElement)) {
            toast = document.createElement('aside');
            toast.className = 'dashboard-order-live-toast';
            toast.setAttribute('role', 'status');
            toast.setAttribute('aria-live', 'polite');
            toast.dataset.orderLiveToast = '';

            const text = document.createElement('p');
            text.dataset.toastMessage = '';
            const viewButton = document.createElement('button');
            viewButton.type = 'button';
            viewButton.textContent = 'View updates';
            viewButton.addEventListener('click', () => {
                if (
                    orderUpdatesDialog instanceof HTMLDialogElement &&
                    !orderUpdatesDialog.open
                ) {
                    orderUpdatesDialog.showModal();
                }
                toast.hidden = true;
            });
            toast.append(text, viewButton);
            document.body.append(toast);
        }

        const toastMessage = toast.querySelector('[data-toast-message]');
        if (toastMessage) {
            toastMessage.textContent =
                `Order ${update.order_number} is now ${update.order_status}.`;
        }

        toast.hidden = false;
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => {
            toast.hidden = true;
        }, 8000);
    };

    const renderUpdates = (updates, unreadCount) => {
        updatesList.replaceChildren();

        updates.slice(0, 5).forEach((update) => {
            const item = document.createElement('li');
            const details = document.createElement('div');
            const orderLabel = document.createElement('strong');
            const status = document.createElement('span');
            const link = document.createElement('a');

            item.dataset.orderUpdateId = String(update.id);
            item.dataset.orderStatus = update.order_status;
            item.dataset.orderUpdatedAt = update.updated_at;
            orderLabel.textContent = `Order ${update.order_number}`;

            const updatedAt = new Date(update.updated_at);
            status.textContent = `${update.order_status.charAt(0).toUpperCase()}${update.order_status.slice(1)}`
                + (Number.isNaN(updatedAt.getTime())
                    ? ''
                    : ` · ${updatedAt.toLocaleDateString()}`);

            link.href = `${document.body.dataset.orderDetailsUrl}?id=${encodeURIComponent(update.id)}`;
            link.textContent = 'View update';

            details.append(orderLabel, status);
            item.append(details, link);
            updatesList.append(item);
        });

        emptyMessage.hidden = unreadCount !== 0;
        updatesList.hidden = unreadCount === 0;
        updatesCount.textContent = `${unreadCount} unread`;
        updatesCount.hidden = unreadCount === 0;
        moreMessage.textContent = `Showing 5 of ${unreadCount} unread updates.`;
        moreMessage.hidden = unreadCount <= 5;

        const totalBadgeCount = unreadCount + productUnreadCount;
        updatesBadge.textContent = String(totalBadgeCount);
        updatesBadge.hidden = totalBadgeCount === 0;
    };

    const pollOrderUpdates = async () => {
        if (
            requestInFlight ||
            document.visibilityState !== 'visible'
        ) {
            return;
        }

        requestInFlight = true;
        try {
            const response = await fetch(document.body.dataset.orderUpdatesUrl, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { Accept: 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`Order update request failed (${response.status}).`);
            }

            const result = await response.json();
            if (!Array.isArray(result.updates) || !Number.isInteger(result.unread_count)) {
                throw new Error('The order update response was invalid.');
            }

            result.updates.forEach((update) => {
                const marker = `${update.order_status}|${update.updated_at}`;
                const previousMarker = knownMarkers.get(Number(update.id));
                if (previousMarker !== marker) {
                    showLiveToast(update);
                }
                knownMarkers.set(Number(update.id), marker);
            });

            const activeIds = new Set(
                result.updates.map((update) => Number(update.id))
            );
            knownMarkers.forEach((_marker, id) => {
                if (!activeIds.has(id)) {
                    knownMarkers.delete(id);
                }
            });

            renderUpdates(result.updates, result.unread_count);
        } catch (error) {
            console.error('Unable to refresh customer order updates.', error);
        } finally {
            requestInFlight = false;
        }
    };

    const pollInterval = window.setInterval(pollOrderUpdates, 10000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            pollOrderUpdates();
        }
    });

    window.addEventListener('pagehide', () => {
        window.clearInterval(pollInterval);
    }, { once: true });

    pollOrderUpdates();
})();
