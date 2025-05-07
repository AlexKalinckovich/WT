"use strict";
document.addEventListener("DOMContentLoaded", () => {
    const form     = document.getElementById("login-form");
    const btn      = document.getElementById("submitBtn");
    const errorBox = document.getElementById("loginError");

    function hashPassword(input) {
        return CryptoJS.SHA256(input).toString(CryptoJS.enc.Hex);
    }

    form.addEventListener("submit", async e => {
        e.preventDefault();
        btn.disabled = true;
        errorBox.textContent = "";

        const email        = document.getElementById("email").value.trim();
        const password            = document.getElementById("password").value;
        const rememberMe  = document.getElementById("rememberMe").checked;

        try {
            const passwordHash = hashPassword(password);

            const payload = { email, passwordHash, rememberMe };
            const res     = await fetch("/authorize", {
                method:  "POST",
                headers: { "Content-Type": "application/json" },
                body:    JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = "/";
            } else {
                errorBox.textContent = data.error || "Неизвестная ошибка";
                btn.disabled = false;
            }

            console.log("INFO", "Авторизация", data);
        } catch (err) {

            errorBox.textContent = "Сбой подключения";
            btn.disabled = false;
            console.log("ERROR", "Ошибка /authorize", {message: err.message});
        }
    });
});
