<?php
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
