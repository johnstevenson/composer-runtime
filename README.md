Composer-Runtime
================

[![Build Status](https://travis-ci.org/johnstevenson/composer-runtime.png?branch=master)](https://travis-ci.org/johnstevenson/composer-runtime)

Run [Composer][composer] from your application

## Contents
* [About](#About)
* [Installation](#Installation)
* [Usage](#Usage)
* [License](#License)

<a name="About"></a>
## About
Composer-Runtime enables you to call Composer from within your application, without having to care about where it is installed.

```php
<?php
$composer = new JohnStevenson\ComposerRuntime\Process();

# run a command
$result = $composer->run(array('dump-autoload', '--optimize'));

# capture the output
$result = $composer->capture('update', $output, $workingDir);
```

In addition to this, and complementing Composer's own package commands, Composer-Runtime includes full package-management capabilities. You can, for example, create and install packages for project requirements:

```php
<?php
$composer->setWorkingDir($project);

# create a composer.json in our $project directory
$package = $composer->packageCreate();
$package->linkAdd('require', 'monolog/monolog', '2.0.*');
$package->save();

# and install it
$composer->packageInstall($package);
```

Or you can create packages for new libraries:

```php
<?php
$composer->setWorkingDir($library);

$values = array(
    'vendor' => 'bloggs',
    'name' => 'test',
    'author' => 'Fred Bloggs',
    'email' => 'fred@somewhere.org',
    'description' => 'Package Test',
    'require' => array(
        'monolog/monolog' => '2.0.*'
    )
);

# create a composer.json in our $library directory
$package = $composer->packageCreate($values);
$package->save();

```

Or you can modify or query existing packages.

```php
<?php
$composer->setWorkingDir($project);

# open composer.json in our $project directory
$package = $composer->packageOpen();

# get the version of a requirement
$version = $package->linkGet('require', 'monolog/monolog');

# delete a requirement
$package->linkDelete('require', 'bloggs/test');
$package->save();

# and update it
$composer->packageUpdate($package);
```

Package management includes json-schema validation and is built on top of the [Json-Works][json-works] library.

<a name="Installation"></a>
## Installation
You must use [Composer][composer]. Add the following to the require section in your *composer.json* file:

```
"johnstevenson/json-works": "1.0.*"
```

<a name="Usage"></a>
## Usage

Full usage [documentation][wiki] is available in the Wiki.

<a name="License"></a>
## License

Composer-Runtime is licensed under the MIT License - see the `LICENSE` file for details.

[composer]: http://getcomposer.org
[json-works]: https://github.com/johnstevenson/json-works
[wiki]:https://github.com/johnstevenson/composer-runtime/wiki/Home

