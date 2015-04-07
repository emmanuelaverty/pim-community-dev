'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'pim/product-manager',
        'text!pimee/template/product/panel/history/revert',
        'oro/mediator',
        'oro/navigation',
        'oro/loading-mask',
        'routing',
        'pim/dialog'
    ],
    function(
        _,
        Backbone,
        BaseForm,
        ProductManager,
        revertTemplate,
        mediator,
        Navigation,
        LoadingMask,
        Routing,
        Dialog
    ) {
        return BaseForm.extend({
            template: _.template(revertTemplate),
            render: function() {
                var $revertAction = $(this.template());
                $revertAction.on('click', _.bind(this.revert, this));

                this.getParent().addAction('revert', $revertAction);

                return this;
            },
            revert: function(event) {
                event.stopPropagation();

                Dialog.confirm(
                    _.__('pimee_enrich.confirmation.revert.product.content'),
                    _.__('pimee_enrich.confirmation.revert.product.title'),
                    _.bind(function() {
                        var navigation = Navigation.getInstance();
                        var loadingMask = new LoadingMask();
                        loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                        $.ajax(
                            Routing.generate('pimee_versioning_revert_product', {
                                id: $(event.currentTarget).parents('.product-version').data('version-id')
                            }),
                            {
                                method: 'GET'
                            }
                        ).done(
                            _.bind(function() {
                                ProductManager.get(this.getRoot().getData().meta.id).done(_.bind(function(product) {
                                    this.getRoot().setData(product);

                                    navigation.addFlashMessage(
                                        'success',
                                        _.__('pimee_enrich.entity.product.history.product_well_reverted')
                                    );
                                    navigation.afterRequest();

                                    loadingMask.hide().$el.remove();
                                    mediator.trigger('post_revert', product);
                                    mediator.trigger('post_save', product);
                                }, this));
                            }, this)
                        ).fail(
                            function(response) {
                                loadingMask.hide().$el.remove();
                                navigation.addFlashMessage('error', response.responseJSON.error);
                                navigation.afterRequest();
                            }
                        );
                    }, this)
                );
            }
        });
    }
);
