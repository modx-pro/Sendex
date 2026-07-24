Sendex.utils.renderActions = function(value, props, row) {
    var res = [];
    for (var i in row.data.actions) {
        if (!row.data.actions.hasOwnProperty(i)) {continue;}
        var a = row.data.actions[i];
        if (a['button']) {
            var cls = typeof(a['class']) == 'object' && a['class']['button']
                ? a['class']['button']
                : '';
            cls += ' ' + (MODx.modx23 ? 'icon icon-' : 'fa fa-') + a['icon'];
            res.push(
                '<li>\
                    <button class="btn btn-default '+ cls +'" type="'+a['type']+'" title="'+_('sendex_action_'+a['type'])+'"></button>\
                </li>'
            );
        }
    }

    return '<ul class="sendex-row-actions">' + res.join('') + '</ul>';
};

Sendex.utils.getMenu = function(actions, grid) {
    var menu = [];
    for (var i in actions) {
        if (!actions.hasOwnProperty(i)) {continue;}
        var a = actions[i];
        if (!a['menu']) {
            if (a == '-') {menu.push('-');}
            continue;
        }
        else if (menu.length > 0 && /^remove/i.test(a['type'])) {
            menu.push('-');
        }

        var cls = typeof(a['class']) == 'object' && a['class']['menu']
            ? a['class']['menu']
            : '';
        cls += ' ' + (MODx.modx23 ? 'icon icon-' : 'fa fa-') + a['icon'];
        menu.push({
            text: '<i class="' + cls + ' x-menu-item-icon"></i> ' + _('sendex_action_' + a['type'])
            ,handler: grid[a['type']]
        });
    }

    return menu;
};

Sendex.utils.getSearchField = function(grid, config) {
    config = config || {};
    var doSearch = function(field) {
        var store = grid.getStore();
        var value = field.getValue();
        store.baseParams.query = value || '';
        var pager = grid.getBottomToolbar();
        if (pager && pager.changePage) {
            pager.changePage(1);
        } else {
            store.reload();
        }
    };

    return Ext.apply({
        xtype: 'textfield'
        ,name: 'query'
        ,emptyText: _('sendex_search')
        ,width: 200
        ,enableKeyEvents: true
        ,listeners: {
            change: {
                fn: function(field) {
                    doSearch(field);
                }
                ,scope: grid
            }
            ,specialkey: {
                fn: function(field, e) {
                    if (e.getKey() === Ext.EventObject.ENTER) {
                        doSearch(field);
                    }
                }
                ,scope: grid
            }
        }
    }, config);
};

Sendex.utils.onAjax = function(el) {
    Ext.Ajax.el = el;
    Ext.Ajax.on('beforerequest', Sendex.utils.beforerequest);
    Ext.Ajax.on('requestcomplete', Sendex.utils.requestcomplete);
};

Sendex.utils.beforerequest = function() {Ext.Ajax.el.mask(_('loading'),'x-mask-loading');};
Sendex.utils.requestcomplete = function() {
    Ext.Ajax.el.unmask();
    Ext.Ajax.el = null;
    Ext.Ajax.un('beforerequest', Sendex.utils.beforerequest);
    Ext.Ajax.un('requestcomplete', Sendex.utils.requestcomplete);
};
