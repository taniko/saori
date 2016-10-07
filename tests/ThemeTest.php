<?php
class ThemeTest extends TestCase
{
    private $template_files = [
        'article.twig',
        'articles.twig',
        'index.twig',
        'page.twig',
        'tags.twig'
    ];

    public function testFileExists()
    {
        foreach (scandir(__DIR__.'/../src/theme') as $theme) {
            if ($theme === '.' || $theme === '..') {
                continue;
            }
            $dir = __DIR__.'/../src/theme/' . $theme;
            foreach ($this->template_files as $template) {
                $this->assertTrue(file_exists("{$dir}/twig/template/{$template}"));
            }
            $this->assertTrue($this->validateJson("{$dir}/config.json"), "{$theme}/config.json does not exist or broken");
        }
    }

    /**
     * validate json file
     * @param  string $file path
     * @return bool         is valid
     */
    private function validateJson(string $file) : bool
    {
        try {
            json_decode(file_get_contents($file));
            $result = true;
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }
}
