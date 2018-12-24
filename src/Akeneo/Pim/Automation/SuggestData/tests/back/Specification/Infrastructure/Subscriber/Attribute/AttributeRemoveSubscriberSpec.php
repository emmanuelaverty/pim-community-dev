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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Attribute;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Attribute\AttributeRemoveSubscriber;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeRemoveSubscriberSpec extends ObjectBehavior
{
    public function let(
        SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping
    ): void {
        $this->beConstructedWith($familyCodesByAttributeQuery, $removeAttributesFromMapping);
    }

    public function it_is_a_product_family_removal_subscriber(): void
    {
        $this->shouldHaveType(AttributeRemoveSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_pre_save_event(): void
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_REMOVE);
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    public function it_is_only_applied_when_an_attribute_is_removed(
        GenericEvent $event,
        FamilyInterface $family,
        $familyCodesByAttributeQuery
    ): void {
        $event->getSubject()->willReturn($family);

        $familyCodesByAttributeQuery->execute(Argument::any())->shouldNotBeCalled();

        $this->onPreRemove($event);
    }

    public function it_gets_family_codes_on_pre_remove(
        GenericEvent $event,
        AttributeInterface $attribute,
        $familyCodesByAttributeQuery
    ): void {
        $event->getSubject()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute_code');

        $familyCodesByAttributeQuery->execute('attribute_code')->shouldBeCalled();

        $this->onPreRemove($event);
    }

    public function it_publishes_a_new_job_in_the_job_queue(
        GenericEvent $preRemoveEvent,
        GenericEvent $postRemoveEvent,
        AttributeInterface $attribute,
        $familyCodesByAttributeQuery,
        $removeAttributesFromMapping
    ): void {
        $preRemoveEvent->getSubject()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute_code');

        $familyCodesByAttributeQuery->execute('attribute_code')->willReturn(['family_1', 'family_2']);

        $this->onPreRemove($preRemoveEvent);

        $postRemoveEvent->getSubject()->willReturn($attribute);
        $attribute->getCode()->willReturn('attribute_code');

        $removeAttributesFromMapping
            ->process(['family_1', 'family_2'], ['attribute_code'])
            ->shouldBeCalled();

        $this->onPostRemove($postRemoveEvent);
    }
}
