Sendex.combo.Snippet = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'snippet'
		,hiddenName: 'snippet'
		,displayField: 'name'
		,valueField: 'id'
		,fields: ['id','name']
		,pageSize: 10
		,hideMode: 'offsets'
		,url: MODx.config.connectors_url + 'element/snippet.php'
		,baseParams: {
			action: 'getlist'
		}
	});
	Sendex.combo.Snippet.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.combo.Snippet,MODx.combo.ComboBox);
Ext.reg('sendex-combo-snippet',Sendex.combo.Snippet);