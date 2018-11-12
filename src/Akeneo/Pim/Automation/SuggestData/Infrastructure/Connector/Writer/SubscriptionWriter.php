<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var DataProviderInterface */
    private $dataProvider;

    /** @var IdentifiersMapping */
    private $identifiersMapping;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->dataProviderFactory = $dataProviderFactory;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->dataProvider = $this->dataProviderFactory->create();
        $this->identifiersMapping = $this->identifiersMappingRepository->find();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $collection = $this->dataProvider->bulkSubscribe($items);

        foreach ($items as $item) {
            $response = $collection->get($item->getProduct()->getId());
            if (null === $response) {
                continue;
            }

            $subscription = $this->buildSubscription($item, $response);
            $this->productSubscriptionRepository->save($subscription);
            $this->stepExecution->incrementSummaryInfo('subscribed');
        }
    }

    /**
     * @param ProductSubscriptionRequest $request
     * @param ProductSubscriptionResponse $response
     *
     * @return ProductSubscription
     */
    private function buildSubscription(
        ProductSubscriptionRequest $request,
        ProductSubscriptionResponse $response
    ): ProductSubscription {
        $subscription = new ProductSubscription(
            $request->getProduct(),
            $response->getSubscriptionId(),
            $request->getMappedValues($this->identifiersMapping)
        );
        $suggestedData = new SuggestedData($response->getSuggestedData());
        $subscription->setSuggestedData($suggestedData);
        $subscription->markAsMissingMapping($response->isMappingMissing());

        return $subscription;
    }
}
