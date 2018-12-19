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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface RemoveAttributesFromMappingInterface
{
    /**
     * @param array $familyCodes
     * @param array $removedAttributes
     */
    public function process(array $familyCodes, array $removedAttributes): void;
}