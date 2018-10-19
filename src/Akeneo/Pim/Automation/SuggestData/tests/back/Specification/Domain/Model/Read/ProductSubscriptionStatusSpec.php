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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model\Read;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ProductSubscriptionStatus;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionStatusSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_status(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, true, true, true);

        $this->beAnInstanceOf(ProductSubscriptionStatus::class);
    }

    public function it_indicates_that_product_is_subscribed(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, true, true, true);

        $this->isSubscribed()->shouldReturn(true);
    }

    public function it_indicates_that_product_is_not_subscribed(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, false, true, true);

        $this->isSubscribed()->shouldReturn(false);
    }

    public function it_has_a_connection_status(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, true, true, true);

        $this->getConnectionStatus()->shouldReturn($connectionStatus);
    }

    public function it_indicates_that_product_has_family(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, true, true, true);

        $this->hasFamily()->shouldReturn(true);
    }

    public function it_indicates_that_product_has_not_family(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, true, false, true);

        $this->hasFamily()->shouldReturn(false);
    }

    public function it_indicates_that_product_has_identifiers_mapping_filled(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, true, true, true);

        $this->isMappingFilled()->shouldReturn(true);
    }

    public function it_indicates_that_product_has_not_identifiers_mapping_filled(): void
    {
        $connectionStatus = new ConnectionStatus(true, true);
        $this->beConstructedWith($connectionStatus, true, true, false);

        $this->isMappingFilled()->shouldReturn(false);
    }
}
