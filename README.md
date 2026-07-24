# Sendex

Sendex runs email newsletters in MODX Revolution: subscribers, a send queue, and a front-end form.

**Requirements:** PHP 7.4–8.4, MODX Revolution 2.x.

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

## Tests

```
composer install
composer test
```

Unit tests use lightweight MODX/xPDO stubs (no MODX install). CI runs `php -l` and PHPUnit on PHP 7.4–8.4.

## Plugin events

Fired from `sxNewsletter::subscribe()` / `unSubscribe()` (front-end and manager). Event names are registered in the transport package (`BUILD_EVENT_UPDATE`); reinstall or upgrade the package so they appear under System Events in the manager. `invokeEvent` by name works even before that.

| Event | When | Cancel |
| --- | --- | --- |
| `sxOnBeforeSubscribe` | Before creating a subscriber | Yes |
| `sxOnSubscribe` | After a successful save | No |
| `sxOnBeforeUnsubscribe` | Before removing a subscriber | Yes |
| `sxOnUnsubscribe` | After a successful remove | No |

Params: `newsletter`, `newsletter_id`, `user_id`, `email`, `subscriber` (object after create / before remove). Unsubscribe also passes `code`.

Cancel a Before event with `$modx->event->output('error message');`. Callers get that string back from `subscribe()` / `unSubscribe()` (`true` on success, `false` on validation/save failure). Already-subscribed / missing or mismatched code paths are no-ops and do not fire events. `unSubscribe` only removes a subscriber that belongs to this newsletter.

### Manual check

1. Subscribe (auth user / guest confirm) → `sxOnBeforeSubscribe` + `sxOnSubscribe`.
2. Plugin `output()` on Before → subscribe aborted; message shown in mgr/front.
3. Unsubscribe front + mgr remove → Before + After; cancel → mgr `failure`, not silent success.
4. Unsubscribe with a code from another newsletter → no remove, no events.

## Docs

- [Documentation](https://docs.modx.pro/komponentyi/sendex)
- [Issues](https://github.com/modx-pro/Sendex/issues)

## License

GPL-2.0. See [LICENSE](LICENSE).
