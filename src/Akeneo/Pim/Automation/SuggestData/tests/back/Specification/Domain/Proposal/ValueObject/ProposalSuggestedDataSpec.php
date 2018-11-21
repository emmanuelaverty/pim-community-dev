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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalSuggestedDataSpec extends ObjectBehavior
{
    public function it_is_a_write_model_for_suggested_data(): void
    {
        $this->beConstructedWith(42, ['abc' => 'def']);
        $this->shouldBeAnInstanceOf(ProposalSuggestedData::class);
    }

    public function it_exposes_its_properties(ProductInterface $product): void
    {
        $this->beConstructedWith(42, ['foo' => 'bar']);
        $this->getProductId()->shouldReturn(42);
        $this->getSuggestedValues()->shouldReturn(['foo' => 'bar']);
    }
}
