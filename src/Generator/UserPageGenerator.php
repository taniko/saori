<?php
namespace Hrgruri\Saori\Generator;

class UserPageGenerator extends Generator
{
    public static function generate(Environment $env)
    {
        static $file_list;
        static $img_list;
        if (!isset($file_list)) {
            $file_list  = self::getFileList("{$env->paths['contents']}/page", ['md']);
            $img_list   = self::getFileList(
                "{$env->paths['contents']}/page",
                ['png', 'jpeg' , 'jpg']
            );
        }
        $template   = $env->twig->loadTemplate('template/page.twig');
        self::copyImage(
            $img_list,
            "{$env->paths['root']}/img",
            "{$env->paths['contents']}/page"
        );
        foreach ($file_list as $file) {
            $contents = self::getHtmlByString(
                self::rewriteImagePath($file, "{$env->paths['contents']}/page")
            );
            $dir = self::trimFilePath($file, "{$env->paths['contents']}/page", true);
            $html = $template->render(array(
                'maker'     => $env->maker,
                'page_contents'  => $contents
            ));
            self::putContents("{$env->paths['root']}/{$dir}/index.html", $html);
        }
    }

    private static function copyImage(array $img_list, string $path_img, string $path_page)
    {
        foreach ($img_list as $from) {
            self::copyFile(
                $from,
                $path_img.'/.page/'.self::trimFilePath($from, $path_page)
            );
        }
    }

    protected static function rewriteImagePath(string $file, string $path_page) : string
    {
        return parent::rewriteImagePath(
            $file,
            '/img/.page/'. self::trimFilePath($file, $path_page, true) .'/..'
        );
    }
}
