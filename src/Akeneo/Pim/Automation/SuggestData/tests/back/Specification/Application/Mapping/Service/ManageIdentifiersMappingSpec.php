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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\ManageIdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ManageIdentifiersMappingSpec extends ObjectBehavior
{
    public function let(
        UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ): void {
        $this->beConstructedWith($updateIdentifiersMappingHandler, $identifiersMappingRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ManageIdentifiersMapping::class);
    }

    public function it_updates_identifiers_mapping($updateIdentifiersMappingHandler): void
    {
        $identifiersMapping = [
            'asin' => 'PIM_asin',
            'brand' => 'PIM_brand',
            'mpn' => 'PIM_mpn',
            'upc' => 'PIM_upc',
        ];

        $updateIdentifiersMappingHandler
            ->handle(new UpdateIdentifiersMappingCommand($identifiersMapping))
            ->shouldBeCalled();

        $this->updateIdentifierMapping($identifiersMapping);
    }
}