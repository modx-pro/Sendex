<form action="" method="post">
	<p>
		[[%sendex_subscribe_intro?name=`[[+name]]`]]
		<small>[[+description]]</small>
	</p>
	<input type="textfield" name="email" value="" placeholder="Email">

	<input type="hidden" name="sx_action" value="subscribe">
	<button type="submit">[[%sendex_btn_subscribe]]</button>

	[[+message]]
</form>