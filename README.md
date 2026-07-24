# Sendex

Sendex runs email newsletters in MODX Revolution: subscribers, a send queue, and a front-end form.

**Requirements:** PHP 7.4–8.4, MODX Revolution 2.x.

## Features

- Newsletters and subscribers in the manager
- Add users one by one or from a MODX user group (active and unblocked accounts only)
- Guest subscribe with email confirmation; authenticated users subscribe directly
- Guest rows with the same email merge onto a new/activated `modUser` (no second subscriber row)
- Queue letters; send one, send all, or flush via cron
- Export subscriber emails
- English and Russian lexicons

## Install

Install the transport package through Package Management, or build one from `_build/`.

### Database migrations (Phinx)

Schema changes ship as Phinx migrations (same pattern as MiniShop3):

- Config: `core/components/sendex/phinx.php`
- Migrations: `core/components/sendex/migrations/`
- Metadata table: `{table_prefix}sendex_migrations`

Before building the transport package, install runtime deps into the component:

```
cd core/components/sendex
composer install --no-dev
cd ../../..
php _build/build.transport.php
```

On install/upgrade the `migrations` resolver runs `phinx migrate`. CLI on a live site:

```
cd core/components/sendex
composer install --no-dev
vendor/bin/phinx migrate -c phinx.php
```

Root `composer.json` remains for PHPUnit/phpcs only.

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

### Guest email vs existing User (#39)

Policy: **merge**, not block.

1. Anonymous confirm / `subscribe()` resolves `modUser` by profile email and stores `user_id` (see `#54`).
2. Unique key `(newsletter_id, email)` prevents a guest+user duplicate row.
3. If a guest subscribed first and the account is created later, the Sendex plugin merges on `OnUserActivate`, `OnBeforeUserActivate`, and `OnUserSave`: guest rows with that email get `user_id` set. Reinstall/upgrade the package so the plugin events are registered.

Logged-in `isSubscribed($userId)` also matches a still-guest row by profile email, so the form shows unsubscribe without waiting for merge.

### Unsubscribe from email (#56)

The page must call `[[!Sendex? &id=`…`]]` (any newsletter id is fine). Query params:

| Param | Required | Meaning |
| --- | --- | --- |
| `sx_action` | yes | `unsubscribe` |
| `code` | yes | `sxSubscriber.code` |
| `newsletter_id` | no | Same as newsletter id; avoids confusion with MODX resource `id`. Snippet resolves the owner newsletter from `code` if the snippet `&id` differs. |

Default letter template links to `site_start` with `sx_action`, `newsletter_id`, and `code`.

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
composer test:coverage   # needs phpdbg
```

Unit tests use lightweight MODX/xPDO stubs (no MODX install). Covered: `sxNewsletter` (100%), subscriber create/remove processors. CI runs `php -l`, PHPUnit, and phpcs on PHP 7.4–8.4.

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
