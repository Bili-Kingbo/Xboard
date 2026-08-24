import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');

function replaceOnce(source, search, replacement, label) {
  const count = source.split(search).length - 1;
  if (count !== 1) {
    throw new Error(`${label}: expected exactly one match, found ${count}`);
  }
  return source.replace(search, replacement);
}

function findBalancedEnd(source, start, openChar, closeChar) {
  let depth = 0;
  let quote = null;
  let escaped = false;

  for (let i = start; i < source.length; i += 1) {
    const char = source[i];
    if (quote) {
      if (escaped) escaped = false;
      else if (char === '\\') escaped = true;
      else if (char === quote) quote = null;
      continue;
    }
    if (char === '"' || char === "'" || char === '`') {
      quote = char;
      continue;
    }
    if (char === openChar) depth += 1;
    else if (char === closeChar && --depth === 0) return i + 1;
  }
  throw new Error(`Unbalanced ${openChar}${closeChar} expression at ${start}`);
}

function removeBalanced(source, marker, openChar, closeChar, label) {
  const start = source.indexOf(marker);
  if (start < 0) throw new Error(`${label}: marker not found`);
  const open = source.indexOf(openChar, start);
  let end = findBalancedEnd(source, open, openChar, closeChar);
  let removeStart = start;
  if (source[end] === ',') end += 1;
  else if (source[start - 1] === ',') removeStart -= 1;
  return source.slice(0, removeStart) + source.slice(end);
}

function removeCallContaining(source, needle, callMarker, label) {
  const needleIndex = source.indexOf(needle);
  if (needleIndex < 0) throw new Error(`${label}: needle not found`);
  const start = source.lastIndexOf(callMarker, needleIndex);
  if (start < 0) throw new Error(`${label}: call start not found`);
  const open = source.indexOf('(', start);
  let end = findBalancedEnd(source, open, '(', ')');
  if (end <= needleIndex) throw new Error(`${label}: marker escaped call`);
  let removeStart = start;
  if (source[end] === ',') end += 1;
  else if (source[start - 1] === ',') removeStart -= 1;
  return source.slice(0, removeStart) + source.slice(end);
}

function extractCall(source, marker) {
  const start = source.indexOf(marker);
  if (start < 0) throw new Error(`Call marker not found: ${marker}`);
  const open = source.indexOf('(', start);
  const end = findBalancedEnd(source, open, '(', ')');
  return { start, end, text: source.slice(start, end) };
}

const userPath = resolve(root, 'theme/Xboard/assets/umi.js');
let user = readFileSync(userPath, 'utf8');

if (!user.includes('internal-subscription-entry')) {
  user = replaceOnce(
    user,
    '__name:"index",setup(e){const t=Et([]),n=Et(!0);return Dn',
    '__name:"index",setup(e){const t=Et([]),n=Et(!0),r=BN(),i=Et(!1);function a(){return v(this,null,(function*(){i.value=!0;try{const e=yield r.getUserSubscribe();e&&e.subscribe_url&&(Xf(e.subscribe_url),window.$message.success(jf.global.t("复制成功")))}finally{i.value=!1}}))}return Dn',
    'node subscription action',
  );
  user = replaceOnce(
    user,
    'return _r(),Rr(h,null,{default:hn((()=>[n.value?',
    'return _r(),Rr(h,null,{default:hn((()=>[Lr(oO,{type:"primary",class:"internal-subscription-entry mb-4",loading:i.value,onClick:a},{default:hn((()=>[Dr(oe(e.$t("复制订阅地址")),1)])),_:1},8,["loading"]),n.value?',
    'node subscription button',
  );
  user = removeBalanced(
    user,
    'Lr(b,{title:e.$t("我的钱包")',
    '(',
    ')',
    'profile wallet card',
  );
  writeFileSync(userPath, user);
  console.log(`Patched ${userPath}`);
} else {
  const oldSubscriptionButton = 'Lr(oO,{type:"primary",class:"internal-subscription-entry mb-4",loading:i.value,onClick:a},{default:hn((()=>[Dr(oe(e.$t("复制订阅地址")),1)])),_:1},8,["loading"]),';
  const subscriptionPanel = 't.value.length>0?(_r(),zr("section",{key:3,class:"internal-subscription-entry relative mb-5 overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-5 shadow-sm dark:border-blue-900/50 dark:from-blue-950/40 dark:via-gray-900 dark:to-indigo-950/30"},[Ir("div",{class:"pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-700/20"}),Ir("div",{class:"relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"},[Ir("div",{class:"max-w-xl"},[Ir("div",{class:"mb-2 inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-white/70 px-3 py-1 text-xs font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-300"},[Ir("span",{class:"h-1.5 w-1.5 rounded-full bg-emerald-500"}),Dr("公司内部网络 · "+oe(t.value.length)+" 个可用节点",1)]),Ir("h2",{class:"text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-50"},"连接公司 VPN"),Ir("p",{class:"mt-1.5 text-sm leading-6 text-gray-600 dark:text-gray-400"},"复制订阅链接并导入客户端，后续节点变更会自动同步，无需重复配置。")]),Lr(oO,{type:"primary",size:"large",round:"",class:"min-w-36 shadow-md shadow-blue-500/20",loading:i.value,onClick:a},{default:hn((()=>[Dr("复制订阅链接")])),_:1},8,["loading"])])],-1)):po("",!0),';
  if (user.includes(oldSubscriptionButton)) {
    user = replaceOnce(
      user,
      oldSubscriptionButton,
      subscriptionPanel,
      'upgrade node subscription panel',
    );
    writeFileSync(userPath, user);
    console.log(`Upgraded ${userPath}`);
  } else {
    console.log(`Already patched ${userPath}`);
  }
}

