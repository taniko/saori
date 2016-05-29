<?php
namespace hrgruri\saori\generator;

class UserPageGenerator extends Generator
{
    public static function generate(
        Environment $env,
        \stdClass $config
    ) {
        static $file_list;
        if (!isset($file_list)) {
            $file_list = self::getFileList("{$env->paths['contents']}/page");
        }
        $template   = $env->twig->loadTemplate('template/page.twig');
        foreach ($file_list as $file) {
            $contents = self::getHTML($file);
            $dir = self::trimFilePath($file, "{$env->paths['contents']}/page", true);
            $html = $template->render(array(
                'maker'     => $env->maker,
                'page_contents'  => $contents
            ));
            self::putContents("{$env->paths['root']}/{$dir}/index.html",$html);
        }
    }
}
