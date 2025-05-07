"use strict";

/**
 * Обработчик загрузки файлов
 */
export function initializeUploadHandler(form, statusElement) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch('/uploadFile', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                statusElement.textContent = 'Файл успешно загружен!';
                statusElement.style.color = 'green';
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showError(statusElement, data.message);
            }
        } catch (error) {
            showError(statusElement, error.message);
        }
    });
}