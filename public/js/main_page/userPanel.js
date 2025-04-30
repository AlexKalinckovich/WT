"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const logoutBtn        = document.getElementById("logoutBtn");
    const changeAccountBtn = document.getElementById("changeAccountBtn");

    if (logoutBtn) {
        logoutBtn.addEventListener("click", async () => {
            await fetch("/logout", { method: "POST" });
            window.location.reload();
        });
    }

    if (changeAccountBtn) {
        changeAccountBtn.addEventListener("click", () => {
            fetch("/logout", { method: "POST" })
                .then(() => window.location.href = "/login");
        });
    }
});
