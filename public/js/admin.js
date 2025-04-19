document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    const uploadStatus = document.getElementById('uploadStatus');
    const modal = document.getElementById('fileModal');
    const modalContent = document.getElementById('fileContent');
    const imageElement = document.getElementById('fileImage');
    const closeModalBtn = modal.querySelector('.close');

    if (uploadForm) {
        initializeUploadHandler(uploadForm, uploadStatus);
    }

    if (modal && closeModalBtn) {
        initializeModalControls(modal, closeModalBtn);
    }

    initializeFileViewHandlers(modalContent, imageElement, modal);
    initializeDeleteHandlers();
});

/**
 * Обработчик загрузки файлов
 */
function initializeUploadHandler(form, statusElement) {
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

function showError(element, message) {
    element.textContent = 'Ошибка: ' + message;
    element.style.color = 'red';
}

/**
 * Обработчик модального окна
 */
function initializeModalControls(modal, closeButton) {
    closeButton.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

/**
 * Обработка нажатий на кнопки удаления
 */
function initializeDeleteHandlers() {
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

/**
 * Инициализация кнопок и элементов для отображения файлов
 */
function initializeFileViewHandlers(textElement, imageElement, modal) {
    const viewButtons = document.querySelectorAll('.view-btn');
    const fileItems = document.querySelectorAll('.file-item');

    viewButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const filePath = button.getAttribute('data-path');
            await handleFileView(filePath, textElement, imageElement, modal);
        });
    });

    fileItems.forEach(item => {
        item.addEventListener('click', async () => {
            const button = item.querySelector('.view-btn');
            if (button) {
                const filePath = button.getAttribute('data-path');
                await handleFileView(filePath, textElement, imageElement, modal);
            }
        });
    });
}

/**
 * Отображение содержимого файла в модальном окне
 */
async function handleFileView(filePath, textElement, imageElement, modal) {
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

