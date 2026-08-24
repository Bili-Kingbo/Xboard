import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const bundlePath = resolve(root, 'theme/Xboard/assets/umi.js');
let bundle = readFileSync(bundlePath, 'utf8');

function replaceOnce(search, replacement, label) {
  const count = bundle.split(search).length - 1;
  if (count !== 1) {
    throw new Error(`${label}: expected exactly one match, found ${count}`);
  }
  bundle = bundle.replace(search, replacement);
}

if (!bundle.includes('__name:"InternalTechDashboard"')) {
  const dashboardComponent = String.raw`
InternalTechDashboard=zn({__name:"InternalTechDashboard",setup(){
  const profile=Et({}),nodes=Et([]),notices=Et([]),noticeIndex=Et(0),loading=Et(!0),copying=Et(!1);
  const extractNodes=e=>Array.isArray(e)?e:Array.isArray(null==e?void 0:e.data)?e.data:Array.isArray(null==e||null==e.data?void 0:e.data.data)?e.data.data:[];
  const displayName=e=>{const t=String(e||"Colleague").split("@")[0].replace(/[._-]+/g," ");return t.replace(/\b[a-z]/g,(e=>e.toUpperCase()))};
  const greeting=()=>{const e=(new Date).getHours();return e<11?"早上好":e<13?"中午好":e<18?"下午好":"你好"};
  const traffic=e=>{const t=Number(e||0)/1073741824;return t>=1024?(t/1024).toFixed(t%1024===0?0:1)+" TB":t.toFixed(t%1===0?0:1)+" GB"};
  const quota=e=>Number(e||0)<=0?"无限":traffic(e);
  function copySubscription(){return v(this,null,(function*(){copying.value=!0;try{if(!profile.value.subscribe_url){const e=yield EN.get("/user/getSubscribe");profile.value=(null==e?void 0:e.data)||{}}profile.value.subscribe_url&&Xf(profile.value.subscribe_url)}finally{copying.value=!1}}))}
  function load(){return v(this,null,(function*(){loading.value=!0;try{const[e,t,n]=yield Promise.all([EN.get("/user/getSubscribe"),ON(),EN.get("/user/notice/fetch")]);profile.value=(null==e?void 0:e.data)||{},nodes.value=extractNodes(t),notices.value=Array.isArray(null==n?void 0:n.data)?n.data:[]}finally{loading.value=!1}}))}
  const shiftNotice=e=>{const t=notices.value.length;t&&(noticeIndex.value=(noticeIndex.value+e+t)%t)};
  Dn((()=>{load();setInterval((()=>{notices.value.length>1&&shiftNotice(1)}),7000)}));
  return()=>{
    const e=profile.value||{},t=e.group||{},n=notices.value.length?notices.value[noticeIndex.value%notices.value.length]:null,o=(Number(e.u||0)+Number(e.d||0)),r=Number(e.transfer_enable||0),i=r>0?Math.min(100,Math.round(o/r*100)):0;
    return li(Zj,null,{default:()=>[
      li("style",null,'.internal-tech-dashboard{max-width:1180px;margin:0 auto;padding-bottom:32px}.tech-hero{position:relative;overflow:hidden;border-radius:24px;padding:32px;background:radial-gradient(circle at 84% 12%,rgba(34,211,238,.24),transparent 28%),linear-gradient(135deg,#07152d 0%,#102a56 58%,#0c4a6e 100%);color:#fff;box-shadow:0 24px 60px rgba(15,23,42,.2)}.tech-hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:28px;align-items:center}.tech-orb{position:absolute;border-radius:999px;filter:blur(2px);opacity:.35}.tech-orb-one{width:220px;height:220px;right:-70px;bottom:-120px;background:#22d3ee}.tech-eyebrow{font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:#67e8f9;font-weight:700}.tech-greeting{margin:12px 0 10px;font-size:34px;line-height:1.15;font-weight:700;letter-spacing:-.03em}.tech-subtitle{max-width:580px;color:#cbd5e1;font-size:14px;line-height:1.8}.tech-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}.tech-pill{display:inline-flex;align-items:center;gap:7px;padding:7px 11px;border:1px solid rgba(148,163,184,.24);border-radius:999px;background:rgba(15,23,42,.34);font-size:12px;color:#e2e8f0}.tech-dot{width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 12px #34d399}.subscription-console{border:1px solid rgba(103,232,249,.28);border-radius:18px;padding:20px;background:rgba(2,8,23,.62);backdrop-filter:blur(14px);box-shadow:inset 0 1px rgba(255,255,255,.08)}.console-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}.console-label{font-size:12px;color:#94a3b8}.console-badge{padding:5px 9px;border-radius:999px;background:rgba(34,211,238,.14);color:#67e8f9;font-size:11px;font-weight:700}.console-url{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border:1px solid rgba(148,163,184,.18);border-radius:10px;padding:11px 12px;background:rgba(15,23,42,.72);color:#cbd5e1;font:12px ui-monospace,SFMono-Regular,Menlo,monospace}.console-actions{display:grid;grid-template-columns:1fr auto;gap:10px;margin-top:12px}.tech-content-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.55fr);gap:20px;margin-top:20px}.tech-card{border:1px solid rgba(148,163,184,.2);border-radius:20px;background:rgba(255,255,255,.94);box-shadow:0 14px 40px rgba(15,23,42,.07)}.tech-card-head{display:flex;align-items:center;justify-content:space-between;padding:22px 24px;border-bottom:1px solid #e2e8f0}.tech-card-title{font-size:17px;font-weight:700;color:#0f172a}.tech-card-meta{font-size:12px;color:#64748b}.announcement-body{min-height:260px;padding:26px 28px;color:#334155;font-size:14px;line-height:1.9}.announcement-title{margin:0 0 14px;color:#0f172a;font-size:24px;line-height:1.35;font-weight:700}.announcement-content p{margin:0 0 12px}.overview-list{padding:8px 22px 18px}.overview-row{display:flex;justify-content:space-between;align-items:center;padding:16px 2px;border-bottom:1px solid #eef2f7}.overview-row:last-child{border-bottom:0}.overview-label{font-size:12px;color:#64748b}.overview-value{font-size:14px;color:#0f172a;font-weight:700}.usage-track{height:6px;margin:2px 24px 24px;border-radius:999px;background:#e2e8f0;overflow:hidden}.usage-bar{height:100%;border-radius:inherit;background:linear-gradient(90deg,#06b6d4,#2563eb)}@media(max-width:820px){.tech-hero{padding:24px}.tech-hero-grid,.tech-content-grid{grid-template-columns:1fr}.tech-greeting{font-size:28px}.console-actions{grid-template-columns:1fr}}'),
      li("div",{class:"internal-tech-dashboard"},[
        li("section",{class:"tech-hero"},[
          li("div",{class:"tech-orb tech-orb-one"}),
          li("div",{class:"tech-hero-grid"},[
            li("div",null,[
              li("div",{class:"tech-eyebrow"},"SHEDIO · INTERNAL NETWORK"),
              li("h1",{class:"tech-greeting"},greeting()+"，"+displayName(e.email)),
              li("p",{class:"tech-subtitle"},"欢迎回到公司安全网络工作台。在这里获取 Meta 订阅、查看部门网络权限，并及时阅读内部公告。"),
              li("div",{class:"tech-pills"},[
                li("span",{class:"tech-pill"},[li("span",{class:"tech-dot"}),"安全连接可用"]),
                li("span",{class:"tech-pill"},"部门组 · "+(t.name||"未分组")),
                li("span",{class:"tech-pill"},nodes.value.length+" 个可用节点")
              ])
            ]),
            li("div",{class:"subscription-console"},[
              li("div",{class:"console-head"},[li("div",{class:"console-label"},"订阅导入"),li("span",{class:"console-badge"},"CLASH META")]),
              li("div",{class:"console-url",title:e.subscribe_url||""},e.subscribe_url||"正在生成安全订阅链接…"),
              li("div",{class:"console-actions"},[
                nodes.value.length?li(oO,{type:"primary",size:"large",round:!0,loading:copying.value,disabled:!e.subscribe_url,onClick:copySubscription},{default:()=>"复制 Meta 订阅链接"}):li("div",{class:"dashboard-no-nodes-copy",style:"display:flex;align-items:center;padding:0 12px;border:1px solid rgba(248,113,113,.24);border-radius:10px;color:#fca5a5;font-size:12px"},"当前部门组暂无可用节点"),
                li(oO,{size:"large",round:!0,onClick:()=>qN.push("/node")},{default:()=>"查看节点"})
              ]),
              li("p",{style:"margin:12px 2px 0;color:#94a3b8;font-size:11px;line-height:1.6"},"复制后粘贴到 Clash Meta 客户端即可导入，节点变更会自动同步。")
            ])
          ])
        ]),
        li("div",{class:"tech-content-grid"},[
          li("article",{class:"tech-card"},[
            li("div",{class:"tech-card-head"},[li("div",{class:"tech-card-title"},"公司公告"),li("div",{class:"tech-card-meta"},"内部动态")]),
            li("div",{class:"announcement-body"},n?[
              li("div",{class:"announcement-seal",style:"display:inline-flex;padding:5px 9px;margin-bottom:14px;border-radius:999px;background:#ecfeff;color:#0e7490;font-size:11px;font-weight:700"},"最新公告"),
              li("h2",{class:"announcement-title"},n.title||"公司公告"),
              li("div",{class:"announcement-content",innerHTML:n.content||""}),
              li("div",{style:"margin-top:22px;color:#94a3b8;font-size:11px"},n.created_at?"发布于 "+new Date(1e3*n.created_at).toLocaleString():""),
              notices.value.length>1?li("div",{class:"announcement-carousel"},[
                li("button",{class:"announcement-arrow",type:"button","aria-label":"上一条公告",onClick:()=>shiftNotice(-1)},"←"),
                li("div",{class:"announcement-dots"},notices.value.map(((e,t)=>li("button",{class:"announcement-dot"+(t===noticeIndex.value?" is-active":""),type:"button","aria-label":"查看第 "+(t+1)+" 条公告",onClick:()=>noticeIndex.value=t})))),
                li("button",{class:"announcement-arrow",type:"button","aria-label":"下一条公告",onClick:()=>shiftNotice(1)},"→")
              ]):null
            ]:[li("h2",{class:"announcement-title"},"欢迎使用公司 VPN"),li("p",null,"管理员发布的重要通知会在这里直接展示。")])
          ]),
          li("aside",{class:"tech-card"},[
            li("div",{class:"tech-card-head"},[li("div",{class:"tech-card-title"},"网络概览"),li("div",{class:"tech-card-meta"},loading.value?"同步中":"已同步")]),
            li("div",{class:"overview-list"},[
              li("div",{class:"overview-row"},[li("span",{class:"overview-label"},"部门身份组"),li("span",{class:"overview-value"},t.name||"未分组")]),
              li("div",{class:"overview-row"},[li("span",{class:"overview-label"},"可用节点"),li("span",{class:"overview-value"},nodes.value.length+" 个")]),
              li("div",{class:"overview-row"},[li("span",{class:"overview-label"},"用户可用流量"),li("span",{class:"overview-value"},quota(r))]),
              li("div",{class:"overview-row"},[li("span",{class:"overview-label"},"本期已使用"),li("span",{class:"overview-value"},traffic(o))])
            ]),
            li("div",{class:"usage-track"},[li("div",{class:"usage-bar",style:"width:"+i+"%"})])
          ])
        ])
      ])
    ]})
  }
}}),InternalTechDashboardModule=Object.freeze(Object.defineProperty({__proto__:null,default:InternalTechDashboard},Symbol.toStringTag,{value:"Module"})),`;

  replaceOnce('Jl={name:"dashboard"', `${dashboardComponent}Jl={name:"dashboard"`, 'dashboard component injection');

  const routeStart = bundle.indexOf('Jl={name:"dashboard"');
  const routeEnd = bundle.indexOf(',es=Object.freeze', routeStart);
  if (routeStart < 0 || routeEnd < 0) throw new Error('dashboard route boundaries not found');
  const dashboardRoute = 'Jl={name:"dashboard",path:"/",component:()=>Zl((()=>Promise.resolve().then((()=>qj))),void 0),redirect:"/dashboard",meta:{isHidden:!1},children:[{name:"dashboard",path:"dashboard",component:()=>Zl((()=>Promise.resolve().then((()=>InternalTechDashboardModule))),void 0),meta:{title:"Dashboard",icon:"mdi:home",order:0,group:{key:"network",label:"公司网络"}}}]}';
  bundle = bundle.slice(0, routeStart) + dashboardRoute + bundle.slice(routeEnd);

  replaceOnce(
    'ys=Object.assign({"/src/views/knowledge/route.ts":rs,"/src/views/node/route.ts":as,"/src/views/profile/route.ts":ps,"/src/views/ticket/route.ts":fs,"/src/views/traffic/route.ts":ms})',
    'ys=Object.assign({"/src/views/dashboard/route.ts":es,"/src/views/node/route.ts":as,"/src/views/profile/route.ts":ps,"/src/views/ticket/route.ts":fs,"/src/views/traffic/route.ts":ms})',
    'internal route map',
  );
  replaceOnce(
    'meta:{title:"节点状态",icon:"mdi-check-circle-outline",order:11,group:{key:"subscribe",label:"订阅"}}',
    'meta:{title:"节点状态",icon:"mdi-check-circle-outline",order:1,group:{key:"network",label:"公司网络"}}',
    'node navigation group',
  );
  replaceOnce(
    'gs=[{name:"Home",path:"/",redirect:"/node"',
    'gs=[{name:"Home",path:"/",redirect:"/dashboard"',
    'home redirect',
  );
  bundle = bundle.replace('n.push("/node")', 'n.push("/dashboard")');
  bundle = bundle.replaceAll('?a:"/node"', '?a:"/dashboard"');
  bundle = bundle.replaceAll('?t:"/node"', '?t:"/dashboard"');

  writeFileSync(bundlePath, bundle);
  console.log(`Patched ${bundlePath}`);
} else {
  console.log(`Already patched ${bundlePath}`);
}

