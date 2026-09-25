# Internal free mode

This fork enables an internal-company workflow in which users select their
node permission group during registration and do not purchase a plan.

## Configuration

```dotenv
INTERNAL_FREE_MODE=true
INTERNAL_FREE_DEFAULT_USER_TRANSFER_GB=0
SETTINGS_CACHE_STORE=redis
```

- Create the required identity groups and assign nodes to those groups before
  opening registration. Identity groups control node access only.
- `INTERNAL_FREE_DEFAULT_USER_TRANSFER_GB` is the personal allowance assigned
  to each new user. `0` means unlimited traffic and is the default.
- `SETTINGS_CACHE_STORE` remains `redis` in production. `array` can be used for
  isolated local tests that do not run Redis.

Run the database migration before enabling the mode:

```bash
php artisan migrate --force
```

User traffic semantics are:

```text
0 = unlimited traffic
positive number = personal traffic limit in GB
```

Identity groups never change a user's traffic allowance.

When internal free mode is enabled:

- Registration requires a valid server-group ID.
- The registration identity-group picker is a Naive UI `Select` bound to the
  same form card as the other fields, so it shares their height, border, focus
  ring and dropdown styling. It renders only when `registration_groups` is not
  empty, and the group is only required when the picker is offered.
- New users receive the selected group, no plan, no expiry, and unlimited
  traffic by default.
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
- Both the user portal and the admin panel keep one surface palette in light
  and dark mode. Naive UI popovers, menus, and selects follow the portal ink
  palette, and the admin's bundled markdown editor plus the external-node
  import overlay follow the shadcn tokens instead of staying white.
- The former user knowledge/documentation navigation is not registered in
  internal mode; operational announcements live directly on the Dashboard.
- The admin landing page is identity-group management. Personal traffic and
  group membership are edited under user management; entering `0` means
  unlimited traffic.
- Machine management, plugin management, and theme configuration are not
  exposed. Internal mode does not load plugins, register plugin schedules, or
  install default plugins; administrators continue to manage nodes and
  identity-group access directly while the fixed portal theme remains active.
- Administrators can enable a daily traffic reset under node settings. When
  enabled, used traffic is cleared once per day at 00:00 in `Asia/Shanghai`,
  with a persistent date marker preventing duplicate resets.
- External node import accepts remote subscriptions, Clash Meta YAML/JSON,
  Base64 subscriptions, and share links for every protocol that the panel can
  deliver through the default Clash Meta subscription: Shadowsocks, VMess,
  VLESS, Trojan, Hysteria 1/2, TUIC, AnyTLS, SOCKS, HTTP, Naive, and Mieru.
  VLESS accepts both the standard URI authority and Shadowrocket's Base64
  authority form, including `remarks`, `peer`, XTLS, Reality, and fingerprint
  parameters.
  Imported client fields are stored statically and merged into subscriptions
  without involving Xboard Node. External nodes remain in the independent
  `v2_special_server` table and never enter the native `v2_server` lifecycle,
  health checks, traffic accounting, deployment, or user-sync flows.
- External nodes are listed in the node management table together with native
  nodes, tagged `外部导入`, and edited or deleted from the same row actions
  menu. The `导入外部节点` button sits next to `添加节点`. External rows are
  never sent to native node endpoints.
- Nodes (native and external) can be delivered to an identity group, to
  individually selected users, or both. The assignment lives in the `user_ids`
  column of `v2_server` / `v2_special_server`; users outside the group still
  receive a node that is explicitly assigned to them.
- The node table shows the `权限组` column by default; individually assigned
  users appear there as `@name` badges.
- Client routing rules are edited under `客户端分流` and stored in the
  `client_routing_profiles` setting. The separate Clash Meta template remains
  under `系统配置 → 订阅模板`. In internal mode, Clash Meta compiles the saved
  rules into policy groups after filtering nodes for the subscribing user.
- Native and external nodes have independent `client_routing_profile_ids`
  fields. OpenAI/Codex, Claude, Shedio, and international policy groups list
  the general proxy group first, then their tagged nodes as manual choices.
  Tagged nodes are never selected automatically. Domestic traffic follows its
  saved direct/proxy setting; nonmatching traffic follows the international
  group. The admin preview shows the resulting groups and rules for one user
  without exposing node connection credentials.
- If upstream user/admin bundles are refreshed, run both patch scripts:

```bash
node scripts/patch-internal-free-theme.mjs
node scripts/patch-internal-access-ui.mjs
node scripts/patch-internal-dashboard.mjs
node scripts/patch-internal-node-admin.mjs
```

For a local visual preview only, seed clearly non-routable example nodes:

```bash
php artisan db:seed --class=Database\\Seeders\\InternalPreviewNodeSeeder
```

The preview seeder refuses to run outside local internal-free mode.

After changing `INTERNAL_FREE_MODE`, clear cached configuration/routes or
restart the Xboard container before verifying the route surface.
