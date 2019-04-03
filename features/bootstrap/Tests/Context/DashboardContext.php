<?php

declare(strict_types=1);

namespace Tests\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use DMore\ChromeDriver\ChromeDriver;
use Webmozart\Assert\Assert;

class DashboardContext implements Context
{
    private const COLORS = [
        'green' => '#00A000',
        'red' => '#D00000',
        'yellow' => '#DDDD00'
    ];

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
     * @Given I am logged into the dashboard
     */
    public function iAmLoggedIntoTheDashboard(): void
    {
        $this->minkContext->iAmOnHomepage();
        $this->minkContext->fillField('username', 'phash-board');
        $this->minkContext->fillField('password', 'phash-board');
        $this->minkContext->pressButton('Sign in');
    }

    /**
     * @Given an empty dashboard
     */
    public function anEmptyDashboard(): void
    {
        // TODO: probably implement wait for "page ready"
        $this->minkContext->assertPageContainsText('No data to display');
        $this->iSeeMonitoringTiles(0);
    }

    /**
     * @Then I see :count monitoring tiles
     */
    public function iSeeMonitoringTiles(int $count): void
    {
        $count++; // breadcrumb
        $countedRects = $this->minkContext->getSession()->evaluateScript('return document.getElementsByTagName("rect").length;');
        Assert::eq($countedRects, $count);
    }

    /**
     * @Then I see the monitoring :id as a :status tile
     */
    public function iSeeTheMonitoringAsATile(string $id, string $status): void
    {
        $style = $this->minkContext->getSession()->evaluateScript(sprintf('return document.getElementById("Monitoring.%s").style.fill;', $id));
        $color = self::COLORS[$status];
        $r = intval(substr($color, 1, 2), 16);
        $g = intval(substr($color, 3, 2), 16);
        $b = intval(substr($color, 5, 2), 16);
        Assert::eq($style, sprintf('rgb(%d, %d, %d)', $r, $g, $b));
    }

    /**
     * @When I click on the monitoring :id
     */
    public function iClickOnTheMonitoring(string $id): void
    {
        $this->minkContext->getSession()->executeScript(
            sprintf(<<<JS
                const e = document.createEvent("HTMLEvents");
                e.initEvent("click", false, true);
                document.getElementById("Monitoring.%s").dispatchEvent(e);
JS
            , $id));
        sleep(1);
    }

    /**
     * @Then I see :details in the detail view
     */
    public function iSeeInTheDetailView(string $details): void
    {
        $page = $this->minkContext->getSession()->getPage();
        $detailView = $page->find('css', 'div.phash-dialog');
        Assert::true($detailView->isVisible());
        Assert::contains($detailView->getText(), $details);
    }

    /**
     * @When I delete the monitoring :id in the dashboard
     */
    public function iDeleteTheMonitoringInTheDashboard(string $id): void
    {
        $this->iClickOnTheMonitoring($id);
        $this->minkContext->pressButton('Delete');
        /** @var ChromeDriver $driver */
        $driver = $this->minkContext->getSession()->getDriver();
        $driver->acceptAlert();
        sleep(1);
    }
}