bundle = readFileSync(bundlePath, 'utf8');
const unlimitedDashboardReplacements = [
  [
    'const quota=e=>{const t=Number(e||0)/1073741824;return t>=1024?(t/1024).toFixed(t%1024===0?0:1)+" TB":t.toFixed(t%1===0?0:1)+" GB"};',
    'const traffic=e=>{const t=Number(e||0)/1073741824;return t>=1024?(t/1024).toFixed(t%1024===0?0:1)+" TB":t.toFixed(t%1===0?0:1)+" GB"};const quota=e=>Number(e||0)<=0?"无限":traffic(e);',
  ],
  ['"有效流量额度"),li("span",{class:"overview-value"},quota(r))', '"用户可用流量"),li("span",{class:"overview-value"},quota(r))'],
  ['"本期已使用"),li("span",{class:"overview-value"},i+"%")', '"本期已使用"),li("span",{class:"overview-value"},traffic(o))'],
];
for (const [search, replacement] of unlimitedDashboardReplacements) {
  bundle = bundle.replace(search, replacement);
}
writeFileSync(bundlePath, bundle);

bundle = readFileSync(bundlePath, 'utf8');
if (!bundle.includes('__name:"InternalNodeStatus"')) {
  const nodeComponent = String.raw`
InternalNodeStatus=zn({__name:"InternalNodeStatus",setup(){
  const nodes=Et([]),profile=Et({}),loading=Et(!0);
  const extract=e=>Array.isArray(e)?e:Array.isArray(null==e?void 0:e.data)?e.data:Array.isArray(null==e||null==e.data?void 0:e.data.data)?e.data.data:[];
  function load(){return v(this,null,(function*(){loading.value=!0;try{const[e,t]=yield Promise.all([ON(),EN.get("/user/getSubscribe")]);nodes.value=extract(e),profile.value=(null==t?void 0:t.data)||{}}finally{loading.value=!1}}))}
  Dn((()=>{load()}));
  return()=>li(Zj,null,{default:()=>[
    li("style",null,'.internal-node-status{max-width:1180px;margin:0 auto;padding-bottom:32px}.node-status-hero{position:relative;overflow:hidden;display:flex;justify-content:space-between;align-items:flex-end;gap:20px;padding:28px 30px;border-radius:22px;background:linear-gradient(135deg,#081b38,#0f3a63);color:#fff;box-shadow:0 18px 44px rgba(15,23,42,.14)}.node-status-eyebrow{font-size:10px;letter-spacing:.18em;color:#67e8f9;font-weight:700}.node-status-title{margin:9px 0 6px;font-size:28px;font-weight:700}.node-status-subtitle{color:#a5b4c9;font-size:13px}.node-status-count{text-align:right}.node-status-number{font-size:38px;line-height:1;font-weight:750;color:#67e8f9}.node-status-unit{margin-top:5px;color:#94a3b8;font-size:11px}.node-card-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:20px}.node-tech-card{position:relative;overflow:hidden;padding:20px;border:1px solid rgba(148,163,184,.2);border-radius:18px;background:rgba(255,255,255,.94);box-shadow:0 10px 28px rgba(15,23,42,.055)}.node-tech-card:before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:linear-gradient(#06b6d4,#2563eb)}.node-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.node-card-name{color:#0f172a;font-size:15px;font-weight:700}.node-card-type{margin-top:5px;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.08em}.node-status-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;font-size:11px;font-weight:700}.node-status-online{background:#ecfdf5;color:#047857}.node-status-offline{background:#fef2f2;color:#b91c1c}.node-status-indicator{width:6px;height:6px;border-radius:50%;background:currentColor}.node-meta{display:flex;align-items:center;justify-content:space-between;margin-top:18px;padding-top:15px;border-top:1px solid #eef2f7}.node-tags{display:flex;flex-wrap:wrap;gap:6px}.node-tag{padding:4px 8px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:10px}.node-rate{font-size:11px;color:#64748b}.node-empty{margin-top:20px;padding:60px 24px;border:1px dashed #cbd5e1;border-radius:20px;background:rgba(255,255,255,.7);text-align:center;color:#64748b}@media(max-width:720px){.node-status-hero{align-items:flex-start;flex-direction:column}.node-status-count{text-align:left}.node-card-grid{grid-template-columns:1fr}}'),
    li("div",{class:"internal-node-status"},[
      li("section",{class:"node-status-hero"},[
        li("div",null,[li("div",{class:"node-status-eyebrow"},"NETWORK OBSERVABILITY"),li("h1",{class:"node-status-title"},"节点状态"),li("p",{class:"node-status-subtitle"},"当前部门组 · "+(((profile.value||{}).group||{}).name||"未分组"))]),
        li("div",{class:"node-status-count"},[li("div",{class:"node-status-number"},String(nodes.value.length)),li("div",{class:"node-status-unit"},loading.value?"正在同步":"个可用节点")])
      ]),
      nodes.value.length?li("div",{class:"node-card-grid"},nodes.value.map((e=>li("article",{class:"node-tech-card",key:e.id},[
        li("div",{class:"node-card-head"},[
          li("div",null,[li("div",{class:"node-card-name"},e.name),li("div",{class:"node-card-type"},e.type||"network node")]),
          li("span",{class:"node-status-badge "+(e.is_special?"node-status-special":e.is_online?"node-status-online":"node-status-offline")},[li("span",{class:"node-status-indicator"}),e.is_special?"特殊节点":e.is_online?"在线":"离线"])
        ]),
        li("div",{class:"node-meta"},[
          li("div",{class:"node-tags"},(e.tags||[]).map((e=>li("span",{class:"node-tag"},e)))),
          li("div",{class:"node-rate"},"流量倍率 "+e.rate+"x")
        ])
      ])))):li("div",{class:"node-empty"},[li("div",{style:"font-size:24px;margin-bottom:10px"},"◎"),li("div",null,loading.value?"正在获取部门节点…":"当前部门组暂无可用节点，请联系管理员。")])
    ])
  ]})
}}),InternalNodeStatusModule=Object.freeze(Object.defineProperty({__proto__:null,default:InternalNodeStatus},Symbol.toStringTag,{value:"Module"})),`;

  const routeIndex = bundle.indexOf('Jl={name:"dashboard"');
  if (routeIndex < 0) throw new Error('dashboard route not found for node component injection');
  bundle = bundle.slice(0, routeIndex) + nodeComponent + bundle.slice(routeIndex);
  const nodeLoader = 'component:()=>Zl((()=>Promise.resolve().then((()=>WK))),void 0),meta:{title:"节点状态"';
  if (!bundle.includes(nodeLoader)) throw new Error('node route loader not found');
  bundle = bundle.replace(
    nodeLoader,
    'component:()=>Zl((()=>Promise.resolve().then((()=>InternalNodeStatusModule))),void 0),meta:{title:"节点状态"',
  );
  writeFileSync(bundlePath, bundle);
  console.log(`Patched node status in ${bundlePath}`);
}

