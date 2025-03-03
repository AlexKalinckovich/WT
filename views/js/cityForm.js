// Получаем элементы
const cityFormModal = document.getElementById('cityFormModal');
const closeFormBtn = document.getElementById('closeFormBtn');

closeFormBtn.addEventListener('click', () => {
    cityFormModal.style.display = 'none';
});

window.addEventListener('click', (event) => {
    if (event.target === cityFormModal) {
        cityFormModal.style.display = 'none';
    }
});
