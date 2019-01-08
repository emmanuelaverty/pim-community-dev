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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SuggestedDataWriter implements ItemWriterInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(ProductSubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $subscriptions): void
    {
        $toRemove = [];
        $toSave = [];
        foreach ($subscriptions as $subscription) {
            if ($subscription->isCancelled()) {
                $toRemove[] = $subscription;
            } else {
                $toSave[] = $subscription;
            }
        }

        if (!empty($toRemove)) {
            $this->subscriptionRepository->bulkDelete($toRemove);
        }
        if (!empty($toSave)) {
            $this->subscriptionRepository->bulkSave($toSave);
        }
    }
}