bundle = readFileSync(bundlePath, 'utf8');
const actionsMarker = 'li("div",{class:"console-actions"},[';
const viewNodesMarker = ',\n                li(oO,{size:"large",round:!0,onClick:()=>qN.push("/node")}' + '';
const actionsStart = bundle.indexOf(actionsMarker);
const viewNodesStart = actionsStart < 0 ? -1 : bundle.indexOf(viewNodesMarker, actionsStart);
if (actionsStart >= 0 && viewNodesStart >= 0) {
  const childStart = actionsStart + actionsMarker.length;
  const guardedAction = '\n                nodes.value.length?li(oO,{type:"primary",size:"large",round:!0,loading:copying.value,disabled:!e.subscribe_url,onClick:copySubscription},{default:()=>"复制 Meta 订阅链接"}):li("div",{class:"dashboard-no-nodes-copy",style:"display:flex;align-items:center;padding:0 12px;border:1px solid rgba(248,113,113,.24);border-radius:10px;color:#fca5a5;font-size:12px"},"当前部门组暂无可用节点")';
  const normalized = bundle.slice(0, childStart) + guardedAction + bundle.slice(viewNodesStart);
  if (normalized !== bundle) {
    bundle = normalized;
    writeFileSync(bundlePath, bundle);
    console.log(`Guarded subscription action in ${bundlePath}`);
  }
} else if (!bundle.includes('dashboard-no-nodes-copy')) {
  throw new Error('dashboard subscription action boundaries not found');
}
if (bundle.includes('dashboard-no-nodes-copy')) {
  writeFileSync(bundlePath, bundle);
}

