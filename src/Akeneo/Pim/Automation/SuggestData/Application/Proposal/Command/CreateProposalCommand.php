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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalCommand
{
    /** @var ProductSubscription */
    private $productSubscription;

    /**
     * @param ProductSubscription $productSubscription
     */
    public function __construct(ProductSubscription $productSubscription)
    {
        if ($productSubscription->getSuggestedData()->isEmpty()) {
            throw new \InvalidArgumentException(
                sprintf('There is no suggested data for subscription %s', $productSubscription->getSubscriptionId())
            );
        }
        $this->productSubscription = $productSubscription;
    }

    /**
     * @return ProductSubscription
     */
    public function getProductSubscription(): ProductSubscription
    {
        return $this->productSubscription;
    }
}
