shopLogistic.window.CreateStore = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_store_create'),
        width: 600,
        baseParams: {
            action: 'mgr/store/create',
        },
    });
    shopLogistic.window.CreateStore.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateStore, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_store_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        },{
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_website'),
                    name: 'website',
                    id: config.id + '-website',
                    anchor: '99%'
                },]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_city'),
                    name: 'city',
                    id: config.id + '-city',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_contact'),
            name: 'contact',
            id: config.id + '-contact',
            anchor: '99%'
        },{
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_email'),
                    name: 'email',
                    id: config.id + '-email',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_phone'),
                    name: 'phone',
                    id: config.id + '-phone',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_file'),
            name: 'file',
            id: config.id + '-file',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_coordinats'),
            name: 'coordinats',
            id: config.id + '-coordinats',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_store_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-store-window-create', shopLogistic.window.CreateStore);

shopLogistic.window.UpdateStore = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_store_update'),
            width: 600,
            action: 'mgr/store/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStore.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStore, shopLogistic.window.CreateStore, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_store_update'),
                layout: 'form',
                items: shopLogistic.window.CreateStore.prototype.getFields.call(this, config),
            }, {
                title: _('shoplogistic_storeusers'),
                items: [{
                    xtype: 'shoplogistic-grid-storeusers',
                    record: config.record,
                }]
            }]
        }];
    }

});
Ext.reg('shoplogistic-store-window-update', shopLogistic.window.UpdateStore);
