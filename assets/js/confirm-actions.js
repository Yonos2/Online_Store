(() => {
    const confirmedForms = new WeakSet();
    let pendingSubmission = null;

    const dialog = document.createElement('dialog');
    dialog.className = 'action-confirm-dialog';
    dialog.setAttribute('aria-labelledby', 'action-confirm-title');
    dialog.setAttribute('aria-describedby', 'action-confirm-message');

    const panel = document.createElement('section');
    panel.className = 'action-confirm-panel';

    const icon = document.createElement('span');
    icon.className = 'action-confirm-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = '!';

    const title = document.createElement('h2');
    title.id = 'action-confirm-title';

    const message = document.createElement('p');
    message.id = 'action-confirm-message';

    const actions = document.createElement('div');
    actions.className = 'action-confirm-actions';

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.className = 'action-confirm-cancel';
    cancelButton.textContent = 'Cancel';

    const confirmButton = document.createElement('button');
    confirmButton.type = 'button';
    confirmButton.className = 'action-confirm-submit';

    const continueSubmission = () => {
        if (!pendingSubmission) {
            return;
        }

        const { form, submitter } = pendingSubmission;
        pendingSubmission = null;
        confirmedForms.add(form);

        if (submitter instanceof HTMLElement && submitter.form === form) {
            form.requestSubmit(submitter);
        } else {
            form.requestSubmit();
        }
    };

    actions.append(cancelButton, confirmButton);
    panel.append(icon, title, message, actions);
    dialog.append(panel);
    document.body.append(dialog);

    cancelButton.addEventListener('click', () => dialog.close('cancel'));
    confirmButton.addEventListener('click', () => dialog.close('confirm'));

    dialog.addEventListener('close', () => {
        if (dialog.returnValue !== 'confirm' || !pendingSubmission) {
            pendingSubmission = null;
            return;
        }

        continueSubmission();
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (confirmedForms.has(form)) {
            confirmedForms.delete(form);
            return;
        }

        const submitter = event.submitter;
        const messageText = submitter?.dataset.confirmMessage
            ?? form.dataset.confirmMessage;
        if (!messageText) {
            return;
        }

        event.preventDefault();

        const selectedStatus = form.querySelector('select[name="order_status"]');
        const selectedStatusLabel = selectedStatus instanceof HTMLSelectElement
            ? selectedStatus.selectedOptions[0]?.textContent?.trim()
            : '';

        pendingSubmission = { form, submitter };
        title.textContent = submitter?.dataset.confirmTitle
            ?? form.dataset.confirmTitle
            ?? 'Please confirm';
        message.textContent = selectedStatusLabel
            ? `${messageText} The new status will be ${selectedStatusLabel}.`
            : messageText;
        confirmButton.textContent = submitter?.dataset.confirmAction
            ?? form.dataset.confirmAction
            ?? 'Continue';

        const isDestructive = (submitter?.dataset.confirmVariant
            ?? form.dataset.confirmVariant) === 'danger';
        dialog.classList.toggle('is-destructive', isDestructive);

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else if (window.confirm(message.textContent)) {
            continueSubmission();
        } else {
            pendingSubmission = null;
        }
    });
})();
