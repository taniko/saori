<?php
namespace Hrgruri\Saori\Generator;

class ThemePageGenerator extends Generator
{
    public static function generate(
        Environment $env,
        \stdClass $config
    ) {
        static $files;
        static $twig_files;
        if (!isset($twig_files)) {
            $twig_files = self::getFileList("{$env->paths['theme']}/twig/page", ['twig']);
            $files      = self::getFileList("{$env->paths['theme']}/twig/page");
            $files = array_values(array_diff($files, $twig_files));
        }
        foreach ($files as $file) {
            self::copyFile(
                $file,
                "{$env->paths['root']}/".self::trimFilePath($file, "{$env->paths['theme']}/twig/page")
            );
        }
        foreach ($twig_files as $twig_file) {
            $dir = self::trimFilePath($twig_file, "{$env->paths['theme']}/twig/page", true);
            $template   = $env->twig->loadTemplate("page/{$dir}.twig");
            $html = $template->render(array(
                'maker'     => $env->maker
            ));
            self::putContents("{$env->paths['root']}/{$dir}/index.html",$html);
        }
    }
}
