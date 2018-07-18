<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlRecordRepositoryTest extends SqlIntegrationTestCase
{
    /** @var RecordRepositoryInterface */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $this->resetDB();
        $this->insertEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_saves_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::fromString('starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            ['en_US' => 'Starck', 'fr_FR' => 'Starck']
        );

        $this->repository->save($record);

        $recordFound = $this->repository->getByIdentifier($identifier, $enrichedEntityIdentifier);
        $this->assertRecord($record, $recordFound);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(EntityNotFoundException::class);

        $identifier = RecordIdentifier::fromString('unknown_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->repository->getByIdentifier($identifier, $enrichedEntityIdentifier);
    }

    /**
     * @test
     */
    public function it_updates_a_record()
    {
        $identifier = RecordIdentifier::fromString('starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']
        );
        $this->repository->save($record);

        $record->updateLabels(
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Styliste',
            ])
        );
        $this->repository->save($record);

        $enrichedEntityFound = $this->repository->getByIdentifier($identifier, $enrichedEntityIdentifier);
        $this->assertRecord($record, $enrichedEntityFound);
    }

    private function assertRecord(
        Record $expectedRecord,
        Record $recordFound
    ): void {
        $this->assertTrue($expectedRecord->equals($recordFound));
        $labelCodesExpected = $expectedRecord->getLabelCodes();
        $labelCodesFound = $recordFound->getLabelCodes();
        sort($labelCodesExpected);
        sort($labelCodesFound);
        $this->assertSame($labelCodesExpected, $labelCodesFound);
        foreach ($expectedRecord->getLabelCodes() as $localeCode) {
            $this->assertEquals($expectedRecord->getLabel($localeCode),
                $recordFound->getLabel($localeCode));
        }
    }

    private function resetDB(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_record;
SQL;

        $this->get('database_connection')->executeQuery($resetQuery);
    }

    private function insertEnrichedEntity(): void
    {
        $repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer'
            ]
        );
        $repository->save($enrichedEntity);
    }
}
