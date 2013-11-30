<?php

include_once 'setting.inc.php';

$_lang['sendex'] = 'Sendex';
$_lang['sendex_menu_desc'] = 'Newsletters management';

$_lang['sendex_newsletters'] = 'Newsletters';
$_lang['sendex_newsletter'] = 'Newsletter';
$_lang['sendex_newsletters_intro'] = 'On this page you create and edit your subscription.';

$_lang['sendex_btn_create'] = 'Create';
$_lang['sendex_select_user'] = 'Select user';
$_lang['sendex_select_newsletter'] = 'Add letters in the queue';

$_lang['sendex_newsletter_err_ae'] = 'An newsletter already exists with that name.';
$_lang['sendex_newsletter_err_nf'] = 'Newsletter not found.';
$_lang['sendex_newsletter_err_ns'] = 'Newsletter not specified.';
$_lang['sendex_newsletter_err_remove'] = 'An error occurred while trying to remove the newsletter.';
$_lang['sendex_newsletter_err_save'] = 'An error occurred while trying to save the newsletter.';
$_lang['sendex_newsletter_err_no_subscribers'] = 'This newsletter has no subscribers.';
$_lang['sendex_newsletter_err_template'] = 'You must select template or template.';
$_lang['sendex_newsletter_err_snippet'] = 'You must select template or snippet.';
$_lang['sendex_newsletter_remove'] = 'Remove newsletter';
$_lang['sendex_newsletter_remove_confirm'] = 'Are you sure you want to remove this newsletter?';
$_lang['sendex_newsletter_create'] = 'Create newsletter';
$_lang['sendex_newsletter_update'] = 'Update newsletter';

$_lang['sendex_newsletter_id'] = 'id';
$_lang['sendex_newsletter_name'] = 'Title';
$_lang['sendex_newsletter_description'] = 'Description';
$_lang['sendex_newsletter_active'] = 'Active';
$_lang['sendex_newsletter_template'] = 'Template';
$_lang['sendex_newsletter_snippet'] = 'Snippet';
$_lang['sendex_newsletter_email_subject'] = 'Email subject';
$_lang['sendex_newsletter_email_from'] = 'Emil from';
$_lang['sendex_newsletter_email_from_name'] = 'Email from name';
$_lang['sendex_newsletter_email_reply'] = 'Email reply';
$_lang['sendex_newsletter_image'] = 'Image';

$_lang['sendex_subscribers'] = 'Subscribers';
$_lang['sendex_subscriber'] = 'Subscriber';

$_lang['sendex_subscriber_err_ae'] = 'This user is already subscribed.';
$_lang['sendex_subscriber_err_nf'] = 'Subscriber not found.';
$_lang['sendex_subscriber_err_ns'] = 'Subscriber not set.';
$_lang['sendex_subscriber_err_remove'] = 'Error when remove subscriber.';
$_lang['sendex_subscriber_err_save'] = 'Error when save subscriber.';
$_lang['sendex_subscriber_err_email'] = 'Email of subscriber is not set.';

$_lang['sendex_subscriber_id'] = 'id';
$_lang['sendex_subscriber_username'] = 'Username';
$_lang['sendex_subscriber_fullname'] = 'Fullname';
$_lang['sendex_subscriber_email'] = 'Email';
$_lang['sendex_subscriber_remove'] = 'Remove subscriber';
$_lang['sendex_subscriber_remove_confirm'] = 'Do you really want unsubscribe subscriber from this newsletter?';

$_lang['sendex_queues'] = 'Email queue';
$_lang['sendex_queue'] = 'Queue';
$_lang['sendex_queue_intro'] = 'Here you control the queue distribution. You can add, delete and send letters.';

$_lang['sendex_queue_err_nf'] = 'Queue not found.';
$_lang['sendex_queue_err_ns'] = 'Queue not specified.';

$_lang['sendex_queue_id'] = 'id';
$_lang['sendex_queue_newsletter_id'] = 'Newsletter id';
$_lang['sendex_queue_subscriber_id'] = 'Subscriber id';
$_lang['sendex_queue_timestamp'] = 'Timestamp';
$_lang['sendex_queue_email_to'] = 'Email to';
$_lang['sendex_queue_email_subject'] = 'Email subject';
$_lang['sendex_queue_email_body'] = 'Email body';
$_lang['sendex_queue_email_from'] = 'Email from';
$_lang['sendex_queue_email_from_name'] = 'Email from name';
$_lang['sendex_queue_email_reply'] = 'Reply to';

$_lang['sendex_queue_update'] = 'Update letter';
$_lang['sendex_queue_send'] = 'Send letter';
$_lang['sendex_queue_remove'] = 'Remove letter';
$_lang['sendex_queue_remove_confirm'] = 'Are you sure you want to remove this letter?';