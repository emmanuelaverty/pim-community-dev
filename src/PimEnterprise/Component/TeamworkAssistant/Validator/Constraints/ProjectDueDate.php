<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectDueDate extends Constraint
{
    /** @var string */
    public $message = 'teamwork_assistant.project.project_due_date';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'project_due_date_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}