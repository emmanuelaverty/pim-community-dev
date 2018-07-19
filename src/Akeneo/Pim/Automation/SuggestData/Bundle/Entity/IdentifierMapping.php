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

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Identifier Mapping doctrine entity
 */
class IdentifierMapping
{
    private $id;
    private $pimAiCode;
    private $attribute;

    /**
     * @param string $pimAiCode
     * @param AttributeInterface $attribute
     */
    public function __construct(string $pimAiCode, AttributeInterface $attribute)
    {
        $this->id = null;
        $this->pimAiCode = $pimAiCode;
        $this->attribute = $attribute;
    }

    /**
     * @return mixed
     */
    public function getPimAiCode(): string
    {
        return $this->pimAiCode;
    }

    /**
     * @return mixed
     */
    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @param mixed $attribute
     *
     * @return IdentifierMapping
     */
    public function updateAttribute($attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }
}
