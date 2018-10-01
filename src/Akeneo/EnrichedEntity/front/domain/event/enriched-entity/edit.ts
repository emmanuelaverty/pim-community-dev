import {NormalizedReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import File from 'akeneoreferenceentity/domain/model/file';

export const referenceEntityEditionReceived = (referenceEntity: NormalizedReferenceEntity) => {
  return {type: 'ENRICHED_ENTITY_EDITION_RECEIVED', referenceEntity};
};

export const referenceEntityEditionUpdated = (referenceEntity: NormalizedReferenceEntity) => {
  return {type: 'ENRICHED_ENTITY_EDITION_UPDATED', referenceEntity};
};

export const referenceEntityEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED', value, locale};
};

export const referenceEntityEditionImageUpdated = (image: File) => {
  return {type: 'ENRICHED_ENTITY_EDITION_IMAGE_UPDATED', image: image.normalize()};
};

export const referenceEntityEditionSubmission = () => {
  return {type: 'ENRICHED_ENTITY_EDITION_SUBMISSION'};
};

export const referenceEntityEditionSucceeded = () => {
  return {type: 'ENRICHED_ENTITY_EDITION_SUCCEEDED'};
};

export const referenceEntityEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ENRICHED_ENTITY_EDITION_ERROR_OCCURED', errors};
};
