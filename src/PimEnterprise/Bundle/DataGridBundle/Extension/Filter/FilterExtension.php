<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Extension\Filter;

use Pim\Bundle\DataGridBundle\Extension\Filter\FilterExtension as BaseFilterExtension;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class FilterExtension extends BaseFilterExtension
{
    /**
     * {@inheritdoc}
     *
     * We override this method to add a new grid that can use the category filter
     */
    protected function getCategoryFilterConfig($gridName)
    {
        $gridConfigs = [
            'published-product-grid' => [
                'type'      => 'published_product_category',
                'data_name' => 'category'
            ],
            'asset-grid' => [
                'type'      => 'asset_category',
                'data_name' => 'category'
            ],
            'asset-picker-grid' => [
                'type'      => 'product_asset_category',
                'data_name' => 'product_asset_category'
            ]
        ];

        return isset($gridConfigs[$gridName]) ? $gridConfigs[$gridName] : parent::getCategoryFilterConfig($gridName);
    }
}
