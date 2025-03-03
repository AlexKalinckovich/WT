const navItems = Array.from(document.getElementsByClassName("nav-item"));
let currentActiveIndex = navItems.findIndex(item =>
    item.querySelector('.nav-link')?.classList.contains("active")
);
for (let i = 0; i < navItems.length; i++) {
    navItems[i].addEventListener("click", async () => {
        const activeLink = navItems[i].querySelector('.nav-link').textContent.trim();
        const response = await fetch('/nav', {
            method: "PUT",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({active: activeLink})
        });

        if (!response.ok) {
            const error = await response.text();
            throw new Error(`HTTP ${response.status}: ${error}`);
        }

        if (currentActiveIndex !== -1) {
            navItems[currentActiveIndex].querySelector('.nav-link').classList.remove('active');
        }

        navItems[i].querySelector('.nav-link').classList.add('active');

        currentActiveIndex = i;
    });
}
