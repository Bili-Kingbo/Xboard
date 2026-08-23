# Internal free mode

This fork enables an internal-company workflow in which users select their
node permission group during registration and do not purchase a plan.

## Configuration

```dotenv
INTERNAL_FREE_MODE=true
INTERNAL_FREE_DEFAULT_USER_TRANSFER_GB=0
SETTINGS_CACHE_STORE=redis
```

- Create the required identity groups, set each group's traffic allowance, and
  assign nodes to those groups before opening registration.
- `INTERNAL_FREE_DEFAULT_USER_TRANSFER_GB` is the personal allowance assigned
  to each new user. The default is `0`, so the selected group's allowance is
  used unless an administrator gives that user a larger personal allowance.
- `SETTINGS_CACHE_STORE` remains `redis` in production. `array` can be used for
  isolated local tests that do not run Redis.

Run the database migration before enabling the mode:

```bash
php artisan migrate --force
```

The effective allowance is always:

```text
max(user personal allowance, identity-group allowance)
```

For example, a user with 100 GB in a 500 GB group receives 500 GB. A user with
750 GB in that same group receives 750 GB.

When internal free mode is enabled:

- Registration requires a valid server-group ID.
- New users receive the selected group, no plan, no expiry, and the configured
  personal default allowance.
- Default login and magic-link redirects go to the internal Dashboard.
- User plan, order, coupon, gift-card, commission, and payment callback routes
  are not registered. The matching admin finance routes are also disabled.
- The Dashboard shows its subscription action only when the selected group has
  at least one available node. The profile wallet and purchase navigation are
  removed.
- Generated subscription URLs end with `?flag=meta`, and unflagged internal
  subscription requests also default to Clash Meta YAML output.
- The user portal uses a shared internal-technology visual system across the
  Dashboard, node status, profile, tickets, and traffic pages. The Dashboard
  derives the greeting name from the capitalized email prefix, contains the
  Meta subscription console, and displays the latest company announcement.
- The former user knowledge/documentation navigation is not registered in
  internal mode; operational announcements live directly on the Dashboard.
- The admin landing page is identity-group management. Group traffic is edited
  there; personal traffic and group membership are edited under user management.
- If upstream user/admin bundles are refreshed, run both patch scripts:

```bash
node scripts/patch-internal-free-theme.mjs
node scripts/patch-internal-access-ui.mjs
node scripts/patch-internal-dashboard.mjs
```

For a local visual preview only, seed clearly non-routable example nodes:

```bash
php artisan db:seed --class=Database\\Seeders\\InternalPreviewNodeSeeder
```

The preview seeder refuses to run outside local internal-free mode.

After changing `INTERNAL_FREE_MODE`, clear cached configuration/routes or
restart the Xboard container before verifying the route surface.