bundle = readFileSync(bundlePath, 'utf8');
const darkSurfaceReplacements = [
  ['background:rgba(255,255,255,.94);box-shadow:0 14px 40px rgba(15,23,42,.07)', 'background:linear-gradient(145deg,#091426,#0b2037);box-shadow:0 18px 44px rgba(0,0,0,.22)'],
  ['border-bottom:1px solid #e2e8f0', 'border-bottom:1px solid rgba(56,189,248,.12)'],
  ['color:#0f172a;font-size:17px', 'color:#f1f5f9;font-size:17px'],
  ['color:#64748b}.announcement-body', 'color:#94a3b8}.announcement-body'],
  ['color:#334155;font-size:14px', 'color:#cbd5e1;font-size:14px'],
  ['color:#0f172a;font-size:24px', 'color:#f8fafc;font-size:24px'],
  ['border-bottom:1px solid #eef2f7', 'border-bottom:1px solid rgba(56,189,248,.1)'],
  ['color:#0f172a;font-weight:700', 'color:#e2e8f0;font-weight:700'],
  ['background:#e2e8f0;overflow:hidden', 'background:#14233d;overflow:hidden'],
  ['background:rgba(255,255,255,.94);box-shadow:0 10px 28px rgba(15,23,42,.055)', 'background:linear-gradient(145deg,#091426,#0b2037);box-shadow:0 16px 36px rgba(0,0,0,.2)'],
  ['color:#0f172a;font-size:15px', 'color:#e2e8f0;font-size:15px'],
  ['background:#f1f5f9;color:#475569', 'background:#132840;color:#94a3b8'],
  ['background:#ecfdf5;color:#047857', 'background:rgba(16,185,129,.12);color:#6ee7b7'],
  ['background:#fef2f2;color:#b91c1c', 'background:rgba(248,113,113,.1);color:#fca5a5'],
  ['background:rgba(255,255,255,.7);text-align:center;color:#64748b', 'background:rgba(9,20,38,.75);text-align:center;color:#94a3b8'],
];
let darkened = bundle;
for (const [from, to] of darkSurfaceReplacements) darkened = darkened.replaceAll(from, to);
if (darkened !== bundle) {
  bundle = darkened;
  writeFileSync(bundlePath, bundle);
  console.log(`Darkened portal surfaces in ${bundlePath}`);
}

