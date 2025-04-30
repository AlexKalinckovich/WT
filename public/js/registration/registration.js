"use strict";
window.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('registration-form');
    const submitBtn = document.getElementById('submitBtn');
    const captchaCanvas = document.getElementById('captchaCanvas');
    const captchaInput = document.getElementById('captchaInput');
    const refreshCaptcha = document.getElementById('refreshCaptcha')

    setupCaptchaHandlers(captchaInput, refreshCaptcha, submitBtn, captchaCanvas);
    function hashPassword(input) {
        return CryptoJS.SHA256(input).toString(CryptoJS.enc.Hex);
    }

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const userName = document.getElementById('userName').value.trim();
        const userSurname = document.getElementById('userSurname').value.trim();
        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;

        const firstHash = hashPassword(password);
        const payload = {
            userName: userName,
            userSurname: userSurname,
            email: email,
            passwordHash: firstHash,
            rememberMe: rememberMe
        };

        try {
            const res  = await fetch('/registerUser', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            await console.log('INFO', 'Ответ сервера при регистрации', data);
            window.location.href = '/';
        } catch (err) {
            await console.log('ERROR', 'Ошибка регистрации', {message: err.message});
        }
    });
});

/**
 * Настраивает обработчики ввода и кнопок капчи.
 * @param {HTMLInputElement} input Поле для ввода капчи.
 * @param {HTMLButtonElement} refreshBtn Кнопка обновления капчи.
 * @param {HTMLButtonElement} submitBtn Кнопка отправки формы.
 * @param {HTMLCanvasElement} canvas Канвас для капчи.
 * @returns {{currentCaptcha: string}} Текущее значение капчи.
 */
function setupCaptchaHandlers(input,
                                     refreshBtn,
                                     submitBtn,
                                     canvas){

    let currentCaptcha = generateCaptcha(canvas);
    input.addEventListener('input',() => {
        submitBtn.disabled = input.value !== currentCaptcha;
    });

    refreshBtn.addEventListener('click', () => {
        currentCaptcha = generateCaptcha(canvas);
        submitBtn.disabled = true;
    });

    return { currentCaptcha };
}

/**
 * Рисует случайный код на canvas и возвращает его.
 * @param {HTMLCanvasElement} canvas Канвас для отображения капчи.
 * @param {number} length Длина генерируемого кода (по умолчанию 6).
 * @returns {string} Сгенерированный код капчи.
 */
function generateCaptcha(canvas, length = 6){
    const context = canvas.getContext('2d');
    context.clearRect(0, 0, canvas.width, canvas.height);

    const captcha = Math.random().toString(36).substring(2, length);
    context.font = '24px Arial';
    context.fillText(captcha,20,35);
    return captcha;
}
