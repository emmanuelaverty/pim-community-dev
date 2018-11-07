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

namespace Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;

/**
 * Checks if a suggest data connection is active or not.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetConnectionStatusHandler
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var DataProviderInterface */
    private $dataProvider;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param DataProviderFactory $dataProviderFactory
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        DataProviderFactory $dataProviderFactory,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->dataProvider = $dataProviderFactory->create();
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @return ConnectionStatus
     */
    public function handle(GetConnectionStatusQuery $query): ConnectionStatus
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        $configuration = $this->configurationRepository->find();
        if (!$configuration->getToken() instanceof Token) {
            return new ConnectionStatus(false, $identifiersMapping->isValid());
        }
        $isActive = $this->dataProvider->authenticate($configuration->getToken());

        return new ConnectionStatus($isActive, $identifiersMapping->isValid());
    }
}