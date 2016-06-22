<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint on a filter field.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingFilterField extends Constraint
{
    /** @var string */
    public $message = 'The field "%field%" cannot be filtered or cannot be used with operator "%operator%".';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_filter_field_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
