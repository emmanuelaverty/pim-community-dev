<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\FilterBundle\Filter\ProductDraftFilterUtility;

/**
 * Choice filter for product draft
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChoiceFilter extends OroChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);

        if (!$data) {
            return false;
        }

        $field    = $this->get(ProductDraftFilterUtility::DATA_NAME_KEY);
        $operator = $this->getOperator($data['type']);
        $value    = $data['value'];

        $this->util->applyFilter($ds, $field, $operator, $value);

        return true;
    }
}
