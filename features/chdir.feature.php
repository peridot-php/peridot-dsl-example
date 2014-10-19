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
