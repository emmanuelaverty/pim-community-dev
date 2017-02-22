<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Validator;

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectLocaleValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($project, Constraint $constraint)
    {
        if (!$project instanceof ProjectInterface) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ProjectInterface');
        }

        if (!$constraint instanceof ProjectLocale) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ProjectLocale');
        }

        $locale = $project->getLocale();
        if (null !== $locale && !$locale->hasChannel($project->getChannel())) {
            $message = $this->translator->trans($constraint->message, ['{{ locale }}' => $locale->getCode()]);

            $this->context->buildViolation($message)->addViolation();
        }
    }
}
