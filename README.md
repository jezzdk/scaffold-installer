# Scaffold Installer

This package is a shameless copy of the Laravel Installer. It is customized to install Wordpress, Bedrock and Sage instead, as well as provide a Docker Compose file for development.

## Installation

First, download the installer using Composer:

```
composer global require jezzdk/scaffold-installer
```

Make sure to place Composer's system-wide vendor bin directory in your $PATH so the scaffold executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

* macOS and GNU / Linux Distributions: `$HOME/.composer/vendor/bin`
* Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`

Once installed, the `scaffold new` command will create a fresh installation in the directory you specify. For instance, `scaffold new myproject mytheme` will create a directory named `myproject` containing a fresh installation with all dependencies already installed and a fresh theme in `web/app/themes/mytheme`:

```
scaffold new myproject mytheme
```

## Configuration

Change the settings in the `.env` file to fit local development. These are the most common values to change: 

```
DB_HOST=mysql
DB_NAME=database_name
DB_USER=database_user
DB_PASSWORD=database_password
WP_HOME=http://localhost
```

Notice the DB_HOST value. In the docker environment we use the service name.

## Start development

Start the docker containers to spin up the services:

```
docker-compose up -d
```

The url from `WP_HOME` can now be loaded in the browser.

See the documentation for Bedrock and Sage for further information:

https://roots.io/bedrock/

https://roots.io/sage/

## License

Scaffold Installer is open-sourced software licensed under the [MIT license](LICENSE.md).