bundle = readFileSync(bundlePath, 'utf8');
const nodeRowReplacements = [
  ['.node-card-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:20px}', '.node-card-grid{display:grid;grid-template-columns:1fr;gap:12px;margin-top:20px}'],
  ['.node-tech-card{position:relative;overflow:hidden;padding:20px;', '.node-tech-card{position:relative;overflow:hidden;display:grid;grid-template-columns:minmax(0,1fr) minmax(240px,.75fr);align-items:center;gap:24px;padding:18px 20px;'],
  ['.node-meta{display:flex;align-items:center;justify-content:space-between;margin-top:18px;padding-top:15px;border-top:1px solid rgba(56,189,248,.1)}', '.node-meta{display:flex;align-items:center;justify-content:space-between;margin:0;padding:2px 0 2px 22px;border-top:0;border-left:1px solid rgba(56,189,248,.12)}'],
  ['@media(max-width:720px){.node-status-hero{align-items:flex-start;flex-direction:column}.node-status-count{text-align:left}.node-card-grid{grid-template-columns:1fr}}', '@media(max-width:720px){.node-status-hero{align-items:flex-start;flex-direction:column}.node-status-count{text-align:left}.node-card-grid{grid-template-columns:1fr}.node-tech-card{grid-template-columns:1fr;gap:14px}.node-meta{padding:14px 0 0;border-left:0;border-top:1px solid rgba(56,189,248,.12)}}'],
];
let rowLayout = bundle;
for (const [from, to] of nodeRowReplacements) rowLayout = rowLayout.replaceAll(from, to);
if (rowLayout !== bundle) {
  bundle = rowLayout;
  writeFileSync(bundlePath, bundle);
  console.log(`Restored long-row node layout in ${bundlePath}`);
}

