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
