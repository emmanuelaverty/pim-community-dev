<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use PhpSpec\ObjectBehavior;

class AttributeIsRequiredSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIsRequired::class);
    }

    function it_tells_if_it_is_yes()
    {
        $this->normalize()->shouldReturn(true);
    }
}