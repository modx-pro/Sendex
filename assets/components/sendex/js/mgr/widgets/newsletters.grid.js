Sendex.grid.Newsletters = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'sendex-grid-newsletters'
		,url: Sendex.config.connector_url
		,baseParams: {
			action: 'mgr/newsletter/getlist'
		}
		,fields: ['id','name','description','active','template','templatename','image','email_subject','email_from','email_from_name','email_reply']
		,autoHeight: true
		,paging: true
		,remoteSort: true
		,columns: [
			{header: _('sendex_newsletter_id'), sortable: true, dataIndex: 'id',width: 50}
			,{header: _('sendex_newsletter_name'), sortable: true, dataIndex: 'name',width: 100}
			,{header: _('sendex_newsletter_active'), sortable: true, dataIndex: 'active',width: 75,renderer: this._renderBoolean}
			,{header: _('sendex_newsletter_template'), sortable: true, dataIndex: 'template',width: 75,renderer: this._renderTemplate}
			,{header: _('sendex_newsletter_email_subject'), sortable: true, dataIndex: 'email_subject',width: 100}
			,{header: _('sendex_newsletter_email_from'), sortable: true, dataIndex: 'email_from',width: 100}
			,{header: _('sendex_newsletter_email_from_name'), sortable: true, dataIndex: 'email_from_name',width: 100, hidden: true}
			,{header: _('sendex_newsletter_email_reply'), sortable: true, dataIndex: 'email_reply',width: 100, hidden: true}
			,{header: _('sendex_newsletter_image'), dataIndex: 'image',width: 75,renderer: this._renderImage, id: 'image'}
		]
		,tbar: [{
			text: _('sendex_btn_create')
			,handler: this.createNewsletter
			,scope: this
		}]
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updateNewsletter(grid, e, row);
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
			,handler: this.updateNewsletter
		});
		m.push('-');
		m.push({
			text: _('sendex_newsletter_remove')
			,handler: this.removeNewsletter
		});
		this.addContextMenuItem(m);
	}

	,_renderBoolean: function(val,cell,row) {
		return val == '' || val == 0
			? '<span style="color:red">' + _('no') + '<span>'
			: '<span style="color:green">' + _('yes') + '<span>';
	}

	,_renderImage: function(val,cell,row) {
		if (!val) {return '';}
		else if (val.substr(0,1) != '/') {
			val = '/' + val;
		}

		return '<img src="' + val + '" alt="" height="50" />';
	}

	,_renderTemplate: function(val,cell,row) {
		if (!val) {return '';}
		else if (row.data['templatename']) {
			val = '<sup>(' + val + ')</sup> ' + row.data['templatename'];
		}
		return val;
	}

	,createNewsletter: function(btn,e) {
		if (!this.windows.createNewsletter) {
			this.windows.createNewsletter = MODx.load({
				xtype: 'sendex-window-newsletter-create'
				,listeners: {
					'success': {fn:function() { this.refresh(); },scope:this}
				}
			});
		}
		this.windows.createNewsletter.fp.getForm().reset();
		this.windows.createNewsletter.show(e.target);
	}

	,updateNewsletter: function(btn,e,row) {
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
					if (this.windows.updateNewsletter) {
						this.windows.updateNewsletter.close();
						this.windows.updateNewsletter.destroy();
					}
					this.windows.updateNewsletter = MODx.load({
						xtype: 'sendex-window-newsletter-update'
						,record: r
						,listeners: {
							success: {fn:function() { this.refresh(); },scope:this}
						}
					});
					this.windows.updateNewsletter.fp.getForm().reset();
					this.windows.updateNewsletter.fp.getForm().setValues(r.object);
					this.windows.updateNewsletter.show(e.target);
				},scope:this}
			}
		});
	}

	,removeNewsletter: function(btn,e) {
		if (!this.menu.record) return;
		
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




Sendex.window.CreateNewsletter = function(config) {
	config = config || {};
	this.ident = config.ident || 'mecnewsletter'+Ext.id();
	Ext.applyIf(config,{
		title: _('sendex_newsletter_create')
		,id: this.ident
		,autoHeight: true
		,width: 650
		,url: Sendex.config.connector_url
		,action: 'mgr/newsletter/create'
		,fields: [
			{xtype: 'textfield',fieldLabel: _('sendex_newsletter_name'),name: 'name',id: 'sendex-'+this.ident+'-name',anchor: '100%'}
			,{xtype: 'modx-combo-template',fieldLabel: _('sendex_newsletter_template'),name: 'template',id: 'sendex-'+this.ident+'-template',anchor: '100%'}
			,{
				layout:'column'
				,border: false
				,anchor: '100%'
				,items: [{
					columnWidth: .5
					,layout: 'form'
					,defaults: { msgTarget: 'under' }
					,border:false
					,style: {margin: '0 10px 0 0'}
					,items: [
						{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_subject'),name: 'email_subject',id: 'sendex-'+this.ident+'-email_subject',anchor: '100%'}
						,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_reply'),name: 'email_reply',id: 'sendex-'+this.ident+'-email_reply',anchor: '100%'}
						,{xtype: 'combo-boolean',fieldLabel: _('sendex_newsletter_active'),name: 'active',hiddenName: 'active',id: 'sendex-'+this.ident+'-active',anchor: '50%'}
					]
				},{
					columnWidth: .5
					,layout: 'form'
					,defaults: { msgTarget: 'under' }
					,border:false
					,style: {margin: 0}
					,items: [
						{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from'),name: 'email_from',id: 'sendex-'+this.ident+'-email_from',anchor: '100%'}
						,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from_name'),name: 'email_from_name',id: 'sendex-'+this.ident+'-email_from_name',anchor: '100%'}
						,{xtype: 'modx-combo-browser',fieldLabel: _('sendex_newsletter_image'),name: 'image',id: 'sendex-'+this.ident+'-image',anchor: '100%'}
					]
				}]
			}
			,{xtype: 'textarea',fieldLabel: _('sendex_newsletter_description'),name: 'description',id: 'sendex-'+this.ident+'-description',height: 75,anchor: '100%'}
		]
		,keys: [{key: Ext.EventObject.ENTER,shift: true,fn: function() {this.submit() },scope: this}]
	});
	Sendex.window.CreateNewsletter.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.window.CreateNewsletter,MODx.Window);
Ext.reg('sendex-window-newsletter-create',Sendex.window.CreateNewsletter);


Sendex.window.UpdateNewsletter = function(config) {
	config = config || {};
	this.ident = config.ident || 'meunewsletter'+Ext.id();
	Ext.applyIf(config,{
		title: _('sendex_newsletter_update')
		,id: this.ident
		,autoHeight: true
		,width: 650
		,url: Sendex.config.connector_url
		,action: 'mgr/newsletter/update'
		,fields: {
			xtype: 'modx-tabs'
			,stateful: true
			,stateId: 'sendex-window-newsletter-update'
			,stateEvents: ['tabchange']
			,getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};}
			,deferredRender: false
			,border: true
			,items: [{
				title: _('sendex_newsletter')
				,hideMode: 'offsets'
				,layout: 'form'
				,border: true
				,cls: MODx.modx23 ? '' : 'main-wrapper'
				,items: [
					{xtype: 'hidden',name: 'id',id: 'sendex-'+this.ident+'-id'}
					,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_name'),name: 'name',id: 'sendex-'+this.ident+'-name',anchor: '100%'}
					,{xtype: 'modx-combo-template',editable:true,fieldLabel: _('sendex_newsletter_template'),name: 'template',id: 'sendex-'+this.ident+'-template',anchor: '100%'}
					,{
						layout:'column'
						,border: false
						,anchor: '100%'
						,items: [{
							columnWidth: .5
							,layout: 'form'
							,defaults: { msgTarget: 'under' }
							,border:false
							,style: {margin: '0 10px 0 0'}
							,items: [
								{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_subject'),name: 'email_subject',id: 'sendex-'+this.ident+'-email_subject',anchor: '100%'}
								,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_reply'),name: 'email_reply',id: 'sendex-'+this.ident+'-email_reply',anchor: '100%'}
								,{xtype: 'combo-boolean',fieldLabel: _('sendex_newsletter_active'),name: 'active',hiddenName: 'active',id: 'sendex-'+this.ident+'-active',anchor: '50%'}
							]
						},{
							columnWidth: .5
							,layout: 'form'
							,defaults: { msgTarget: 'under' }
							,border:false
							,style: {margin: 0}
							,items: [
								{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from'),name: 'email_from',id: 'sendex-'+this.ident+'-email_from',anchor: '100%'}
								,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from_name'),name: 'email_from_name',id: 'sendex-'+this.ident+'-email_from_name',anchor: '100%'}
								,{xtype: 'modx-combo-browser',fieldLabel: _('sendex_newsletter_image'),name: 'image',id: 'sendex-'+this.ident+'-image',anchor: '100%'}
							]
						}]
					}
					,{xtype: 'textarea',fieldLabel: _('sendex_newsletter_description'),name: 'description',id: 'sendex-'+this.ident+'-description',height: 75,anchor: '100%'}
				]
			},{
				title: _('sendex_subscribers')
				,xtype: 'sendex-grid-newsletter-subscribers'
				,cls: MODx.modx23 ? '' : 'main-wrapper'
				,record: config.record.object
				,pageSize: 5
			}]
		}
		,keys: [{key: Ext.EventObject.ENTER,shift: true,fn: function() {this.submit() },scope: this}]
	});
	Sendex.window.UpdateNewsletter.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.window.UpdateNewsletter,MODx.Window);
Ext.reg('sendex-window-newsletter-update',Sendex.window.UpdateNewsletter);



Sendex.grid.NewsletterSubscribers = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'sendex-grid-newsletter-subscribers'
		,url: Sendex.config.connector_url
		,baseParams: {
			action: 'mgr/newsletter/subscriber/getlist'
			,newsletter_id: config.record.id
		}
		,fields: ['id','username','fullname','email']
		,autoHeight: true
		,paging: true
		,remoteSort: true
		,columns: [
			{header: _('sendex_subscriber_id'),dataIndex: 'id',width: 50}
			,{header: _('sendex_subscriber_username'),dataIndex: 'username',width: 100}
			,{header: _('sendex_subscriber_fullname'),dataIndex: 'fullname',width: 100}
			,{header: _('sendex_subscriber_email'),dataIndex: 'email',width: 100}
		]
		,tbar: [{
			xtype: 'sendex-combo-user'
			,name: 'user_id'
			,hiddenName: 'user_id'
			,width: '50%'
			,listeners: {
				select: {fn: this.addSubscriber, scope: this}
			}
		}]
	});
	Sendex.grid.NewsletterSubscribers.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.grid.NewsletterSubscribers,MODx.grid.Grid, {

	getMenu: function() {
		var m = [];
		m.push({
			text: _('sendex_subscriber_remove')
			,handler: this.removeSubscriber
		});
		this.addContextMenuItem(m);
	}

	,addSubscriber: function(combo, user, e) {
		combo.reset();

		MODx.Ajax.request({
			url: Sendex.config.connector_url
			,params: {
				action: 'mgr/newsletter/subscriber/create'
				,user_id: user.id
				,newsletter_id: this.config.record.id
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

	,removeSubscriber:function(btn,e) {
		MODx.msg.confirm({
			title: _('sendex_subscriber_remove')
			,text: _('sendex_subscriber_remove_confirm')
			,url: Sendex.config.connector_url
			,params: {
				action: 'mgr/newsletter/subscriber/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

});
Ext.reg('sendex-grid-newsletter-subscribers',Sendex.grid.NewsletterSubscribers);
