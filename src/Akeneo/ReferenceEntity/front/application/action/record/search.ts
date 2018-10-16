import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import updateResultsWithFetcher from 'akeneoreferenceentity/application/action/search';
import {updateFilter} from 'akeneoreferenceentity/application/event/search';

const stateToQuery = async (state: EditState): Promise<Query> => {
  return {
    locale: undefined === state.user.catalogLocale ? '' : state.user.catalogLocale,
    channel: undefined === state.user.catalogChannel ? '' : state.user.catalogChannel,
    size: state.grid.query.size,
    page: state.grid.query.page,
    filters: [
      ...state.grid.query.filters,
      {
        field: 'reference_entity',
        operator: '=',
        value: state.form.data.identifier,
        context: {},
      },
    ],
  };
};

export const needMoreResults = () => (dispatch: any, getState: any) => {
  if (
    !getState().grid.isFetching &&
    getState().grid.items.length < 500 &&
    getState().grid.items.length < getState().grid.total
  ) {
    dispatch(updateRecordResults(true));
  }
};

export const searchUpdated = (searchInput: string) => (dispatch: any) => {
  dispatch(updateFilter('search', '=', searchInput));
  dispatch(updateRecordResults(false));
};

export const updateRecordResults = updateResultsWithFetcher<NormalizedRecord>(recordFetcher, stateToQuery);
