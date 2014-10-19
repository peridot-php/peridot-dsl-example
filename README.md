Peridot Custom DSL Example
==========================

This repo demonstrates creating a custom DSL for use with the [Peridot](https://github.com/peridot-php/peridot) testing framework for PHP.

##Peridot acceptance testing DSL

This DSL will allow us to write acceptance tests like so:

```php
<?php // features/chdir.feature.php
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

Our DSL defines a small set of feature based functions. `Context` is the only singleton in the `Peridot` ecosystem,
and we use it to add suites and tests. You can browse it's documentation [here](http://peridot-php.github.io/docs/class-Peridot.Runner.Context.html).

```php
<?php // src/feature.dsl.php
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

Notice the use of `Scope` to store additional information about our tests and our DSL.

##Configuring Peridot

We wire up our custom DSL via the Peridot configuration file.

```php
<?php // peridot.php
require_once __DIR__ . '/vendor/autoload.php';

return function($emitter) {
    //set the DSL and change the file extension we search for
    $emitter->on('peridot.configure', function($config) {
        $config->setDsl(__DIR__ . '/src/feature.dsl.php');
        $config->setGrep('*.feature.php');
    });

    //register a more appropriate reporter for our DSL
    $emitter->on('peridot.reporters', function($input, $reporters) {
        $reporters->register('feature', 'A feature reporter', 'Peridot\Example\FeatureReporter');
    });
};
```

To complement our DSL, we have also extended the `SpecReporter`
with the `FeatureReporter`.

```php
<?php // src/Example/FeatureReporter.php
namespace Peridot\Example;

use Peridot\Core\Test;
use Peridot\Reporter\SpecReporter;

/**
 * The FeatureReporter extends SpecReporter to be more friendly with feature language
 *
 * @package Peridot\Example
 */
class FeatureReporter extends SpecReporter
{
    /**
     * @param Test $test
     */
    public function onTestPassed(Test $test)
    {
        $title = $this->handleGivenWhen($test);

        $this->output->writeln(sprintf(
            "  %s%s %s",
            $this->indent(),
            $this->color('success', $title),
            $this->color('muted', $test->getDescription())
        ));
    }

    /**
     * Given and When don't represent true tests themselves, so we decrement
     * the "passing" count that is reported for each one
     *
     * @param Test $test
     * @return string
     */
    protected function handleGivenWhen(Test $test)
    {
        $scope = $test->getScope();
        $title = $scope->acceptanceDslTitle;
        if (preg_match('/Given|When/', $title)) {
            $this->passing--;
        }
        return $title;
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
