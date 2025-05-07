<?php

namespace services;

class TranslatorService
{
    public static function getMessages(string $lang): array
    {
        return yaml_parse_file(__LANG__ . '/' . $lang . '.yaml');
    }
}