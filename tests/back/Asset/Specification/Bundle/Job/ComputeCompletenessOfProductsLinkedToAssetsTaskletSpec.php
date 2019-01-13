<?php

namespace Specification\Akeneo\Asset\Bundle\Job;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Bundle\Job\ComputeCompletenessOfProductsLinkedToAssetsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

class ComputeCompletenessOfProductsLinkedToAssetsTaskletSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        EntityManagerInterface $entityManager,
        BulkIndexerInterface $indexer,
        BulkObjectDetacherInterface $bulkDetacher,
        StepExecution $stepExecution
    ): void {
        $this->beConstructedWith(
            $attributeRepository,
            $productQueryBuilderFactory,
            $entityManager,
            $indexer,
            $bulkDetacher,
            'pim_catalog_completeness'
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_compute_completeness_of_products_linked_to_assets_tasklet(): void
    {
        $this->shouldBeAnInstanceOf(ComputeCompletenessOfProductsLinkedToAssetsTasklet::class);
    }

    function it_is_a_tasklet(): void
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_resets_completenesses_of_products_linked_to_assets(
        $attributeRepository,
        $productQueryBuilderFactory,
        $entityManager,
        $indexer,
        $bulkDetacher,
        $stepExecution,
        Connection $connection,
        ProductQueryBuilderInterface $pqb,
        JobParameters $jobParameters,
        CursorInterface $cursor,
        ProductInterface $product123,
        Collection $completenesses123,
        ProductInterface $product456,
        Collection $completenesses456
    ): void {
        $productQueryBuilderFactory->create()->willReturn($pqb);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('asset_codes')->willReturn(['asset_code_1', 'asset_code_2']);

        $product123->getId()->willReturn(123);
        $product123->getCompletenesses()->willReturn($completenesses123);
        $product456->getId()->willReturn(456);
        $product456->getCompletenesses()->willReturn($completenesses456);
        $cursor->rewind()->shouldBeCalled();
        $cursor->valid()->willReturn(true, true, false);
        $cursor->current()->willReturn($product123, $product456);
        $cursor->next()->shouldBeCalled();

        $attributeRepository->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION)->willReturn(['assets']);
        $pqb->addFilter('assets', Operators::IN_LIST, ['asset_code_1', 'asset_code_2'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $entityManager->getConnection()->willReturn($connection);

        $completenesses123->clear()->shouldBeCalled();
        $completenesses456->clear()->shouldBeCalled();

        $connection->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.product_id IN (:productIds)',
            ['productIds' => [123, 456]],
            ['productIds' => Connection::PARAM_INT_ARRAY]
        )->shouldBeCalled();

        $indexer->indexAll([$product123, $product456]);
        $bulkDetacher->detachAll([$product123, $product456])->shouldBeCalled();

        $this->execute()->shouldReturn(null);
    }
}