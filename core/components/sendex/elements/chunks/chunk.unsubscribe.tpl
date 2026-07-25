<div class="sendex-widget" data-sendex-widget>
    <p>
        [[%sendex_unsubscribe_intro?name=`[[+name]]`]]
        <small>[[+description]]</small>
    </p>

    <p class="sendex-message [[+class]]" data-sendex-message><b>[[+message]]</b></p>

    <form action="" method="post" data-sendex-form>
        <input type="hidden" name="sx_action" value="unsubscribe">

        <input type="hidden" name="code" value="[[+code]]">

        <button type="submit">[[%sendex_btn_unsubscribe]]</button>
    </form>
</div>
