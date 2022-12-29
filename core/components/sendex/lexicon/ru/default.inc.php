<?php

include_once 'setting.inc.php';

$_lang['sendex'] = 'Sendex';
$_lang['sendex_menu_desc'] = 'Управление подписками';

$_lang['sendex_newsletters'] = 'Подписки';
$_lang['sendex_newsletter'] = 'Подписка';
$_lang['sendex_newsletters_intro'] = 'На этой странице вы создаёте и редактируете ваши рассылки.';

$_lang['sendex_btn_create'] = 'Создать';
$_lang['sendex_btn_subscribe'] = 'Подписаться!';
$_lang['sendex_btn_unsubscribe'] = 'Отписаться';
$_lang['sendex_btn_send_all'] = 'Отправить все';
$_lang['sendex_btn_remove_all'] = 'Удалить все';
$_lang['sendex_btn_subscrubers_export'] = 'Экспорт';
$_lang['sendex_select_user'] = 'Добавить пользователя';
$_lang['sendex_select_group'] = 'Добавить группу';
$_lang['sendex_select_newsletter'] = 'Добавить письма рассылки в очередь';

$_lang['sendex_newsletter_err_ae'] = 'Подписка с таким именем уже существует.';
$_lang['sendex_newsletter_err_nf'] = 'Подписка не найдена.';
$_lang['sendex_newsletter_err_ns'] = 'Подписка не указана.';
$_lang['sendex_newsletters_err_ns'] = 'Подписки не указаны.';
$_lang['sendex_newsletter_err_disabled'] = 'Эта подписка неактивна.';
$_lang['sendex_newsletter_err_remove'] = 'Ошибка при удалении подписки.';
$_lang['sendex_newsletter_err_save'] = 'Ошибка при сохранении подписки.';
$_lang['sendex_newsletter_err_no_subscribers'] = 'У этой рассылки нет подписчиков.';
$_lang['sendex_newsletter_err_no_template'] = 'У этой рассылки нет шаблона.';
$_lang['sendex_newsletter_err_template'] = 'Вы должны выбрать шаблон.';

$_lang['sendex_newsletters_remove'] = 'Удалить подписки';
$_lang['sendex_newsletters_remove_confirm'] = 'Вы уверены, что хотите удалить выбранные подписки?';
$_lang['sendex_newsletter_create'] = 'Создать подписку';
$_lang['sendex_newsletter_update'] = 'Изменить подписку';

$_lang['sendex_newsletter_id'] = 'id';
$_lang['sendex_newsletter_name'] = 'Название';
$_lang['sendex_newsletter_description'] = 'Описание';
$_lang['sendex_newsletter_active'] = 'Включено';
$_lang['sendex_newsletter_template'] = 'Шаблон';
$_lang['sendex_newsletter_snippet'] = 'Сниппет';
$_lang['sendex_newsletter_email_subject'] = 'Тема письма';
$_lang['sendex_newsletter_email_from'] = 'Исходящий email';
$_lang['sendex_newsletter_email_from_name'] = 'Отправитель';
$_lang['sendex_newsletter_email_reply'] = 'Ответный email';
$_lang['sendex_newsletter_image'] = 'Изображение';

$_lang['sendex_subscribers'] = 'Подписчики';
$_lang['sendex_subscriber'] = 'Подписчик';

$_lang['sendex_subscriber_err_ae'] = 'Этот пользователь уже подписан.';
$_lang['sendex_subscriber_err_nf'] = 'Подписчик не найден.';
$_lang['sendex_subscriber_err_ns'] = 'Подписчик не указан.';
$_lang['sendex_subscribers_err_ns'] = 'Подписчики не указаны.';
$_lang['sendex_subscriber_err_remove'] = 'Ошибка при удалении подписчика.';
$_lang['sendex_subscriber_err_save'] = 'Ошибка при сохранении подписчика.';
$_lang['sendex_subscriber_err_email'] = 'Не указан email пописчика.';
$_lang['sendex_subscriber_err_group'] = 'Вы должны указать группу для добавления подписчиков.';

