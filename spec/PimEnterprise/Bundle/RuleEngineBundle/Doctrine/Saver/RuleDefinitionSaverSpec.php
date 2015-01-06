<?php

namespace spec\PimEnterprise\Bundle\RuleEngineBundle\Doctrine\Saver;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Saver\BaseSavingOptionsResolver;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleDefinitionSaverSpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        BaseSavingOptionsResolver $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $entityManager,
            $optionsResolver,
            $eventDispatcher,
            'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Persistence\SaverInterface');
        $this->shouldHaveType('Akeneo\Component\Persistence\BulkSaverInterface');
    }

    function it_saves_a_rule_object($entityManager, $optionsResolver)
    {
        $rule = new RuleDefinition();
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'flush_only_object' => false]);

        $entityManager->persist($rule)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->save($rule);
    }

    function it_saves_multiple_rule_objects($entityManager, $optionsResolver)
    {
        $optionsResolver->resolveSaveAllOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'flush_only_object' => false]);

        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalledTimes(2)
            ->willReturn(['flush' => false, 'flush_only_object' => false]);

        $rule1 = new RuleDefinition();
        $rule2 = new RuleDefinition();
        $rules = [$rule1, $rule2];

        $entityManager->persist($rule1)->shouldBeCalled();
        $entityManager->persist($rule2)->shouldBeCalled();

        $entityManager->flush()->shouldBeCalledTimes(1);

        $this->saveAll($rules);
    }

    function it_throws_an_exception_if_object_is_not_a_rule_on_save(
        $entityManager,
        ProductInterface $productInterface
    ) {
        $entityManager->persist($productInterface)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->during('save', [$productInterface]);
    }
}
