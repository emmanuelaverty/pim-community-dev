<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;

class AddToVariantGroupSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }
}
