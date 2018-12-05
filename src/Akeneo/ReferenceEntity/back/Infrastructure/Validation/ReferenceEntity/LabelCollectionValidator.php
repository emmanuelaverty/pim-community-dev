<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class LabelCollectionValidator extends ConstraintValidator
{
    /** @var FindActivatedLocalesByIdentifiersInterface */
    private $findActivatedLocales;

    public function __construct(FindActivatedLocalesByIdentifiersInterface $findActivatedLocales)
    {
        $this->findActivatedLocales = $findActivatedLocales;
    }

    /**
     * @param mixed      $labels     The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($labels, Constraint $constraint)
    {
        if (!$constraint instanceof LabelCollection) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();

        foreach ($labels as $localeCode => $label) {
            $this->validateLocaleCode($validator, $localeCode);
            $this->validateLabelForLocale($validator, $localeCode, $label);
        }

        $this->validateActivatedLocales($labels);
    }

    /**
     * @param mixed $localeCode
     */
    private function validateLocaleCode(ValidatorInterface $validator, $localeCode): void
    {
        $violations = $validator->validate($localeCode, [
            new Constraints\NotBlank(),
            new Constraints\Type(['type' => 'string']),
        ]);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    sprintf('invalid locale code: %s', $violation->getMessage()),
                    $violation->getParameters()
                );
            }
        }
    }

    /**
     * @param mixed $label
     */
    private function validateLabelForLocale(ValidatorInterface $validator, $localeCode, $label): void
    {
        $violations = $validator->validate($label, [
            new Constraints\NotNull(),
            new Constraints\Type(['type' => 'string']),
        ]);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    sprintf(
                        'invalid label for locale code "%s": %s, "%s" given',
                        $localeCode,
                        $violation->getMessage(),
                        $label
                    ),
                    $violation->getParameters()
                );
            }
        }
    }

    private function validateActivatedLocales(array $labels): void
    {
        $locales = array_filter(array_keys($labels), function ($label) {
            return is_string($label) && '' !== $label;
        });

        if (empty($locales)) {
            return;
        }

        $activatedLocales = ($this->findActivatedLocales)(LocaleIdentifierCollection::fromNormalized($locales));
        $notActivatedLocales = array_diff($locales, $activatedLocales->normalize());

        foreach ($notActivatedLocales as $notActivatedLocale) {
            $this->context->addViolation(sprintf('The locale "%s" is not activated.', $notActivatedLocale));
        }
    }
}
