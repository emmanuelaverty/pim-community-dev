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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Query\Subscription;

/**
 * @author Mathias Métayer <mathias.metayer@akeneo.com>
 */
interface EmptySuggestedDataQueryInterface
{
    /**
     * @param array $subscriptionIds
     */
    public function execute(array $subscriptionIds): void;
}
