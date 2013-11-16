Sendex.page.Home = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		components: [{
			xtype: 'sendex-panel-home'
			,renderTo: 'sendex-panel-home-div'
		}]
	}); 
	Sendex.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.page.Home,MODx.Component);
Ext.reg('sendex-page-home',Sendex.page.Home);