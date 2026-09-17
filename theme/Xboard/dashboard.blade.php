<!doctype html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no" />
  <title>{{$title}}</title>
  <script type="module" crossorigin src="/theme/{{$theme}}/assets/umi.js?v=internal-dashboard-15"></script>
  <style id="internal-tech-theme">
    :root {
      --portal-navy: #07152d;
      --portal-blue: #2563eb;
      --portal-cyan: #06b6d4;
      --portal-surface: rgba(255, 255, 255, .92);
      --portal-border: rgba(148, 163, 184, .22);
    }

    html, body, #app { min-height: 100%; }
    body {
      margin: 0;
      color: #0f172a;
      background:
        linear-gradient(rgba(37, 99, 235, .025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(37, 99, 235, .025) 1px, transparent 1px),
        #f3f7fb;
      background-size: 32px 32px;
      font-family: Inter, ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .n-layout, .n-layout-content { background: transparent !important; }
    .n-layout-sider {
      border-right: 1px solid rgba(103, 232, 249, .12) !important;
      background: linear-gradient(180deg, #07152d 0%, #0b1f3f 58%, #0b2944 100%) !important;
      box-shadow: 18px 0 50px rgba(15, 23, 42, .08);
    }
    aside.n-layout-sider,
    aside.n-layout-sider > .n-scrollbar,
    aside.n-layout-sider .n-scrollbar-container,
    aside.n-layout-sider .n-scrollbar-content,
    aside.n-layout-sider .h-15,
    aside.n-layout-sider .side-menu {
      background: linear-gradient(180deg, #07152d 0%, #0b1f3f 58%, #0b2944 100%) !important;
    }
    aside.n-layout-sider .h-15 {
      border-bottom: 1px solid rgba(56, 189, 248, .1);
    }
    aside.n-layout-sider .title-text { color: #67e8f9 !important; }
    .n-layout.n-layout--static-positioned > .n-layout-scroll-container {
      min-height: 100dvh;
      background: #07152d !important;
    }
    aside.n-layout-sider { min-height: 100dvh !important; }
    .n-layout-sider .n-menu {
      --n-color: transparent !important;
      --n-item-color-active: rgba(34, 211, 238, .12) !important;
      --n-item-color-active-hover: rgba(34, 211, 238, .16) !important;
      --n-item-text-color: #94a3b8 !important;
      --n-item-text-color-hover: #e2e8f0 !important;
      --n-item-text-color-active: #67e8f9 !important;
      --n-item-icon-color: #64748b !important;
      --n-item-icon-color-hover: #cbd5e1 !important;
      --n-item-icon-color-active: #22d3ee !important;
      --n-arrow-color: #64748b !important;
      padding: 8px 10px;
    }
    .n-layout-sider .n-menu-item,
    .n-layout-sider .n-submenu-title { margin: 4px 0; border-radius: 10px; }
    .n-layout-sider .n-menu-item-group-title {
      color: #475569 !important;
      font-size: 10px !important;
      font-weight: 700;
      letter-spacing: .14em;
      text-transform: uppercase;
    }
    .n-layout-header {
      border-bottom: 1px solid rgba(148, 163, 184, .18) !important;
      background: rgba(255, 255, 255, .78) !important;
      backdrop-filter: blur(18px);
    }
    .n-breadcrumb { font-size: 12px; }
    .n-card {
      border-color: var(--portal-border) !important;
      border-radius: 18px !important;
      background: var(--portal-surface) !important;
      box-shadow: 0 12px 34px rgba(15, 23, 42, .055);
    }
    .n-card > .n-card-header { padding: 20px 22px 14px; }
    .n-card > .n-card__content { padding: 18px 22px 22px; }
    .n-button { border-radius: 10px !important; font-weight: 600; }
    .n-button.n-button--primary-type {
      border: 0 !important;
      background: linear-gradient(135deg, var(--portal-cyan), var(--portal-blue)) !important;
      box-shadow: 0 8px 20px rgba(37, 99, 235, .18);
    }
    .subscription-console .n-button:not(.n-button--primary-type) {
      color: #cbd5e1 !important;
      border-color: rgba(148, 163, 184, .32) !important;
      background: rgba(15, 23, 42, .5) !important;
    }
    .internal-tech-dashboard {
      padding-bottom: 0 !important;
      overflow: visible;
      border: 0;
      border-radius: 0;
      background: transparent;
      box-shadow: none;
    }
    .internal-tech-dashboard .tech-hero {
      border-radius: 0 !important;
      border-bottom: 1px solid rgba(56, 189, 248, .12);
      box-shadow: none !important;
    }
    .internal-tech-dashboard .subscription-console {
      padding: 6px 0 6px 28px !important;
      border: 0 !important;
      border-left: 1px solid rgba(103, 232, 249, .2) !important;
      border-radius: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
      backdrop-filter: none !important;
    }
    .internal-tech-dashboard .tech-content-grid {
      display: block !important;
      margin-top: 0 !important;
    }
    .internal-tech-dashboard .tech-content-grid > article.tech-card {
      border: 0 !important;
      border-radius: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
    }
    .internal-tech-dashboard .tech-content-grid > aside { display: none !important; }
    .internal-tech-dashboard .tech-card-head {
      padding-top: 24px;
      background: rgba(3, 12, 26, .28);
    }
    .internal-tech-dashboard .announcement-body { min-height: 220px; }

    /* Continuous node list: no detached cards */
    .internal-node-status .node-status-hero {
      padding: 8px 4px 26px !important;
      border: 0 !important;
      border-bottom: 1px solid rgba(56, 189, 248, .15) !important;
      border-radius: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
    }
    .internal-node-status .node-status-title { font-size: 30px; }
    .internal-node-status .node-card-grid {
      gap: 0 !important;
      margin-top: 0 !important;
      border-bottom: 1px solid rgba(56, 189, 248, .12);
      background: transparent !important;
    }
    .internal-node-status .node-tech-card {
      min-height: 76px;
      padding: 16px 4px !important;
      border: 0 !important;
      border-bottom: 1px solid rgba(56, 189, 248, .1) !important;
      border-radius: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
    }
    .internal-node-status .node-tech-card:last-child { border-bottom: 0 !important; }
    .internal-node-status .node-tech-card::before { display: none !important; }
    .internal-node-status .node-meta {
      border-left-color: rgba(56, 189, 248, .1) !important;
    }
    .n-input, .n-input-number, .n-select .n-base-selection {
      border-radius: 10px !important;
      background: rgba(248, 250, 252, .9) !important;
    }
    .n-data-table {
      overflow: hidden;
      border: 1px solid var(--portal-border);
      border-radius: 16px;
      background: rgba(255, 255, 255, .94);
      box-shadow: 0 12px 32px rgba(15, 23, 42, .05);
    }
    .n-data-table .n-data-table-th {
      color: #475569;
      background: #f8fafc !important;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .04em;
    }
    .n-data-table .n-data-table-td { border-bottom-color: #eef2f7 !important; }
    .n-alert { border-radius: 14px !important; }
    .n-tag { border-radius: 999px !important; }
    ::selection { color: #fff; background: #0891b2; }

    /* Full dark internal-portal surface */
    html, body, #app {
      color: #dbeafe !important;
      background:
        radial-gradient(circle at 78% 0%, rgba(8, 145, 178, .13), transparent 28%),
        radial-gradient(circle at 22% 100%, rgba(37, 99, 235, .11), transparent 30%),
        linear-gradient(rgba(56, 189, 248, .035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(56, 189, 248, .035) 1px, transparent 1px),
        #030712 !important;
      background-size: auto, auto, 32px 32px, 32px 32px, auto !important;
    }
    .n-layout, .n-layout-content, .n-layout-scroll-container {
      color: #dbeafe !important;
      background: transparent !important;
    }
    .n-layout-header {
      color: #cbd5e1 !important;
      border-bottom-color: rgba(56, 189, 248, .12) !important;
      background: rgba(3, 7, 18, .82) !important;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .18);
    }
    article.flex.flex-col.flex-1.overflow-hidden > header.flex.items-center,
    header.flex.items-center.bg-white {
      color: #cbd5e1 !important;
      border-bottom: 1px solid rgba(56, 189, 248, .1) !important;
      background: rgba(3, 7, 18, .9) !important;
    }
    article.flex.flex-col.flex-1.overflow-hidden > section.flex-1,
    article.flex.flex-col.flex-1.overflow-hidden .cus-scroll-y {
      background:
        radial-gradient(circle at 82% 0%, rgba(8, 145, 178, .1), transparent 26%),
        linear-gradient(rgba(56, 189, 248, .025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(56, 189, 248, .025) 1px, transparent 1px),
        #030712 !important;
      background-size: auto, 32px 32px, 32px 32px, auto !important;
    }
    .n-layout-header .n-button,
    .n-layout-header .n-icon { color: #cbd5e1 !important; }
    .n-breadcrumb, .n-breadcrumb a, .n-breadcrumb-item,
    .n-breadcrumb-item__link, .n-breadcrumb-item__separator {
      color: #64748b !important;
    }
    .n-card {
      --n-color: rgba(9, 20, 38, .94) !important;
      --n-title-text-color: #f1f5f9 !important;
      --n-text-color: #cbd5e1 !important;
      color: #cbd5e1 !important;
      border-color: rgba(56, 189, 248, .13) !important;
      background: linear-gradient(145deg, rgba(9, 20, 38, .97), rgba(8, 28, 48, .92)) !important;
      box-shadow: 0 18px 45px rgba(0, 0, 0, .22), inset 0 1px rgba(255, 255, 255, .025) !important;
    }
    .n-card-header__main, .n-card__content, .n-card__footer { color: #cbd5e1 !important; }
    .n-input, .n-input-number, .n-select .n-base-selection {
      --n-color: rgba(15, 30, 52, .88) !important;
      --n-color-focus: rgba(15, 36, 62, .96) !important;
      --n-text-color: #e2e8f0 !important;
      --n-placeholder-color: #64748b !important;
      --n-border: 1px solid rgba(71, 85, 105, .55) !important;
      --n-border-hover: 1px solid rgba(34, 211, 238, .55) !important;
      --n-border-focus: 1px solid #22d3ee !important;
      color: #e2e8f0 !important;
      background: rgba(15, 30, 52, .88) !important;
      box-shadow: inset 0 1px rgba(255, 255, 255, .025);
    }
    .n-input input, .n-input__input-el, .n-input__textarea-el { color: #e2e8f0 !important; }
    .n-data-table {
      --n-td-color: rgba(9, 20, 38, .94) !important;
      --n-th-color: rgba(12, 29, 51, .98) !important;
      --n-td-color-hover: rgba(14, 42, 68, .94) !important;
      --n-border-color: rgba(56, 189, 248, .1) !important;
      --n-th-text-color: #94a3b8 !important;
      --n-td-text-color: #cbd5e1 !important;
      border-color: rgba(56, 189, 248, .12) !important;
      background: rgba(9, 20, 38, .94) !important;
      box-shadow: 0 18px 40px rgba(0, 0, 0, .2) !important;
    }
    .n-data-table .n-data-table-th { color: #94a3b8 !important; background: #0c1d33 !important; }
    .n-data-table .n-data-table-td { color: #cbd5e1 !important; background: #091426 !important; }
    .n-alert {
      --n-color: rgba(8, 47, 73, .65) !important;
      --n-border: 1px solid rgba(34, 211, 238, .18) !important;
      --n-title-text-color: #e0f2fe !important;
      --n-content-text-color: #94a3b8 !important;
    }
    .n-modal, .n-drawer-content, .n-dropdown-menu {
      color: #cbd5e1 !important;
      border-color: rgba(56, 189, 248, .13) !important;
      background: #091426 !important;
    }
    .n-form-item-label, label { color: #94a3b8 !important; }
    .n-empty__description { color: #64748b !important; }
    .n-pagination { --n-item-text-color: #94a3b8 !important; }
    .n-button:not(.n-button--primary-type) {
      color: #cbd5e1 !important;
      border-color: rgba(71, 85, 105, .6) !important;
      background: rgba(15, 30, 52, .82) !important;
    }
    .n-button:not(.n-button--primary-type):hover {
      color: #67e8f9 !important;
      border-color: rgba(34, 211, 238, .58) !important;
      background: rgba(14, 50, 76, .9) !important;
    }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #030712; }
    ::-webkit-scrollbar-thumb { border-radius: 999px; background: #1e3a5f; }

    /* Calm, continuous internal workspace */
    :root {
      --portal-blue: #4f8cff;
      --portal-cyan: #68b7c8;
    }
    html, body, #app {
      background: #080c13 !important;
    }
    article.flex.flex-col.flex-1.overflow-hidden > section.flex-1,
    article.flex.flex-col.flex-1.overflow-hidden .cus-scroll-y {
      background: linear-gradient(180deg, #0b1019 0%, #080c13 100%) !important;
    }
    .n-layout-sider,
    aside.n-layout-sider,
    aside.n-layout-sider > .n-scrollbar,
    aside.n-layout-sider .n-scrollbar-container,
    aside.n-layout-sider .n-scrollbar-content,
    aside.n-layout-sider .h-15,
    aside.n-layout-sider .side-menu {
      background: #0d131d !important;
      box-shadow: none !important;
    }
    aside.n-layout-sider {
      border-right: 1px solid rgba(148, 163, 184, .11) !important;
    }
    aside.n-layout-sider .h-15 {
      border-bottom: 1px solid rgba(148, 163, 184, .09) !important;
    }
    aside.n-layout-sider .title-text { color: #dce5f3 !important; }
    .n-layout-sider .n-menu {
      --n-item-color-active: transparent !important;
      --n-item-color-active-hover: rgba(255, 255, 255, .035) !important;
      --n-item-text-color: #758196 !important;
      --n-item-text-color-hover: #d5deeb !important;
      --n-item-text-color-active: #e8eef8 !important;
      --n-item-icon-color: #657085 !important;
      --n-item-icon-color-active: #74a1f7 !important;
      padding: 12px 14px !important;
    }
    .n-layout-sider .n-menu-item,
    .n-layout-sider .n-submenu-title {
      position: relative;
      margin: 2px 0 !important;
      border-radius: 0 !important;
    }
    .n-layout-sider .n-menu-item--selected::before {
      content: "";
      position: absolute;
      left: -14px;
      top: 10px;
      bottom: 10px;
      width: 2px;
      border-radius: 2px;
      background: #74a1f7;
    }
    .n-layout-sider .n-menu-item-group-title {
      color: #4f596b !important;
      letter-spacing: .1em;
    }
    .n-layout-header,
    article.flex.flex-col.flex-1.overflow-hidden > header.flex.items-center,
    header.flex.items-center.bg-white {
      background: rgba(8, 12, 19, .94) !important;
      box-shadow: none !important;
    }
    .internal-tech-dashboard,
    .internal-node-status {
      width: 100% !important;
      max-width: 1040px !important;
      box-sizing: border-box;
    }
    .internal-tech-dashboard .tech-hero {
      padding: 30px 0 26px !important;
      background: transparent !important;
    }
    .internal-tech-dashboard .tech-orb { display: none !important; }
    .internal-tech-dashboard .tech-eyebrow,
    .internal-node-status .node-status-eyebrow { color: #7694c8 !important; }
    .internal-tech-dashboard .tech-greeting,
    .internal-node-status .node-status-title { color: #edf2f9 !important; }
    .internal-tech-dashboard .tech-subtitle,
    .internal-node-status .node-status-subtitle { color: #7f8ba0 !important; }
    .internal-tech-dashboard .tech-pill {
      border-color: rgba(148, 163, 184, .12) !important;
      background: transparent !important;
      color: #9aa6b8 !important;
    }
    .internal-tech-dashboard .tech-dot {
      background: #65a88d !important;
      box-shadow: none !important;
    }
    .internal-tech-dashboard .tech-card-head {
      padding: 24px 0 14px !important;
      background: transparent !important;
    }
    .internal-tech-dashboard .announcement-body {
      position: relative;
      min-height: 240px;
      padding: 26px 0 70px !important;
      color: #adb8c8 !important;
    }
    .internal-tech-dashboard .announcement-title { color: #e9eef7 !important; }
    .announcement-carousel {
      position: absolute;
      left: 0;
      right: 0;
      bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
    }
    .announcement-arrow,
    .announcement-dot {
      appearance: none;
      border: 0;
      cursor: pointer;
    }
    .announcement-arrow {
      width: 34px;
      height: 30px;
      color: #7f8ba0;
      background: transparent;
      font-size: 16px;
    }
    .announcement-arrow:hover { color: #dbe4f1; }
    .announcement-dots { display: flex; align-items: center; gap: 8px; }
    .announcement-dot {
      width: 18px;
      height: 3px;
      padding: 0;
      border-radius: 3px;
      background: #303a49;
      transition: width .2s ease, background .2s ease;
    }
    .announcement-dot.is-active { width: 34px; background: #6f91ca; }
    .internal-node-status .node-status-number { color: #87a7dc !important; }
    .internal-node-status .node-tech-card {
      grid-template-columns: minmax(220px, .9fr) minmax(260px, 1.1fr) !important;
      color: #adb8c8 !important;
    }
    .internal-node-status .node-card-name { color: #dfe6f1 !important; }
    .internal-node-status .node-status-badge,
    .internal-node-status .node-tag {
      border-radius: 4px !important;
      background: transparent !important;
    }
    .internal-node-status .node-status-online { color: #70b091 !important; }
    .internal-node-status .node-status-offline { color: #bf7d85 !important; }

    @media (max-width: 1024px) {
      .internal-tech-dashboard .tech-hero-grid { grid-template-columns: 1fr !important; gap: 22px !important; }
      .internal-tech-dashboard .subscription-console {
        padding: 22px 0 0 !important;
        border-left: 0 !important;
        border-top: 1px solid rgba(148, 163, 184, .11) !important;
      }
      .internal-node-status .node-tech-card {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
      }
      .internal-node-status .node-meta {
        padding: 10px 0 0 !important;
        border-left: 0 !important;
        border-top: 1px solid rgba(148, 163, 184, .08) !important;
      }
    }

    @media (max-width: 768px) {
      .n-card > .n-card-header { padding: 16px 16px 10px; }
      .n-card > .n-card__content { padding: 14px 16px 18px; }
      article.flex.flex-col.flex-1.overflow-hidden .cus-scroll-y { padding: 14px !important; }
      .internal-tech-dashboard .tech-hero { padding: 18px 0 20px !important; }
      .internal-tech-dashboard .tech-hero { padding: 22px 18px !important; }
      .internal-tech-dashboard .tech-hero-grid { grid-template-columns: 1fr !important; gap: 22px !important; }
      .internal-tech-dashboard .subscription-console {
        margin-top: 2px;
        padding: 20px 0 0 !important;
        border-left: 0 !important;
        border-top: 1px solid rgba(103, 232, 249, .18) !important;
      }
      .internal-tech-dashboard .console-actions { grid-template-columns: 1fr !important; }
      .internal-tech-dashboard .tech-card-head { padding: 20px 18px 14px !important; }
      .internal-tech-dashboard .announcement-body { min-height: 0; padding: 22px 18px !important; }
      .internal-tech-dashboard .announcement-body { padding: 20px 0 66px !important; }
      .internal-node-status .node-status-hero {
        flex-direction: row !important;
        align-items: flex-end !important;
        padding: 4px 0 20px !important;
      }
      .internal-node-status .node-status-title { font-size: 24px; }
      .internal-node-status .node-status-number { font-size: 30px; }
      .internal-node-status .node-tech-card {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
        padding: 14px 0 !important;
      }
      .internal-node-status .node-meta {
        padding: 10px 0 0 !important;
        border-left: 0 !important;
        border-top: 1px solid rgba(56, 189, 248, .08) !important;
      }
    }
  </style>
  <style id="ink-wash-theme">
    :root {
      color-scheme: light;
      --ink-paper: #f4f1e9;
      --ink-paper-deep: #e9e3d7;
      --ink-surface: rgba(250, 248, 242, .78);
      --ink-surface-solid: #f8f5ee;
      --ink-text: #1d2825;
      --ink-muted: #65716c;
      --ink-faint: #909a95;
      --ink-line: rgba(35, 52, 47, .13);
      --ink-line-strong: rgba(35, 52, 47, .24);
      --ink-pine: #315f55;
      --ink-pine-hover: #274f47;
      --ink-pine-soft: rgba(49, 95, 85, .1);
      --ink-seal: #a45143;
      --ink-seal-soft: rgba(164, 81, 67, .11);
      --ink-shadow: rgba(35, 48, 44, .1);
      --ink-sidebar: #ebe6da;
      --ink-header: rgba(244, 241, 233, .82);
      --ink-wash-a: rgba(67, 103, 92, .12);
      --ink-wash-b: rgba(112, 119, 109, .09);
    }
    html.dark {
      color-scheme: dark;
      --ink-paper: #171b1a;
      --ink-paper-deep: #101413;
      --ink-surface: rgba(29, 35, 32, .78);
      --ink-surface-solid: #1d2320;
      --ink-text: #eee9df;
      --ink-muted: #a3ada6;
      --ink-faint: #747f79;
      --ink-line: rgba(222, 229, 219, .11);
      --ink-line-strong: rgba(222, 229, 219, .2);
      --ink-pine: #78a99a;
      --ink-pine-hover: #91bcaf;
      --ink-pine-soft: rgba(120, 169, 154, .12);
      --ink-seal: #cf7b6d;
      --ink-seal-soft: rgba(207, 123, 109, .12);
      --ink-shadow: rgba(0, 0, 0, .28);
      --ink-sidebar: #111614;
      --ink-header: rgba(23, 27, 26, .84);
      --ink-wash-a: rgba(99, 141, 128, .1);
      --ink-wash-b: rgba(153, 160, 148, .06);
    }

    html, body, #app {
      color: var(--ink-text) !important;
      background:
        radial-gradient(ellipse at 78% 7%, var(--ink-wash-a), transparent 31%),
        radial-gradient(ellipse at 24% 94%, var(--ink-wash-b), transparent 34%),
        var(--ink-paper) !important;
      transition: color .45s ease, background-color .45s ease !important;
    }
    body::before {
      content: "";
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      opacity: .28;
      background-image:
        linear-gradient(92deg, transparent 0 49%, rgba(79, 88, 82, .025) 50%, transparent 51%),
        radial-gradient(circle at 20% 30%, rgba(40, 52, 47, .025) 0 1px, transparent 1.5px);
      background-size: 100% 100%, 7px 7px;
      mix-blend-mode: multiply;
    }
    html.dark body::before { mix-blend-mode: screen; opacity: .12; }
    #app { position: relative; z-index: 1; }
    .n-layout, .n-layout-content, .n-layout-scroll-container {
      color: var(--ink-text) !important;
      background: transparent !important;
    }
    article.flex.flex-col.flex-1.overflow-hidden > section.flex-1,
    article.flex.flex-col.flex-1.overflow-hidden .cus-scroll-y {
      color: var(--ink-text) !important;
      background:
        radial-gradient(ellipse at 83% 4%, var(--ink-wash-a), transparent 29%),
        radial-gradient(ellipse at 16% 96%, var(--ink-wash-b), transparent 32%),
        var(--ink-paper) !important;
      background-size: auto !important;
      transition: background .45s ease, color .35s ease !important;
    }

    /* Header: floating rice-paper toolbar */
    .n-layout-header,
    article.flex.flex-col.flex-1.overflow-hidden > header.flex.items-center,
    header.flex.items-center.bg-white {
      color: var(--ink-muted) !important;
      border-bottom: 1px solid var(--ink-line) !important;
      background: var(--ink-header) !important;
      box-shadow: 0 8px 30px transparent !important;
      backdrop-filter: blur(18px) saturate(120%);
      transition: background .4s ease, border-color .4s ease, color .3s ease !important;
    }
    .n-layout-header svg,
    .n-layout-header .n-icon { color: var(--ink-muted) !important; transition: color .25s ease, transform .35s cubic-bezier(.22,1,.36,1) !important; }
    .n-layout-header svg:hover { color: var(--ink-pine) !important; transform: translateY(-1px) rotate(-3deg); }
    .n-breadcrumb, .n-breadcrumb a, .n-breadcrumb-item,
    .n-breadcrumb-item__link, .n-breadcrumb-item__separator { color: var(--ink-faint) !important; }

    /* Sidebar: one continuous ink scroll, no stacked colour blocks */
    .n-layout-sider,
    aside.n-layout-sider,
    aside.n-layout-sider > .n-scrollbar,
    aside.n-layout-sider .n-scrollbar-container,
    aside.n-layout-sider .n-scrollbar-content,
    aside.n-layout-sider .h-15,
    aside.n-layout-sider .side-menu {
      color: var(--ink-text) !important;
      background:
        radial-gradient(ellipse at 14% 12%, var(--ink-wash-a), transparent 34%),
        radial-gradient(ellipse at 84% 88%, var(--ink-wash-b), transparent 30%),
        var(--ink-sidebar) !important;
      box-shadow: none !important;
      transition:
        width .52s cubic-bezier(.22, 1, .36, 1),
        min-width .52s cubic-bezier(.22, 1, .36, 1),
        max-width .52s cubic-bezier(.22, 1, .36, 1),
        transform .52s cubic-bezier(.22, 1, .36, 1),
        background .45s ease !important;
    }
    aside.n-layout-sider {
      overflow: hidden;
      border-right: 1px solid var(--ink-line) !important;
    }
    aside.n-layout-sider .h-15 { border-bottom: 1px solid var(--ink-line) !important; }
    aside.n-layout-sider .title-text {
      color: var(--ink-text) !important;
      font-weight: 650;
      letter-spacing: .04em;
      transition: opacity .3s ease, transform .42s cubic-bezier(.22,1,.36,1) !important;
    }
    .n-layout-sider .n-menu {
      --n-item-color: transparent !important;
      --n-item-color-hover: transparent !important;
      --n-item-color-active: transparent !important;
      --n-item-color-active-hover: transparent !important;
      --n-item-text-color: var(--ink-muted) !important;
      --n-item-text-color-hover: var(--ink-text) !important;
      --n-item-text-color-active: var(--ink-pine) !important;
      --n-item-icon-color: var(--ink-faint) !important;
      --n-item-icon-color-hover: var(--ink-pine) !important;
      --n-item-icon-color-active: var(--ink-pine) !important;
      padding: 12px 14px !important;
    }
    .n-layout-sider .n-menu-item,
    .n-layout-sider .n-submenu-title {
      position: relative;
      isolation: isolate;
      overflow: hidden;
      margin: 3px 0 !important;
      border-radius: 7px !important;
      transition: transform .38s cubic-bezier(.22,1,.36,1), color .3s ease !important;
    }
    .n-layout-sider .n-menu-item::after,
    .n-layout-sider .n-submenu-title::after {
      content: "";
      position: absolute;
      z-index: -1;
      inset: 4px 2px;
      border-radius: 45% 55% 52% 48% / 48% 42% 58% 52%;
      background: radial-gradient(ellipse at 22% 50%, var(--ink-pine-soft), transparent 72%);
      opacity: 0;
      transform: scaleX(.68) translateX(-10px);
      transform-origin: left center;
      transition: opacity .32s ease, transform .48s cubic-bezier(.22,1,.36,1);
    }
    .n-layout-sider .n-menu-item:hover { transform: translateX(3px); }
    .n-layout-sider .n-menu-item:hover::after,
    .n-layout-sider .n-menu-item--selected::after {
      opacity: 1;
      transform: scaleX(1) translateX(0);
    }
    .n-layout-sider .n-menu-item--selected::before {
      left: 0;
      top: 12px;
      bottom: 12px;
      width: 2px;
      background: linear-gradient(transparent, var(--ink-pine) 22% 78%, transparent);
      box-shadow: 0 0 12px var(--ink-pine-soft);
    }
    .n-layout-sider .n-menu-item-group-title {
      color: var(--ink-faint) !important;
      font-size: 10px !important;
      font-weight: 600;
      letter-spacing: .18em;
    }

    /* Buttons: ink, paper and seal */
    .n-button {
      position: relative;
      overflow: hidden;
      border-radius: 7px !important;
      font-weight: 620;
      letter-spacing: .02em;
      transition:
        transform .34s cubic-bezier(.22,1,.36,1),
        box-shadow .34s ease,
        color .28s ease,
        border-color .28s ease,
        background .32s ease !important;
    }
    .n-button::after {
      content: "";
      position: absolute;
      inset: -60% -20%;
      pointer-events: none;
      opacity: 0;
      background: linear-gradient(105deg, transparent 32%, rgba(255,255,255,.18) 50%, transparent 68%);
      transform: translateX(-48%) rotate(3deg);
      transition: opacity .3s ease, transform .58s cubic-bezier(.22,1,.36,1);
    }
    .n-button:hover { transform: translateY(-2px); }
    .n-button:hover::after { opacity: 1; transform: translateX(48%) rotate(3deg); }
    .n-button:active { transform: translateY(0) scale(.985); transition-duration: .1s !important; }
    .n-button.n-button--primary-type {
      color: #f8f5ed !important;
      border: 1px solid color-mix(in srgb, var(--ink-pine) 76%, black) !important;
      background:
        radial-gradient(ellipse at 22% 10%, rgba(255,255,255,.13), transparent 38%),
        var(--ink-pine) !important;
      box-shadow: 0 8px 22px color-mix(in srgb, var(--ink-pine) 22%, transparent) !important;
    }
    .n-button.n-button--primary-type:hover {
      background: var(--ink-pine-hover) !important;
      box-shadow: 0 12px 26px color-mix(in srgb, var(--ink-pine) 27%, transparent) !important;
    }
    .n-button:not(.n-button--primary-type) {
      color: var(--ink-text) !important;
      border-color: var(--ink-line-strong) !important;
      background: color-mix(in srgb, var(--ink-surface-solid) 72%, transparent) !important;
      box-shadow: 0 5px 18px transparent !important;
    }
    .n-button:not(.n-button--primary-type):hover {
      color: var(--ink-pine) !important;
      border-color: var(--ink-pine) !important;
      background: var(--ink-pine-soft) !important;
      box-shadow: 0 8px 20px var(--ink-shadow) !important;
    }
    .internal-tech-dashboard .subscription-console .n-button:not(.n-button--primary-type) {
      color: var(--ink-text) !important;
      border-color: var(--ink-line-strong) !important;
      background: color-mix(in srgb, var(--ink-surface-solid) 72%, transparent) !important;
    }
    .internal-tech-dashboard .subscription-console .n-button:not(.n-button--primary-type):hover {
      color: var(--ink-pine) !important;
      border-color: var(--ink-pine) !important;
      background: var(--ink-pine-soft) !important;
    }

    /* Shared controls and surfaces */
    .n-card {
      --n-color: var(--ink-surface) !important;
      --n-title-text-color: var(--ink-text) !important;
      --n-text-color: var(--ink-muted) !important;
      color: var(--ink-muted) !important;
      border-color: var(--ink-line) !important;
      background: var(--ink-surface) !important;
      box-shadow: 0 15px 38px var(--ink-shadow) !important;
      backdrop-filter: blur(12px);
    }
    .n-input, .n-input-number, .n-select .n-base-selection {
      --n-color: color-mix(in srgb, var(--ink-surface-solid) 82%, transparent) !important;
      --n-color-focus: var(--ink-surface-solid) !important;
      --n-text-color: var(--ink-text) !important;
      --n-placeholder-color: var(--ink-faint) !important;
      --n-border: 1px solid var(--ink-line-strong) !important;
      --n-border-hover: 1px solid var(--ink-pine) !important;
      --n-border-focus: 1px solid var(--ink-pine) !important;
      color: var(--ink-text) !important;
      border-radius: 7px !important;
      background: color-mix(in srgb, var(--ink-surface-solid) 82%, transparent) !important;
      box-shadow: inset 0 1px rgba(255,255,255,.04) !important;
      transition: border-color .25s ease, box-shadow .3s ease, background .35s ease !important;
    }
    .n-input:focus-within { box-shadow: 0 0 0 3px var(--ink-pine-soft) !important; }
    .n-input input, .n-input__input-el, .n-input__textarea-el { color: var(--ink-text) !important; }
    .n-data-table {
      --n-td-color: var(--ink-surface) !important;
      --n-th-color: var(--ink-paper-deep) !important;
      --n-td-color-hover: var(--ink-pine-soft) !important;
      --n-border-color: var(--ink-line) !important;
      --n-th-text-color: var(--ink-muted) !important;
      --n-td-text-color: var(--ink-text) !important;
      border-color: var(--ink-line) !important;
      background: var(--ink-surface) !important;
      box-shadow: 0 14px 34px var(--ink-shadow) !important;
    }
    .n-data-table .n-data-table-th { color: var(--ink-muted) !important; background: var(--ink-paper-deep) !important; }
    .n-data-table .n-data-table-td { color: var(--ink-text) !important; background: var(--ink-surface) !important; }
    .n-modal, .n-drawer-content, .n-dropdown-menu,
    .n-popover, .n-base-select-menu, .n-base-selection-menu, .n-tooltip {
      --n-color: var(--ink-surface-solid) !important;
      --n-arrow-color: var(--ink-surface-solid) !important;
      --n-text-color: var(--ink-text) !important;
      --n-option-text-color: var(--ink-text) !important;
      --n-option-text-color-active: var(--ink-pine) !important;
      --n-option-text-color-pending: var(--ink-pine) !important;
      --n-option-color-active: var(--ink-pine-soft) !important;
      --n-option-color-pending: var(--ink-pine-soft) !important;
      --n-option-check-color: var(--ink-pine) !important;
      --n-border-color: var(--ink-line-strong) !important;
      --n-divider-color: var(--ink-line) !important;
      color: var(--ink-text) !important;
      border-color: var(--ink-line-strong) !important;
      background: var(--ink-surface-solid) !important;
      box-shadow: 0 18px 50px var(--ink-shadow) !important;
    }
    /* The register form's verification-code row ships with a near-white
       `bg-[--n-color-embedded]` utility; keep it on the ink palette. */
    .bg-\[--n-color-embedded\] {
      background-color: color-mix(in srgb, var(--ink-surface-solid) 82%, transparent) !important;
    }
    .n-drawer {
      background: var(--ink-sidebar) !important;
      transition: transform .52s cubic-bezier(.22,1,.36,1), background .45s ease !important;
    }
    .n-form-item-label, label, .n-empty__description { color: var(--ink-muted) !important; }

    /* Dashboard: generous paper canvas with ink-wash depth */
    .internal-tech-dashboard .tech-hero {
      position: relative;
      isolation: isolate;
      padding: 34px 4px 30px !important;
      color: var(--ink-text) !important;
      border-bottom-color: var(--ink-line) !important;
      background: transparent !important;
    }
    .internal-tech-dashboard .tech-hero::before {
      content: "";
      position: absolute;
      z-index: -1;
      top: -80px;
      right: 4%;
      width: 360px;
      height: 230px;
      border-radius: 42% 58% 55% 45% / 54% 40% 60% 46%;
      background:
        radial-gradient(ellipse at 32% 42%, var(--ink-wash-a), transparent 62%),
        radial-gradient(ellipse at 64% 54%, var(--ink-wash-b), transparent 68%);
      filter: blur(8px);
      transform: rotate(-7deg);
    }
    .internal-tech-dashboard .tech-hero::after {
      content: "";
      position: absolute;
      z-index: -1;
      right: 0;
      bottom: 18px;
      width: min(38%, 390px);
      height: 150px;
      opacity: .16;
      background: linear-gradient(155deg, var(--ink-pine), color-mix(in srgb, var(--ink-pine) 28%, transparent));
      clip-path: polygon(0 100%, 0 78%, 14% 56%, 25% 76%, 42% 30%, 53% 67%, 66% 44%, 78% 70%, 90% 27%, 100% 57%, 100% 100%);
      filter: blur(.5px);
      transform-origin: bottom center;
      animation: ink-mountain-arrive .9s cubic-bezier(.22,1,.36,1) both;
    }
    @keyframes ink-mountain-arrive {
      from { opacity: 0; transform: translateY(14px) scaleY(.88); }
      to { opacity: .16; transform: translateY(0) scaleY(1); }
    }
    .internal-tech-dashboard .tech-eyebrow,
    .internal-node-status .node-status-eyebrow {
      color: var(--ink-pine) !important;
      letter-spacing: .22em;
    }
    .internal-tech-dashboard .tech-greeting,
    .internal-tech-dashboard .tech-card-title,
    .internal-tech-dashboard .announcement-title,
    .internal-node-status .node-status-title,
    .internal-node-status .node-card-name { color: var(--ink-text) !important; }
    .internal-tech-dashboard .tech-subtitle,
    .internal-node-status .node-status-subtitle,
    .internal-node-status .node-card-type,
    .internal-node-status .node-rate { color: var(--ink-muted) !important; }
    .internal-tech-dashboard .tech-pill {
      color: var(--ink-muted) !important;
      border-color: var(--ink-line) !important;
      background: color-mix(in srgb, var(--ink-surface-solid) 48%, transparent) !important;
      backdrop-filter: blur(5px);
    }
    .internal-tech-dashboard .tech-dot {
      background: var(--ink-pine) !important;
      box-shadow: 0 0 0 3px var(--ink-pine-soft) !important;
    }
    .internal-tech-dashboard .subscription-console {
      border-left-color: var(--ink-line-strong) !important;
      background: transparent !important;
    }
    .internal-tech-dashboard .console-label,
    .internal-tech-dashboard .tech-card-meta { color: var(--ink-muted) !important; }
    .internal-tech-dashboard .console-badge {
      color: var(--ink-pine) !important;
      border: 1px solid var(--ink-line) !important;
      background: var(--ink-pine-soft) !important;
    }
    .internal-tech-dashboard .console-url {
      color: var(--ink-muted) !important;
      border-color: var(--ink-line-strong) !important;
      background: color-mix(in srgb, var(--ink-surface-solid) 65%, transparent) !important;
      box-shadow: inset 0 1px 8px rgba(30, 42, 38, .04);
    }
    .internal-tech-dashboard .tech-card-head {
      border-bottom-color: var(--ink-line) !important;
      background: transparent !important;
    }
    .internal-tech-dashboard .announcement-body { color: var(--ink-muted) !important; }
    .internal-tech-dashboard .announcement-seal {
      color: var(--ink-seal) !important;
      border: 1px solid var(--ink-seal) !important;
      border-radius: 3px !important;
      background: var(--ink-seal-soft) !important;
      letter-spacing: .08em;
    }
    .announcement-arrow { color: var(--ink-faint) !important; transition: color .25s ease, transform .35s cubic-bezier(.22,1,.36,1); }
    .announcement-arrow:hover { color: var(--ink-pine) !important; transform: scale(1.12); }
    .announcement-dot { background: var(--ink-line-strong) !important; }
    .announcement-dot.is-active { background: var(--ink-pine) !important; }

    /* Node list: calligraphic rules, still continuous */
    .internal-node-status .node-status-hero,
    .internal-node-status .node-card-grid,
    .internal-node-status .node-tech-card { border-color: var(--ink-line) !important; }
    .internal-node-status .node-status-number { color: var(--ink-seal) !important; }
    .internal-node-status .node-tech-card {
      background: transparent !important;
      transition: padding .38s cubic-bezier(.22,1,.36,1), background .3s ease !important;
    }
    .internal-node-status .node-tech-card:hover {
      padding-left: 12px !important;
      padding-right: 12px !important;
      background: linear-gradient(90deg, var(--ink-pine-soft), transparent 68%) !important;
    }
    .internal-node-status .node-meta { border-color: var(--ink-line) !important; }
    .internal-node-status .node-tag {
      color: var(--ink-muted) !important;
      border: 1px solid var(--ink-line) !important;
      background: transparent !important;
    }
    .internal-node-status .node-status-online { color: var(--ink-pine) !important; }
    .internal-node-status .node-status-offline { color: var(--ink-seal) !important; }
    .internal-node-status .node-status-special {
      color: var(--ink-seal) !important;
      border: 1px solid color-mix(in srgb, var(--ink-seal) 46%, transparent) !important;
      background: var(--ink-seal-soft) !important;
    }

    ::selection { color: #f8f5ed; background: var(--ink-pine); }
    ::-webkit-scrollbar-track { background: var(--ink-paper-deep); }
    ::-webkit-scrollbar-thumb { background: color-mix(in srgb, var(--ink-pine) 42%, var(--ink-paper-deep)); }

    @media (max-width: 1024px) {
      .internal-tech-dashboard .subscription-console { border-top-color: var(--ink-line) !important; }
      .internal-node-status .node-meta { border-top-color: var(--ink-line) !important; }
    }
    @media (max-width: 768px) {
      .internal-tech-dashboard .tech-hero::before { width: 250px; height: 180px; right: -60px; }
      .internal-tech-dashboard .tech-greeting { font-size: 29px !important; }
      .n-button:hover { transform: none; }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
      }
    }
  </style>
</head>

<body>

  <script>
    window.routerBase = "/";
    window.settings = {
      title: '{{$title}}',
      assets_path: '/theme/{{$theme}}/assets',
      theme: {
        color: '{{ $theme_config['theme_color'] ?? "default" }}',
      },
      version: '{{$version}}',
      background_url: '{{$theme_config['background_url']}}',
      description: '{{$description}}',
      i18n: [
        'zh-CN',
        'en-US',
        'ja-JP',
        'vi-VN',
        'ko-KR',
        'zh-TW',
        'fa-IR'
      ],
      logo: '{{$logo}}'
    }
  </script>
  <div id="app"></div>
  {!! $theme_config['custom_html'] !!}
</body>

</html>
