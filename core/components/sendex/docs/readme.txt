--------------------
Sendex
--------------------
Author: Vasily Naumkin <bezumkin@yandex.ru>
--------------------

Sendex runs email newsletters in MODX Revolution: subscribers, a send
queue, and a front-end form.

Features:
- Newsletters and subscribers in the manager
- Add users one by one or from a MODX user group (active and unblocked
  accounts only)
- Guest subscribe with email confirmation; authenticated users subscribe
  directly
- Queue letters; send one, send all, or flush via cron
- Export subscriber emails

Front-end snippet:
[[!Sendex? &id=`1`]]

Cron (from the site root, or adjust the path):
php core/components/sendex/cron/send.php

Documentation:
https://docs.modx.pro/komponentyi/sendex

Issues:
https://github.com/modx-pro/Sendex/issues

License: GPL-2.0
