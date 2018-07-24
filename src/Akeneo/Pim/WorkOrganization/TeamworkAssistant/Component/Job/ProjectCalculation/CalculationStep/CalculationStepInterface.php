<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

/**
 * Used to execute an action between Project and Products. For example, extract data from Products to add informations
 * in the Project. This action is called on the Project creation and before saving it.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface CalculationStepInterface
{
    /**
     * Execute the action.
     *
     * @param ProductInterface $product
     * @param ProjectInterface $project
     */
    public function execute(ProductInterface $product, ProjectInterface $project);
}