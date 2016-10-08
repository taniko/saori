# Saori
[![Build Status](https://travis-ci.org/hrgruri/saori.svg?branch=ci)](https://travis-ci.org/hrgruri/saori)

Saori is PHP static site generator for github.io

## Installation
### Use hrgruri/saori-skeleton
```sh
composer create-project hrgruri/saori-skeleton blog
```
### Use hrgruri/saori
```sh
mkdir blog
cd blog
composer require hrgruri/saori
```

## Usage
```sh
php saori

# initialize
php saori init

# generate draft file
php saori draft first_article

# edit draft file
vim draft/first_article/article.md
vim draft/first_article/config.json

# post
php saori post first_article

# generate static site
php saori build

cd username.github.io
git init
git remote add origin git@github.com:username/username.github.io.git
git add --all
git commit -m 'Initial commit'
git push origin master
```

## Setting
contents/config.json
```json
{
    "id"        :   "username",
    "local"     :   "http://localhost:8000",
    "title"     :   "Example Blog",
    "author"    :   "John",
    "theme"     :   "saori",
    "lang"      :   "en",
    "link"      :   {
        "github"    :   "https://github.com",
        "twitter"   :   "https://twitter.com"
    }
}
```

***
my [blog](https://hrgruri.github.io/) and [repository](https://github.com/hrgruri/blog)
