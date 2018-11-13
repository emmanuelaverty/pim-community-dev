<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditOptionCollectionValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditOptionCollectionValueCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EditOptionCollectionValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_a_value_command_factory()
    {
        $this->shouldBeAnInstanceOf(EditValueCommandFactoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EditOptionCollectionValueCommandFactory::class);
    }

    function it_supports_the_option_collection_attribute(
        OptionCollectionAttribute $optionCollectionAttribute,
        TextAttribute $textAttribute
    ) {
        $normalizedData = ['data' => ['blue', 'red']];
        $this->supports($optionCollectionAttribute, $normalizedData)->shouldReturn(true);
        $this->supports($textAttribute, $normalizedData)->shouldReturn(false);

        $normalizedData = ['data' => []];
        $this->supports($optionCollectionAttribute, $normalizedData)->shouldReturn(false);
        $normalizedData = ['data' => 'blue'];
        $this->supports($optionCollectionAttribute, $normalizedData)->shouldReturn(false);
    }

    function it_creates_an_edit_option_collection_value_command(
        OptionCollectionAttribute $optionCollectionAttribute
    ) {
        $normalizedValue = [
            'data' => ['blue', 'red'],
            'channel' => 'mobile',
            'locale' => 'fr_FR',
        ];

        $command = $this->create($optionCollectionAttribute, $normalizedValue);
        $command->shouldBeAnInstanceOf(EditOptionCollectionValueCommand::class);
        $command->attribute->shouldBeAnInstanceOf(OptionCollectionAttribute::class);
        $command->channel->shouldBe('mobile');
        $command->locale->shouldBe('fr_FR');
    }
}
