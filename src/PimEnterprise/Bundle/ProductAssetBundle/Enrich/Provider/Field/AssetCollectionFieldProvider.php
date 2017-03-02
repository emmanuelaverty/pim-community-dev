<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Enrich\Provider\Field;

use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;

/**
 * Field provider for asset collections
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AssetCollectionFieldProvider implements FieldProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getField($attribute)
    {
        return 'akeneo-asset-collection-field';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            AttributeTypes::ASSETS_COLLECTION === $element->getType();
    }
}