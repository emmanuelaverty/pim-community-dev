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

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateRecordCollectionAttributeCommand;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateRecordCollectionAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && 'record_collection' === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $command = new CreateRecordCollectionAttributeCommand();
        $this->fillCommonProperties($command, $normalizedCommand);

        $this->checkAdditionalProperties($normalizedCommand);

        $command->recordType = $normalizedCommand['record_type'];

        return $command;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkAdditionalProperties(array $nomalizedCommand): void
    {
        $keysToCheck = [
            'record_type',
        ];

        foreach ($keysToCheck as $keyToCheck) {
            if (!key_exists($keyToCheck, $nomalizedCommand)) {
                throw new \InvalidArgumentException(
                    sprintf('Expects normalized command to have key "%s"', $keyToCheck)
                );
            }
        }
    }
}