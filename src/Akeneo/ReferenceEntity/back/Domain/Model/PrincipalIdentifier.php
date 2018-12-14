<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class PrincipalIdentifier
{
    /** @var string  */
    private $identifier;

    private function __construct(string $identifier)
    {
        Assert::stringNotEmpty($identifier);
        $this->identifier = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public function stringValue(): string
    {
        return $this->identifier;
    }
}
