"use strict";

export async function handleFileView(filePath, textElement, imageElement, modal) {
    try {
        const response = await fetch(`/getFileContent?path=${encodeURIComponent(filePath)}`);

        if (!response.ok) {
            alert('Ошибка сети: ' + response.message);
        } else {
            const data = await response.json();

            if (!data.success) {
                alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                return;
            }

            const {isImage, content, src} = data;

            if (isImage) {
                imageElement.src = src;
                imageElement.style.display = 'block';
                textElement.style.display = 'none';
            } else {
                textElement.textContent = content;
                textElement.style.display = 'block';
                imageElement.style.display = 'none';
            }

            modal.style.display = 'block';
        }

    } catch (error) {
        alert('Ошибка сети: ' + error.message);
    }
}
