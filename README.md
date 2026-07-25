# Sendex

Sendex runs email newsletters in MODX Revolution: subscribers, a send queue, and a front-end form.

**Requirements:** PHP 7.4–8.4, MODX Revolution 2.8+ or 3.x (ExtJS manager).

## MODX 3 compatibility

Sendex keeps global `sx*` xPDO models (not `MODX\Revolution\sx*`). Verified on MODX **3.2.0-pl** (2026-07-25): transport install, namespace `assets_path`, mgr menu (`namespace` + `action`), Phinx migrations, `sx*` class map, connector bootstrap, mgr processors, ExtJS UI.

Build/package notes:

- `_build/build.transport.php` registers `PKG_ASSETS_PATH` and skips `build.model.php` on MODX 3 (xPDO 3 schema generator would rewrite maps to `sendex\sx*`).
- Mgr menu uses legacy `modAction` on MODX 2 and `modMenu.action` + `namespace` on MODX 3.
- `core/components/sendex/bootstrap.php` — shared init for connector, mgr, and cron (autoload + processor base aliases).
- `sxModxCompat` / `sxUserProfile` — MODX 2/3 boundary for mail, parser, registry, and user/profile placeholders.

Not covered yet (see [#74](https://github.com/modx-pro/Sendex/issues/74)): new non-ExtJS admin UI, CI matrix MODX 2.8 + 3.x.

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
| `confirmEmail` | system `sendex_confirm_email` (default `1`) | Guest flow: `1` = confirm link by email; `0` = subscribe immediately |
| `loadJs` | `1` | Register `assets/components/sendex/js/web/sendex.js` for AJAX forms |
| `widgetKey` | *(empty)* | Optional key when several `[[!Sendex]]` widgets share one page; must match hidden `sendex_widget_key` in the form |

When `confirmEmail` is off, guest addresses are saved without a confirmation message. Typos and spam signups are harder to catch; use only when you accept that tradeoff.

Several widgets on one page need distinct `&widgetKey=` values (for example `instant` vs `confirm`) so AJAX POST is handled only by the matching snippet instance. Email confirm/unsubscribe links omit `sendex_widget_key`; keep one snippet on the landing page without `widgetKey` to handle those URLs.

### AJAX subscribe / unsubscribe (#42)

Default chunks include `data-sendex-*` hooks; the snippet registers `sendex.js`, which submits forms via `fetch` and swaps the widget without a full page reload. The server answers with JSON `{success, message, html}`.

Minimal page markup:

```html
[[!Sendex? &id=`1`]]
```

Guest chunk structure (for custom templates):

```html
<div class="sendex-widget" data-sendex-widget>
  <p class="sendex-message [[+class]]" data-sendex-message><b>[[+message]]</b></p>
  <form action="" method="post" data-sendex-form>
    <input type="hidden" name="sx_action" value="subscribe">
    <input type="email" name="email" required>
    <button type="submit">Subscribe</button>
  </form>
</div>
```

Set `&loadJs=`0`` if you ship your own JS but keep the same JSON contract (`X-Requested-With: XMLHttpRequest` or `ajax=1`).

### Guest email vs existing User (#39)

Policy: **merge**, not block.

1. Anonymous confirm / `subscribe()` resolves `modUser` by profile email and stores `user_id` (see `#54`).
2. Unique key `(newsletter_id, email)` prevents a guest+user duplicate row.
3. If a guest subscribed first and the account is created later, the Sendex plugin merges on `OnUserActivate` and `OnUserSave`: guest rows with that email get `user_id` set. Merge does not run on `OnBeforeUserActivate` (cancelled activation must not attach guests). Reinstall/upgrade the package so the plugin events are registered.

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

Unit tests use lightweight MODX/xPDO stubs (no MODX install). Coverage includes subscription/confirm flows, queue lifecycle events, queue claim, group subscribe, ACL contracts, frontend snippet contracts, and mail header sanitization. CI runs `php -l`, PHPUnit, and PHPCS on PHP 7.4–8.4; one PHP 8.2 job publishes Clover coverage as an artifact. Remaining integration gaps are tracked in issue [#103](https://github.com/modx-pro/Sendex/issues/103).

## Plugin events

Event names are registered in the transport package (`BUILD_EVENT_UPDATE`); reinstall or upgrade so they appear under System Events. `invokeEvent` by name works even before that.

### Subscribe / unsubscribe

| Event | When | Cancel |
| --- | --- | --- |
| `sxOnBeforeSubscribe` | Before creating a subscriber | Yes |
| `sxOnSubscribe` | After a successful save | No |
| `sxOnBeforeUnsubscribe` | Before removing a subscriber | Yes |
| `sxOnUnsubscribe` | After a successful remove | No |

Params: `newsletter`, `newsletter_id`, `user_id`, `email`, `subscriber`, `source` (`snippet`\|`ajax`\|`confirm`\|`mgr`\|`guest`). Unsubscribe also passes `code`.

### Queue lifecycle

| Event | When | Cancel |
| --- | --- | --- |
| `sxOnBeforeAddQueues` | Before building queue rows | Yes (abort batch) |
| `sxOnAddQueues` | After rows created | No |
| `sxOnBeforeQueueSend` | Before sending one row (after claim) | Yes (skip, no requeue); may mutate `message` |
| `sxOnQueueSend` | After successful send | No |
| `sxOnQueueSendFailed` | After mail failure + requeue | No |
| `sxOnQueueFlushComplete` | After `flush` batch | No |

### Listens to (plugin.sendex.php)

| MODX event | Behavior |
| --- | --- |
| `OnManagerPageInit` | Mgr CSS |
| `OnUserActivate` / `OnUserSave` | Merge guest rows onto user by email |
| `OnBeforeUserActivate` | Not used (cancelled activation must not merge) |

Cancel a Before event with `$modx->event->output('error message');`. Already-subscribed / missing or mismatched code paths are no-ops and do not fire subscribe events.

## Docs

- [Documentation](https://docs.modx.pro/komponentyi/sendex)
- [Issues](https://github.com/modx-pro/Sendex/issues)

## License

GPL-2.0. See [LICENSE](LICENSE).
