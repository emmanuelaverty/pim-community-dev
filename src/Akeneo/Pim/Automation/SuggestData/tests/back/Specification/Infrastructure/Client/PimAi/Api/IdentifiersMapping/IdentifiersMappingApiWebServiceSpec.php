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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use PhpSpec\ObjectBehavior;

class IdentifiersMappingApiWebServiceSpec extends ObjectBehavior
{
    public function let(
        UriGenerator $uriGenerator,
        Client $httpClient
    ): void {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_subscription_collection(): void
    {
        $this->shouldHaveType(IdentifiersMapping\IdentifiersMappingApiWebService::class);
    }

    public function it_updates_mapping(
        UriGenerator $uriGenerator,
        Client $httpClient
    ): void {
        $normalizedMapping = ['foo' => 'bar'];
        $generatedRoute = '/api/mapping/identifiers';

        $uriGenerator->generate('/api/mapping/identifiers')
            ->shouldBeCalled()
            ->willReturn($generatedRoute);
        $httpClient->request('PUT', $generatedRoute, [
            'form_params' => $normalizedMapping,
        ])->shouldBeCalled();

        $this->update($normalizedMapping);
    }
}