<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep;

use PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\CalculationStep\AddUserGroupStep;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

class AddUserGroupStepSpec extends ObjectBehavior
{
    function let(ProjectItemCalculatorInterface $contributorGroupCalculator)
    {
        $this->beConstructedWith($contributorGroupCalculator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddUserGroupStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_adds_to_the_project_the_user_group_that_have_edit_permission_on_categories_and_attribute_groups(
        $contributorGroupCalculator,
        ProjectInterface $project,
        ProductInterface $product,
        Group $userGroup,
        Group $otherUserGroup
    ) {
        $contributorGroupCalculator->calculate($project, $product)->willReturn([$userGroup]);

        $project->addUserGroup($userGroup)->shouldBeCalled();
        $project->addUserGroup($otherUserGroup)->shouldNotBeCalled();

        $this->execute($product, $project);
    }
}