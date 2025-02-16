// JavaScript to handle order button clicks
const orderButtons = document.querySelectorAll('.order-btn');
orderButtons.forEach(button => {
    button.addEventListener('click', () => {
        const foodItem = button.parentElement.querySelector('h3').innerText;
        alert(`You have ordered a ${foodItem}!`);
    });
});