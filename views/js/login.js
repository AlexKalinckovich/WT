document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Отменяем стандартное поведение формы

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

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
        } else {
            errorMessage.textContent = 'Пожалуйста, заполните все поля!';
        }
    });
});