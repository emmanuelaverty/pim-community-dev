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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\Franklin;
use PhpSpec\ObjectBehavior;

class FranklinSpec extends ObjectBehavior
{
    public function let(
        AuthenticationProviderInterface $authenticationProvider,
        SubscriptionProviderInterface $productSubscriptionProvider,
        AttributesMappingProviderInterface $attributesMappingProvider,
        IdentifiersMappingProviderInterface $identifiersMappingProvider,
        AttributeOptionsMappingProviderInterface $attributeOptionsMappingProvider
    ): void {
        $this->beConstructedWith(
            $authenticationProvider,
            $productSubscriptionProvider,
            $attributesMappingProvider,
            $identifiersMappingProvider,
            $attributeOptionsMappingProvider
        );
    }

    public function it_is_franklin_adapter(): void
    {
        $this->shouldHaveType(Franklin::class);
        $this->shouldImplement(DataProviderInterface::class);
    }

    public function it_updates_the_identifiers_mapping(
        $identifiersMappingProvider,
        IdentifiersMapping $mapping
    ): void {
        $identifiersMappingProvider->updateIdentifiersMapping($mapping)->shouldBeCalled();

        $this->updateIdentifiersMapping($mapping);
    }

    public function it_unsubscribes_a_subscription_id_from_franklin($productSubscriptionProvider): void
    {
        $productSubscriptionProvider->unsubscribe('foo-bar')->shouldBeCalled();

        $this->unsubscribe('foo-bar');
    }

    public function it_gets_attributes_mapping($attributesMappingProvider, AttributesMappingResponse $response): void
    {
        $attributesMappingProvider->getAttributesMapping('camcorders')->willReturn($response);

        $this->getAttributesMapping('camcorders')->shouldReturn($response);
    }

    public function it_updates_attributes_mapping($attributesMappingProvider): void
    {
        $familyCode = 'foobar';
        $attributesMapping = ['foo' => 'bar'];

        $attributesMappingProvider->updateAttributesMapping($familyCode, $attributesMapping)->shouldBeCalled();

        $this->updateAttributesMapping($familyCode, $attributesMapping);
    }

    public function it_retrieves_attribute_options_mapping($attributeOptionsMappingProvider): void
    {
        $familyCode = new FamilyCode('family_code');
        $franklinAttributeId = new FranklinAttributeId('franklin_attr_id');
        $mapping = new AttributeOptionsMapping('family_code', 'franklin_attr_id', []);

        $attributeOptionsMappingProvider
            ->getAttributeOptionsMapping($familyCode, $franklinAttributeId)
            ->willReturn($mapping);

        $this
            ->getAttributeOptionsMapping($familyCode, $franklinAttributeId)
            ->shouldReturn($mapping);
    }
}