const oldEmptyState = '(_r(),Rr(p,{key:2,type:"info"},{default:hn((()=>[Ir("div",null,[Dr(oe(e.$t("当前身份组暂无可用节点，请联系管理员。")),1)])])),_:1}))';
if (user.includes(oldEmptyState)) {
  user = user.replace(
    oldEmptyState,
    '(_r(),zr("section",{key:2,class:"mx-auto mt-12 max-w-xl rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900/50"},[Ir("div",{class:"mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-xl text-gray-400 dark:bg-gray-800 dark:text-gray-500"},"◎"),Ir("h3",{class:"text-base font-semibold text-gray-900 dark:text-gray-100"},"暂无可用节点"),Ir("p",{class:"mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400"},"当前身份组还没有分配节点，请联系管理员完成配置。")]))',
  );
  writeFileSync(userPath, user);
  console.log(`Polished empty state in ${userPath}`);
}

const shadowedSubscriptionAction = ',r=_N(),i=Et(!1);function a(){return v(this,null,(function*(){i.value=!0;try{const e=yield r.getUserSubscribe();e&&e.subscribe_url&&(Xf(e.subscribe_url),window.$message.success(jf.global.t("复制成功")))}finally{i.value=!1}}))}';
if (user.includes(shadowedSubscriptionAction)) {
  user = user.replace(
    shadowedSubscriptionAction,
    ',q=_N(),k=Et(!1);function w(){return v(this,null,(function*(){k.value=!0;try{const e=yield q.getUserSubscribe();e&&e.subscribe_url&&(Xf(e.subscribe_url),window.$message.success(jf.global.t("复制成功")))}finally{k.value=!1}}))}',
  );
  user = replaceOnce(
    user,
    'loading:i.value,onClick:a},{default:hn((()=>[Dr("复制订阅链接")]))',
    'loading:k.value,onClick:w},{default:hn((()=>[Dr("复制订阅链接")]))',
    'subscription action bindings',
  );
}

const fragileNodeFetch = 'n.value=!0;const e=yield ON(),{data:o}=e;t.value=o,n.value=!1';
if (user.includes(fragileNodeFetch)) {
  user = user.replace(
    fragileNodeFetch,
    'n.value=!0;try{const e=yield ON();t.value=(null==e?void 0:e.data)||[]}finally{n.value=!1}',
  );
}
user = user.replace(
  'n.value=!0;try{const e=yield ON();t.value=(null==e?void 0:e.data)||[]}finally{n.value=!1}',
  'try{const e=yield ON();t.value=(null==e?void 0:e.data)||[]}finally{n.value=!1}',
);
user = user.replace(
  '__name:"index",setup(e){const t=Et([]),n=Et(!0),q=_N()',
  '__name:"index",setup(e){const t=Et([]),n=Et(!1),q=_N()',
);
user = user.replace(
  '!n.value&&0===t.value.length?',
  '!n.value&&!(t.value&&t.value.length)?',
);

