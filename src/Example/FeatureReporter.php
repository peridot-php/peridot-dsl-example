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
         * Given and When language aren't really tests, so decrement the pass count that reported
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
