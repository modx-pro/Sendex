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


Sendex.combo.User = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'user_id'
		,fieldLabel: _('sendex_subscriber')
		,hiddenName: config.name || 'user_id'
		,displayField: 'username'
		,valueField: 'id'
		,anchor: '99%'
		,fields: ['username','id','fullname']
		,pageSize: 20
		,url: MODx.config.connectors_url + 'security/user.php'
		,editable: true
		,allowBlank: true
		,emptyText: _('sendex_select_user')
		,baseParams: {
			action: 'getlist'
			,combo: 1
		}
		,tpl: new Ext.XTemplate(''
			+'<tpl for="."><div class="sendex-list-item">'
			+'<span><small>({id})</small> <b>{username}</b> ({fullname})</span>'
			+'</div></tpl>',{
			compiled: true
		})
		,itemSelector: 'div.sendex-list-item'
	});
	Sendex.combo.User.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.combo.User,MODx.combo.ComboBox);
Ext.reg('sendex-combo-user',Sendex.combo.User);