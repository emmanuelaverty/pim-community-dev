import CommonRows from 'akeneoreferenceentity/application/component/record/index/common';
import ActionView from 'akeneoreferenceentity/application/component/record/index/action';
import DetailsView from 'akeneoreferenceentity/application/component/record/index/detail';
import NoResult from 'akeneoreferenceentity/application/component/record/index/no-result';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {CellViews} from 'akeneoreferenceentity/application/component/reference-entity/edit/record';
import {MAX_DISPLAYED_RECORDS} from 'akeneoreferenceentity/application/action/record/search';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {getLabel} from 'pimui/js/i18n';
import {Filter} from 'akeneoreferenceentity/application/reducer/grid';
import {getFilter} from 'akeneoreferenceentity/tools/filter';
import SearchField from 'akeneoreferenceentity/application/component/record/index/search-field';

interface TableState {
  locale: string;
  channel: string;
  grid: {
    records: NormalizedRecord[];
    columns: Column[];
    total: number;
    isLoading: boolean;
    page: number;
    filters: Filter[];
  };
  cellViews: CellViews;
  recordCount: number;
  referenceEntity: ReferenceEntity;
}

export type RowView = React.SFC<{
  isLoading: boolean;
  record: NormalizedRecord;
  locale: string;
  onRedirectToRecord: (record: NormalizedRecord) => void;
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  position: number;
  columns: Column[];
  cellViews: CellViews;
}>;

interface TableDispatch {
  onRedirectToRecord: (record: NormalizedRecord) => void;
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  onNeedMoreResults: () => void;
  onSearchUpdated: (userSearch: string) => void;
}

interface TableProps extends TableState, TableDispatch {}

/**
 * This table is divided in three tables: one on the left to have sticky columns on common properties (common.tsx)
 * On the second table, you will have the additional properties of the records (details.tsx)
 * On the thrid one, you have all the actions of the record.
 */
