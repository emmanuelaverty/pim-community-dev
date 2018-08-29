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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A specialized stateful context to deal with constraint violations.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class ConstraintViolationsContext implements Context
{
    /** @var ConstraintViolationListInterface */
    private $violations;

    public function __construct()
    {
        $this->violations = new ConstraintViolationList();
    }

    /**
     * @Then /^there should be a validation error on the property \'([^\']*)\' with message \'([^\']*)\'$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyWithMessage(string $expectedPropertyPath, string $message): void
    {
        $this->assertThereShouldBeViolations();
        $this->assertViolationOnPropertyWithMesssage($expectedPropertyPath, $message);
    }

    /**
     * @Then /^there should be a validation error with message \'([^\']*)\'$/
     */
    public function thereShouldBeAValidationErrorWithMessage(string $message): void
    {
        $this->assertThereShouldBeViolations();
        $this->assertViolation($message);
    }

    public function addViolations(ConstraintViolationListInterface $violationList): void
    {
        $this->violations->addAll($violationList);
    }

    public function assertThereIsNoViolations(): void
    {
        Assert::assertEquals(0, $this->violations->count(), 'There should be no violations');
    }

    public function assertThereShouldBeViolations(): void
    {
        Assert::assertGreaterThan(0, $this->violations->count(), 'There was some violations expected.');
    }

    public function assertViolationOnPropertyWithMesssage(string $expectedPropertyPath, string $expectedMessage): void
    {
        $found = false;
        foreach ($this->violations as $violation) {
            if ($expectedMessage === $violation->getMessage()
                && $expectedPropertyPath === $violation->getPropertyPath()
            ) {
                $found = true;
            }
        }

        Assert::assertTrue(
            $found,
            sprintf(
                'Expected violation with on property "%s" with message "%s" not found.',
                $expectedPropertyPath,
                $expectedMessage
            )
        );
    }

    public function assertViolation(string $expectedMessage): void
    {
        $found = false;
        foreach ($this->violations as $violation) {
            if ($expectedMessage === $violation->getMessage()) {
                $found = true;
            }
        }

        Assert::assertTrue($found, sprintf('Expected violation with message "%s" not found.', $expectedMessage));
    }

    public function hasViolations(): bool
    {
        return 0 < $this->violations->count();
    }
}
