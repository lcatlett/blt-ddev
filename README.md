Acquia BLT integration with ddev
====

This is an [Acquia BLT](https://github.com/acquia/blt) plugin providing [ddev](https://ddev.readthedocs.io) integration.

This plugin is **community-created** and **community-supported**. Acquia does not provide any direct support for this software or provide any warranty as to its stability.

## Installation and usage

To use this plugin, you must already have a Drupal project using BLT 10 and you must be using ddev [v1.10.0](https://github.com/drud/ddev/releases/tag/v1.10.0) or newer. 

You can check your version of ddev with `ddev version`.

In your project, require the plugin with Composer:

`composer require lcatlett/blt-ddev`

Initialize the ddev integration by calling `recipes:ddev:project:init`, which is provided by this plugin:

`blt recipes:ddev:project:init`

This command will initialize a .ddev folder as well as BLT configuration in the /blt directory of your project.

The plugin adds a custom ddev command in the web container which makes the `ddev blt` command available. **All blt commands should be prefixed with `ddev` to ensure it is excuted within the docker container**, for example:

`ddev blt setup`

# BLT commands support

## Behat configuration

- BLT makes some assumptions about the local development environment which informs behat testing configuration. This plugin extends the default `blt behat` command to run behat tests in a container as a dedicated service via the `ddev blt behat` command. 