const polishedEmptyState = '(_r(),zr("section",{key:2,class:"mx-auto mt-12 max-w-xl rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900/50"},[Ir("div",{class:"mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-xl text-gray-400 dark:bg-gray-800 dark:text-gray-500"},"◎"),Ir("h3",{class:"text-base font-semibold text-gray-900 dark:text-gray-100"},"暂无可用节点"),Ir("p",{class:"mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400"},"当前身份组还没有分配节点，请联系管理员完成配置。")]))';
const explicitEmptyState = '(_r(),zr("section",{key:4,class:"internal-empty-state-entry mx-auto mt-12 max-w-xl rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900/50"},[Ir("div",{class:"mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-xl text-gray-400 dark:bg-gray-800 dark:text-gray-500"},"◎"),Ir("h3",{class:"text-base font-semibold text-gray-900 dark:text-gray-100"},"暂无可用节点"),Ir("p",{class:"mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400"},"当前身份组还没有分配节点，请联系管理员完成配置。")]))';
if (!user.includes('internal-empty-state-entry')) {
  if (user.includes(polishedEmptyState)) {
    user = user.replace(polishedEmptyState, 'po("",!0)');
  }
  user = replaceOnce(
    user,
    ':po("",!0),n.value?',
    `:po("",!0),!n.value&&0===t.value.length?${explicitEmptyState}:po("",!0),n.value?`,
    'explicit empty node state',
  );
}

// Keep the original node-loading lifecycle, while using names that cannot be
// shadowed by the render function's minified component aliases.
user = user.replace(
  '__name:"index",setup(e){const t=Et([]),n=Et(!1),q=_N(),k=Et(!1);function w(){return v(this,null,(function*(){k.value=!0;try{const e=yield q.getUserSubscribe();e&&e.subscribe_url&&(Xf(e.subscribe_url),window.$message.success(jf.global.t("复制成功")))}finally{k.value=!1}}))}',
  '__name:"index",setup(e){const t=Et([]),n=Et(!0),subscriptionStore=BN(),copyingSubscription=Et(!1);function copySubscription(){return v(this,null,(function*(){copyingSubscription.value=!0;try{const e=yield subscriptionStore.getUserSubscribe();e&&e.subscribe_url&&(Xf(e.subscribe_url),window.$message.success(jf.global.t("复制成功")))}finally{copyingSubscription.value=!1}}))}',
);
user = user.replace('subscriptionStore=_N()', 'subscriptionStore=BN()');
user = user.replace(
  'const t=Et([]),n=Et(!0),subscriptionStore=BN(),copyingSubscription=Et(!1);function copySubscription(){return v(this,null,(function*(){copyingSubscription.value=!0;try{const e=yield subscriptionStore.getUserSubscribe();e&&e.subscribe_url&&(Xf(e.subscribe_url),window.$message.success(jf.global.t("复制成功")))}finally{copyingSubscription.value=!1}}))}',
  'const t=Et([]),n=Et(!0),copyingSubscription=Et(!1);function copySubscription(){return v(this,null,(function*(){copyingSubscription.value=!0;try{const e=yield EN.get("/user/getSubscribe"),{data:o}=e;o&&o.subscribe_url&&(Xf(o.subscribe_url),window.$message.success(jf.global.t("复制成功")))}finally{copyingSubscription.value=!1}}))}',
);
user = user.replace(
  'try{const e=yield ON();t.value=(null==e?void 0:e.data)||[]}finally{n.value=!1}',
  'n.value=!0;const e=yield ON(),{data:o}=e;t.value=o,n.value=!1',
);
user = user.replace(
  'n.value=!0;const e=yield ON(),{data:o}=e;t.value=o,n.value=!1',
  'n.value=!0;try{let e=yield ON(),o=Array.isArray(e)?e:Array.isArray(null==e?void 0:e.data)?e.data:Array.isArray(null==e||null==e.data?void 0:e.data.data)?e.data.data:null;Array.isArray(o)||(yield new Promise((e=>setTimeout(e,300))),e=yield ON(),o=Array.isArray(e)?e:Array.isArray(null==e?void 0:e.data)?e.data:Array.isArray(null==e||null==e.data?void 0:e.data.data)?e.data.data:null),t.value=Array.isArray(o)?o:[]}finally{n.value=!1}',
);
user = user.replace(
  'n.value=!0;try{let e=yield ON(),o=null==e?void 0:e.data;Array.isArray(o)||(yield new Promise((e=>setTimeout(e,300))),e=yield ON(),o=null==e?void 0:e.data),t.value=Array.isArray(o)?o:[]}finally{n.value=!1}',
  'n.value=!0;try{let e=yield ON(),o=Array.isArray(e)?e:Array.isArray(null==e?void 0:e.data)?e.data:Array.isArray(null==e||null==e.data?void 0:e.data.data)?e.data.data:null;Array.isArray(o)||(yield new Promise((e=>setTimeout(e,300))),e=yield ON(),o=Array.isArray(e)?e:Array.isArray(null==e?void 0:e.data)?e.data:Array.isArray(null==e||null==e.data?void 0:e.data.data)?e.data.data:null),t.value=Array.isArray(o)?o:[]}finally{n.value=!1}',
);
user = user.replace(
  'loading:k.value,onClick:w},{default:hn((()=>[Dr("复制订阅链接")]))',
  'loading:copyingSubscription.value,onClick:copySubscription},{default:hn((()=>[Dr("复制订阅链接")]))',
);
user = user.replaceAll(
  'Xf(o.subscribe_url),window.$message.success(jf.global.t("复制成功"))',
  'Xf(o.subscribe_url)',
);
user = user.replaceAll(
  'Xf(e.subscribe_url),window.$message.success(jf.global.t("复制成功"))',
  'Xf(e.subscribe_url)',
);
user = user.replace(
  'Array.isArray(o)||(yield new Promise((e=>setTimeout(e,300)))',
  'Array.isArray(o)&&o.length>0||(yield new Promise((e=>setTimeout(e,300)))',
);
user = user.replace(
  `,!n.value&&!(t.value&&t.value.length)?${explicitEmptyState}:po("",!0),n.value?`,
  ',n.value?',
);

