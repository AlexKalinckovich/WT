export function loadPage(url, targetSelector) {
    fetch(url)
        .then(response => {
            if(!response.ok){
                throw new Error("Error on page loading")
            }
            return response.text();
        })
        .then(html => {
            const targetElement = document.querySelector(targetSelector);
            if(targetElement){
                targetElement.innerHTML = html;
            }else{
                console.error(`${targetSelector} not found`);
            }
        })
        .catch(error => {
            console.error("Error on page loading");
        })
}