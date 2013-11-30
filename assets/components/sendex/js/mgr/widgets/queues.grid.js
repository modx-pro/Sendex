Sendex.grid.Queues = function(config) {
	config = config || {};
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
		,columns: [
			{header: _('sendex_queue_id'),dataIndex: 'id',width: 50}
			,{header: _('sendex_newsletter'),dataIndex: 'newsletter',width: 100}
			,{header: _('sendex_queue_email_to'),dataIndex: 'email_to',width: 75}
			//,{header: _('sendex_queue_email_body'),dataIndex: 'email_body',width: 100}
			,{header: _('sendex_queue_email_subject'),dataIndex: 'email_subject',width: 100}
			//,{header: _('sendex_queue_email_from_name'),dataIndex: 'email_from_name',width: 100}
			//,{header: _('sendex_queue_email_reply'),dataIndex: 'email_reply',width: 100}
			,{header: _('sendex_queue_email_from'),dataIndex: 'email_from',width: 100}
			,{header: _('sendex_queue_timestamp'),dataIndex: 'timestamp',width: 75}
		]
		,tbar: [{
			xtype: 'sendex-combo-newsletter'
			,width: 300
			,listeners: {
				select: {fn:this.createQueues, scope:this}
			}
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
		m.push({
			text: _('sendex_queue_update')
			,handler: this.updateQueue
		});
		*/
		m.push({
			text: _('sendex_queue_send')
			,handler: this.sendQueue
		});
		m.push('-');
		m.push({
			text: _('sendex_queue_remove')
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

	,sendQueue: function(btn,e,row) {
		if (!this.menu.record) return;

		MODx.Ajax.request({
			url: Sendex.config.connector_url
			,params: {
				action: 'mgr/queue/send'
				,id: this.menu.record.id
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

	,updateQueue: function(btn,e,row) {
		return true;
	}

	,removeQueue: function(btn,e,row) {
		if (!this.menu.record) return;

		MODx.msg.confirm({
			title: _('sendex_queue_remove')
			,text: _('sendex_queue_remove_confirm')
			,url: Sendex.config.connector_url
			,params: {
				action: 'mgr/queue/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				success: {fn:function(r) {this.refresh();},scope:this}
			}
		});
	}

});
Ext.reg('sendex-grid-queues',Sendex.grid.Queues);