(() => {
    const fileInput = document.querySelector('#product_image');
    const previewPanel = document.querySelector('[data-image-preview]');
    const previewImage = document.querySelector('[data-preview-image]');
    const fileInfo = document.querySelector('[data-preview-file-info]');

    if (!fileInput || !previewPanel || !previewImage || !fileInfo) {
        return;
    }

    let previewUrl = null;
    const supportedTypes = new Set([
        'image/jpeg',
        'image/png',
        'image/webp'
    ]);

    fileInput.addEventListener('change', () => {
        if (previewUrl !== null) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        const file = fileInput.files?.[0];
        if (!file) {
            previewPanel.hidden = true;
            previewImage.removeAttribute('src');
            fileInfo.textContent = '';
            return;
        }

        const extensionIsSupported = /\.(jpe?g|png|webp)$/i.test(file.name);
        if (
            !extensionIsSupported ||
            (file.type !== '' && !supportedTypes.has(file.type))
        ) {
            previewPanel.hidden = true;
            previewImage.removeAttribute('src');
            fileInfo.textContent = '';
            return;
        }

        previewUrl = URL.createObjectURL(file);
        previewImage.src = previewUrl;
        fileInfo.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
        previewPanel.hidden = false;
    });
})();
