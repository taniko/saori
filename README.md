Saori
====

static site generator

## Setting
main.php
```php
<?php
require ('vendor/autoload.php');
$saori = new hrgruri\saori\Saori(__DIR__);
$saori->run($argv);

```
config.json
```json
{
    "id"        :   "username",
    "local"     :   "http://localhost:8000",
    "title"     :   "Example Blog",
    "author"    :   "John Doe",
    "theme"     :   "sample",
    "lang"      :   "ja",
    "link"      :   {
        "github"    :   "https://github.com",
        "twitter"   :   "https://twitter.com"
    }
}
```

## Usage
```sh
# initialize
php main.php init

# post article
php main.php post (article_title)

# edit article
vim contents/:year/:month/:article_title/article.md
vim contents/:year/:month/:article_title/config.json

# make local & username.github.io
php main.php make
```
