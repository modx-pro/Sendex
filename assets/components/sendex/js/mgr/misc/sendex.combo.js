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
		,url: MODx.modx23
			? MODx.config.connector_url
			: MODx.config.connectors_url + 'security/user.php'
		,editable: true
		,allowBlank: true
		,emptyText: _('sendex_select_user')
		,baseParams: {
			action: MODx.modx23
				? 'security/user/getlist'
				: 'getlist'
			,combo: 1
		}
		,tpl: new Ext.XTemplate(
			'<tpl for=".">\
				<div class="x-combo-list-item">\
					<sup>({id})</sup> <strong>{username}</strong><br/>{fullname}\
				</div>\
			</tpl>'
			,{compiled: true}
		)
	});
	Sendex.combo.User.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.combo.User,MODx.combo.ComboBox);
Ext.reg('sendex-combo-user',Sendex.combo.User);


Sendex.combo.UserGroup = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'group_id'
		,fieldLabel: _('sendex_subscribers')
		,hiddenName: config.name || 'group_id'
		,displayField: 'name'
		,valueField: 'id'
		,anchor: '99%'
		,fields: ['name','id','description']
		,pageSize: 20
		,url: MODx.modx23
			? MODx.config.connector_url
			: MODx.config.connectors_url + 'security/group.php'
		,editable: true
		,allowBlank: true
		,emptyText: _('sendex_select_group')
		,baseParams: {
			action: MODx.modx23
				? 'security/group/getlist'
				: 'getlist'
			,combo: 0
		}
		,tpl: new Ext.XTemplate(
			'<tpl for=".">\
				<div class="x-combo-list-item">\
					<tpl if="id"><sup>({id})</sup></tpl>\
					<strong>{name}</strong><br/>{description}\
				</div>\
			</tpl>'
			,{compiled: true}
		)
	});
	Sendex.combo.UserGroup.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.combo.UserGroup,MODx.combo.ComboBox);
Ext.reg('sendex-combo-group',Sendex.combo.UserGroup);


Sendex.combo.Newsletter = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'user_id'
		,fieldLabel: _('sendex_newsletter')
		,hiddenName: config.name || 'user_id'
		,displayField: 'name'
		,valueField: 'id'
		,anchor: '99%'
		,fields: ['id','name','description']
		,pageSize: 20
		,url: Sendex.config.connector_url
		,editable: true
		,allowBlank: true
		,emptyText: _('sendex_select_newsletter')
		,baseParams: {
			action: 'mgr/newsletter/getlist'
			,combo: 1
		}
		,tpl: new Ext.XTemplate(
			'<tpl for=".">\
				<div class="x-combo-list-item">\
					<sup>({id})</sup> <strong>{name}</strong><br/>{description}\
				</div>\
			</tpl>'
			,{compiled: true}
		)
	});
	Sendex.combo.Newsletter.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.combo.Newsletter,MODx.combo.ComboBox);
Ext.reg('sendex-combo-newsletter',Sendex.combo.Newsletter);