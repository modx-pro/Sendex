Sendex.grid.Queues = function(config) {
	config = config || {};
	this.sm = new Ext.grid.CheckboxSelectionModel();

	Ext.applyIf(config,{
		id: 'sendex-grid-queues'
		,url: Sendex.config.connector_url
		,baseParams: {
			action: 'mgr/queue/getlist'
		}
		,fields: ['id','newsletter_id','subscriber_id','timestamp','email_to','email_subject'
			,'email_body','email_from','email_from_name','email_reply','newsletter','actions']
		,autoHeight: true
		,paging: true
		,remoteSort: true
		,sm: this.sm
		,columns: [
			{header: _('sendex_queue_id'), sortable: true, dataIndex: 'id',width: 50}
			,{header: _('sendex_newsletter'), sortable: true, dataIndex: 'newsletter',width: 100}
			,{header: _('sendex_queue_email_to'), sortable: true, dataIndex: 'email_to',width: 75}
			,{header: _('sendex_queue_email_subject'), sortable: true, dataIndex: 'email_subject',width: 100}
			,{header: _('sendex_queue_email_from_name'), sortable: true, dataIndex: 'email_from_name',width: 100}
			,{header: _('sendex_queue_email_reply'), sortable: true, dataIndex: 'email_reply',width: 100, hidden: true}
			,{header: _('sendex_queue_email_from'), sortable: true, dataIndex: 'email_from',width: 100, hidden: true}
			,{header: _('sendex_queue_timestamp'), sortable: true, dataIndex: 'timestamp',width: 100}
			,{header: '', dataIndex: 'actions',width: 75,renderer: Sendex.utils.renderActions, id: 'actions'}
		],
		tbar: [
			{
				xtype: 'sendex-combo-newsletter',
				width: 300,
				listeners: {
					select: {fn: this.createQueues, scope: this}
				}
			},
			'->',
			{
				xtype: 'button',
				text: '<i class="' + (MODx.modx23 ? 'icon icon-trash-o' : 'fa fa-trash-o') + '"></i> ' + _('sendex_btn_remove_all'),
				handler: this.removeAll,
				scope: this
			},
			{
				xtype: 'button',
				text: '<i class="' + (MODx.modx23 ? 'icon icon-send' : 'fa fa-send') + '"></i> ' + _('sendex_btn_send_all'),
				handler: this.sendAll,
				scope: this
			}
		]
		/*
		 listeners: {
		 	rowDblClick: function(grid, rowIndex, e) {
		 		var row = grid.store.getAt(rowIndex);
		 		this.update(grid, e, row);
		 	}
		 }
		 */
	});

	Sendex.grid.Queues.superclass.constructor.call(this, config);
};
Ext.extend(Sendex.grid.Queues,MODx.grid.Grid, {
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

	,createQueues: function(combo, newsletter, e) {
		combo.reset();

		MODx.Ajax.request({
			url: Sendex.config.connector_url
			,params: {
				action: 'mgr/queue/add'
				,newsletter_id: newsletter.id
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

	,updateQueue: function(btn,e,row) {
		return true;
	}

	,sendQueue: function(btn,e,row) {
		var ids = this._getSelectedIds();
		if (!ids) {return;}
		Sendex.utils.onAjax(this.getEl());

		MODx.Ajax.request({
			url: Sendex.config.connector_url
			,params: {
				action: 'mgr/queue/send'
				,ids: ids.join(',')
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

	,removeQueue: function(btn,e,row) {
		var ids = this._getSelectedIds();
		if (!ids) {return;}

		MODx.msg.confirm({
			title: _('sendex_queues_remove')
			,text: _('sendex_queues_remove_confirm')
			,url: Sendex.config.connector_url
			,params: {
				action: 'mgr/queue/remove'
				,ids: ids.join(',')
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

	,sendAll: function() {
		Sendex.utils.onAjax(this.getEl());

		MODx.msg.confirm({
			title: _('sendex_queues_send_all')
			,text: _('sendex_queues_send_all_confirm')
			,url: Sendex.config.connector_url
			,params: {
				action: 'mgr/queue/send_all'
			},
			listeners: {
				success: {
					fn: function () {
						this.refresh();
					}, scope: this
				}
			}
		});
	},

	removeAll: function() {
		Sendex.utils.onAjax(this.getEl());

		MODx.msg.confirm({
			title: _('sendex_queues_remove_all'),
			text: _('sendex_queues_remove_all_confirm'),
			url: Sendex.config.connector_url,
			params: {
				action: 'mgr/queue/remove_all'
			},
			listeners: {
				success: {
					fn: function () {
						this.refresh();
					}, scope: this
				}
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
Ext.reg('sendex-grid-queues',Sendex.grid.Queues);
