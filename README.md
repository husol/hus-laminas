# Hus Laminas MVC Skeleton

## Introduction

This is a skeleton application using the Laminas MVC layer and module
systems. This application is meant to be used as a starting place for those
looking to get their feet wet with Laminas MVC. After that, Husol integrated some useful libraries to this framework.

## Installation via GitHub

Get Hus Laminas project at [GitHub Hus Laminas](https://github.com/husol/hus-laminas).
If you don't have composer already installed,
then please install as per the [documentation](https://getcomposer.org/doc/00-intro.md).

1. To run your new Hus Laminas MVC project:

```bash
$ cd /path/to/your/hus_laminas_project
$ composer update
```
* In case of missing some PHP extensions, you need to install them firstly.
Once updated, you can make configuration in the project by clone these config files:
`ROOT_PROJECT/config/autoload/development.local.php.dist => ROOT_PROJECT/config/autoload/development.local.php`
`ROOT_PROJECT/config/development.config.php.dist => ROOT_PROJECT/config/development.config.php`
`ROOT_PROJECT/config/database.config.php.dist => ROOT_PROJECT/config/database.config.php`
`ROOT_PROJECT/module/Application/config/config.php.dist => ROOT_PROJECT/module/Application/config/config.php`

2. Next, we should adjust `database.config.php` file and `config.php` file with correct information.

## Web server setup

Create Vitual Host for the project. Ex: `laptrinhweb.tech` host.

### Apache setup

Most of Windows developers use Apache server. So, we can setup a virtual host to point to the public/ directory of the
project and you should be ready to go! It should look something like below:

```apache
<VirtualHost *:80>
    ServerName laptrinhweb.tech
    DocumentRoot /path/to/laptrinhweb.tech/public
    <Directory /path/to/laptrinhweb.tech/public>
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
        <IfModule mod_authz_core.c>
        Require all granted
        </IfModule>
    </Directory>
</VirtualHost>
```

### Nginx setup

To setup nginx, open your `/path/to/nginx/nginx.conf` and add an
[include directive](http://nginx.org/en/docs/ngx_core_module.html#include) below
into `http` block if it does not already exist:

```nginx
http {
    # ...
    include sites-enabled/*;
}
```

Create a virtual host configuration file for your project under `/path/to/nginx/sites-enabled/laptrinhweb.tech`
it should look something like below:

```nginx
server {
    listen   80;

    root /path/to/laptrinhweb.tech/public;
    index index.php index.html index.htm;

    server_name laptrinhweb.tech;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ /(.*)(css|js)/(.*)\.\d+\.(css|js)$ {
        rewrite "^(.*)(css|js)/(.*)\.[\d]{10}\.(css|js)$" $1$2/$3.$4 break;
        return  401;
    }

    # pass the PHP scripts to FastCGI server listening on socket
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.3-fpm.sock; #Adjust this path to your correct path of php-fpm.sock version
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Restart the nginx `# systemctl restart nginx`, now you should be ready to go!

## QA Tools

The skeleton does not come with any QA tooling by default, but does ship with
configuration for each of:

- [phpcs](https://github.com/squizlabs/php_codesniffer)
- [phpunit](https://phpunit.de)

Additionally, it comes with some basic tests for the shipped
`Application\Controller\IndexController`.

If you want to add these QA tools, execute the following:

```bash
$ composer require --dev phpunit/phpunit squizlabs/php_codesniffer laminas/laminas-test
```

We provide aliases for each of these tools in the Composer configuration:

```bash
# Run CS checks:
$ composer cs-check
# Fix CS errors:
$ composer cs-fix
# Run PHPUnit tests:
$ composer test
```
