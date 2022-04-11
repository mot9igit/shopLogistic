shopLogistic.window.CreateWarehouse = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_warehouse_create'),
        width: 600,
        baseParams: {
            action: 'mgr/warehouse/create',
        },
    });
    shopLogistic.window.CreateWarehouse.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateWarehouse, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_apikey'),
            name: 'apikey',
            id: config.id + '-apikey',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_warehouse_active'),
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
                    fieldLabel: _('shoplogistic_warehouse_website'),
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
                    fieldLabel: _('shoplogistic_warehouse_city'),
                    name: 'city',
                    id: config.id + '-city',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_contact'),
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
                    fieldLabel: _('shoplogistic_warehouse_email'),
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
                    fieldLabel: _('shoplogistic_warehouse_phone'),
                    name: 'phone',
                    id: config.id + '-phone',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_file'),
            name: 'file',
            id: config.id + '-file',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_coordinats'),
            name: 'coordinats',
            id: config.id + '-coordinats',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_warehouse_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-warehouse-window-create', shopLogistic.window.CreateWarehouse);

shopLogistic.window.UpdateWarehouse = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_warehouse_update'),
            width: 600,
            action: 'mgr/warehouse/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateWarehouse.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateWarehouse, shopLogistic.window.CreateWarehouse, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_warehouse_update'),
                layout: 'form',
                items: shopLogistic.window.CreateWarehouse.prototype.getFields.call(this, config),
            }, {
                title: _('shoplogistic_warehouseusers'),
                items: [{
                    xtype: 'shoplogistic-grid-warehouseusers',
                    record: config.record,
                }]
            }]
        }];
    }

});
Ext.reg('shoplogistic-warehouse-window-update', shopLogistic.window.UpdateWarehouse);
