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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingResponse implements \IteratorAggregate
{
    /** @var array */
    private $attributes;

    public function __construct()
    {
        $this->attributes = [];
    }

    /**
     * @param AttributeMapping $attribute
     *
     * @return AttributesMappingResponse
     */
    public function addAttribute(AttributeMapping $attribute): self
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->attributes);
    }
}