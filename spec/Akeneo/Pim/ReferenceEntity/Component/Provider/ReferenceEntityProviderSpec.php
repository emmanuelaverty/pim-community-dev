<?php

namespace spec\Akeneo\Pim\ReferenceEntity\Component\Provider;

use Akeneo\Pim\ReferenceEntity\Component\Provider\ReferenceEntityProvider;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Prophecy\Argument;

class ReferenceEntityProviderSpec extends ObjectBehavior {
    function it_is_initializable()
    {
        $this->shouldHaveType(FieldProviderInterface::class);
        $this->shouldHaveType(EmptyValueProviderInterface::class);
        $this->shouldHaveType(ReferenceEntityProvider::class);
    }

    function it_provides_an_empty_value(AttributeInterface $designer)
    {
        $this->getEmptyValue($designer)->shouldReturn([]);
    }

    function it_provides_a_field(AttributeInterface $designer)
    {
        $this->getField($designer)->shouldReturn('akeneo-reference-entity-field');
    }

    function it_supports_an_reference_entity_attribute(AttributeInterface $designer, AttributeInterface $sku)
    {
        $designer->getType()->willReturn('akeneo_reference_entity_collection');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $this->supports($designer)->shouldReturn(true);
        $this->supports($sku)->shouldReturn(false);
    }
}