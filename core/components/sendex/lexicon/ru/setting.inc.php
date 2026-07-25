<?php

$_lang['area_sendex_main'] = 'Основные';

$_lang['setting_sendex_export_fields'] = 'Поля для экспорта';
$_lang['setting_sendex_export_fields_desc'] = 'Введите данные через запятую. Доступные значения:'
    . ' id,user_id,email,username,fullname,phone,mobilephone';
$_lang['setting_sendex_hide_export_button'] = 'Скрыть кнопку экспорта';
$_lang['setting_sendex_hide_export_button_desc'] = 'Отключает кнопку экспорта списка email адресов в рассылке';
$_lang['setting_sendex_confirm_email'] = 'Требовать подтверждение email гостя';
$_lang['setting_sendex_confirm_email_desc'] = 'Если включено, гость подтверждает подписку по ссылке из письма. '
    . 'Если выключено, email сразу попадает в подписчики (выше риск мусорных адресов).';
$_lang['setting_sendex_confirm_rate_limit'] = 'Лимит confirm-писем (секунды)';
$_lang['setting_sendex_confirm_rate_limit_desc'] = 'Не более одного confirm-письма на адрес за окно. '
    . '0 отключает ограничение.';
$_lang['setting_sendex_csrf_protect'] = 'Включить CSRF-защиту на фронтенде';
$_lang['setting_sendex_csrf_protect_desc'] = 'Добавляет CSRF-токен в формы подписки/отписки и проверяет его в snippet/AJAX.';