writeFileSync(userPath, user);

const adminPath = resolve(root, 'public/assets/admin/assets/index-CEIYH7i8.js');
let admin = readFileSync(adminPath, 'utf8');

// Identity groups only grant node access. Remove traffic controls from bundles
// produced by the earlier internal-access patch.
admin = admin.replace(',transfer_enable_gb:yy().min(0).default(0)', '');
admin = admin.replace(',transfer_enable_gb:0', '');
if (admin.includes('control:s.control,name:"transfer_enable_gb"')) {
  admin = removeCallContaining(
    admin,
    'control:s.control,name:"transfer_enable_gb"',
    'Q.jsx($y,',
    'legacy group traffic field',
  );
}
const legacyGroupTrafficColumn = '{accessorKey:"transfer_enable",header:({column:e})=>Q.jsx(eQt,{column:e,title:"组流量"})';
if (admin.includes(legacyGroupTrafficColumn)) {
  admin = removeBalanced(admin, legacyGroupTrafficColumn, '{', '}', 'legacy group traffic column');
}

// Internal VPN mode manages nodes directly. Remove machine/plugin management
// navigation and routes, and keep the sidebar from requesting plugin metadata.
for (const [marker, label] of [
  ['{id:"plugin-management"', 'plugin navigation'],
  ['{id:"theme-config"', 'theme configuration navigation'],
  ['{id:"machine-management"', 'machine navigation'],
  ['{path:"plugin",lazy', 'plugin management route'],
  ['{path:"plugin/menu-demo",lazy', 'plugin menu demo route'],
  ['{path:"plugin/crud-demo",lazy', 'plugin crud demo route'],
  ['{path:"theme",lazy', 'theme configuration route'],
  ['{path:"plugins/:pluginCode/*",lazy', 'dynamic plugin route'],
  ['{path:"machine",lazy', 'machine management route'],
]) {
  if (admin.includes(marker)) {
    admin = removeBalanced(admin, marker, '{', '}', label);
  }
}

admin = admin.replace(
  'function Qlt(){const{data:e}=Ult();return H.useMemo(()=>{const t=Vlt.map(e=>({...e,sub:e.sub?.map(e=>({...e}))}));return[...t,...Zlt(e??[])]},[e])}',
  'function Qlt(){return H.useMemo(()=>Vlt.map(e=>({...e,sub:e.sub?.map(e=>({...e}))})),[])}',
);

