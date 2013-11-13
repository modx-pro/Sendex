var Sendex = function(config) {
	config = config || {};
	Sendex.superclass.constructor.call(this,config);
};
Ext.extend(Sendex,Ext.Component,{
	page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {},view: {}
});
Ext.reg('sendex',Sendex);

Sendex = new Sendex();