export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  private timer: undefined | number;
  private needResize = false;
  private horizontalScrollContainer: React.RefObject<HTMLDivElement>;
  private verticalScrollContainer: React.RefObject<HTMLDivElement>;
  private detailTable: React.RefObject<HTMLTableElement>;
  private commonTable: React.RefObject<HTMLTableElement>;
  private actionTable: React.RefObject<HTMLTableElement>;

  readonly state = {
    nextItemToAddPosition: 0,
  };

  constructor(props: TableProps) {
    super(props);

    this.horizontalScrollContainer = React.createRef();
    this.verticalScrollContainer = React.createRef();
    this.detailTable = React.createRef();
    this.commonTable = React.createRef();
    this.actionTable = React.createRef();
  }

  componentDidMount() {
    const detailTable = this.detailTable.current;
    const verticalScrollContainer = this.verticalScrollContainer.current;
    if (
      null !== detailTable &&
      null !== verticalScrollContainer &&
      detailTable.offsetWidth !== verticalScrollContainer.offsetWidth
    ) {
      this.needResize = true;
      window.addEventListener('resize', this.resizeScrollContainer.bind(this));
    }
  }

  componentDidUpdate(previousProps: TableProps) {
    if (this.needResize) {
      this.resizeScrollContainer();
    }
    const horizontalScrollContainer = this.horizontalScrollContainer.current;
    if (this.props.grid.page === 0 && null !== horizontalScrollContainer) {
      horizontalScrollContainer.scrollTop = 0;
    }

    if (this.props.grid.records.length !== previousProps.grid.records.length) {
      this.setState({nextItemToAddPosition: previousProps.grid.records.length});
    }
  }

  componentDidUnMount() {
    if (this.needResize) {
      window.removeEventListener('resize', this.resizeScrollContainer.bind(this));
    }
  }

  resizeScrollContainer() {
    const verticalScrollContainer = this.verticalScrollContainer.current;
    const horizontalScrollContainer = this.horizontalScrollContainer.current;
    const commonTable = this.commonTable.current;
    const detailTable = this.detailTable.current;
    const actionTable = this.actionTable.current;
    if (
      null !== verticalScrollContainer &&
      null !== horizontalScrollContainer &&
      null !== commonTable &&
      null !== detailTable &&
      null !== actionTable
    ) {
      const newWidth = commonTable.offsetWidth + detailTable.offsetWidth + actionTable.offsetWidth;
      const minWidth = horizontalScrollContainer.offsetWidth;
      if (
        newWidth !== verticalScrollContainer.offsetWidth ||
        detailTable.offsetWidth !== verticalScrollContainer.offsetWidth
      ) {
        verticalScrollContainer.style.width = `${newWidth}px`;
        verticalScrollContainer.style.minWidth = `${minWidth}px`;
      }
    }
  }

  handleScroll() {
    const verticalScrollContainer = this.verticalScrollContainer.current;
    const horizontalScrollContainer = this.horizontalScrollContainer.current;
    if (null !== verticalScrollContainer && null !== horizontalScrollContainer) {
      const scrollSize = verticalScrollContainer.offsetHeight;
      const scrollPosition = horizontalScrollContainer.scrollTop;
      const containerSize = horizontalScrollContainer.offsetHeight;
      const remainingHeightToBottom = scrollSize - scrollPosition - containerSize;
      if (remainingHeightToBottom < 5 * containerSize) {
        this.props.onNeedMoreResults();
      }
    }
  }

  renderItems(
    records: NormalizedRecord[],
    locale: string,
    isLoading: boolean,
    onRedirectToRecord: (record: NormalizedRecord) => void,
    onDeleteRecord: (recordCode: RecordCode, label: string) => void,
    View: RowView,
    columns: Column[],
    cellViews: CellViews,
    recordCount: number
  ): JSX.Element[] {
    if (0 === records.length && isLoading) {
      const record = {
        identifier: '',
        reference_entity_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
      };

      const placeholderCount = recordCount < 30 ? recordCount : 30;

      return Array.from(Array(placeholderCount).keys()).map(key => (
        <View
          isLoading={isLoading}
          key={key}
          record={record}
          locale={locale}
          onRedirectToRecord={() => {}}
          onDeleteRecord={() => {}}
          position={key}
          columns={columns}
          cellViews={cellViews}
        />
      ));
    }

    return records.map((record: NormalizedRecord, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <View
          isLoading={false}
          key={record.identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          onDeleteRecord={onDeleteRecord}
          position={itemPosition > 0 ? itemPosition : 0}
          columns={columns}
          cellViews={cellViews}
        />
      );
    });
  }

  render(): JSX.Element | JSX.Element[] {
    const {grid, locale, channel, onRedirectToRecord, onDeleteRecord, recordCount, cellViews} = this.props;
    const columnsToDisplay = grid.columns.filter(
      (column: Column) => column.channel === channel && column.locale === locale
    );
    const userSearch = getFilter(grid.filters, 'full_text').value;
    const noResult = 0 === grid.records.length && false === grid.isLoading;
    const placeholder = 0 === grid.records.length && grid.isLoading;

    return (
      <React.Fragment>
        <SearchField value={userSearch} onChange={this.props.onSearchUpdated} />
        {noResult ? (
          <NoResult entityLabel={this.props.referenceEntity.getLabel(locale)} />
        ) : (
          <div
            className="AknDefault-horizontalScrollContainer"
            onScroll={this.handleScroll.bind(this)}
            ref={this.horizontalScrollContainer}
          >
            <div className="AknDefault-verticalScrollContainer" ref={this.verticalScrollContainer}>
              <table className="AknGrid AknGrid--light AknGrid--left" ref={this.commonTable}>
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.image')}</th>
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.label')}</th>
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.code')}</th>
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  <CommonRows
                    records={grid.records}
                    locale={locale}
                    placeholder={placeholder}
                    onRedirectToRecord={onRedirectToRecord}
                    recordCount={recordCount}
                    nextItemToAddPosition={this.state.nextItemToAddPosition}
                  />
                </tbody>
              </table>
              <table className="AknGrid AknGrid--light AknGrid--center" style={{flex: 1}} ref={this.detailTable}>
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    {0 === columnsToDisplay.length ? (
                      <th className="AknGrid-headerCell" />
                    ) : (
                      columnsToDisplay.map((column: Column) => {
                        return (
                          <th key={column.key} className="AknGrid-headerCell">
                            {getLabel(column.labels, locale, column.code)}
                          </th>
                        );
                      })
                    )}
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(
                    grid.records,
                    locale,
                    grid.isLoading,
                    onRedirectToRecord,
                    onDeleteRecord,
                    DetailsView,
                    columnsToDisplay,
                    cellViews,
                    recordCount
                  )}
                </tbody>
              </table>
              <table className="AknGrid AknGrid--light AknGrid--right" ref={this.actionTable}>
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell AknGrid-headerCell--action" />
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(
                    grid.records,
                    locale,
                    grid.isLoading,
                    onRedirectToRecord,
                    onDeleteRecord,
                    ActionView,
                    [],
                    {},
                    recordCount
                  )}
                </tbody>
              </table>
            </div>
            {grid.records.length >= MAX_DISPLAYED_RECORDS ? (
              <div className="AknDescriptionHeader AknDescriptionHeader--sticky">
                <div
                  className="AknDescriptionHeader-icon"
                  style={{backgroundImage: 'url("/bundles/pimui/images/illustrations/Product.svg")'}}
                />
                <div className="AknDescriptionHeader-title">
                  {__('pim_reference_entity.record.grid.more_result.title')}
                  <div className="AknDescriptionHeader-description">
                    {__('pim_reference_entity.record.grid.more_result.description', {total: grid.total})}
                  </div>
                </div>
              </div>
            ) : null}
          </div>
        )}
      </React.Fragment>
    );
  }
}
