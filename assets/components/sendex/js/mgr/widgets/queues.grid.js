Sendex.grid.Queues = function(config) {
	config = config || {};
	this.sm = new Ext.grid.CheckboxSelectionModel();

	Ext.applyIf(config,{
		id: 'sendex-grid-queues'
		,url: Sendex.config.connector_url
		,baseParams: {
			action: 'mgr/queue/getlist'
		}
		,fields: ['id','newsletter_id','subscriber_id','timestamp','email_to','email_subject','email_body','email_from','email_from_name','email_reply','newsletter']
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
			,{header: _('sendex_queue_timestamp'), sortable: true, dataIndex: 'timestamp',width: 75}
		]
		,tbar: [{
			xtype: 'sendex-combo-newsletter'
			,width: 300
			,listeners: {
				select: {fn:this.createQueues, scope:this}
			}
		}, '->' ,{
			xtype: 'button'
			,text: _('sendex_btn_send_all')
			,handler: this.sendAll
			,scope: this
		}]
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updateQueue(grid, e, row);
			}
		}
	});
	Sendex.grid.Queues.superclass.constructor.call(this,config);
};
Ext.extend(Sendex.grid.Queues,MODx.grid.Grid, {
	windows: {}

	,getMenu: function() {
		var m = [];
		/*
		 var ids = this._getSelectedIds();
		if (ids.length == 1) {
			 m.push({
			 text: _('sendex_queue_update')
			 ,handler: this.updateQueue
			 });
		}
		*/
		m.push({
			text: _('sendex_queues_send')
			,handler: this.sendQueue
		});
		m.push('-');
		m.push({
			text: _('sendex_queues_remove')
			,handler: this.removeQueue
		});

		this.addContextMenuItem(m);
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

});
Ext.reg('sendex-grid-queues',Sendex.grid.Queues);