# Sendex

Sendex runs email newsletters in MODX Revolution: subscribers, a send queue, and a front-end form.

## Features

- Newsletters and subscribers in the manager
- Add users one by one or from a MODX user group (active and unblocked accounts only)
- Guest subscribe with email confirmation; authenticated users subscribe directly
- Queue letters; send one, send all, or flush via cron
- Export subscriber emails
- English and Russian lexicons

## Install

Install the transport package through Package Management, or build one from `_build/`.

## Front-end

```
[[!Sendex? &id=`1`]]
```

| Property | Default | Purpose |
| --- | --- | --- |
| `id` | — | Newsletter ID |
| `showInactive` | `false` | Show the form when the newsletter is disabled |
| `msgClass` | `active` | CSS class for `[[+class]]` |
| `tplSubscribeAuth` | `tpl.Sendex.subscribe.auth` | Chunk for logged-in users |
| `tplSubscribeGuest` | `tpl.Sendex.subscribe.guest` | Chunk for guests |
| `tplUnsubscribe` | `tpl.Sendex.unsubscribe` | Unsubscribe chunk |
| `tplActivate` | `tpl.Sendex.activate` | Confirmation email chunk |

## Cron

Process the queue from the site root (or adjust the path):

```
php core/components/sendex/cron/send.php
```

Set the batch size with the `sendex_queue_limit` system setting (default `100`).

## Docs

- [Documentation](https://docs.modx.pro/komponentyi/sendex)
- [Issues](https://github.com/modx-pro/Sendex/issues)

## License

GPL-2.0. See [LICENSE](LICENSE).
