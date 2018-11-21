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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetConnectionStatusHandlerSpec extends ObjectBehavior
{
    public function let(
        ConfigurationRepositoryInterface $configurationRepository,
        AuthenticationProviderInterface $authenticationProvider,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ): void {
        $this->beConstructedWith($configurationRepository, $authenticationProvider, $identifiersMappingRepository);
    }

    public function it_checks_that_a_connection_is_active(
        IdentifiersMapping $identifiersMapping,
        GetConnectionStatusQuery $query,
        $authenticationProvider,
        $configurationRepository,
        $identifiersMappingRepository
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('bar'));

        $configurationRepository->find()->willReturn($configuration);
        $authenticationProvider->authenticate('bar')->willReturn(true);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->isValid()->willReturn(false);

        $this->handle($query)->shouldReturnAnActiveStatus();
    }

    public function it_checks_that_a_connection_is_inactive(
        IdentifiersMapping $identifiersMapping,
        GetConnectionStatusQuery $query,
        $authenticationProvider,
        $configurationRepository,
        $identifiersMappingRepository
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('bar'));

        $configurationRepository->find()->willReturn($configuration);
        $authenticationProvider->authenticate('bar')->willReturn(false);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->isValid()->willReturn(false);

        $this->handle($query)->shouldReturnAnInactiveStatus();
    }

    public function it_checks_that_an_identifiers_mapping_is_valid(
        IdentifiersMapping $identifiersMapping,
        GetConnectionStatusQuery $query,
        $configurationRepository,
        $identifiersMappingRepository
    ): void {
        $configuration = new Configuration();
        $configurationRepository->find()->willReturn($configuration);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->isValid()->willReturn(true);

        $this->handle($query)->shouldReturnValidIdentifiersMappingStatus();
    }

    public function it_checks_that_an_identifiers_mapping_is_invalid(
        IdentifiersMapping $identifiersMapping,
        GetConnectionStatusQuery $query,
        $configurationRepository,
        $identifiersMappingRepository
    ): void {
        $configuration = new Configuration();
        $configurationRepository->find()->willReturn($configuration);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->isValid()->willReturn(false);

        $this->handle($query)->shouldReturnInvalidIdentifiersMappingStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'returnAnActiveStatus' => function (ConnectionStatus $connectionStatus) {
                return $connectionStatus->isActive();
            },
            'returnAnInactiveStatus' => function (ConnectionStatus $connectionStatus) {
                return !$connectionStatus->isActive();
            },
            'returnValidIdentifiersMappingStatus' => function (ConnectionStatus $connectionStatus) {
                return $connectionStatus->isIdentifiersMappingValid();
            },
            'returnInvalidIdentifiersMappingStatus' => function (ConnectionStatus $connectionStatus) {
                return !$connectionStatus->isIdentifiersMappingValid();
            },
        ];
    }
}