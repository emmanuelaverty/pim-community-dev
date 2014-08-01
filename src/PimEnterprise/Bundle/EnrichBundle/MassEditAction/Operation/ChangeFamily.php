<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily as BaseChangeFamily;

use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Batch operation to change the family of products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChangeFamily extends BaseChangeFamily
{
    /**
     * Override to bypass the creation of a product draft
     *
     * @return array
     */
    public function getSavingOptions()
    {
        $options = parent::getSavingOptions();
        $options['bypass_product_draft'] = true;

        return $options;
    }
}
