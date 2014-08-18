Sendex.panel.Home = function(config) {
	config = config || {};
	Ext.apply(config,{
		border: false
		,baseCls: 'modx-formpanel'
		,layout: 'anchor'
		,items: [{
			html: '<h2>'+_('sendex')+'</h2>'
			,border: false
			,cls: 'modx-page-header container'
		},{
			xtype: 'modx-tabs'
			,defaults: { border: false ,autoHeight: true }
			,border: true
			,activeItem: 0
			,hideMode: 'offsets'
			,items: [{
				title: _('sendex_newsletters')
				,items: [{
					html: _('sendex_newsletters_intro')
					,bodyCssClass: 'panel-desc'
				},{
					xtype: 'sendex-grid-newsletters'
					,cls: 'container'
					,preventRender: true
				}]
			},{
				title: _('sendex_queues')
				,items: [{
					html: _('sendex_queue_intro')
					,bodyCssClass: 'panel-desc'
				},{
					xtype: 'sendex-grid-queues'
					,cls: 'container'
					,preventRender: true
				}]
			}]
		}]
	});
	Sendex.panel.Home.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.panel.Home,MODx.Panel);
Ext.reg('sendex-panel-home',Sendex.panel.Home);
