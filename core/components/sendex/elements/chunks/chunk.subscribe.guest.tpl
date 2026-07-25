<div class="sendex-widget" data-sendex-widget data-sendex-newsletter-id="[[+id]]">
    <p>
        [[%sendex_subscribe_intro?name=`[[+name]]`]]
        <small>[[+description]]</small>
    </p>

    <p class="sendex-message [[+class]]" data-sendex-message><b>[[+message]]</b></p>

    <form action="" method="post" data-sendex-form>
        <input type="hidden" name="sx_action" value="subscribe">
        <input type="hidden" name="newsletter_id" value="[[+id]]">
        [[+widget_key:notempty=`<input type="hidden" name="sendex_widget_key" value="[[+widget_key]]">`]]

        <input type="email" name="email" value="" placeholder="Email" required>

        <button type="submit">[[%sendex_btn_subscribe]]</button>
    </form>
</div>
