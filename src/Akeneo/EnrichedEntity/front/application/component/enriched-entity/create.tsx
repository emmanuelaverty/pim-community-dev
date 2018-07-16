import * as React from 'react';
import {connect} from 'react-redux';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import {
  enrichedEntityCreationCodeUpdated,
  enrichedEntityCreationLabelUpdated,
  enrichedEntityCreationCancel
} from 'akeneoenrichedentity/domain/event/enriched-entity/create';
import {createEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/create';

interface StateProps {
  context: {
    locale: string;
  };
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
  };
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onCodeUpdated: (value: string) => void
    onLabelUpdated: (value: string, locale: string) => void
    onCancel: () => void
    onSubmit: (identifier: string, labels: { [localeCode: string]: string }) => void
  }
}

interface CreateProps extends StateProps, DispatchProps {}

class Create extends React.Component<CreateProps> {
  public props: CreateProps;

  constructor(props: CreateProps) {
    super(props);
  }

  private onCodeUpdate = (event: any) => {
    this.props.events.onCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: any) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onCancel = () => {
    this.props.events.onCancel();
  };

  private onSubmit = () => {
    this.props.events.onSubmit(this.props.data.code, this.props.data.labels);
  };

  private getCodeValidationErrorsMessages = () => {
    let errors = this.props.errors.filter((error: ValidationError) => {
      return 'identifier' == error.propertyPath;
    });

    let errorMessages = errors.map((error: ValidationError, key:number) => {
      return <span className="error-message" key={key}>{__(error.messageTemplate, error.parameters)}</span>;
    });

    if (errorMessages.length > 0) {
      return (
          <div className="AknFieldContainer-footer AknFieldContainer-validationErrors validation-errors">
                        <span className="AknFieldContainer-validationError">
                          <i className="icon-warning-sign"></i>
                          {errorMessages}
                        </span>
          </div>
      );
    }

    return null;
  };

  render(): JSX.Element | JSX.Element[] | null {
    const errorContainer: JSX.Element | null = this.getCodeValidationErrorsMessages();

    return (
        <div className="modal in modal--fullPage" aria-hidden="false" style={{zIndex: 1041}}>
          <div className="modal-header">
            <a className="close">×</a>
            <h3>Create</h3>
          </div>
          <div className="modal-body  creation">
            <div className="AknFullPage AknFullPage--modal">
              <div className="AknFullPage-content">
                <div className="AknFullPage-left">
                  <img src="bundles/pimui/images/illustrations/Product.svg" className="AknFullPage-image"/>
                </div>
                <div className="AknFullPage-right">
                  <div
                      className="AknFullPage-subTitle">{__('pim_enriched_entity.enriched_entity.create.subtitle')}</div>
                  <div className="AknFullPage-title">{__('pim_enriched_entity.enriched_entity.create.title')}</div>
                  <div data-drop-zone="fields">
                    <div className="AknFieldContainer" data-code="label">
                      <div className="AknFieldContainer-header">
                        <label title="Code" className="AknFieldContainer-label control-label required truncate"
                               htmlFor="creation_label">{__('pim_enriched_entity.enriched_entity.create.input.label')}</label>
                      </div>
                      <div className="AknFieldContainer-inputContainer field-input">
                        <input type="text" className="AknTextField" id="creation_label" name="label"
                               value={this.props.data.labels[this.props.context.locale]}
                               onChange={this.onLabelUpdate} />
                        <Flag locale={this.props.context.locale} displayLanguage={false}/>
                      </div>
                    </div>
                    <div className="AknFieldContainer" data-code="code">
                      <div className="AknFieldContainer-header">
                        <label title="Code" className="AknFieldContainer-label control-label required truncate"
                               htmlFor="creation_code">{__('pim_enriched_entity.enriched_entity.create.input.code')}</label>
                      </div>
                      <div className="AknFieldContainer-inputContainer field-input">
                        <input type="text" className="AknTextField" id="creation_code" name="code"
                               value={this.props.data.code}
                               onChange={this.onCodeUpdate} />
                      </div>
                      {errorContainer}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div className="AknButtonList AknButtonList--right modal-footer">
            <span title="Cancel"
                  className="AknButtonList-item AknButton AknButton--grey cancel icons-holder-text"
                  onClick={this.onCancel}
            >{__('pim_enriched_entity.enriched_entity.create.cancel')}</span>
            <a title="Save"
               className="AknButtonList-item AknButton AknButton--apply ok icons-holder-text" onClick={this.onSubmit}>{__('pim_enriched_entity.enriched_entity.create.save')}</a>
          </div>
        </div>
    );
  };
}

export default connect((state: State): StateProps => {
  const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

  return {
    data: state.create.data,
    errors: state.create.errors,
    context: {
      locale: locale
    }
  } as StateProps;
}, (dispatch: any): DispatchProps => {
  return {
    events: {
      onCodeUpdated: (value: string) => {
        dispatch(enrichedEntityCreationCodeUpdated(value));
      },
      onLabelUpdated: (value: string, locale: string) => {
        dispatch(enrichedEntityCreationLabelUpdated(value, locale));
      },
      onCancel: () => {
        dispatch(enrichedEntityCreationCancel());
      },
      onSubmit: (identifier: string, labels: { [localeCode: string]: string }) => {
        dispatch(createEnrichedEntity(identifier, labels));
      }
    }
  } as DispatchProps
})(Create);
