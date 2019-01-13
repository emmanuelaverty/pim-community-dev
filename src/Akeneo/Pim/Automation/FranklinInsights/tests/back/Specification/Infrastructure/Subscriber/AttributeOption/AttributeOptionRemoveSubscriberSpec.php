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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\AttributeOption;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\RemoveAttributeOptionFromMappingInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\AttributeOption\AttributeOptionRemoveSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionRemoveSubscriberSpec extends ObjectBehavior
{
    public function let(
        RemoveAttributeOptionFromMappingInterface $removeAttributeOptionFromMapping,
        GetConnectionStatusHandler $connectionStatusHandler
    ): void {
        $connectionStatus = new ConnectionStatus(true, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);

        $this->beConstructedWith($removeAttributeOptionFromMapping, $connectionStatusHandler);
    }

    public function it_is_a_product_family_removal_subscriber(): void
    {
        $this->shouldHaveType(AttributeOptionRemoveSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_post_remove_event(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    public function it_publishes_a_new_job_in_the_job_queue(
        GenericEvent $event,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute,
        $removeAttributeOptionFromMapping
    ): void {
        $event->getSubject()->willReturn($attributeOption);

        $attributeOption->getCode()->willReturn('red');
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');

        $removeAttributeOptionFromMapping->process('color', 'red')->shouldBeCalled();

        $this->removeAttributeOptionFromMapping($event);
    }

    public function it_is_only_applied_when_franklin_insights_is_activated(
        GenericEvent $event,
        AttributeOptionInterface $attributeOption,
        $removeAttributeOptionFromMapping,
        $connectionStatusHandler
    ): void {
        $event->getSubject()->willReturn($attributeOption);

        $connectionStatus = new ConnectionStatus(false, false, false, 0);
        $connectionStatusHandler->handle(new GetConnectionStatusQuery(false))->willReturn($connectionStatus);

        $removeAttributeOptionFromMapping->process(Argument::any())->shouldNotBeCalled();

        $this->removeAttributeOptionFromMapping($event);
    }

    public function it_is_only_applied_when_an_attribute_option_is_removed(
        GenericEvent $event,
        $removeAttributeOptionFromMapping
    ): void {
        $event->getSubject()->willReturn(new \stdClass());

        $removeAttributeOptionFromMapping->process(Argument::any())->shouldNotBeCalled();

        $this->removeAttributeOptionFromMapping($event);
    }
}