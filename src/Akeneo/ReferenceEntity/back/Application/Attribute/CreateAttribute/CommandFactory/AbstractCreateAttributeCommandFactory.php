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

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractCreateAttributeCommandFactory implements CreateAttributeCommandFactoryInterface
{
    protected function fillCommonProperties(
        AbstractCreateAttributeCommand $command,
        array $normalizedCommand
    ): AbstractCreateAttributeCommand {
        $command->code = $normalizedCommand['code'] ?? null;
        $command->referenceEntityIdentifier = $normalizedCommand['reference_entity_identifier'] ?? null;
        $command->labels = $normalizedCommand['labels'] ?? null;
        $command->order = $normalizedCommand['order'] ?? null;
        $command->isRequired = $normalizedCommand['is_required'] ?? false;
        $command->valuePerChannel = $normalizedCommand['value_per_channel'] ?? null;
        $command->valuePerLocale = $normalizedCommand['value_per_locale'] ?? null;

        return $command;
    }
}