<?php
Feature("chdir","
    As a PHP user
    I need to be able to change the current working directory",
    function() {

        Scenario(function() {
        
            Given('I am in this directory', function() {
                chdir(__DIR__);
            });

            When('I run getcwd()', function() {
                $this->cwd = getcwd();
            });

            Then('I should get this directory', function() {
                if ($this->cwd != __DIR__) {
                    throw new \Exception("Should be current directory");
                }
            });

        });

    });
