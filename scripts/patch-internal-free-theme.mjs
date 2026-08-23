import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const bundlePath = resolve(root, 'theme/Xboard/assets/umi.js');
let bundle = readFileSync(bundlePath, 'utf8');

const patchedMarkers = [
  'registration_groups',
  'group_id:c.group_id',
  'n.push("/node")',
  '当前身份组暂无可用节点，请联系管理员。',
];
if (patchedMarkers.every((marker) => bundle.includes(marker))) {
  console.log(`Already patched ${bundlePath}`);
  process.exit(0);
}

function replaceOnce(search, replacement, label) {
  const occurrences = bundle.split(search).length - 1;
  if (occurrences !== 1) {
    throw new Error(`${label}: expected exactly one match, found ${occurrences}`);
  }
  bundle = bundle.replace(search, replacement);
}

replaceOnce(
  'c=vt({email:"",email_code:"",password:"",confirm_password:"",confirm:"",invite_code:"",lock_invite_code:!1,suffix:""})',
  'c=vt({email:"",email_code:"",password:"",confirm_password:"",confirm:"",invite_code:"",group_id:"",lock_invite_code:!1,suffix:""})',
  'registration form state',
);

replaceOnce(
  'if(!e||!t)return void window.$message.warning(r("请输入账号密码"));if(t!==i)',
  'if(!e||!t)return void window.$message.warning(r("请输入账号密码"));if(!c.group_id)return void window.$message.warning(r("请选择身份组"));if(t!==i)',
  'registration group validation',
);

replaceOnce(
  'email:i,password:e,invite_code:t,email_code:o',
  'email:i,password:e,invite_code:t,email_code:o,group_id:c.group_id',
  'registration request payload',
);

replaceOnce(
  'fn(Ir("div",yQ,[Lr(i,{value:c.invite_code',
  'fn(Ir("div",yQ,[Ir("select",{value:c.group_id,onChange:n[30]||(n[30]=e=>c.group_id=Number(e.target.value)),class:"w-full h-9 rounded-md border border-gray-300 bg-[--n-color] px-3 text-base"},[Ir("option",{value:"",disabled:""},"请选择身份组"),(_r(!0),zr(yr,null,eo((null==(o=x.value)?void 0:o.registration_groups)||[],(e=>(_r(),zr("option",{key:e.id,value:e.id},oe(e.name),9,["value"])))),128))],40,["value"])],512),[[Mi,["register"].includes($.value)]]),fn(Ir("div",yQ,[Lr(i,{value:c.invite_code',
  'registration group selector',
);

replaceOnce(
  'ys=Object.assign({"/src/views/dashboard/route.ts":es,"/src/views/invite/route.ts":ns,"/src/views/knowledge/route.ts":rs,"/src/views/node/route.ts":as,"/src/views/order/route.ts":ss,"/src/views/plan/route.ts":us,"/src/views/profile/route.ts":ps,"/src/views/ticket/route.ts":fs,"/src/views/traffic/route.ts":ms})',
  'ys=Object.assign({"/src/views/knowledge/route.ts":rs,"/src/views/node/route.ts":as,"/src/views/profile/route.ts":ps,"/src/views/ticket/route.ts":fs,"/src/views/traffic/route.ts":ms})',
  'internal navigation routes',
);

replaceOnce(
  'Dr(oe(e.$t("没有可用节点，如果您未订阅或已过期请"))+" ",1),Lr(d,{class:"font-semibold",to:"/plan"},{default:hn((()=>[Dr(oe(e.$t("订阅")),1)])),_:1}),Dr("。 ")',
  'Dr(oe(e.$t("当前身份组暂无可用节点，请联系管理员。")),1)',
  'empty node state',
);

const dashboardRedirects = bundle.split(':"/dashboard"').length - 1;
if (dashboardRedirects !== 5) {
  throw new Error(`node redirects: expected exactly five matches, found ${dashboardRedirects}`);
}
bundle = bundle.replaceAll(':"/dashboard"', ':"/node"');
replaceOnce('n.push("/")', 'n.push("/node")', 'registration redirect');

writeFileSync(bundlePath, bundle);
console.log(`Patched ${bundlePath}`);
