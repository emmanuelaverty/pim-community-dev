<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

class ChangesCollectorSpec extends ObjectBehavior
{
    function let(MediaManager $manager)
    {
        $this->beConstructedWith($manager);
    }

    function it_collects_values_changes(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getScope()->willReturn('ecommerce');
        $value->getLocale()->willReturn('fr_FR');
        $attribute->getCode()->willReturn('description');

        $this->add('name', ['foo' => 'bar'], $value);
        $this->getChanges()->shouldReturn([
            'values' => [
                'name' => [
                    'foo' => 'bar',
                    '__context__' => [
                        'attribute' => 'description',
                        'locale' => 'fr_FR',
                        'scope' => 'ecommerce',
                    ]
                ]
            ]
        ]);
    }

    function it_does_not_collect_twice_the_same_changes(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getScope()->willReturn('ecommerce');
        $value->getLocale()->willReturn('fr_FR');
        $attribute->getCode()->willReturn('description');

        $this->add('name', ['foo' => 'bar'], $value);
        $this->add('name', ['foo' => 'baz'], $value);
        $this->getChanges()->shouldReturn([
            'values' => [
                'name' => [
                    'foo' => 'bar',
                    '__context__' => [
                        'attribute' => 'description',
                        'locale' => 'fr_FR',
                        'scope' => 'ecommerce',
                    ]
                ]
            ]
        ]);
    }
}
