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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponseCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionResponseCollectionSpec extends ObjectBehavior
{
    public function it_is_a_subscription_responses_collection(): void
    {
        $this->shouldHaveType(ProductSubscriptionResponseCollection::class);
    }

    public function it_returns_null_if_index_does_not_exist(): void
    {
        $this->get(42)->shouldReturn(null);
    }

    public function it_can_add_and_retrieve_subscription_responses(): void
    {
        $response = new ProductSubscriptionResponse(42, '123-456-789', [], false);
        $this->add($response)->shouldReturn(null);

        $this->get(42)->shouldReturn($response);
    }
}
