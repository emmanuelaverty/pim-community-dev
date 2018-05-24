import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoenrichedentity/tools/translator';
import Table from 'akeneoenrichedentity/application/component/enriched-entity/index/table';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';

interface State {
  context: {
    locale: string;
  };

  grid: {
    enrichedEntities: EnrichedEntity[];
    total: number;
  }
}

const enrichedEntityView = ({ grid, context }: State) => (
  <div className="AknDefault-contentWithColumn">
    <div className="AknDefault-thirdColumnContainer">
      <div className="AknDefault-thirdColumn"></div>
    </div>
    <div className="AknDefault-contentWithBottom">
      <div className="AknDefault-mainContent">
        <header className="AknTitleContainer">
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-mainContainer">
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-breadcrumbs">
                  <div className="AknBreadcrumb">
                    <a href="#" className="AknBreadcrumb-item AknBreadcrumb-item--routable breadcrumb-tab" data-code="pim-menu-entities">
                      {__('pim_enriched_entity.enriched_entity.title')}
                    </a>
                  </div>
                </div>
                <div className="AknTitleContainer-buttonsContainer">
                  <div className="AknTitleContainer-userMenu">
                    <PimView viewName="pim-enriched-entity-index-user-navigation"/>
                  </div>
                </div>
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-title">
                  {__('pim_enriched_entity.enriched_entity.index.grid.count', {count: grid.enrichedEntities.length})}
                </div>
                <div className="AknTitleContainer-state"></div>
              </div>
            </div>
            <div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-context AknButtonList"></div>
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-meta AknButtonList"></div>
              </div>
            </div>
          </div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-navigation"></div>
          </div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-search"></div>
          </div>
        </header>
        <div className="AknGrid--gallery">
          <div className="AknGridContainer AknGridContainer--withCheckbox">
            <Table
              onRedirectToEnrichedEntity={() => {}}
              locale={context.locale}
              enrichedEntities={grid.enrichedEntities}
            />
          </div>
        </div>
      </div>
    </div>
  </div>
);

export default connect((state: any): State => {
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;
  const enrichedEntities = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
  const total = undefined === state.grid || undefined === state.grid.total ? 0 : state.grid.total;

  return {
    context: {
      locale
    },
    grid: {
      enrichedEntities,
      total
    }
  }
})(enrichedEntityView);
