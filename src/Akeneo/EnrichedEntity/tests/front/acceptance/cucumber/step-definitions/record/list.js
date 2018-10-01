const path = require('path');
const Sidebar = require('../../decorators/reference-entity/app/sidebar.decorator');
const Records = require('../../decorators/reference-entity/edit/records.decorator');

const {
  decorators: {createElementDecorator},
  tools: {answerJson},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Given, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Sidebar: {
      selector: '.AknColumn',
      decorator: Sidebar,
    },
    Records: {
      selector: '.AknDefault-mainContent',
      decorator: Records,
    },
  };

  const getElement = createElementDecorator(config);

  const showRecordTab = async function(page) {
    const sidebar = await await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('record');
  };

  Given('the following records for the enriched entity {string}:', async function(referenceEntityIdentifier, records) {
    const recordsSaved = records.hashes().map(normalizedRecord => {
      return {
        identifier: normalizedRecord.identifier,
        reference_entity_identifier: referenceEntityIdentifier,
        code: normalizedRecord.code,
        labels: JSON.parse(normalizedRecord.labels),
      };
    });

    this.page.on('request', request => {
      if (
        `http://pim.com/rest/reference_entity/${referenceEntityIdentifier}/record` === request.url() &&
        'GET' === request.method()
      ) {
        answerJson(request, {items: recordsSaved, total: recordsSaved.length});
      }
    });
  });

  Then('the list of records should be:', async function(expectedRecords) {
    await showRecordTab(this.page);

    const recordList = await await getElement(this.page, 'Records');
    const isValid = await expectedRecords.hashes().reduce(async (isValid, expectedRecord) => {
      return (await isValid) && (await recordList.hasRecord(expectedRecord.identifier));
    }, true);
    assert.strictEqual(isValid, true);
  });

  Then('the list of records should be empty', async function() {
    await showRecordTab(this.page);

    const records = await await getElement(this.page, 'Records');
    const isEmpty = await records.isEmpty();

    assert.strictEqual(isEmpty, true);
  });
};
