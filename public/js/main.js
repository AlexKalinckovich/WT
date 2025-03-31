"use strict"
document.addEventListener("DOMContentLoaded", () => {
    console.log("Page Loaded");
});

const adminBtn = document.getElementById("adminBtn");
adminBtn.addEventListener("click",() => {
    const username = "admin";
    const password = prompt("Enter password:");
    let errorMessage;
    if (username && password) {
        fetch(`/checkPassword?username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`)
            .then(response => {
                if (!response.ok) throw new Error('Ошибка сети');
                return response.json();
            })
            .then(data => {
                if (data.valid) {
                    window.location.href = '/admin_panel';
                } else {
                    errorMessage.textContent = 'Неверный логин или пароль!';
                }
            })
            .catch(() => {
                errorMessage.textContent = 'Ошибка при проверке пароля. Попробуйте снова.';
            });
    }
});