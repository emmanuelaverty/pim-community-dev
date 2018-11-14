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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AttributesMapping\AttributesMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\AttributesMappingProvider;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributesMappingProviderSpec extends ObjectBehavior
{
    public function let(
        AttributesMappingApiInterface $api,
        AttributesMappingNormalizer $normalizer,
        ConfigurationRepositoryInterface $configurationRepo
    ): void {
        $this->beConstructedWith($api, $normalizer, $configurationRepo);
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);
        $api->setToken(Argument::any())->shouldBeCalled();
    }

    public function it_is_an_attributes_mapping_provider(): void
    {
        $this->shouldHaveType(AttributesMappingProvider::class);
        $this->shouldImplement(AttributesMappingProviderInterface::class);
    }

    public function it_gets_attributes_mapping($api): void
    {
        $response = new AttributesMapping([
            [
                'from' => [
                    'id' => 'product_weight',
                    'label' => [
                        'en_us' => 'Product Weight',
                    ],
                    'type' => 'metric',
                ],
                'to' => null,
                'summary' => ['23kg',  '12kg'],
                'status' => 'pending',
            ],
            [
                'from' => [
                    'id' => 'color',
                    'type' => 'multiselect',
                ],
                'to' => ['id' => 'color'],
                'status' => 'pending',
                'summary' => ['blue',  'red'],
            ],
        ]);
        $api->fetchByFamily('camcorders')->willReturn($response);

        $attributesMappingResponse = $this->getAttributesMapping('camcorders');
        $attributesMappingResponse->shouldHaveCount(2);
    }

    public function it_updates_attributes_mapping($api, $normalizer): void
    {
        $familyCode = 'foobar';
        $attributesMapping = ['foo' => 'bar'];
        $normalizedMapping = ['bar' => 'foo'];

        $normalizer->normalize($attributesMapping)->willReturn($normalizedMapping);
        $api->update($familyCode, $normalizedMapping)->shouldBeCalled();

        $this->updateAttributesMapping($familyCode, $attributesMapping);
    }
}
