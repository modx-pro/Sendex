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