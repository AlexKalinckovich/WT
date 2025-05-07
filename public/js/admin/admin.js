"use strict";
import {initializeUploadHandler} from "./initializeUploadHandlers.js";
import {initializeModalControls} from "./initializeModalControls.js";
import {initializeDeleteHandlers} from "./initializeDeleteHandlers.js";
import {initializeFileViewHandlers} from "./initializeFileViewHandlers.js";

document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    const uploadStatus = document.getElementById('uploadStatus');
    const modal = document.getElementById('fileModal');
    const modalContent = document.getElementById('fileContent');
    const imageElement = document.getElementById('fileImage');
    const closeModalBtn = modal.querySelector('.close');

    if (uploadForm) {
        initializeUploadHandler(uploadForm, uploadStatus);
    }

    if (modal && closeModalBtn) {
        initializeModalControls(modal, closeModalBtn);
    }

    initializeFileViewHandlers(modalContent, imageElement, modal);
    initializeDeleteHandlers();
});


