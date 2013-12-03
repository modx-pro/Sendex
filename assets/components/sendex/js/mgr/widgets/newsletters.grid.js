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
			,{header: _('sendex_newsletter_email_subject'),dataIndex: 'email_subject',width: 100}
			,{header: _('sendex_newsletter_email_from'),dataIndex: 'email_from',width: 100}
			//,{header: _('sendex_newsletter_email_from_name'),dataIndex: 'email_from_name',width: 100}
			//,{header: _('sendex_newsletter_email_reply'),dataIndex: 'email_reply',width: 100}
			,{header: _('sendex_newsletter_image'),dataIndex: 'image',width: 75,renderer: this.renderImage}
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
		,height: 350
		,width: 600
		,url: Sendex.config.connector_url
		,action: 'mgr/newsletter/create'
		,fields: [
			{xtype: 'textfield',fieldLabel: _('sendex_newsletter_name'),name: 'name',id: 'sendex-'+this.ident+'-name',anchor: '99%'}
			,{
				layout:'column'
				,border: false
				,anchor: '100%'
				,items: [{
					columnWidth: .5
					,layout: 'form'
					,defaults: { msgTarget: 'under' }
					,border:false
					,items: [
						{xtype: 'modx-combo-template',fieldLabel: _('sendex_newsletter_template'),name: 'template',id: 'sendex-'+this.ident+'-template',anchor: '99%'}
						,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_subject'),name: 'email_subject',id: 'sendex-'+this.ident+'-email_subject',anchor: '99%'}
						,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_reply'),name: 'email_reply',id: 'sendex-'+this.ident+'-email_reply',anchor: '99%'}
						,{xtype: 'combo-boolean',fieldLabel: _('sendex_newsletter_active'),name: 'active',hiddenName: 'active',id: 'sendex-'+this.ident+'-active',anchor: '50%'}
					]
				},{
					columnWidth: .5
					,layout: 'form'
					,defaults: { msgTarget: 'under' }
					,border:false
					,items: [
						{xtype: 'sendex-combo-snippet',fieldLabel: _('sendex_newsletter_snippet'),name: 'snippet',id: 'sendex-'+this.ident+'-snippet',anchor: '99%'}
						,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from'),name: 'email_from',id: 'sendex-'+this.ident+'-email_from',anchor: '99%'}
						,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from_name'),name: 'email_from_name',id: 'sendex-'+this.ident+'-email_from_name',anchor: '99%'}
						,{xtype: 'modx-combo-browser',fieldLabel: _('sendex_newsletter_image'),name: 'image',id: 'sendex-'+this.ident+'-image',anchor: '99%'}
					]
				}]
			}
			,{xtype: 'textarea',fieldLabel: _('sendex_sendmail_description'),name: 'description',id: 'sendex-'+this.ident+'-description',height: 75,anchor: '99%'}
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
		,height: 350
		,width: 600
		,url: Sendex.config.connector_url
		,action: 'mgr/newsletter/update'
		,fields: {
			xtype: 'modx-tabs'
			,deferredRender: false
			,border: true
			,bodyStyle: 'padding:5px;'
			,items: [{
				title: _('sendex_newsletter')
				,hideMode: 'offsets'
				,layout: 'form'
				,border:false
				,items: [
					{xtype: 'hidden',name: 'id',id: 'sendex-'+this.ident+'-id'}
					,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_name'),name: 'name',id: 'sendex-'+this.ident+'-name',anchor: '99%'}
					,{
						layout:'column'
						,border: false
						,anchor: '100%'
						,items: [{
							columnWidth: .5
							,layout: 'form'
							,defaults: { msgTarget: 'under' }
							,border:false
							,items: [
								{xtype: 'modx-combo-template',editable:true,fieldLabel: _('sendex_newsletter_template'),name: 'template',id: 'sendex-'+this.ident+'-template',anchor: '99%'}
								,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_subject'),name: 'email_subject',id: 'sendex-'+this.ident+'-email_subject',anchor: '99%'}
								,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_reply'),name: 'email_reply',id: 'sendex-'+this.ident+'-email_reply',anchor: '99%'}
								,{xtype: 'combo-boolean',fieldLabel: _('sendex_newsletter_active'),name: 'active',hiddenName: 'active',id: 'sendex-'+this.ident+'-active',anchor: '50%'}
							]
						},{
							columnWidth: .5
							,layout: 'form'
							,defaults: { msgTarget: 'under' }
							,border:false
							,items: [
								{xtype: 'sendex-combo-snippet',editable:true,fieldLabel: _('sendex_newsletter_snippet'),name: 'snippet',id: 'sendex-'+this.ident+'-snippet',anchor: '99%'}
								,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from'),name: 'email_from',id: 'sendex-'+this.ident+'-email_from',anchor: '99%'}
								,{xtype: 'textfield',fieldLabel: _('sendex_newsletter_email_from_name'),name: 'email_from_name',id: 'sendex-'+this.ident+'-email_from_name',anchor: '99%'}
								,{xtype: 'modx-combo-browser',fieldLabel: _('sendex_newsletter_image'),name: 'image',id: 'sendex-'+this.ident+'-image',anchor: '99%'}
							]
						}]
					}
					,{xtype: 'textarea',fieldLabel: _('sendex_sendmail_description'),name: 'description',id: 'sendex-'+this.ident+'-description',height: 75,anchor: '99%'}
				]
			},{
				title: _('sendex_subscribers')
				,xtype: 'sendex-grid-newsletter-subscribers'
				,record: config.record.object
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