"use strict";
import {handleFileView} from "./handleFileView.js";

export function initializeFileViewHandlers(textElement, imageElement, modal) {
    const viewButtons = document.querySelectorAll('.view-btn');
    const fileItems = document.querySelectorAll('.file-item');

    viewButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const filePath = button.getAttribute('data-path');
            await handleFileView(filePath, textElement, imageElement, modal);
        });
    });

    fileItems.forEach(item => {
        item.addEventListener('click', async () => {
            const button = item.querySelector('.view-btn');
            if (button) {
                const filePath = button.getAttribute('data-path');
                await handleFileView(filePath, textElement, imageElement, modal);
            }
        });
    });
}