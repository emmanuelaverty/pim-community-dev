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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributesMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyHandlerSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider
    ) {
        $this->beConstructedWith($familyRepository, $attributeRepository, $dataProviderFactory);

        $dataProviderFactory->create()->willReturn($dataProvider);
    }

    function it_is_initializabel()
    {
        $this->shouldHaveType(UpdateAttributesMappingByFamilyHandler::class);
    }

    function it_throws_an_exception_if_family_does_not_exist(
        UpdateAttributesMappingByFamilyCommand $command,
        FamilyRepositoryInterface $familyRepository
    ) {
        $command->getFamilyCode()->willReturn('router');
        $familyRepository->findOneByIdentifier('router')->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$command]);
    }

    function it_throws_an_exception_if_an_attribute_does_not_exist(
        UpdateAttributesMappingByFamilyCommand $command,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeMapping $attributeMapping
    ) {
        $command->getFamilyCode()->willReturn('router');
        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeCode = 'memory';
        $attributeMapping->getPimAttributeCode()->willReturn($attributeCode);

        $command->getAttributesMapping()->willReturn([$attributeMapping]);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$command]);
    }

    function it_fills_attribute_and_calls_data_provider(
        UpdateAttributesMappingByFamilyCommand $command,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeMapping $attributeMapping,
        AttributeInterface $attribute,
        DataProviderInterface $dataProvider
    ) {
        $command->getFamilyCode()->willReturn('router');
        $familyRepository->findOneByIdentifier('router')->willReturn(Argument::any());

        $attributeCode = 'memory';

        $attributeMapping->getPimAttributeCode()->willReturn($attributeCode);

        $command->getAttributesMapping()->willReturn([$attributeMapping]);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);

        $attributeMapping->setAttribute($attribute)->shouldBeCalled();
        $dataProvider->updateAttributesMapping('router', [$attributeMapping])->shouldBeCalled();

        $this->handle($command);
    }
}