if (!admin.includes('group_id:dy().nullable().default(null)')) {
  admin = removeBalanced(admin, '{id:"dashboard"', '{', '}', 'dashboard navigation');
  admin = removeBalanced(admin, '{id:"payment-config"', '{', '}', 'payment navigation');
  admin = removeBalanced(admin, '{id:"subscription-management"', '{', '}', 'finance navigation');
  admin = removeBalanced(admin, '{path:"payment",lazy', '{', '}', 'payment route');
  admin = removeBalanced(admin, '{path:"finance",errorElement', '{', '}', 'finance routes');
  admin = replaceOnce(
    admin,
    'index:!0,lazy:async()=>({Component:(await xp(async()=>{const{default:e}=await Promise.resolve().then(()=>$Gt);return{default:e}}',
    'index:!0,lazy:async()=>({Component:(await xp(async()=>{const{default:e}=await Promise.resolve().then(()=>U3t);return{default:e}}',
    'admin landing page',
  );

  admin = replaceOnce(
    admin,
    'plan_id:dy().nullable().default(null),banned:',
    'group_id:dy().nullable().default(null),plan_id:dy().nullable().default(null),banned:',
    'user group schema',
  );
  admin = replaceOnce(
    admin,
    't&&RD().then(({data:e})=>{l(e)})',
    't&&iD().then(({data:e})=>{l(e)})',
    'user group choices',
  );
  admin = replaceOnce(
    admin,
    'password:null,u:t.u?',
    'password:null,transfer_enable:t.personal_transfer_enable??t.transfer_enable,u:t.u?',
    'personal traffic edit value',
  );

  const planField = extractCall(admin, 'Q.jsx($y,{control:u.control,name:"plan_id"');
  let userGroupField = planField.text
    .replace('name:"plan_id"', 'name:"group_id"')
    .replace('e("edit.form.subscription")', '"身份组"')
    .replaceAll('e("edit.form.subscription_none")', '"请选择身份组"');
  admin = admin.slice(0, planField.start) + userGroupField + admin.slice(planField.end);

  for (const field of ['balance', 'commission_balance', 'commission_type', 'commission_rate', 'discount']) {
    admin = removeCallContaining(
      admin,
      `control:u.control,name:"${field}"`,
      'Q.jsx($y,',
      `user ${field} field`,
    );
  }

  admin = removeCallContaining(
    admin,
    'dialog.financialInfo',
    'Q.jsxs("div",',
    'user financial detail',
  );
  admin = removeCallContaining(
    admin,
    'columns.actions_menu.assign_order',
    'Q.jsx($st,',
    'assign order action',
  );
  admin = removeCallContaining(
    admin,
    '/finance/order?user_id=eq:',
    'Q.jsx($st,',
    'user orders action',
  );

  admin = replaceOnce(
    admin,
    'columns:H8t(h,0,v,_,f),',
    'columns:H8t(h,0,v,_,f).filter(e=>!["plan_id","balance","commission_balance"].includes(e.accessorKey)),',
    'user financial columns',
  );
  admin = replaceOnce(
    admin,
    'RD().then(({data:e})=>{_(e)})},[]);const v=f.map',
    '_([])},[]);const v=f.map',
    'remove plan list request',
  );

  writeFileSync(adminPath, admin);
  console.log(`Patched ${adminPath}`);
} else {
  console.log(`Already patched ${adminPath}`);
}

admin = admin.replace(
  'Q.jsx(Zy,{children:e("edit.form.total_traffic")})',
  'Q.jsx(Zy,{children:"用户可用流量（GB）"})',
);
admin = admin.replace(
  'Q.jsx(u8e,{type:"number",value:t.value/1024/1024/1024||"",onChange:e=>t.onChange(1024*parseInt(e.target.value)*1024*1024),placeholder:e("edit.form.total_traffic_placeholder"),className:"rounded-r-none"})',
  'Q.jsx(u8e,{type:"number",min:0,step:"any",value:0===t.value?0:t.value/1024/1024/1024||"",onChange:e=>t.onChange(1073741824*Number(e.target.value||0)),placeholder:"0 表示无限流量",className:"rounded-r-none"})',
);
writeFileSync(adminPath, admin);
