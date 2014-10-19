Peridot Custom DSL Example
==========================

This repo demonstrates creating a custom DSL for use with the [Peridot](https://github.com/peridot-php/peridot) testing framework for PHP.

##Peridot acceptance testing DSL

This DSL will allow us to write acceptance tests like so:

```php
<?php
Feature("chdir","
    As a PHP user
    I need to be able to change the current working directory",
    function() {

        Scenario(function() {

            Given('I am in this directory', function() {
                chdir(__DIR__);
            });

            $cwd = null;
            When('I run getcwd()', function() use (&$cwd) {
                $cwd = getcwd();
            });

            Then('I should get this directory', function() use (&$cwd) {
                if ($cwd != __DIR__) {
                    throw new \Exception("Should be current directory");
                }
            });

        });

    });
```

##The DSL file

```php
<?php
use Peridot\Runner\Context;

function Feature($name, $description,  callable $fn)
{
    $description = 'Feature: ' . $name . $description . "\n";
    Context::getInstance()->addSuite($description, $fn);
}

function Scenario(callable $fn)
{
    Context::getInstance()->addSuite("Scenario:", $fn);
}

function Given($description, callable $fn)
{
    $test = Context::getInstance()->addTest($description, $fn);
    $test->getScope()->acceptanceDslTitle = "Given";
}

function When($description, callable $fn)
{
    $test = Context::getInstance()->addTest($description, $fn);
    $test->getScope()->acceptanceDslTitle = "When";
}

function Then($description, callable $fn)
{
    $test = Context::getInstance()->addTest($description, $fn);
    $test->getScope()->acceptanceDslTitle = "Then";
}
```

##Configuring Peridot

We can wire up our custom DSL via the Peridot configuration file.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

return function($emitter) {
    $emitter->on('peridot.configure', function($config) {
        $config->setDsl(__DIR__ . '/src/feature.dsl.php');
        $config->setGrep('*.feature.php');
    });

    $emitter->on('peridot.reporters', function($input, $reporters) {
        $reporters->register('feature', 'A feature reporter', 'Peridot\Example\FeatureReporter');
    });
};
```

To complement our DSL, we also extended the `SpecReporter`
with the `FeatureReporter`.

```php
<?php
namespace Peridot\Example;

use Peridot\Reporter\SpecReporter;

/**
 * The FeatureReporter extends SpecReporter to be more friendly with feature language
 *
 * @package Peridot\Example
 */
class FeatureReporter extends SpecReporter
{
    /**
     * @var \Peridot\Core\TestInterface
     */
    protected $lastTest;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        /**
         * Track the last test that was started
         */
        $this->eventEmitter->on('test.start', function($test) {
            $this->lastTest = $test;
        });

        /**
         * Given and When language aren't really tests, so decrement the pass count thats reported
         */
        $this->eventEmitter->on('test.passed', function($test) {
            $scope = $test->getScope();
            $title = $scope->acceptanceDslTitle;
            if (preg_match('/Given|When/', $title)) {
                $this->passing--;
            }
        });
    }

    /**
     * Instead of a symbol, render the feature language
     *
     * @param $name
     * @return string
     */
    public function symbol($name)
    {
        $scope = $this->lastTest->getScope();
        return $scope->acceptanceDslTitle;
    }
}
```

##Running the features

```
$ vendor/bin/peridot features/ -r feature
```

![Peridot acceptance testing](https://raw.githubusercontent.com/peridot-php/peridot-dsl-example/master/output.png "Peridot acceptance testing")

##Note

This is just an example of creating a custom DSL for Peridot. It probably isn't the most robust solution in it's current state, but it is instead meant to demonstrate what Peridot is capable of.
