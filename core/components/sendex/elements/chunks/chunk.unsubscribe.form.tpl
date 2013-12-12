<form action="" method="post">
	<p>
		[[%sendex_unsubscribe_intro?name=`[[+name]]`]]
		<small>[[+description]]</small>
	</p>
	<input type="hidden" name="code" value="[[+code]]">

	<input type="hidden" name="sx_action" value="unsubscribe">
	<button type="submit">[[%sendex_btn_unsubscribe]]</button>

	[[+message]]
</form>