<?php
declare(strict_types=1);
namespace MyTemplate;
require_once __TEMPLATE_ENGINE__ . "/TemplateRenderer.php";
require_once __TEMPLATE_ENGINE__ . "/TemplateCache.php";
require_once __TEMPLATE_ENGINE__ . "/TemplateCompiler.php";
require_once __UTILS__ . "/SingletonTrait.php";

use Exception;
use services\TranslatorService;
use utils\SingletonTrait;

class TemplateFacade
{
    use SingletonTrait;
    private TemplateCompiler $compiler;
    private TemplateRenderer $renderer;
    private TemplateCache $cache;

    protected function __construct(string $cacheDir = __TEMPLATE_CACHE__)
    {
        $this->cache = new TemplateCache($cacheDir);
        $this->compiler = new TemplateCompiler();
        $this->renderer = new TemplateRenderer();
    }

    /**
     * Рендерит шаблон с данными.
     * @throws Exception
     */
    public function render(string $templatePath, array $data): string
    {
        $lang = __CURRENT_LANG__;
        if(empty($lang) || !in_array($lang, ['ru', 'en'], true)){
            $lang = 'en';
        }
        $languageMessages = TranslatorService::getMessages($lang);
        $data['languageMessages'] = $languageMessages;

        if (!file_exists($templatePath)) {
            throw new Exception("Template $templatePath not found");
        }

        $cacheFile = $this->cache->getCacheFile($templatePath);

        if (!$this->cache->isFresh($cacheFile, $templatePath)) {
            $templateContent = file_get_contents($templatePath);
            $phpCode = $this->compileTemplate($templateContent);
            $this->cache->write($cacheFile, $phpCode);
        }

        return $this->renderer->render($cacheFile, $data);
    }

    /**
     * Компилирует шаблон в PHP-код.
     */
    private function compileTemplate(string $template): string
    {
        return $this->compiler->compile($template);
    }

    /**
     * Преобразует MJML-строку в HTML, вызывая MJML-CLI.
     *
     * @param string $mjmlContent Содержимое MJML-документа
     * @return string             Сгенерированный HTML
     *
     * @throws Exception при ошибке компиляции
     */
    public function compileMjmlToHtml(string $mjmlContent): string
    {
        return $this->compiler->compileMjmlToHtml($mjmlContent);
    }
}