<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exceptions\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exceptions\DataProviderServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exceptions\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exceptions\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionWebservice implements SubscriptionApiInterface
{
    private $uriGenerator;

    private $httpClient;

    public function __construct(UriGenerator $uriGenerator, Client $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(array $identifiers): ApiResponse
    {
        $route = $this->uriGenerator->generate('/subscriptions');

        try {
            $response = $this->httpClient->request('POST', $route, [
                'form_params' => [$identifiers],
            ]);

            return new ApiResponse(
                $response->getStatusCode(),
                new SubscriptionCollection(json_decode($response->getBody()->getContents(), true))
            );
        } catch (ServerException $e) {
            throw new DataProviderServerException(sprintf('Something went wrong on pim.ai side during product subscription : ', $e->getMessage()));
        } catch (ClientException $e) {
            if ($e->getCode() === Response::HTTP_PAYMENT_REQUIRED) {
                throw new InsufficientCreditsException('Not enough credits on pim.ai to subscribe');
            }
            if ($e->getCode() === Response::HTTP_FORBIDDEN) {
                throw new InvalidTokenException('The pim.ai token is missing or invalid');
            }

            throw new BadRequestException(sprintf('Something went wrong during product subscription : ', $e->getMessage()));
        }
    }
}
