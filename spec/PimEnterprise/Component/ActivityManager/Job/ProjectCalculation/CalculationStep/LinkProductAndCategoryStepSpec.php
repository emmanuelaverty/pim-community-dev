<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\LinkProductAndCategoryStep;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

class LinkProductAndCategoryStepSpec extends ObjectBehavior
{
    function let(PreProcessingRepositoryInterface $preProcessingRepository)
    {
        $this->beConstructedWith($preProcessingRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LinkProductAndCategoryStep::class);
    }

    function it_is_a_calculation_step()
    {
        $this->shouldImplement(CalculationStepInterface::class);
    }

    function it_links_product_and_category(
        $preProcessingRepository,
        ProjectInterface $project,
        ProductInterface $product,
        ArrayCollection $categories
    ) {
        $product->getCategories()->willReturn($categories);

        $preProcessingRepository->link($product, $categories)->shouldBeCalled();

        $this->execute($product, $project);
    }
}