bundle = readFileSync(bundlePath, 'utf8');
const compactRowReplacements = [
  ['grid-template-columns:minmax(0,1fr) minmax(240px,.75fr);align-items:center;gap:24px;padding:18px 20px', 'grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr);align-items:center;gap:18px;padding:14px 18px'],
];
let compactRows = bundle;
for (const [from, to] of compactRowReplacements) compactRows = compactRows.replaceAll(from, to);
if (compactRows !== bundle) {
  bundle = compactRows;
  writeFileSync(bundlePath, bundle);
  console.log(`Compacted long-row node layout in ${bundlePath}`);
}

bundle = readFileSync(bundlePath, 'utf8');
const dashboardStart = bundle.indexOf('InternalTechDashboard=');
const dashboardEnd = bundle.indexOf('InternalTechDashboardModule=', dashboardStart);
if (dashboardStart < 0 || dashboardEnd < 0) throw new Error('dashboard component boundaries not found for carousel');
let dashboardSlice = bundle.slice(dashboardStart, dashboardEnd);
const carouselReplacements = [
  ['const profile=Et({}),nodes=Et([]),notices=Et([]),loading=Et(!0),copying=Et(!1);', 'const profile=Et({}),nodes=Et([]),notices=Et([]),noticeIndex=Et(0),loading=Et(!0),copying=Et(!1);'],
  ['Dn((()=>{load()}));', 'const shiftNotice=e=>{const t=notices.value.length;t&&(noticeIndex.value=(noticeIndex.value+e+t)%t)};Dn((()=>{load();setInterval((()=>{notices.value.length>1&&shiftNotice(1)}),7000)}));'],
  ['n=notices.value[0]', 'n=notices.value.length?notices.value[noticeIndex.value%notices.value.length]:null'],
  ['notices.value.length?"共 "+notices.value.length+" 条":"暂无公告"', '"内部动态"'],
  ['notices.value.length>1?"自动轮播":"内部动态"', '"内部动态"'],
  ['"LATEST UPDATE"', '"最新公告"'],
  ['li("div",{style:"display:inline-flex;padding:5px 9px;margin-bottom:14px;border-radius:999px;background:#ecfeff;color:#0e7490;font-size:11px;font-weight:700"},"最新公告")', 'li("div",{class:"announcement-seal",style:"display:inline-flex;padding:5px 9px;margin-bottom:14px;border-radius:999px;background:#ecfeff;color:#0e7490;font-size:11px;font-weight:700"},"最新公告")'],
  ['li("div",{style:"margin-top:22px;color:#94a3b8;font-size:11px"},n.created_at?"发布于 "+new Date(1e3*n.created_at).toLocaleString():"")', 'li("div",{style:"margin-top:22px;color:#94a3b8;font-size:11px"},n.created_at?"发布于 "+new Date(1e3*n.created_at).toLocaleString():""),notices.value.length>1?li("div",{class:"announcement-carousel"},[li("button",{class:"announcement-arrow",type:"button","aria-label":"上一条公告",onClick:()=>shiftNotice(-1)},"←"),li("div",{class:"announcement-dots"},notices.value.map(((e,t)=>li("button",{class:"announcement-dot"+(t===noticeIndex.value?" is-active":""),type:"button","aria-label":"查看第 "+(t+1)+" 条公告",onClick:()=>noticeIndex.value=t})))),li("button",{class:"announcement-arrow",type:"button","aria-label":"下一条公告",onClick:()=>shiftNotice(1)},"→")]):null'],
];
let carouselDashboard = dashboardSlice;
for (const [from, to] of carouselReplacements) carouselDashboard = carouselDashboard.replace(from, to);
const carouselMarkup = 'notices.value.length>1?li("div",{class:"announcement-carousel"},[li("button",{class:"announcement-arrow",type:"button","aria-label":"上一条公告",onClick:()=>shiftNotice(-1)},"←"),li("div",{class:"announcement-dots"},notices.value.map(((e,t)=>li("button",{class:"announcement-dot"+(t===noticeIndex.value?" is-active":""),type:"button","aria-label":"查看第 "+(t+1)+" 条公告",onClick:()=>noticeIndex.value=t})))),li("button",{class:"announcement-arrow",type:"button","aria-label":"下一条公告",onClick:()=>shiftNotice(1)},"→")]):null';
while (carouselDashboard.includes(carouselMarkup + ',' + carouselMarkup)) {
  carouselDashboard = carouselDashboard.replace(carouselMarkup + ',' + carouselMarkup, carouselMarkup);
}
if (carouselDashboard !== dashboardSlice) {
  bundle = bundle.slice(0, dashboardStart) + carouselDashboard + bundle.slice(dashboardEnd);
  writeFileSync(bundlePath, bundle);
  console.log(`Added announcement carousel in ${bundlePath}`);
}

