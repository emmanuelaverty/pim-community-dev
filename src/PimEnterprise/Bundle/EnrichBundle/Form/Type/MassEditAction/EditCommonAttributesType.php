<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\EditCommonAttributesType as BaseEditCommonAttributesType;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber\CollectProductMassEditValuesSubscriber;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type of the EditCommonAttributes operation
 * configured with a data collector subscriber
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class EditCommonAttributesType extends BaseEditCommonAttributesType
{
    /** @var CollectProductValuesSubscriber */
    protected $subscriber;

    /**
     * @param ProductFormViewInterface       $productFormView
     * @param LocaleHelper                   $localeHelper
     * @param string                         $attributeClass
     * @param CollectProductValuesSubscriber $subscriber
     */
    public function __construct(
        ProductFormViewInterface $productFormView,
        LocaleHelper $localeHelper,
        $attributeClass,
        CollectProductMassEditValuesSubscriber $subscriber
    ) {
        parent::__construct($productFormView, $localeHelper, $attributeClass);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber($this->subscriber);
    }
}
