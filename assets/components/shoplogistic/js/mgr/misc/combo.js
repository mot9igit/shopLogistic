shopLogistic.combo.ComboBoxDefault = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        assertValue : function () {
            var val = this.getRawValue(),
                rec;
            if (this.valueField && Ext.isDefined(this.value)) {
                rec = this.findRecord(this.valueField, this.value);
            }
            /* fix for https://github.com/bezumkin/miniShop2/pull/350
            if(!rec || rec.get(this.displayField) != val){
                rec = this.findRecord(this.displayField, val);
            }*/
            if (rec && rec.get(this.displayField) != val) {
                rec = null;
            }
            if (!rec && this.forceSelection) {
                if (val.length > 0 && val != this.emptyText) {
                    this.el.dom.value = Ext.value(this.lastSelectionText, '');
                    this.applyEmptyText();
                } else {
                    this.clearValue();
                }
            } else {
                if (rec && this.valueField) {
                    if (this.value == val) {
                        return;
                    }
                    val = rec.get(this.valueField || this.displayField);
                }
                this.setValue(val);
            }
        },

    });
    shopLogistic.combo.ComboBoxDefault.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.ComboBoxDefault, MODx.combo.ComboBox);
Ext.reg('shoplogistic-combo-combobox-default', shopLogistic.combo.ComboBoxDefault);

shopLogistic.combo.User = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'user',
        fieldLabel: config.name || 'createdby',
        hiddenName: config.name || 'createdby',
        displayField: 'fullname',
        valueField: 'id',
        anchor: '99%',
        fields: ['username', 'id', 'fullname'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/system/user/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{username}</b>\
                        <tpl if="fullname && fullname != username"> - {fullname}</tpl>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.User.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.User, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-user', shopLogistic.combo.User);

shopLogistic.combo.Store = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'store_id',
        fieldLabel: config.name || 'store_id',
        hiddenName: config.name || 'store_id',
        displayField: 'name',
        valueField: 'id',
        anchor: '99%',
        fields: ['id', 'name'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/store/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Store.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Store, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-store', shopLogistic.combo.Store);

shopLogistic.combo.Warehouse = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'warehouse_id',
        fieldLabel: config.name || 'warehouse_id',
        hiddenName: config.name || 'warehouse_id',
        displayField: 'name',
        valueField: 'id',
        anchor: '99%',
        fields: ['id', 'name'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: shopLogistic.config['connector_url'],
        baseParams: {
            action: 'mgr/warehouse/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate(
            '\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    shopLogistic.combo.Warehouse.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.combo.Warehouse, shopLogistic.combo.ComboBoxDefault);
Ext.reg('shoplogistic-combo-warehouse', shopLogistic.combo.Warehouse);

shopLogistic.combo.Search = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        xtype: 'twintrigger',
        ctCls: 'x-field-search',
        allowBlank: true,
        msgTarget: 'under',
        emptyText: _('search'),
        name: 'query',
        triggerAction: 'all',
        clearBtnCls: 'x-field-search-clear',
        searchBtnCls: 'x-field-search-go',
        onTrigger1Click: this._triggerSearch,
        onTrigger2Click: this._triggerClear,
    });
    shopLogistic.combo.Search.superclass.constructor.call(this, config);
    this.on('render', function () {
        this.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
            this._triggerSearch();
        }, this);
    });
    this.addEvents('clear', 'search');
};
Ext.extend(shopLogistic.combo.Search, Ext.form.TwinTriggerField, {

    initComponent: function () {
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);
        this.triggerConfig = {
            tag: 'span',
            cls: 'x-field-search-btns',
            cn: [
                {tag: 'div', cls: 'x-form-trigger ' + this.searchBtnCls},
                {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls}
            ]
        };
    },

    _triggerSearch: function () {
        this.fireEvent('search', this);
    },

    _triggerClear: function () {
        this.fireEvent('clear', this);
    },

});
Ext.reg('shoplogistic-combo-search', shopLogistic.combo.Search);
Ext.reg('shoplogistic-field-search', shopLogistic.combo.Search);