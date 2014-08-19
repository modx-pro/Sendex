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