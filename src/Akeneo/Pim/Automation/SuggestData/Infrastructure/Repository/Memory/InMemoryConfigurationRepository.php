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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * In memory implementation of the configuration repository.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class InMemoryConfigurationRepository implements ConfigurationRepositoryInterface
{
    /** @var ArrayCollection */
    private $configurations;

    /**
     * @param Configuration[] $configurations
     */
    public function __construct(array $configurations = [])
    {
        $this->configurations = new ArrayCollection($configurations);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByCode(string $code): ?Configuration
    {
        foreach ($this->configurations as $configuration) {
            if ($configuration->getCode() ===  $code) {
                return $configuration;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Configuration $configuration): void
    {
        $this->configurations->add($configuration);
    }
}