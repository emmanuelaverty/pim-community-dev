<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\MultipleSelectAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class MultipleSelectAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Couleur', 'en_US' => 'Color']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        ]);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn([
                'identifier'                  => 'name_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                        => 'name',
                'labels'                      => ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => true,
                'value_per_locale'            => true,
                'type'                        => 'multiple_select',
                'attribute_options'            => [],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MultipleSelectAttribute::class);
    }

    function it_can_have_a_multiple_options_set_to_it()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                ),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['fr_FR' => 'vert'])
                ),
            ]
        );
        $subject = $this->normalize();
        $subject->shouldReturn([
            'identifier'                  => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                        => 'name',
            'labels'                      => ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => true,
            'value_per_locale'            => true,
            'type'                        => 'multiple_select',
            'attribute_options'           => [
                [
                    'option_code' => 'red',
                    'labels'      => [
                        'fr_FR' => 'rouge',
                    ],
                ],
                [
                    'option_code' => 'green',
                    'labels'      => [
                        'fr_FR' => 'vert',
                    ],
                ],
            ],
        ]);
    }

    function it_cannot_have_more_too_options()
    {
        for($i = 0; $i < 101; $i++) {
            $tooManyOptions[] = AttributeOption::create(
                OptionCode::fromString((string) $i),
                LabelCollection::fromArray([])
            );
        }
        $this->shouldThrow(\InvalidArgumentException::class)->during('setOptions', [$tooManyOptions]);
    }

    function it_cannot_have_options_with_the_same_code()
    {
        $duplicates = [
            AttributeOption::create(
                OptionCode::fromString('red'),
                LabelCollection::fromArray([])
            ),
            AttributeOption::create(
                OptionCode::fromString('red'),
                LabelCollection::fromArray([])
            ),
        ];
        $this->shouldThrow(\InvalidArgumentException::class)->during('setOptions', [$duplicates]);
    }
}
