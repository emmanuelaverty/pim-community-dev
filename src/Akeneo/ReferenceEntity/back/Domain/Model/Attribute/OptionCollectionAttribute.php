<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use http\Exception\InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'option_collection';
    private const MAX_OPTIONS = 100;

    /** @var AttributeOption[] */
    private $attributeOptions = [];

    public static function create(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale
    ) : self {
        return new self(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );
    }

    public function setOptions(array $attributeOptions): void
    {
        Assert::maxCount(
            $attributeOptions,
            self::MAX_OPTIONS,
            sprintf(
                'A multiselect attribute can have a maximum of %d options, %d given',
                self::MAX_OPTIONS,
                \count($attributeOptions)
            )
        );

        $this->attributeOptions = [];

        foreach ($attributeOptions as $attributeOption) {
            $optionCode = (string) $attributeOption->getCode();
            if (array_key_exists($optionCode, $this->attributeOptions)) {
                throw new \InvalidArgumentException('Expected to have a unique set of option codes');
            }

            $this->attributeOptions[$optionCode] = $attributeOption;
        }
    }

    public function addOption(AttributeOption $option): void
    {
        Assert::maxCount(
            $this->attributeOptions,
            self::MAX_OPTIONS - 1,
            sprintf(
                'It is not possible to add a new option as a multiselect attribute can have a maximum of %d options',
                self::MAX_OPTIONS
            )
        );

        Assert::false(isset($this->attributeOptions[(string) $option->getCode()]), 'Option already exists in the collection');

        $this->attributeOptions[(string) $option->getCode()] = $option;
    }

    public function updateOption(AttributeOption $option): void
    {
        Assert::true(isset($this->attributeOptions[(string) $option->getCode()]), 'Option cannot be set as it does not exist');

        $this->attributeOptions[(string) $option->getCode()] = $option;
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $this->getAttributeOptions()
                ),
            ]
        );
    }

    /**
     * @return AttributeOption[]
     */
    public function getAttributeOptions(): array
    {
        return array_values($this->attributeOptions);
    }

    public function hasAttributeOption(OptionCode $optionCode): bool
    {
        return array_key_exists((string) $optionCode, $this->attributeOptions);
    }

    public function getAttributeOption(OptionCode $code): AttributeOption
    {
        if (!isset($this->attributeOptions[(string) $code])) {
            throw new \InvalidArgumentException(sprintf('Attribute option "%s" does not exist.', (string) $code));
        }

        return $this->attributeOptions[(string) $code];
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
