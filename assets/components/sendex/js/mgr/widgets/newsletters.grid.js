Sendex.grid.Newsletters = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'sendex-grid-newsletters'
		,url: Sendex.config.connector_url
		,baseParams: {
			action: 'mgr/newsletter/getlist'
		}
		,fields: ['id','name','description','active','template','snippet','image','email_subject','email_from','email_from_name','email_reply']
		,autoHeight: true
		,paging: true
		,remoteSort: true
		,columns: [
			{header: _('sendex_newsletter_id'),dataIndex: 'id',width: 50}
			,{header: _('sendex_newsletter_name'),dataIndex: 'name',width: 100}
			//,{header: _('sendex_newsletter_description'),dataIndex: 'description',width: 250}
			,{header: _('sendex_newsletter_active'),dataIndex: 'active',width: 75,renderer: this.renderBoolean}
			,{header: _('sendex_newsletter_template'),dataIndex: 'template',width: 75}
			,{header: _('sendex_newsletter_snippet'),dataIndex: 'snippet',width: 75}
			,{header: _('sendex_newsletter_email_subject'),dataIndex: 'description',width: 100}
			,{header: _('sendex_newsletter_email_from'),dataIndex: 'email_from',width: 100}
			//,{header: _('sendex_newsletter_email_from_name'),dataIndex: 'email_from_name',width: 100}
			//,{header: _('sendex_newsletter_email_reply'),dataIndex: 'email_reply',width: 100}
			,{header: _('sendex_newsletter_image'),dataIndex: 'image',width: 75,renderer: this.renderImage}
		]
		,tbar: [{
			text: _('sendex_btn_create')
			,handler: this.createItem
			,scope: this
		}]
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updateItem(grid, e, row);
			}
		}
	});
	Sendex.grid.Newsletters.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.grid.Newsletters,MODx.grid.Grid,{
	windows: {}

	,getMenu: function() {
		var m = [];
		m.push({
			text: _('sendex_newsletter_update')
			,handler: this.updateItem
		});
		m.push('-');
		m.push({
			text: _('sendex_newsletter_remove')
			,handler: this.removeItem
		});
		this.addContextMenuItem(m);
	}

	,renderBoolean: function(val,cell,row) {
		return val == '' || val == 0
			? '<span style="color:red">' + _('no') + '<span>'
			: '<span style="color:green">' + _('yes') + '<span>';
	}

	,renderImage: function(val,cell,row) {
		return val != ''
			? '<img src="' + val + '" alt="" height="50" />'
			: '';
	}

	,createItem: function(btn,e) {
		if (!this.windows.createItem) {
			this.windows.createItem = MODx.load({
				xtype: 'sendex-window-newsletter-create'
				,listeners: {
					'success': {fn:function() { this.refresh(); },scope:this}
				}
			});
		}
		this.windows.createItem.fp.getForm().reset();
		this.windows.createItem.show(e.target);
	}

	,updateItem: function(btn,e,row) {
		if (typeof(row) != 'undefined') {this.menu.record = row.data;}
		var id = this.menu.record.id;

		MODx.Ajax.request({
			url: Sendex.config.connector_url
			,params: {
				action: 'mgr/newsletter/get'
				,id: id
			}
			,listeners: {
				success: {fn:function(r) {
					if (!this.windows.updateItem) {
						this.windows.updateItem = MODx.load({
							xtype: 'sendex-window-newsletter-update'
							,record: r
							,listeners: {
								'success': {fn:function() { this.refresh(); },scope:this}
							}
						});
					}
					this.windows.updateItem.fp.getForm().reset();
					this.windows.updateItem.fp.getForm().setValues(r.object);
					this.windows.updateItem.show(e.target);
				},scope:this}
			}
		});
	}

	,removeItem: function(btn,e) {
		if (!this.menu.record) return false;
		
		MODx.msg.confirm({
			title: _('sendex_newsletter_remove')
			,text: _('sendex_newsletter_remove_confirm')
			,url: this.config.url
			,params: {
				action: 'mgr/newsletter/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:function(r) { this.refresh(); },scope:this}
			}
		});
	}
});
Ext.reg('sendex-grid-newsletters',Sendex.grid.Newsletters);




Sendex.window.CreateItem = function(config) {
	config = config || {};
	this.ident = config.ident || 'mecnewsletter'+Ext.id();
	Ext.applyIf(config,{
		title: _('sendex_newsletter_create')
		,id: this.ident
		,height: 200
		,width: 475
		,url: Sendex.config.connector_url
		,action: 'mgr/newsletter/create'
		,fields: [
			{xtype: 'textfield',fieldLabel: _('name'),name: 'name',id: 'sendex-'+this.ident+'-name',anchor: '99%'}
			,{xtype: 'textarea',fieldLabel: _('description'),name: 'description',id: 'sendex-'+this.ident+'-description',height: 150,anchor: '99%'}
		]
		,keys: [{key: Ext.EventObject.ENTER,shift: true,fn: function() {this.submit() },scope: this}]
	});
	Sendex.window.CreateItem.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.window.CreateItem,MODx.Window);
Ext.reg('sendex-window-newsletter-create',Sendex.window.CreateItem);


Sendex.window.UpdateItem = function(config) {
	config = config || {};
	this.ident = config.ident || 'meunewsletter'+Ext.id();
	Ext.applyIf(config,{
		title: _('sendex_newsletter_update')
		,id: this.ident
		,height: 200
		,width: 475
		,url: Sendex.config.connector_url
		,action: 'mgr/newsletter/update'
		,fields: [
			{xtype: 'hidden',name: 'id',id: 'sendex-'+this.ident+'-id'}
			,{xtype: 'textfield',fieldLabel: _('name'),name: 'name',id: 'sendex-'+this.ident+'-name',anchor: '99%'}
			,{xtype: 'textarea',fieldLabel: _('description'),name: 'description',id: 'sendex-'+this.ident+'-description',height: 150,anchor: '99%'}
		]
		,keys: [{key: Ext.EventObject.ENTER,shift: true,fn: function() {this.submit() },scope: this}]
	});
	Sendex.window.UpdateItem.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.window.UpdateItem,MODx.Window);
Ext.reg('sendex-window-newsletter-update',Sendex.window.UpdateItem);