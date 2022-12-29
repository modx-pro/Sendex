Sendex.grid.Newsletters = function(config) {
	config = config || {};
	this.sm = new Ext.grid.CheckboxSelectionModel();

	Ext.applyIf(config,{
		id: 'sendex-grid-newsletters'
		,url: Sendex.config.connector_url
		,baseParams: {
			action: 'mgr/newsletter/getlist'
		}
		,fields: ['id','name','description','active','template','templatename'
			,'image','email_subject','email_from','email_from_name','email_reply','subscribers','actions']
		,autoHeight: true
		,paging: true
		,remoteSort: true
		,sm: this.sm
		,columns: [
			{header: _('sendex_newsletter_id'), sortable: true, dataIndex: 'id',width: 50}
			,{header: _('sendex_newsletter_name'), sortable: true, dataIndex: 'name',width: 100}
			//,{header: _('sendex_newsletter_active'), sortable: true, dataIndex: 'active',width: 75,renderer: this._renderBoolean}
			,{header: _('sendex_newsletter_template'), sortable: true, dataIndex: 'template',width: 75,renderer: this._renderTemplate}
			,{header: _('sendex_subscribers'), sortable: true, dataIndex: 'subscribers',width: 75}
			,{header: _('sendex_newsletter_email_subject'), sortable: true, dataIndex: 'email_subject',width: 100}
			,{header: _('sendex_newsletter_email_from'), sortable: true, dataIndex: 'email_from',width: 100}
			,{header: _('sendex_newsletter_email_from_name'), sortable: true, dataIndex: 'email_from_name',width: 100, hidden: true}
			,{header: _('sendex_newsletter_email_reply'), sortable: true, dataIndex: 'email_reply',width: 100, hidden: true}
			,{header: _('sendex_newsletter_image'), dataIndex: 'image',width: 75,renderer: this._renderImage, id: 'image'}
			,{header: '', dataIndex: 'actions',width: 75,renderer: Sendex.utils.renderActions, id: 'actions'}
		]
		,tbar: [{
			text: '<i class="' + (MODx.modx23 ? 'icon icon-plus' : 'fa fa-plus') + '"></i> ' + _('sendex_btn_create')
			,handler: this.createNewsletter
			,scope: this
		}]
		,viewConfig: {
			forceFit: true
			,enableRowBody: true
			,autoFill: true
			,showPreview: true
			,scrollOffset: 0
			,getRowClass : function(rec, ri, p) {
				if (!rec.data.active) {
					return 'sendex-row-disabled';
				}
				return '';
			}
		}
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

	,getMenu: function(grid, rowIndex) {
		var row = grid.getStore().getAt(rowIndex);
		var menu = Sendex.utils.getMenu(row.data.actions, this);
		this.addContextMenuItem(menu);
	}

	,onClick: function(e) {
		var elem = e.getTarget();
		if (elem.nodeName == 'BUTTON') {
			var row = this.getSelectionModel().getSelected();
			if (typeof(row) != 'undefined') {
				var type = elem.getAttribute('type');
				if (type == 'menu') {
					var ri = this.getStore().find('id', row.id);
					return this._showMenu(this, ri, e);
				}
				else {
					this.menu.record = row.data;
					return this[type](this, e);
				}
			}
		}
		return this.processEvent('click', e);
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

	,updateNewsletter: function(grid, e, row) {
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

	,removeNewsletter: function(grid, e) {
		var ids = this._getSelectedIds();
		if (!ids) {return;}
		Sendex.utils.onAjax(this.getEl());
		
		MODx.msg.confirm({
			title: _('sendex_newsletters_remove')
			,text: _('sendex_newsletters_remove_confirm')
			,url: this.config.url
			,params: {
				action: 'mgr/newsletter/remove'
				,ids: ids.join(',')
			}
			,listeners: {
				success: {fn:function(r) { this.refresh(); },scope:this}
			}
		});
	}

	,disableNewsletter: function(grid, e) {
		var ids = this._getSelectedIds();
		if (!ids) {return;}
		Sendex.utils.onAjax(this.getEl());

		MODx.Ajax.request({
			url: this.config.url
			,params: {
				action: 'mgr/newsletter/disable'
				,ids: ids.join(',')
			}
			,listeners: {
				success: {fn:function(r) { this.refresh(); },scope:this}
			}
		});
	}

	,enableNewsletter: function(grid, e) {
		var ids = this._getSelectedIds();
		if (!ids) {return;}
		Sendex.utils.onAjax(this.getEl());

		MODx.Ajax.request({
			url: this.config.url
			,params: {
				action: 'mgr/newsletter/enable'
				,ids: ids.join(',')
			}
			,listeners: {
				success: {fn:function(r) { this.refresh(); },scope:this}
			}
		});
	}

	,_getSelectedIds: function() {
		var ids = [];
		var selected = this.getSelectionModel().getSelections();

		for (var i in selected) {
			if (!selected.hasOwnProperty(i)) {continue;}
			ids.push(selected[i]['id']);
		}

		return ids;
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
				,layout: 'anchor'
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
	this.sm = new Ext.grid.CheckboxSelectionModel();

	Ext.applyIf(config,{
		id: 'sendex-grid-newsletter-subscribers'
		,url: Sendex.config.connector_url
		,baseParams: {
			action: 'mgr/newsletter/subscriber/getlist'
			,newsletter_id: config.record.id
		}
		,fields: ['id','username','fullname','email','actions']
		,autoHeight: true
		,paging: true
		,remoteSort: true
		,sm: this.sm
		,columns: [
			{header: _('sendex_subscriber_id'), sortable: true, dataIndex: 'id',width: 50}
			,{header: _('sendex_subscriber_username'), sortable: true, dataIndex: 'username',width: 100}
			,{header: _('sendex_subscriber_fullname'), sortable: true, dataIndex: 'fullname',width: 100}
			,{header: _('sendex_subscriber_email'), sortable: true, dataIndex: 'email',width: 100}
			,{header: '', dataIndex: 'actions',width: 50,renderer: Sendex.utils.renderActions, id: 'actions'}
		]
		,tbar: [{
			xtype: 'sendex-combo-user'
			,name: 'user_id'
			,hiddenName: 'user_id'
			,width: 200
			,listeners: {
				select: {fn: this.addSubscriber, scope: this}
			}
		},{
            xtype: 'button', 
            hidden: Sendex.config.hideExportButton,
            text: '<i class="icon-arrow-circle-up icon"></i> ' + _('sendex_btn_subscrubers_export'),
            id: 'sendex-export-form',
            cls: 'x-btn-restore-all',
            listeners: {
                'click': {fn: this.exportSubscribers, scope: this}
            }
        }
        ,'->'
        , {
			xtype: 'sendex-combo-group'
			,name: 'group_id'
			,hiddenName: 'group_id'
			,width: 200
			,listeners: {
				select: {fn: this.addSubscribers, scope: this}
			}
		}]
	});
	Sendex.grid.NewsletterSubscribers.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.grid.NewsletterSubscribers,MODx.grid.Grid, {

	getMenu: function(grid, rowIndex) {
		var row = grid.getStore().getAt(rowIndex);
		var menu = Sendex.utils.getMenu(row.data.actions, this);
		this.addContextMenuItem(menu);
	}

	,onClick: function(e) {
		var elem = e.getTarget();
		if (elem.nodeName == 'BUTTON') {
			var row = this.getSelectionModel().getSelected();
			if (typeof(row) != 'undefined') {
				var type = elem.getAttribute('type');
				if (type == 'menu') {
					var ri = this.getStore().find('id', row.id);
					return this._showMenu(this, ri, e);
				}
				else {
					this.menu.record = row.data;
					return this[type](this, e);
				}
			}
		}
		return this.processEvent('click', e);
	}

	,addSubscriber: function(combo, user, e) {
		combo.reset();
		Sendex.utils.onAjax(this.getEl());

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

	,addSubscribers: function(combo, group, e) {
		combo.reset();
		Sendex.utils.onAjax(this.getEl());

		MODx.Ajax.request({
			url: Sendex.config.connector_url
			,params: {
				action: 'mgr/newsletter/subscriber/add_group'
				,group_id: group.id
				,newsletter_id: this.config.record.id
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

	,removeSubscriber:function(btn,e) {
		var ids = this._getSelectedIds();
		if (!ids) {return;}
		Sendex.utils.onAjax(this.getEl());

		MODx.msg.confirm({
			title: _('sendex_subscribers_remove')
			,text: _('sendex_subscribers_remove_confirm')
			,url: Sendex.config.connector_url
			,params: {
				action: 'mgr/newsletter/subscriber/remove'
				,ids: ids.join(',')
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

	,_getSelectedIds: function() {
		var ids = [];
		var selected = this.getSelectionModel().getSelections();

		for (var i in selected) {
			if (!selected.hasOwnProperty(i)) {continue;}
			ids.push(selected[i]['id']);
		}

		return ids;
	}

	,exportSubscribers: function() {
		MODx.msg.confirm({
            title: _('sendex_subscribers_export_confirm_title')
            ,text: _('sendex_subscribers_export_confirm_text')
			,url: Sendex.config.connector_url
			,params: {
				action: 'mgr/newsletter/subscriber/export'
				,newsletter_id: this.config.record.id
			}
            ,listeners: {
                'success': {
                    fn: function (data) {
                        var date = new Date().format('Ymd-His'); 
                        var newlink = document.createElement('a');
                        newlink.setAttribute('target', '_blank');
                        newlink.setAttribute('download', 'subscribers_' + date + '.csv');
                        newlink.setAttribute('href',data.url);
                        newlink.click()
                    }, scope: this
                },
                'error': {
                    fn: function (data) {
                        MODx.msg.status({
                            title: _('error'),
                            message: _('sendex_subscribers_export_error')
                        });
                    }, scope: this
                }
            }
        });
	}

});
Ext.reg('sendex-grid-newsletter-subscribers',Sendex.grid.NewsletterSubscribers);