$_lang['sendex_subscriber_id'] = 'id';
$_lang['sendex_subscriber_username'] = 'Псевдоним';
$_lang['sendex_subscriber_fullname'] = 'Полное имя';
$_lang['sendex_subscriber_email'] = 'Email';
$_lang['sendex_subscribers_remove'] = 'Удалить подписчиков';
$_lang['sendex_subscribers_remove_confirm'] = 'Вы действительно хотите отписать выбранных пользователей от этой подписки?';

$_lang['sendex_queues'] = 'Очередь писем';
$_lang['sendex_queue'] = 'Письмо';
$_lang['sendex_queue_intro'] = 'Здесь вы управляете очередью рассылки. Вы можете добавлять, удалять и отправлять письма.';

$_lang['sendex_queue_err_nf'] = 'Письмо не найдено.';
$_lang['sendex_queue_err_ns'] = 'Не указаны идентификаторы писем.';

$_lang['sendex_queue_id'] = 'id';
$_lang['sendex_queue_newsletter_id'] = 'id подписки';
$_lang['sendex_queue_subscriber_id'] = 'id подписчика';
$_lang['sendex_queue_timestamp'] = 'Время';
$_lang['sendex_queue_email_to'] = 'Кому';
$_lang['sendex_queue_email_subject'] = 'Тема письма';
$_lang['sendex_queue_email_body'] = 'Тело письма';
$_lang['sendex_queue_email_from'] = 'Исходящий email';
$_lang['sendex_queue_email_from_name'] = 'Отправитель';
$_lang['sendex_queue_email_reply'] = 'Ответить';

$_lang['sendex_queue_update'] = 'Изменить письмо';
$_lang['sendex_queue_send'] = 'Отправить письмо';
$_lang['sendex_queues_send'] = 'Отправить письма';
$_lang['sendex_queues_send_confirm'] = 'Вы действительно хотите отправить эти письма?';
$_lang['sendex_queues_remove'] = 'Удалить письма';
$_lang['sendex_queues_remove_confirm'] = 'Вы действительно хотите удалить эти письма?';
$_lang['sendex_queues_send_all'] = 'Отправить все письма';
$_lang['sendex_queues_send_all_confirm'] = 'Вы действительно хотите отправить все письма?';
$_lang['sendex_queues_remove_all'] = 'Удалить все письма';
$_lang['sendex_queues_remove_all_confirm'] = 'Вы действительно хотите удалить все письма?';

$_lang['sendex_err_auth_req'] = 'Вы должны быть авторизованы для работы с подписками.';

$_lang['sendex_subscribe_intro'] = 'Вы можете подписаться на рассылку «[[+name]]»!';
$_lang['sendex_unsubscribe_intro'] = 'Вы уже подписаны на рассылку «[[+name]]». Хотите отписаться?';

$_lang['sendex_subscribe_email_subscribed'] = 'На указанный email отправлено письмо со ссылкой для подтверждения.';
$_lang['sendex_subscribe_email_confirmed'] = 'Ваш email успешно подписан на рассылку.';
$_lang['sendex_subscribe_email_unsubscribed'] = 'Ваш email успешно отписан от рассылки.';

$_lang['sendex_subscribe_activate_subject'] = 'Подтвердите ваш email!';
$_lang['sendex_subscribe_err_already'] = 'Этот email уже подписан на рассылку.';
$_lang['sendex_subscribe_err_email_wrong'] = 'Неверный email.';
$_lang['sendex_subscribe_err_email_ns'] = 'Нужно указать email.';
$_lang['sendex_subscribe_err_email_send'] = 'Не могу отправить email.';

$_lang['sendex_action_updateNewsletter'] = 'Изменить рассылку';
$_lang['sendex_action_disableNewsletter'] = 'Отключить рассылку';
$_lang['sendex_action_enableNewsletter'] = 'Включить рассылку';
$_lang['sendex_action_removeNewsletter'] = 'Удалить рассылку';
$_lang['sendex_action_removeSubscriber'] = 'Отписать пользователя';
$_lang['sendex_action_removeQueue'] = 'Удалить письмо';
$_lang['sendex_action_sendQueue'] = 'Отправить письмо';

$_lang['sendex_subscribers_export_confirm_title'] = 'Подтвердите экспорт';
$_lang['sendex_subscribers_export_confirm_text'] = 'Экспортировать email адреса?';
$_lang['sendex_subscribers_export_error'] = 'Ошибка при экспорте подписчиков!';
