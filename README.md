# Scaffold Installer

This package is a shameless copy of the Laravel Installer. It is customized to install Wordpress, Bedrock and Sage instead, as well as provide a Docker Compose file for development.

## Installation

First, download the installer using Composer:

`composer global require jezzdk/scaffold-installer`

Make sure to place Composer's system-wide vendor bin directory in your $PATH so the scaffold executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

* macOS and GNU / Linux Distributions: `$HOME/.composer/vendor/bin`
* Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`

Once installed, the `scaffold new` command will create a fresh installation in the directory you specify. For instance, `scaffold new myproject mytheme` will create a directory named `myproject` containing a fresh installation with all dependencies already installed and a fresh theme in `web/app/themes/mytheme`:

`scaffold new myproject mytheme`

## License

Scaffold Installer is open-sourced software licensed under the [MIT license](LICENSE.md).
