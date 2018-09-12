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

namespace Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command;

/**
 * This command is a DTO holding and validating the raw values of a suggest data configuration.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfigurationCommand
{
    /** @var array */
    private $values;

    /**
     * @param array  $configurationValues
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $configurationValues)
    {
        $this->addValues($configurationValues);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Validates and adds configuration values.
     *
     * @throws \InvalidArgumentException
     *
     * @param array $configurationValues
     */
    private function addValues(array $configurationValues): void
    {
        if (empty($configurationValues)) {
            throw new \InvalidArgumentException('Configuration values cannot be empty.');
        }

        foreach ($configurationValues as $key => $value) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException(sprintf(
                    'The key of a configuration value must be a string, "%s" given.',
                    gettype($key)
                ));
            }
            if (!is_string($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'The value of a configuration value must be a string, "%s" given.',
                    gettype($value)
                ));
            }
            if (empty($value)) {
                throw new \InvalidArgumentException('The value of a configuration value cannot be empty.');
            }

            $this->values[$key] = $value;
        }
    }
}
