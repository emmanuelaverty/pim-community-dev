<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\FileUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FileUpdaterSpec extends ObjectBehavior
{
    function let(
        FileStorerInterface $fileStorer
    ) {
        $this->beConstructedWith($fileStorer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileUpdater::class);
    }

    function it_only_supports_edit_file_value_command()
    {
        $this->supports(new EditFileValueCommand())->shouldReturn(true);
        $this->supports(new EditTextValueCommand())->shouldReturn(false);
    }

    function it_edits_the_file_value_of_a_record(
        $fileStorer,
        Record $record
    ) {
        $imageAttribute = $this->getAttribute();

        $editFileValueCommand = new EditFileValueCommand();
        $editFileValueCommand->attribute = $imageAttribute;
        $editFileValueCommand->channel = 'ecommerce';
        $editFileValueCommand->locale = 'fr_FR';
        $editFileValueCommand->filePath = '/a/file/key';
        $editFileValueCommand->originalFilename = 'my_image.png';

        $fileInfo = new FileInfo();
        $fileInfo->setKey('/b/file/key');
        $fileInfo->setOriginalFilename('my_image.png');

        $value = Value::create(
            $editFileValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editFileValueCommand->channel),
            LocaleReference::createfromNormalized($editFileValueCommand->locale),
            FileData::createFromFileinfo($fileInfo)
        );
        $fileStorer->store(Argument::type(\SplFileInfo::class), 'catalogStorage')->willReturn($fileInfo);
        $this->__invoke($record, $editFileValueCommand);
        $record->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(Record $record)
    {
        $wrongCommand = new EditTextValueCommand();
        $this->supports($wrongCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$record, $wrongCommand]);
    }

    private function getAttribute(): ImageAttribute
    {
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['fr_FR' => 'image', 'en_US' => 'image']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('3000'),
            AttributeAllowedExtensions::fromList(['gif', 'jfif', 'jif', 'jpeg', 'jpg', 'pdf'])
        );

        return $imageAttribute;
    }
}