bundle = readFileSync(bundlePath, 'utf8');
const nodeStart = bundle.indexOf('InternalNodeStatus=');
const nodeEnd = bundle.indexOf('InternalNodeStatusModule=', nodeStart);
if (nodeStart >= 0 && nodeEnd > nodeStart) {
  const brokenNodeMount = 'const shiftNotice=e=>{const t=notices.value.length;t&&(noticeIndex.value=(noticeIndex.value+e+t)%t)};Dn((()=>{load();setInterval((()=>{notices.value.length>1&&shiftNotice(1)}),7000)}));';
  const nodeSlice = bundle.slice(nodeStart, nodeEnd);
  const repairedNode = nodeSlice.replace(brokenNodeMount, 'Dn((()=>{load()}));');
  if (repairedNode !== nodeSlice) {
    bundle = bundle.slice(0, nodeStart) + repairedNode + bundle.slice(nodeEnd);
    writeFileSync(bundlePath, bundle);
    console.log(`Repaired node lifecycle in ${bundlePath}`);
  }
}

bundle = readFileSync(bundlePath, 'utf8');
const specialNodeStart = bundle.indexOf('InternalNodeStatus=');
const specialNodeEnd = bundle.indexOf('InternalNodeStatusModule=', specialNodeStart);
if (specialNodeStart >= 0 && specialNodeEnd > specialNodeStart) {
  const oldBadge = 'li("span",{class:"node-status-badge "+(e.is_online?"node-status-online":"node-status-offline")},[li("span",{class:"node-status-indicator"}),e.is_online?"在线":"离线"])';
  const newBadge = 'li("span",{class:"node-status-badge "+(e.is_special?"node-status-special":e.is_online?"node-status-online":"node-status-offline")},[li("span",{class:"node-status-indicator"}),e.is_special?"特殊节点":e.is_online?"在线":"离线"])';
  const specialNodeSlice = bundle.slice(specialNodeStart, specialNodeEnd);
  const patchedSpecialNodeSlice = specialNodeSlice.replace(oldBadge, newBadge);
  if (patchedSpecialNodeSlice !== specialNodeSlice) {
    bundle = bundle.slice(0, specialNodeStart) + patchedSpecialNodeSlice + bundle.slice(specialNodeEnd);
    writeFileSync(bundlePath, bundle);
    console.log(`Marked special nodes in ${bundlePath}`);
  }
}
