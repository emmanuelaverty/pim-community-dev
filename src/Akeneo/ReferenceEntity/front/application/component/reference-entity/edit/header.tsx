import * as React from 'react';
import BreadCrumb, {BreadcrumbConfiguration} from 'akeneoreferenceentity/application/component/app/breadcrumb';
import __ from 'akeneoreferenceentity/tools/translator';
import EditState from 'akeneoreferenceentity/application/component/app/edit-state';
import Image from 'akeneoreferenceentity/application/component/app/image';
import {connect} from 'react-redux';
import LocaleSwitcher from 'akeneoreferenceentity/application/component/app/locale-switcher';
import PimView from 'akeneoreferenceentity/infrastructure/component/pim-view';
import File from 'akeneoreferenceentity/domain/model/file';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import {EditState as State} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {catalogLocaleChanged} from 'akeneoreferenceentity/domain/event/user';

interface OwnProps {
  label: string;
  image: File;
  primaryAction: () => JSX.Element | null;
  secondaryActions: () => JSX.Element | null;
  withLocaleSwitcher: boolean;
  withChannelSwitcher: boolean;
  isDirty: boolean;
  breadcrumbConfiguration: BreadcrumbConfiguration;
}

interface StateProps extends OwnProps {
  context: {
    locale: string;
  };
  structure: {
    locales: Locale[];
  };
}

interface DispatchProps {
  events: {
    onLocaleChanged: (locale: Locale) => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

const Header = ({
  label,
  image,
  secondaryActions,
  primaryAction,
  isDirty,
  breadcrumbConfiguration,
  context,
  structure,
  events,
}: EditProps) => {
  return (
    <header className="AknTitleContainer">
      <div className="AknTitleContainer-line">
        <Image alt={__('pim_reference_entity.reference_entity.img', {'{{ label }}': label})} image={image} />
        <div className="AknTitleContainer-mainContainer">
          <div>
            <div className="AknTitleContainer-line">
              <div className="AknTitleContainer-breadcrumbs">
                <BreadCrumb items={breadcrumbConfiguration} />
              </div>
              <div className="AknTitleContainer-buttonsContainer">
                <div className="user-menu">
                  <PimView
                    className="AknTitleContainer-userMenu"
                    viewName="pim-reference-entity-index-user-navigation"
                  />
                </div>
                <div className="AknButtonList">
                  {secondaryActions()}
                  <div className="AknTitleContainer-rightButton">{primaryAction()}</div>
                </div>
              </div>
            </div>
            <div className="AknTitleContainer-line">
              <div className="AknTitleContainer-title">{label}</div>
              {isDirty ? <EditState /> : null}
            </div>
          </div>
          <div>
            <div className="AknTitleContainer-line">
              <div className="AknTitleContainer-context AknButtonList">
                <LocaleSwitcher
                  localeCode={context.locale}
                  locales={structure.locales}
                  onLocaleChange={events.onLocaleChanged}
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default connect(
  (state: State, ownProps: OwnProps): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      ...ownProps,
      context: {
        locale,
      },
      structure: {
        locales: state.structure.locales,
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onLocaleChanged: (locale: Locale) => {
          dispatch(catalogLocaleChanged(locale.code));
        },
      },
    };
  }
)(Header);