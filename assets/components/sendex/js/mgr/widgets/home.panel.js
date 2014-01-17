Sendex.panel.Home = function(config) {
	config = config || {};
	Ext.apply(config,{
		border: false
		,baseCls: 'modx-formpanel'
		,items: [{
			html: '<h2>'+_('sendex')+'</h2>'
			,border: false
			,cls: 'modx-page-header container'
		},{
			xtype: 'modx-tabs'
			,bodyStyle: 'padding: 10px'
			,defaults: { border: false ,autoHeight: true }
			,border: true
			,activeItem: 0
			,hideMode: 'offsets'
			,items: [{
				title: _('sendex_newsletters')
				,items: [{
					html: _('sendex_newsletters_intro')
					,border: false
					,bodyCssClass: 'panel-desc'
					,bodyStyle: 'margin-bottom: 10px'
				},{
					xtype: 'sendex-grid-newsletters'
					,preventRender: true
				}]
			},{
				title: _('sendex_queues')
				,items: [{
					html: _('sendex_queue_intro')
					,border: false
					,bodyCssClass: 'panel-desc'
					,bodyStyle: 'margin-bottom: 10px'
				},{
					xtype: 'sendex-grid-queues'
					,preventRender: true
				}]
			}]
		}]
	});
	Sendex.panel.Home.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.panel.Home,MODx.Panel);
Ext.reg('sendex-panel-home',Sendex.panel.Home);

// Functions
/******************************************************/
function renderGridImage(img, height) {
	if (height == '') {height = 50;}
	if (img.length > 0) {
		if (!/(jpg|jpeg|png|gif|bmp)$/.test(img)) {return img;}
		else if (/^(http|https)/.test(img)) {return '<img src="'+img+'" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
		else {return '<img src="'+MODx.config.connectors_url+'system/phpthumb.php?&src='+img+'&wctx=web&h='+height+'&zc=0&source=0" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
	}
	else {return '';}
}
