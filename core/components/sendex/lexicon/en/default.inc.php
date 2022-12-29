<?php

include_once 'setting.inc.php';

$_lang['sendex'] = 'Sendex';
$_lang['sendex_menu_desc'] = 'Newsletters management';

$_lang['sendex_newsletters'] = 'Newsletters';
$_lang['sendex_newsletter'] = 'Newsletter';
$_lang['sendex_newsletters_intro'] = 'On this page you create and edit your newsletters.';

$_lang['sendex_btn_create'] = 'Create';
$_lang['sendex_btn_subscribe'] = 'Subscribe!';
$_lang['sendex_btn_unsubscribe'] = 'Unsubscribe';
$_lang['sendex_btn_send_all'] = 'Send all';
$_lang['sendex_btn_remove_all'] = 'Remove all';
$_lang['sendex_btn_subscrubers_export'] = 'Export';
$_lang['sendex_select_user'] = 'Add user';
$_lang['sendex_select_group'] = 'Add group';
$_lang['sendex_select_newsletter'] = 'Add letters in the queue';

$_lang['sendex_newsletter_err_ae'] = 'An newsletter already exists with that name.';
$_lang['sendex_newsletter_err_nf'] = 'Newsletter not found.';
$_lang['sendex_newsletter_err_ns'] = 'Newsletter not specified.';
$_lang['sendex_newsletters_err_ns'] = 'Newsletters not specified.';
$_lang['sendex_newsletter_err_disabled'] = 'This newsletter is inactive.';
$_lang['sendex_newsletter_err_remove'] = 'An error occurred while trying to remove the newsletter.';
$_lang['sendex_newsletter_err_save'] = 'An error occurred while trying to save the newsletter.';
$_lang['sendex_newsletter_err_no_subscribers'] = 'This newsletter has no subscribers.';
$_lang['sendex_newsletter_err_no_template'] = 'This newsletter has no template.';
$_lang['sendex_newsletter_err_template'] = 'You must select template.';

$_lang['sendex_newsletters_remove'] = 'Remove newsletters';
$_lang['sendex_newsletters_remove_confirm'] = 'Are you sure you want to remove selected newsletter?';
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
$_lang['sendex_subscribers_err_ns'] = 'Subscribers not set.';
$_lang['sendex_subscriber_err_remove'] = 'Error when remove subscriber.';
$_lang['sendex_subscriber_err_save'] = 'Error when save subscriber.';
$_lang['sendex_subscriber_err_email'] = 'Email of subscriber is not set.';
$_lang['sendex_subscriber_err_group'] = 'You must specify the group for add of subscribers.';

$_lang['sendex_subscriber_id'] = 'id';
$_lang['sendex_subscriber_username'] = 'Username';
$_lang['sendex_subscriber_fullname'] = 'Fullname';
$_lang['sendex_subscriber_email'] = 'Email';
$_lang['sendex_subscribers_remove'] = 'Remove subscribers';
$_lang['sendex_subscribers_remove_confirm'] = 'Do you really want to unsubscribe selected subscribers from this newsletter?';

$_lang['sendex_queues'] = 'Email queue';
$_lang['sendex_queue'] = 'Queue';
$_lang['sendex_queue_intro'] = 'Here you control the queue distribution. You can add, delete and send letters.';

$_lang['sendex_queue_err_nf'] = 'Queues not found.';
$_lang['sendex_queue_err_ns'] = 'Queues ids not specified.';

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
$_lang['sendex_queues_send'] = 'Send letters';
$_lang['sendex_queues_send_confirm'] = 'Are you sure you want to send this letters?';
$_lang['sendex_queues_remove'] = 'Remove letters';
$_lang['sendex_queues_remove_confirm'] = 'Are you sure you want to remove this letters?';
$_lang['sendex_queues_send_all'] = 'Send all emails';
$_lang['sendex_queues_send_all_confirm'] = 'Are you sure you want to send all emails?';
$_lang['sendex_queues_remove_all'] = 'Remove all letters';
$_lang['sendex_queues_remove_all_confirm'] = 'Are you sure you want to remove all letters?';

$_lang['sendex_err_auth_req'] = 'You must be authorized for work with newsletters.';

$_lang['sendex_subscribe_intro'] = 'You can subscribe to newsletter «[[+name]]»!';
$_lang['sendex_unsubscribe_intro'] = 'You already subscribed to newsletter «[[+name]]». Do you want to unsubscribe?';

$_lang['sendex_subscribe_email_subscribed'] = 'Letter was sent to the email with a confirmation link.';
$_lang['sendex_subscribe_email_confirmed'] = 'Your email has been successfully subscribed.';
$_lang['sendex_subscribe_email_unsubscribed'] = 'Your email has been successfully unsubscribed.';

$_lang['sendex_subscribe_activate_subject'] = 'Confirm your email!';
$_lang['sendex_subscribe_err_already'] = 'This email is already subscribed to newsletter.';
$_lang['sendex_subscribe_err_email_wrong'] = 'Wrong email.';
$_lang['sendex_subscribe_err_email_ns'] = 'You need to specify email.';
$_lang['sendex_subscribe_err_email_send'] = 'Could not send email.';

$_lang['sendex_action_updateNewsletter'] = 'Update newsletter';
$_lang['sendex_action_disableNewsletter'] = 'Disable newsletter';
$_lang['sendex_action_enableNewsletter'] = 'Enable newsletter';
$_lang['sendex_action_removeNewsletter'] = 'Remove newsletter';
$_lang['sendex_action_removeSubscriber'] = 'Unsubscribe user';
$_lang['sendex_action_removeQueue'] = 'Remove email';
$_lang['sendex_action_sendQueue'] = 'Send email';

$_lang['sendex_subscribers_export_confirm_title'] = 'Confirm export';
$_lang['sendex_subscribers_export_confirm_text'] = 'Export email addresses?';
$_lang['sendex_subscribers_export_error'] = 'Error while exporting subscribers!';
