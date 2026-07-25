<?php

$_lang['area_sendex_main'] = 'Main';

$_lang['setting_sendex_export_fields'] = 'Fields for export';
$_lang['setting_sendex_export_fields_desc'] = 'Enter fields separated by commas. Available values:'
    . ' id,user_id,email,username,fullname,phone,mobilephone';
$_lang['setting_sendex_hide_export_button'] = 'Hide export button';
$_lang['setting_sendex_hide_export_button_desc'] = 'Disables the button to export the list of email'
    . ' addresses in the mailing list';
$_lang['setting_sendex_confirm_email'] = 'Require guest email confirmation';
$_lang['setting_sendex_confirm_email_desc'] = 'When enabled, guests must confirm subscription via email link. '
    . 'When disabled, guest emails are subscribed immediately (higher risk of invalid addresses).';
$_lang['setting_sendex_confirm_rate_limit'] = 'Confirm email rate limit (seconds)';
$_lang['setting_sendex_confirm_rate_limit_desc'] = 'Maximum one confirmation email per address in this window. '
    . 'Set 0 to disable rate limiting.';
$_lang['setting_sendex_csrf_protect'] = 'Enable frontend CSRF protection';
$_lang['setting_sendex_csrf_protect_desc'] = 'Adds a CSRF token to subscribe/unsubscribe forms and validates it in snippet/AJAX requests.';
