"use strict";

export function initializeDeleteHandlers() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const filePath = button.getAttribute('data-path');

            if (confirm('Вы уверены?')) {
                try {
                    const response = await fetch(`/deleteFile?path=${encodeURIComponent(filePath)}`, {
                        method: 'PUT'
                    });

                    const data = await response.json();

                    if (data.success) {
                        button.closest('.file-item').remove();
                    } else {
                        alert(data.message || 'Ошибка удаления');
                    }
                } catch (error) {
                    alert('Ошибка удаления: ' + error.message);
                }
            }
        });
    });
}