<?php

include_once 'setting.inc.php';

$_lang['sendex'] = 'Sendex';
$_lang['sendex_menu_desc'] = 'Управление подписками';

$_lang['sendex_newsletters'] = 'Подписки';
$_lang['sendex_newsletter'] = 'Подписка';
$_lang['sendex_newsletters_intro'] = 'На этой странице вы создаёте и редактируете ваши подписки.';

$_lang['sendex_btn_create'] = 'Создать';
$_lang['sendex_btn_subscribe'] = 'Подписаться!';
$_lang['sendex_btn_unsubscribe'] = 'Отписаться';
$_lang['sendex_select_user'] = 'Выберите пользователя';
$_lang['sendex_select_newsletter'] = 'Добавить письма рассылки в очередь';

$_lang['sendex_newsletter_err_ae'] = 'Подписка с таким именем уже существует.';
$_lang['sendex_newsletter_err_nf'] = 'Подписка не найдена.';
$_lang['sendex_newsletter_err_ns'] = 'Подписка не указана.';
$_lang['sendex_newsletter_err_disabled'] = 'Эта подписка неактивна.';
$_lang['sendex_newsletter_err_remove'] = 'Ошибка при удалении подписки.';
$_lang['sendex_newsletter_err_save'] = 'Ошибка при сохранении подписки.';
$_lang['sendex_newsletter_err_no_subscribers'] = 'У этой рассылки нет подписчиков.';
$_lang['sendex_newsletter_err_template'] = 'Вы должны выбрать шаблон или сниппет.';
$_lang['sendex_newsletter_err_snippet'] = 'Вы должны выбрать сниппет или шаблон.';

$_lang['sendex_newsletter_remove'] = 'Удалить подписку';
$_lang['sendex_newsletter_remove_confirm'] = 'Вы уверены, что хотите удалить эту подписку?';
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
$_lang['sendex_subscriber_err_remove'] = 'Ошибка при удалении подписчика.';
$_lang['sendex_subscriber_err_save'] = 'Ошибка при сохранении подписчика.';
$_lang['sendex_subscriber_err_email'] = 'Не указан email пописчика.';

$_lang['sendex_subscriber_id'] = 'id';
$_lang['sendex_subscriber_username'] = 'Псевдоним';
$_lang['sendex_subscriber_fullname'] = 'Полное имя';
$_lang['sendex_subscriber_email'] = 'Email';
$_lang['sendex_subscriber_remove'] = 'Удалить подписчика';
$_lang['sendex_subscriber_remove_confirm'] = 'Вы действительно хотите отписать пользователя от этой подписки?';

$_lang['sendex_queues'] = 'Очередь писем';
$_lang['sendex_queue'] = 'Письмо';
$_lang['sendex_queue_intro'] = 'Здесь вы управляете очередью рассылки. Вы можете добавлять, удалять и отправлять письма.';

$_lang['sendex_queue_err_nf'] = 'Письмо не найдено.';
$_lang['sendex_queue_err_ns'] = 'Письмо не указано.';

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
$_lang['sendex_queue_remove'] = 'Удалить письмо';
$_lang['sendex_queue_remove_confirm'] = 'Вы действительно хотите удалить это письмо?';

$_lang['sendex_err_auth_req'] = 'Вы должны быть авторизованы для работы с подписками.';

$_lang['sendex_subscribe_intro'] = 'Вы можете подписаться на рассылку "[[+name]]"!';
$_lang['sendex_unsubscribe_intro'] = 'Вы уже подписаны на рассылку "[[+name]]". Хотите отписаться?';

$_lang['sendex_subscribe_activate_subject'] = 'Подтвердите ваш email!';
$_lang['sendex_subscribe_err_already'] = 'Этот email уже подписан на рассылку.';
$_lang['sendex_subscribe_err_email_wrong'] = 'Неверный email.';
$_lang['sendex_subscribe_err_email_ns'] = 'Нужно указать email.';