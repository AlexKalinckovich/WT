const navItems = Array.from(document.getElementsByClassName("nav-item"));
let currentActiveIndex = navItems.findIndex(item =>
    item.querySelector('.nav-link')?.classList.contains("active")
);

for (let i = 0; i < navItems.length; i++) {
    navItems[i].addEventListener("click", async () => {
        navItems[i].querySelector('.nav-link').textContent.trim();
        if (currentActiveIndex !== -1) {
            navItems[currentActiveIndex].querySelector('.nav-link').classList.remove('active');
        }

        navItems[i].querySelector('.nav-link').classList.add('active');

        currentActiveIndex = i;
    });
}
