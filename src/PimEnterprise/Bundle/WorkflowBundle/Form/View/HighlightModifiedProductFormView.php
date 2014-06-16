<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\View;

use Symfony\Component\Form\FormView;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\PropositionChangesApplier;

/**
 * Product form view decorator that adds classes and fields on which proposal value is applied
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class HighlightModifiedProductFormView implements ProductFormViewInterface
{
    /** @var ProductFormView */
    protected $productFormView;

    /** @var PropositionChangesApplier */
    protected $applier;

    /** @var array|FormView */
    protected $view = [];

    /**
     * @param ProductFormView           $productFormView
     * @param PropositionChangesApplier $applier
     */
    public function __construct(
        ProductFormViewInterface $productFormView,
        PropositionChangesApplier $applier
    ) {
        $this->productFormView = $productFormView;
        $this->applier = $applier;
    }

    /**
     * {@inheritdoc}
     */
    public function addChildren(ProductValueInterface $value, FormView $view)
    {
        $this->productFormView->addChildren($value, $view);
    }

    /**
     * Get the computed view
     *
     * @return array|FormView
     */
    public function getView()
    {
        $views = $this->productFormView->getView();

        foreach ($views as $key => $view) {
            foreach (array_keys($view['attributes']) as $name) {
                if (isset($views[$key]['attributes'][$name]['value']) && $this->applier->isMarkedAsModified($views[$key]['attributes'][$name])) {

                    $this->markFieldAsModified($views[$key]['attributes'][$name]['value']);

                } elseif (isset($views[$key]['attributes'][$name]['values'])) {

                    foreach ($views[$key]['attributes'][$name]['values'] as $scope => $value) {
                        if ($this->applier->isMarkedAsModified($views[$key]['attributes'][$name], $scope)) {
                            $this->markFieldAsModified($views[$key]['attributes'][$name]['values'][$scope]);
                        }
                    }

                }
            }
        }

        return $views;
    }

    /**
     * Mark a form view and all its children as modified
     *
     * @param FormView $view
     */
    protected function markFieldAsModified(FormView $view)
    {
        foreach ($view as $child) {
            $child->vars['modified'] = 'Display current value(s) here';
            $this->markFieldAsModified($child);
        }
    }
}
