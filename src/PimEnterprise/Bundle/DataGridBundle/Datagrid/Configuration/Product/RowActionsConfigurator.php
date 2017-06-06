<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Row actions configurator for product grid
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RowActionsConfigurator implements ConfiguratorInterface
{
    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ConfigurationRegistry */
    protected $registry;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param ConfigurationRegistry         $registry
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ProductRepositoryInterface    $productRepository
     * @param LocaleRepositoryInterface     $localeRepository
     */
    public function __construct(
        ConfigurationRegistry $registry,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->registry = $registry;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addCustomEditAction();
        $this->addRowActions();
    }

    /**
     * Returns a callback to ease the configuration of different actions for each row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $product = $this->productRepository->findOneByIdentifier($record->getValue('identifier'));

            $editGranted = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);
            $ownershipGranted = $editGranted && $this->authorizationChecker->isGranted(Attributes::OWN, $product);

            return [
                'show'            => !$editGranted,
                'edit'            => $editGranted,
                'edit_categories' => $ownershipGranted,
                'delete'          => $editGranted,
                'toggle_status'   => $ownershipGranted
            ];
        };
    }

    /**
     * Add a custom edit action to redirect on granted action (view or edit)
     */
    protected function addCustomEditAction()
    {
        $properties = $this->configuration->offsetGetByPath('[properties]');
        $properties['row_action_link'] = [
            'type'   => 'url',
            'route'  => 'pim_enrich_product_edit',
            'params' => ['id', 'dataLocale']
        ];
        $this->configuration->offsetSetByPath('[properties]', $properties);

        $actions = $this->configuration->offsetGetByPath('[actions]');
        unset($actions['edit']['rowAction']);
        $actions['row_action'] = [
            'type'      => 'tab-redirect',
            'label'     => 'Dispatch a product',
            'tab'       => 'attributes',
            'link'      => 'row_action_link',
            'rowAction' => true,
            'hidden'    => true
        ];
        $this->configuration->offsetSetByPath('[actions]', $actions);
    }

    /**
     * Add dynamic row action and configure the closure
     */
    protected function addRowActions()
    {
        $this->addShowRowAction();
        $this->addShowLinkProperty();
        $this->configuration->offsetSetByPath(
            '[action_configuration]',
            $this->getActionConfigurationClosure()
        );
    }

    /**
     * Get row action configuration
     *
     * @return array
     */
    protected function getActionConfiguration()
    {
        return $this->actionConfiguration;
    }

    /**
     * Add a show action to the configuration.
     *
     * @return RowActionsConfigurator
     */
    protected function addShowRowAction()
    {
        $viewAction = [
            'type'      => 'tab-redirect',
            'label'     => 'View the product',
            'tab'       => 'attributes',
            'icon'      => 'eye-open',
            'link'      => 'show_link',
            'rowAction' => true,
        ];
        $this->configuration->offsetSetByPath('[actions][show]', $viewAction);

        return $this;
    }

    /**
     * Add show link property to the configuration.
     *
     * @return RowActionsConfigurator
     */
    protected function addShowLinkProperty()
    {
        $showLink = [
            'type'   => 'url',
            'route'  => 'pim_enrich_product_edit',
            'params' => ['id', 'dataLocale'],
        ];
        $this->configuration->offsetSetByPath('[properties][show_link]', $showLink);

        return $this;
    }
}
