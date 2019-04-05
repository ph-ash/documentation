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
use Buzz\Exception\ClientException;
use Buzz\Exception\InvalidArgumentException;
use Buzz\Exception\LogicException;
use DateTime;
use Exception;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException as PropertyAccessInvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Webmozart\Assert\Assert;
use function in_array;
use function json_encode;

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
        $dataToSend = $this->createDefaultDataToSend();

        $pa = new PropertyAccessor();
        foreach ($table->getHash() as $row) {
            $pa->setValue($dataToSend, '[' . $row['property'] . ']', $this->castValue($row));
        }

        $response = $this->requestApi('/monitoring/data', $dataToSend, 'POST');

        Assert::lessThan(
            $response->getStatusCode(),
            300,
            'received status code %s, expected 2xx: ' . $response->getBody()->getContents()
        );

        sleep(1);
    }

    /**
     * @When I add/update some monitorings through the API:
     *
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function iAddOrUpdateSomeMonitoringsThroughTheApi(TableNode $table): void
    {
        $dataToSend = $this->createBulkDtoFromTable($table);

        $response = $this->requestApi('/monitoring/data/bulk', $dataToSend, 'POST');

        Assert::lessThan(
            $response->getStatusCode(),
            300,
            'received status code %s, expected 2xx: ' . $response->getBody()->getContents()
        );

        sleep(1);
    }

    /**
     * @When I add/update some monitorings through the API with errors:
     *
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function iAddOrUpdateSomeMonitoringsThroughTheApiWithErrors(TableNode $table): void
    {
        $dataToSend = $this->createBulkDtoFromTable($table);

        $response = $this->requestApi('/monitoring/data/bulk', $dataToSend, 'POST');

        Assert::range(
            $response->getStatusCode(),
            400,
            499,
            'received status code %s, expected 4xx: ' . $response->getBody()->getContents()
        );

        sleep(1);
    }

    /**
     * @When I delete the monitoring :id through the API
     *
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function iDeleteTheMonitoringThroughTheApi(string $id): void
    {
        $response = $this->requestApi(sprintf('/monitoring/%s', rawurlencode($id)), null, 'DELETE');

        Assert::lessThan(
            $response->getStatusCode(),
            300,
            'received status code %s, expected 2xx: ' . $response->getBody()->getContents()
        );

        sleep(1);
    }

    /**
     * @throws AccessException
     * @throws PropertyAccessInvalidArgumentException
     * @throws UnexpectedTypeException
     * @throws Exception
     */
    private function createBulkDtoFromTable(TableNode $table): array
    {
        $monitorings = [];
        $pa = new PropertyAccessor();
        foreach ($table->getHash() as $row) {
            if (!isset($monitorings[$row['id']])) {
                $pa->setValue($monitorings, '[' . $row['id'] . ']', ['id' => $row['id']] + $this->createDefaultDataToSend());
            }
            $pa->setValue($monitorings, '[' . $row['id'] . '][' . $row['property'] . ']', $this->castValue($row));
        }
        return ['monitoringData' => array_values($monitorings)];
    }

    /**
     * @throws Exception
     */
    private function createDefaultDataToSend(): array
    {
        return [
            'status' => 'ok',
            'payload' => '',
            'idleTimeoutInSeconds' => 60,
            'priority' => 1,
            'date' => (new DateTime())->format(DATE_ATOM),
            'path' => null
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws \InvalidArgumentException
     */
    private function requestApi(string $endpoint, ?array $dataToSend, string $method): ResponseInterface
    {
        $request = $this->requestFactory->createRequest(
            $method,
            $this->minkContext->getMinkParameter('base_url') . '/api' . $endpoint
        )->withHeader(
            'Authorization',
            'Bearer pleaseChooseASecretTokenForThePublicAPI'
        )->withHeader(
            'Content-Type',
            'application/json'
        );
        if ($dataToSend) {
            $request = $request->withBody(
                $this->streamFactory->createStream(json_encode($dataToSend))
            );
        }
        return $this->client->sendRequest($request);
    }

    private function castValue(array $row)
    {
        if (in_array($row['property'], ['idleTimeoutInSeconds', 'priority', 'tileExpansionIntervalCount'], true)) {
            return (int) $row['value'];
        }
        return $row['value'];
    }
}
