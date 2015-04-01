<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;

/**
 * Batch operation to unpublish products
 *
 * @author Julien Janvier <nicolas@akeneo.com>
 */
class Unpublish extends AbstractMassEditOperation implements
    ConfigurableOperationInterface,
    BatchableOperationInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setActions([]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pimee_enrich_mass_unpublish';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'unpublish';
    }

    /**
     * Get configuration to send to the BatchBundle command
     *
     * @return string
     */
    public function getBatchConfig()
    {
        return addslashes(
            json_encode(
                [
                    'filters' => $this->getFilters(),
                    'actions' => $this->getActions(),
                ]
            )
        );
    }

    /**
     * Get the code of the JobInstance
     *
     * @return string
     */
    public function getBatchJobCode()
    {
        return 'unpublish_product';
    }

    /**
     * Get the form options to configure the operation
     *
     * @return array
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * Get the name of items this operation applies to
     *
     * @return string
     */
    public function getItemsName()
    {
        return 'published_product';
    }
}
