<?php

namespace spec\Akeneo\Asset\Component\Model;

use PhpSpec\ObjectBehavior;

class VariationSpec extends ObjectBehavior
{
    function it_is_a_variation_interface()
    {
        $this->shouldImplement('Akeneo\Asset\Component\Model\VariationInterface');
    }
}