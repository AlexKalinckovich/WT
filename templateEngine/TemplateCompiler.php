<?php

namespace MyTemplate;
use Exception;
use utils\SingletonTrait;

require_once __UTILS__ . '/SingletonTrait.php';

class TemplateCompiler
{
    use SingletonTrait;
    public function compile(string $template): string
    {
        $template = preg_replace('/{{\s*(.*?)\s*}}/', '<?php echo $1 ?>', $template);

        $template = preg_replace('/@if\s*\(\s*(.*?(\(.*?\))?)\s*\)/','<?php if($1): ?>',$template);

        $template = str_replace('@else','<?php else: ?>',$template);

        $template = str_replace('@endif','<?php endif; ?>',$template);

        $template = preg_replace('/@foreach\(\s*(.*?)\s*\)/','<?php foreach($1): ?>',$template);

        $template = str_replace('@endforeach','<?php endforeach; ?>',$template);

        return $template;
    }


    /**
     * @throws Exception
     */
    /**
     * Преобразует MJML-строку в HTML, вызывая MJML-CLI.
     *
     * @param string $mjmlContent
     * @return string
     * @throws \RuntimeException
     */
    public function compileMjmlToHtml(string $mjmlContent): string
    {
        $mjmlPath = __MJML_CMD_PATH__;
        $tempDir = sys_get_temp_dir();
        $prefix = uniqid('mjml_', true);
        $in  = "$tempDir/$prefix.mjml";
        $out = "$tempDir/$prefix.html";

        file_put_contents($in, $mjmlContent);

        $cmd = escapeshellarg($mjmlPath) . " " .
            escapeshellarg($in) .
            " -o " .
            escapeshellarg($out) .
            " 2>&1";

        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($out)) {
            throw new \RuntimeException("Ошибка компиляции: " . implode("\n", $output));
        }

        return file_get_contents($out);
    }

}