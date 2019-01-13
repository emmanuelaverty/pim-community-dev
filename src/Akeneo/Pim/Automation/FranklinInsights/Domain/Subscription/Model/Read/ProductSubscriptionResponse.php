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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

/**
 * Represents a standard response from a subscription request
 * Holds a subscription id and optional suggested data.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionResponse
{
    /** @var int */
    private $productId;

    /** @var string */
    private $subscriptionId;

    /** @var array */
    private $suggestedData;

    /** @var bool */
    private $isMappingMissing;

    /** @var bool */
    private $isCancelled;

    /**
     * @param int $productId
     * @param string $subscriptionId
     * @param array $suggestedData
     * @param bool $isMappingMissing
     * @param bool $isCancelled
     */
    public function __construct(
        int $productId,
        string $subscriptionId,
        array $suggestedData,
        bool $isMappingMissing,
        bool $isCancelled
    ) {
        $this->validate($subscriptionId, $suggestedData);

        $this->productId = $productId;
        $this->subscriptionId = $subscriptionId;
        $this->suggestedData = $suggestedData;
        $this->isMappingMissing = $isMappingMissing;
        $this->isCancelled = $isCancelled;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    /**
     * @return array
     */
    public function getSuggestedData(): array
    {
        return $this->suggestedData;
    }

    /**
     * @return bool
     */
    public function isMappingMissing(): bool
    {
        return $this->isMappingMissing;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }

    /**
     * @param string $subscriptionId
     * @param array $suggestedData
     */
    private function validate(string $subscriptionId, array $suggestedData): void
    {
        if ('' === $subscriptionId) {
            throw new \InvalidArgumentException('subscription id cannot be empty');
        }
        // TODO: validate suggested data format?
    }
}