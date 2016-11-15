# Initial Setup

## Source code

All source code is available on the [GitHub project page](https://github.com/cultuurnet/uitpas-beheer-silex).


## Setup with Vagrant (in progress)

CultuurNet Vlaanderen vzw offers a ready-to-go setup with 
[Vagrant](https://www.vagrantup.com/) which resembles the test and production
environments the most. We recommend using this Vagrant box.
Get in touch with CultuurNet Vlaanderen vzw to get access to the Vagrant box.

## Setup on your own hosting stack

You will need a web server (for example Apache or Nginx) with at least PHP 5.6.

Web server requirements:
- at least PHP 5.6 (PHP 7 preferred)
- Rabbit MQ should be installed (https://www.rabbitmq.com/download.html)
- Optional: If you want to automatically run the consumer: http://supervisord.org/

Steps:

- Git clone the source code.
- Install the dependencies with ``composer install``.
- Set the web directory as the document root in your web server 
- configuration.
- Configure your web server to rewrite all requests that do not match with an existing file,
  to index.php. If you are using Apache, the .htaccess file already takes care 
  of this.
- Copy `config.dist.yml` to [`config.yml`] and adapt to your needs.
- Run following command in your terminal ./bin/console orm:schema-tool:create --force

# Configuration
`config.dist.yml` contains an example configuration you can use to kickstart your own
`config.yml`. If you need to know what the intention of a particular configuration
setting is, look in here for the documentation.

# Console commands

A console is provided in the application. This can be used to run certain silex commands. Most important ones:
- projectaanvraag:consumer: Run this command if you want to consume messages on your rabbit mq. This is 
 used to handle events async.
  - If you have supervisor. This is the required command that should be run.
  - If you have no supervisor: Make sure you run this command after you executed code that throws an event
- orm:schema-tool:update: Run this command to show what sql is needed to update your DB to latest version. Use the --force flag to execute that query.
- orm:schema-tool:create: When this is your first time running the project.