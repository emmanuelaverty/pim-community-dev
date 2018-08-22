import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {ConcreteImageAttribute} from 'akeneoenrichedentity/domain/model/attribute/type/image';
import {createIdentifier as denormalizeAttributeIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

const description = denormalizeAttribute({
  identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
  enriched_entity_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
});
const frontView = denormalizeAttribute({
  identifier: {identifier: 'front_view', enriched_entity_identifier: 'designer'},
  enriched_entity_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'image',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  required: true,
  max_file_size: null,
  allowed_extensions: [],
});

describe('akeneo > attribute > domain > model --- attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getIdentifier()).toEqual(denormalizeAttributeIdentifier('designer', 'description'));
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      denormalizeAttribute({
        identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
        enriched_entity_identifier: 'designer',
        labels: {en_US: 'My label'},
        code: 'description',
        type: 'awesome',
      });
    }).toThrow('Attribute type "awesome" is not supported');
  });

  expect(() => {
    new ConcreteImageAttribute(
      denormalizeAttributeIdentifier('designer', 'front_view'),
      createEnrichedEntityIdentifier('designer'),
      createCode('front_view'),
      createLabelCollection({en_US: 'Front View'}),
      true,
      false,
      0
    );
  }).toThrow('Attribute expect a boolean as required value');

  expect(() => {
    new ConcreteImageAttribute(
      denormalizeAttributeIdentifier('designer', 'front_view'),
      createEnrichedEntityIdentifier('designer'),
      createCode('front_view'),
      createLabelCollection({en_US: 'Front View'}),
      true,
      false
    );
  }).toThrow('Attribute expect a number as order');
});
