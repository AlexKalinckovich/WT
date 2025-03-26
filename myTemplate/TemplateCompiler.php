<?php

namespace MyTemplate;

class TemplateCompiler
{
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

}