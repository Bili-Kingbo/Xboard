import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const bundlePath = resolve(root, 'public/assets/admin/assets/index-CEIYH7i8.js');
let bundle = readFileSync(bundlePath, 'utf8');

const remotePlanFetch = 'RD=()=>IL(ID+"/plan/fetch")';
const disabledPlanFetch = 'RD=()=>Promise.resolve({data:[]})';

if (bundle.includes(remotePlanFetch)) {
  bundle = bundle.replace(remotePlanFetch, disabledPlanFetch);
  writeFileSync(bundlePath, bundle);
  console.log(`Disabled obsolete plan fetch in ${bundlePath}`);
} else if (bundle.includes(disabledPlanFetch)) {
  console.log(`Already patched ${bundlePath}`);
} else {
  throw new Error('Admin plan fetch signature was not found');
}

bundle = readFileSync(bundlePath, 'utf8');
const exactRemovals = [
  [',{path:"subscribe",lazy:async()=>({Component:(await xp(async()=>{const{default:e}=await Promise.resolve().then(()=>vZt);return{default:e}},void 0,import.meta.url)).default})}', 'commercial subscription settings route'],
  [',{path:"invite",lazy:async()=>({Component:(await xp(async()=>{const{default:e}=await Promise.resolve().then(()=>wZt);return{default:e}},void 0,import.meta.url)).default})}', 'invite commission settings route'],
  [',{path:"knowledge",lazy:async()=>({Component:(await xp(async()=>{const{default:e}=await Promise.resolve().then(()=>n4t);return{default:e}},void 0,import.meta.url)).default})}', 'knowledge route'],
  [',{id:"knowledge-management",title:"nav:knowledgeManagement",label:"",href:"/config/knowledge",icon:Q.jsx(af,{size:18})}', 'knowledge navigation'],
  [',{title:"subscribe.title",key:"subscribe",icon:Q.jsx(wf,{size:18}),href:"/config/system/subscribe",description:"subscribe.description"}', 'subscription settings tab'],
  [',{title:"invite.title",key:"invite",icon:Q.jsx(Cf,{size:18}),href:"/config/system/invite",description:"invite.description"}', 'invite commission settings tab'],
  [',{data:s}=pC({queryKey:["plans"],queryFn:()=>RD()})', 'site plan query'],
  [',try_out_plan_id:dy().nullable().default(0),try_out_hour:yy().nullable().default(0)', 'trial plan schema'],
  [',currency:cy().nullable().default(""),currency_symbol:cy().nullable().default("")', 'currency schema'],
];

let cleaned = bundle;
const removed = [];
for (const [signature, label] of exactRemovals) {
  if (cleaned.includes(signature)) {
    cleaned = cleaned.replace(signature, '');
    removed.push(label);
  }
}

const siteComponentStart = cleaned.indexOf('function cZt(){');
const siteComponentEnd = cleaned.indexOf('}const dZt=', siteComponentStart);
if (siteComponentStart < 0 || siteComponentEnd < 0) {
  throw new Error('Site settings component boundaries were not found');
}
const commercialFieldsStart = cleaned.indexOf(',Q.jsx($y,{control:o.control,name:"try_out_plan_id"', siteComponentStart);
const savingIndicator = cleaned.indexOf(',t&&Q.jsx("div",{className:"text-sm text-muted-foreground",children:e("site.form.saving")})', siteComponentStart);
if (commercialFieldsStart >= 0 && savingIndicator > commercialFieldsStart && savingIndicator < siteComponentEnd) {
  cleaned = cleaned.slice(0, commercialFieldsStart) + cleaned.slice(savingIndicator);
  removed.push('trial and currency controls');
}

if (cleaned !== bundle) {
  writeFileSync(bundlePath, cleaned);
  console.log(`Removed internal-mode admin leftovers (${removed.join(', ')}) from ${bundlePath}`);
} else {
  console.log(`Admin commercial and knowledge UI already cleaned in ${bundlePath}`);
}
