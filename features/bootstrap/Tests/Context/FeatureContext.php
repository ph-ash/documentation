<?php

declare(strict_types=1);

namespace Tests\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class FeatureContext implements Context
{
    /** @var MinkContext */
    private $minkContext;

    /**
     * @BeforeScenario
     *
     * @throws ContextNotFoundException
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext(MinkContext::class);
    }

    /**
     * @BeforeScenario
     *
     * @throws ProcessFailedException
     * @throws RuntimeException
     */
    public function beforeScenario(): void
    {
        $process = new Process(
            ['docker-compose', '--project-name', 'phash', '--file', 'docker-compose.yaml', '--file', 'docker-compose.test.yaml', 'exec', '-T', 'server', 'bin/console', 'doc:mon:sch:drop']
        );
        $process->mustRun();
        $process = new Process(
            ['docker-compose', '--project-name', 'phash', '--file', 'docker-compose.yaml', '--file', 'docker-compose.test.yaml', 'exec', '-T', 'server', 'bin/console', 'doc:mon:sch:create']
        );
        $process->mustRun();
    }

    /**
     * @AfterStep
     */
    public function afterStep(AfterStepScope $scope): void
    {
        if (!$scope->getTestResult()->isPassed()) {
            $this->minkContext->saveScreenshot($scope->getFeature()->getTitle() . ' - line ' . $scope->getStep()->getLine() . '.png');
        }
    }
}
