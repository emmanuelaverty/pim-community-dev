'use strict';
/**
 * Save extension that adds a save draft button if ownership rights are not granted
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product-edit-form/save'
    ],
    function ($, _, Save) {
        return Save.extend({
            render: function () {
                var isOwner = this.getFormData().meta.is_owner;

                if (!isOwner) {
                    this.updateSuccessMessage = _.__('pimee_enrich.entity.product_draft.flash.update.success');
                    this.updateFailureMessage = _.__('pimee_enrich.entity.product_draft.flash.update.fail');

                    if (this.parent.getExtension('save-buttons')) {
                        var buttons = this.parent.getExtension('save-buttons').model.get('buttons');
                        var saveButton = _.findWhere(buttons, {className: 'save'});
                        if (saveButton) {
                            saveButton.label = _.__('pimee_enrich.entity.product_draft.module.create.label');
                        }
                    }
                }

                return Save.prototype.render.apply(this, arguments);
            }
        });
    }
);