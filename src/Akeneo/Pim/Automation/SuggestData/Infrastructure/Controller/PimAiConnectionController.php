<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi\ConnectionStatusNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimAiConnectionController
{
    /** @var ActivateConnectionHandler */
    private $activateConnectionHandler;

    /** @var GetConfigurationHandler */
    private $getConfigurationHandler;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /** @var ConnectionStatusNormalizer */
    private $connectionStatusNormalizer;

    /**
     * @param ActivateConnectionHandler $activateConnectionHandler
     * @param GetConfigurationHandler $getConfigurationHandler
     * @param GetConnectionStatusHandler $getConnectionStatus
     * @param ConnectionStatusNormalizer $connectionStatusNormalizer
     */
    public function __construct(
        ActivateConnectionHandler $activateConnectionHandler,
        GetConfigurationHandler $getConfigurationHandler,
        GetConnectionStatusHandler $getConnectionStatus,
        ConnectionStatusNormalizer $connectionStatusNormalizer
    ) {
        $this->activateConnectionHandler = $activateConnectionHandler;
        $this->getConfigurationHandler = $getConfigurationHandler;
        $this->getConnectionStatusHandler = $getConnectionStatus;
        $this->connectionStatusNormalizer = $connectionStatusNormalizer;
    }

    /**
     * @return Response
     */
    public function getAction(): Response
    {
        $configuration = $this->getConfigurationHandler->handle(new GetConfigurationQuery());
        $token = $configuration->getToken();

        return new JsonResponse(
            [
                'code' => 'pim-ai',
                'values' => ['token' => (null === $token) ? null : (string) $token],
            ]
        );
    }

    /**
     * @return Response
     */
    public function isActiveAction(): Response
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());

        return new JsonResponse($this->connectionStatusNormalizer->normalize($connectionStatus));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request): Response
    {
        // TODO: We should $request->get('token', '') instead decoding json and getting back value
        // TODO: Should not we assert it is an XML HTTP Request
        // TODO: Why do we put message here instead of handling response code?
        // TODO: success = 200, invalid argument = 401, conf exception = 422
        $configurationFields = json_decode($request->getContent(), true);

        try {
            $tokenString = $configurationFields['token'] ?? '';

            $token = new Token($tokenString);
            $command = new ActivateConnectionCommand($token);
            $this->activateConnectionHandler->handle($command);
        } catch (ConnectionConfigurationException $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'message' => 'akeneo_suggest_data.connection.flash.error',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'message' => 'akeneo_suggest_data.connection.flash.success',
        ]);
    }
}