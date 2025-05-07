"use strict";

document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-element-type][data-element-id]');
        if (el) {
            const {elementType, elementId} = el.dataset;
            try {
                await fetch('/trackClick', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({elementType, elementId})
                });
            } catch (error) {
                console.error(error);
            }
        }
    });
});
