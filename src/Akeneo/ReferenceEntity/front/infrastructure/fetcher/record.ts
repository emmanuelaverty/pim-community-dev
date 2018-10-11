import RecordFetcher from 'akeneoreferenceentity/domain/fetcher/record';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import hydrator from 'akeneoreferenceentity/application/hydrator/record';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON, putJSON} from 'akeneoreferenceentity/tools/fetch';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {Filter} from 'akeneoreferenceentity/application/reducer/grid';

const routing = require('routing');

class InvalidArgument extends Error {}

export class RecordFetcherImplementation implements RecordFetcher {
  constructor(private hydrator: (backendRecord: any) => Record) {
    Object.freeze(this);
  }

  async fetch(referenceEntityIdentifier: ReferenceEntityIdentifier, recordCode: RecordCode): Promise<Record> {
    const backendRecord = await getJSON(
      routing.generate('akeneo_reference_entities_record_get_rest', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
        recordCode: recordCode.stringValue(),
      })
    ).catch(errorHandler);

    const image = undefined === backendRecord.image ? null : backendRecord.image;

    return this.hydrator({
      ...backendRecord,
      image,
    });
  }

  async fetchAll(referenceEntityIdentifier: ReferenceEntityIdentifier): Promise<Record[]> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_reference_entities_record_index_rest', {referenceEntityIdentifier})
    ).catch(errorHandler);

    return hydrateAll<Record>(this.hydrator)(backendRecords.items);
  }

  async search(query: Query): Promise<{items: NormalizedRecord[]; total: number}> {
    const referenceEntityCode = query.filters.find((filter: Filter) => 'reference_entity' === filter.field);
    if (undefined === referenceEntityCode) {
      throw new InvalidArgument('The search repository expect a reference_entity filter');
    }

    const backendRecords = await putJSON(
      routing.generate('akeneo_reference_entities_record_index_rest', {
        referenceEntityIdentifier: referenceEntityCode.value,
      }),
      query
    ).catch(errorHandler);

    return backendRecords;
  }
}

export default new RecordFetcherImplementation(hydrator);
