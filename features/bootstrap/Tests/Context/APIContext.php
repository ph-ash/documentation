<?php

declare(strict_types=1);

namespace Tests\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Buzz\Browser;
use Buzz\Client\Curl;
use DateTime;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Webmozart\Assert\Assert;

class APIContext implements Context
{
    /** @var RequestFactoryInterface */
    private $requestFactory;
    /** @var StreamFactoryInterface */
    private $streamFactory;
    /** @var ClientInterface */
    private $client;
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
     * @throws Exception
     */
    public function __construct()
    {
        $this->requestFactory = new Psr17Factory();
        $this->streamFactory = new Psr17Factory();
        $this->client = new Browser(new Curl(new Psr17Factory()), $this->requestFactory);
    }

    /**
     * @When I add/update a monitoring through the API:
     *
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function iAddOrUpdateAMonitoringThroughTheApi(TableNode $table): void
    {
        $dataToSend = [
            'status' => 'ok',
            'payload' => '',
            'idleTimeoutInSeconds' => 60,
            'priority' => 1,
            'date' => (new DateTime())->format(DATE_ATOM),
            'path' => null
        ];

        $pa = new PropertyAccessor();
        foreach ($table->getHash() as $row) {
            $pa->setValue($dataToSend, '[' . $row['property'] . ']', $row['value']);
        }

        $request = $this->requestFactory->createRequest('POST', $this->minkContext->getMinkParameter('base_url') . '/api/monitoring/data')
            ->withHeader('Authorization', 'Bearer pleaseChooseASecretTokenForThePublicAPI')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(\json_encode($dataToSend)));
        $response = $this->client->sendRequest($request);

        Assert::lessThan($response->getStatusCode(), 300, 'received status code %s, expected 2xx');

        sleep(1);
    }
}
