<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\NonEmptyValueConditionValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Automation\RuleEngine\Bundle\Validator\Constraint\NonEmptyValueCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NonEmptyValueConditionValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NonEmptyValueConditionValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_adds_a_violation_if_the_operator_is_not_empty_and_the_value_is_empty(
        $context,
        ProductConditionInterface $condition,
        NonEmptyValueCondition $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $condition->getOperator()->willReturn('foo');
        $condition->getValue()->willReturn(null);

        $context->buildViolation(Argument::any(), Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($condition, $constraint);
    }

    function it_does_not_add_a_violation_if_the_operator_is_empty_and_the_value_is_empty(
        ProductConditionInterface $condition,
        NonEmptyValueCondition $constraint
    ) {
        $condition->getOperator()->willReturn(Operators::IS_EMPTY);
        $condition->getValue()->willReturn(null);

        $this->validate($condition, $constraint);
    }

    function it_does_not_add_a_violation_if_the_operator_is_not_empty_and_the_value_is_empty(
        ProductConditionInterface $condition,
        NonEmptyValueCondition $constraint
    ) {
        $condition->getOperator()->willReturn(Operators::IS_NOT_EMPTY);
        $condition->getValue()->willReturn(null);

        $this->validate($condition, $constraint);
    }
}
