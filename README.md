# Saori
[![Build Status](https://travis-ci.org/taniko/saori.svg?branch=ci)](https://travis-ci.org/taniko/saori)

Saori is PHP static site generator for blog

## Installation
```sh
composer create-project taniko/saori-skeleton blog
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
vim draft/first_article/config.yml

# post
php saori post first_article

# generate static site
php saori build

# push to GitHub
cd public
git init
git remote add origin
## username is your GitHub account
git@github.com:username/username.github.io.git
git add --all
git commit -m 'Initial commit'
git push origin master
```

if you not set draft name, create draft/temp
```sh
php saori draft
vim draft/temp/article.md
vim draft/temp/config.yml

# move temp to contents/article/YYYY/MM/DDHHMM
php saori post temp
php saori build
```

## Setting
config/env.yml
```yml
title: Example Blog
author: John
local: 'http://localhost:8000'
public: 'https://example.com'
theme: saori
lang: en
link:
    GitHub: 'https://github.com/'
    Twitter: 'https://twitter.com/'
    'Speaker Deck': 'https://speakerdeck.com/'
feed:
    type: atom
    number: 50
google-analytics : null
share:
    - twitter
    - pocket
```

config/theme.yml
```yml
saori:
    color:
        header        : '#A9EEE6'
        title         : '#F7FBFC'
        body          : '#FEFAEC'
        page-contents : '#FFF1CF'
    date-format: 'F j, Y'
```

***
my [blog](https://taniko.github.io/) and [repository](https://github.com/taniko/blog)
