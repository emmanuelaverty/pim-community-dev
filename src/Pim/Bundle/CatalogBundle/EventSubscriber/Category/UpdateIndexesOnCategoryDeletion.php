<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber\Category;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Category\GetDescendentCategoryCodes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscribes to category deletion events and updates all ES indexes accordingly.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UpdateIndexesOnCategoryDeletion implements EventSubscriberInterface
{
    /** @var GetDescendentCategoryCodes */
    private $getDescendentCategoryCodes;

    /** @var Client */
    private $productClient;

    /** @var Client */
    private $productModelClient;

    /** @var Client */
    private $productAndProductModelClient;

    public function __construct(
        GetDescendentCategoryCodes $getDescendentCategoryCodes,
        Client $productClient,
        Client $productModelClient,
        Client $productAndProductModelClient
    ) {
        $this->getDescendentCategoryCodes = $getDescendentCategoryCodes;
        $this->productClient = $productClient;
        $this->productModelClient = $productModelClient;
        $this->productAndProductModelClient = $productAndProductModelClient;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_REMOVE => 'updateIndexes',
        ];
    }

    public function updateIndexes(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof CategoryInterface) {
            return;
        }

        $categoryCodesToRemove = $this->getDescendentCategoryCodes($event->getSubject());

        $body = [
            'query' => [
                'terms' => ['categories' => $categoryCodesToRemove],
            ],
            'script' => [
                'source' => 'ctx._source.categories.removeAll(categories)',
                'lang'   => 'painless',
                'params' => ['categories' => $categoryCodesToRemove],
            ],
        ];

        $this->productClient->updateByQuery($body);
        $this->productModelClient->updateByQuery($body);
        $this->productAndProductModelClient->updateByQuery($body);
    }
}
