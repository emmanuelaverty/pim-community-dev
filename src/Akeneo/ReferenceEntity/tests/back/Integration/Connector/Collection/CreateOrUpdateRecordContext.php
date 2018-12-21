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

namespace Akeneo\ReferenceEntity\Integration\Connector\Collection;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryChannelExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class CreateOrUpdateRecordContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Record/Connector/Collect/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var null|Response */
    private $pimResponse;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $activatedLocalesPerChannels;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        RecordRepositoryInterface $recordRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordRepository = $recordRepository;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
    }

    /**
     * @Given a record of the Brand reference entity existing in the ERP but not in the PIM
     */
    public function aRecordOfTheBrandReferenceEntityExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_kartell_record_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->loadDescriptionAttribute();
        $this->loadBrandReferenceEntity();
    }

    /**
     * @When the connector collects this record from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsThisRecordFromTheErpToSynchronizeItWithThePim()
    {
        Assert::notNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the record is created in the PIM with the information from the ERP
     */
    public function theRecordIsCreatedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_kartell_record_creation.json'
        );

        $kartellRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('kartell')
        );

        Assert::same('Kartell english label', $kartellRecord->getLabel('en_US'));
        Assert::same('Kartell french label', $kartellRecord->getLabel('fr_FR'));

        $englishDescriptionValue = $kartellRecord->getValues()->findValue(ValueKey::create(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('en_US')
        ));
        Assert::isInstanceOf($englishDescriptionValue, Value::class);
        Assert::isInstanceOf($englishDescriptionValue->getData(), TextData::class);
        Assert::same('Kartell english description.', $englishDescriptionValue->getData()->normalize());

        $frenchDescriptionValue = $kartellRecord->getValues()->findValue(ValueKey::create(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('fr_FR')
        ));
        Assert::isInstanceOf($frenchDescriptionValue, Value::class);
        Assert::isInstanceOf($frenchDescriptionValue->getData(), TextData::class);
        Assert::same('Kartell french description.', $frenchDescriptionValue->getData()->normalize());
    }

    /**
     * @Given a record of the Brand reference entity existing in the ERP and the PIM with different information
     */
    public function aRecordOfTheBrandReferenceEntityExistingInTheErpAndThePimWithDifferentInformation()
    {
        $this->requestContract = 'successful_kartell_record_update.json';

        $this->loadBrandReferenceEntity();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->loadBrandKartellRecord();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
    }

    /**
     * @Then the record is correctly synchronized in the PIM with the information from the ERP
     */
    public function theRecordIsCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_kartell_record_update.json'
        );

        $kartellRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('kartell')
        );

        Assert::same('Kartell updated english label', $kartellRecord->getLabel('en_US'));
        Assert::same('Kartell updated french label', $kartellRecord->getLabel('fr_FR'));

        $englishDescriptionValue = $kartellRecord->getValues()->findValue(ValueKey::create(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('en_US')
        ));
        Assert::isInstanceOf($englishDescriptionValue, Value::class);
        Assert::isInstanceOf($englishDescriptionValue->getData(), TextData::class);
        Assert::same('Kartell english description', $englishDescriptionValue->getData()->normalize());

        $englishNameValue = $kartellRecord->getValues()->findValue(ValueKey::create(
            AttributeIdentifier::create('brand', 'name', 'fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::createFromNormalized('en_US')
        ));
        Assert::isInstanceOf($englishNameValue, Value::class);
        Assert::isInstanceOf($englishNameValue->getData(), TextData::class);
        Assert::same('Updated english name', $englishNameValue->getData()->normalize());

        $frenchNameValue = $kartellRecord->getValues()->findValue(ValueKey::create(
            AttributeIdentifier::create('brand', 'name', 'fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::createFromNormalized('fr_FR')
        ));

        Assert::null($frenchNameValue);
    }

    /**
     * @When the connector collects a record that has an invalid format
     */
    public function theConnectorCollectsARecordThatHasAnInvalidFormat()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_format.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the record has an invalid format
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheRecordHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_format.json'
        );
    }

    /**
     * @When the connector collects a record whose data does not comply with the business rules
     */
    public function theConnectorCollectsARecordWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['fr_FR', 'en_US']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $this->loadDescriptionAttribute();
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the record has data that does not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheRecordHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_data.json'
        );
    }

    /**
     * @Given some records of the Brand reference entity existing in the ERP but not in the PIM
     */
    public function someRecordsOfTheBrandReferenceEntityExistingInTheErpButNotInThePim()
    {
        $this->loadBrandReferenceEntity();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
    }

    /**
     * @Given some records of the Brand reference entity existing in the ERP and in the PIM but with different information
     */
    public function someRecordsOfTheBrandReferenceEntityExistingInTheErpAndInThePimButWithDifferentInformation()
    {
        $this->loadBrandKartellRecord();
        $this->loadBrandLexonRecord();
    }

    /**
     * @When the connector collects these records from the ERP to synchronize them with the PIM
     */
    public function theConnectorCollectsTheseRecordsFromTheErpToSynchronizeThemWithThePim()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_synchronization.json'
        );
    }

    /**
     * @Then the records that existed only in the ERP are correctly created in the PIM
     */
    public function theRecordsThatExistedOnlyInTheErpAreCorrectlyCreatedInThePim()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_synchronization.json'
        );

        $fatboyRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('fatboy')
        );

        $expectedFatboyRecord = Record::create(
            $fatboyRecord->getIdentifier(),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('fatboy'),
            ['en_US' => 'Fatboy label'],
            Image::createEmpty(),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Fatboy name')
                )
            ])
        );

        \PHPUnit\Framework\Assert::assertEquals($expectedFatboyRecord, $fatboyRecord);
    }

    /**
     * @Then the records existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP
     */
    public function theRecordsExistingBothInTheErpAndThePimAreCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $expectedKartellRecord = Record::create(
            RecordIdentifier::fromString('brand_kartell_fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('kartell'),
            ['en_US' => 'Kartell updated english label', 'fr_FR' => 'Kartell updated french label'],
            Image::createEmpty(),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell updated english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english description')
                )
            ])
        );

        $kartellRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('kartell')
        );

        \PHPUnit\Framework\Assert::assertEquals($expectedKartellRecord, $kartellRecord);

        $expectedLexonRecord = Record::create(
            RecordIdentifier::fromString('brand_lexon_fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('lexon'),
            ['en_US' => 'Lexon updated english label'],
            Image::createEmpty(),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Updated Lexon english name')
                )
            ])
        );

        $lexonRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('lexon')
        );

        \PHPUnit\Framework\Assert::assertEquals($expectedLexonRecord, $lexonRecord);
    }

    /**
     * @When the connector collects records from the ERP among which some records have data that do not comply with the business rules
     */
    public function theConnectorCollectsRecordsFromTheErpAmongWhichSomeRecordsHaveDataThatDoNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'collect_brand_records_with_unprocessable_records.json'
        );
    }

    /**
     * @Then the PIM notifies the connector which records have data that do not comply with the business rules and what are the errors
     */
    public function thePimNotifiesTheConnectorWhichRecordsHaveDataThatDoNotComplyWithTheBusinessRulesAndWhatAreTheErrors()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'collect_brand_records_with_unprocessable_records.json'
        );
    }

    /**
     * @When the connector collects a number of records exceeding the maximum number of records in one request
     */
    public function theConnectorCollectsANumberOfRecordsExceedingTheMaximumNumberOfRecordsInOneRequest()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'too_many_records_to_process.json'
        );
    }

    /**
     * @Then the PIM notifies the connector that there were too many records to collect in one request
     */
    public function thePimNotifiesTheConnectorThatThereWereTooManyRecordsToCollectInOneRequest()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'too_many_records_to_process.json'
        );
    }

    private function loadBrandReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            ['en_US' => 'Brand'],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function loadDescriptionAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadNameAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'name', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadBrandKartellRecord(): void
    {
        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('kartell.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');

        $record = Record::create(
            RecordIdentifier::fromString('brand_kartell_fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('kartell'),
            ['en_US' => 'Kartell English label'],
            Image::fromFileInfo($mainImageInfo),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell french name')
                )
            ])
        );

        $this->recordRepository->create($record);
    }

    private function loadBrandLexonRecord(): void
    {
        $record = Record::create(
            RecordIdentifier::fromString('brand_lexon_fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('lexon'),
            ['en_US' => 'Lexon'],
            Image::createEmpty(),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon description')
                )
            ])
        );

        $this->recordRepository->create($record);
    }
}
