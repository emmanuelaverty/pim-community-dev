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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMapping;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Component\Exception\DuplicateMappingAttributeException;

class UpdateIdentifiersMappingSpec extends ObjectBehavior
{
    function it_is_an_update_identifiers_mapping_command()
    {
        $this->beConstructedWith([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldHaveType(UpdateIdentifiersMapping::class);
    }

    function it_returns_identifiers_mapping()
    {
        $identifiersMapping = [
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ];
        $this->beConstructedWith($identifiersMapping);

        $this->getIdentifiersMapping()->shouldReturn($identifiersMapping);
    }

    function it_does_not_fail_whatever_identifiers_order()
    {
        $identifiersMapping = [
            'mpn' => 'model',
            'brand' => 'manufacturer',
            'asin' => 'id',
            'upc' => 'ean',
        ];
        $this->beConstructedWith($identifiersMapping);

        $this->getIdentifiersMapping()->shouldReturn($identifiersMapping);
    }

    function it_throws_an_exception_if_identifiers_are_missing()
    {
        $mapping = [
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
        ];
        $this->beConstructedWith($mapping);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_if_an_attribute_is_used_more_than_once()
    {
        $this->beConstructedWith([
            'brand' => 'ean',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldThrow(new DuplicateMappingAttributeException('An attribute cannot be used more than once'))->duringInstantiation();
    }
}
