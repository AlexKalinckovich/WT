document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.getElementById('uploadForm');
    const uploadStatus = document.getElementById('uploadStatus');

    if (uploadForm) { setUploadFileHandlers(uploadForm,uploadStatus) }

    const modal = document.getElementById('fileModal');
    const modalContent = document.querySelector('#fileModal .modal-content pre');
    const closeModal = document.querySelector('#fileModal .close');

    setViewBtnHandlers(modalContent,modal)
    setDeleteButtonHandlers()

    if (closeModal) {
        closeModal.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

});

function setUploadFileHandlers(uploadForm,uploadStatus){
    uploadForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(uploadForm);
        fetch('/uploadFile', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    uploadStatus.textContent = 'Файл успешно загружен!';
                    uploadStatus.style.color = 'green';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    uploadStatus.textContent = 'Ошибка: ' + data.message;
                    uploadStatus.style.color = 'red';
                }
            })
            .catch(error => {
                uploadStatus.textContent = 'Ошибка сети: ' + error.message;
                uploadStatus.style.color = 'red';
            });
    });
}

//###################Return info in file for showing######################
async function fetchFileContent(filePath,modalContent,modal) {
    await fetch(`/getFileContent?path=${encodeURIComponent(filePath)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalContent.textContent = data.content;
                modal.style.display = 'block';
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            alert('Ошибка сети: ' + error.message);
        });
}
//###################Return info in file for showing######################


function setDeleteButtonHandlers() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const filePath = button.getAttribute('data-path');
            if (confirm('Вы уверены?')) {
                const response = await fetch(`/deleteFile?path=${encodeURIComponent(filePath)}`, {
                    method:'PUT'
                });

                if (!response.ok) {
                    alert("Error deleting file.");
                }

                const data = await response.json();
                if (data.success) {
                    button.closest('.file-item').remove();
                } else {
                    alert(data.message || 'Ошибка удаления');
                }
            }
        });
    });
}

//###################Show file data by click on item or view.btn ######################
function setViewBtnHandlers(modalContent, modal) {
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const filePath = button.getAttribute('data-path');
            await fetchFileContent(filePath,modalContent,modal);
        });
    });
    document.querySelectorAll('.file-item').forEach(item =>{
        item.addEventListener('click', async () => {
            const btn = item.querySelector('.view-btn');
            const filePath = btn.getAttribute('data-path');
            await fetchFileContent(filePath,modalContent,modal);
        });
    })
}
//###################Show file data by click on item or view.btn ######################