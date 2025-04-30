<?php
// utils/Logger.php

namespace utils;

class Logger
{
    /**
     * Записать строку в лог.
     *
     * @param string $level   Уровень: INFO, ERROR, DEBUG…
     * @param string $message Текст сообщения
     * @param array  $context Доп. данные для контекста
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $ctx  = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[$date] [$level] $message$ctx\n";
        file_put_contents(__LOGS__ . '/server.log', $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $msg, array $ctx = []): void  { self::log('INFO',  $msg, $ctx); }
    public static function error(string $msg, array $ctx = []): void { self::log('ERROR', $msg, $ctx); }
    public static function debug(string $msg, array $ctx = []): void { self::log('DEBUG', $msg, $ctx); }
}
