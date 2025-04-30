"use strict";

/**
 * Отправляет сообщение в серверный лог.
 * @param {'INFO'|'ERROR'|'DEBUG'} level
 * @param {string} message
 * @param {Object} [context]
 */
export async function logToServer(level, message, context = {}) {
    try {
        await fetch('/toLog', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ level, message, context })
        });
    } catch (e) {
        console.error('Ошибка логирования на сервере:', e);
    }
}
