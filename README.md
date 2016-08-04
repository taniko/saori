Saori
====

static site generator

## Setting
main.php
```php
<?php
require ('vendor/autoload.php');
$saori = new Hrgruri\Saori\Saori(__DIR__);
$saori->run($argv);

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

***
my [blog](https://hrgruri.github.io/) and [repository](https://github.com/hrgruri/blog)
