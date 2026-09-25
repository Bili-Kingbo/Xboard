import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const bundlePath = resolve(root, 'public/assets/admin/assets/index-CEIYH7i8.js');
let bundle = readFileSync(bundlePath, 'utf8');

function replaceOnce(search, replacement, label, marker) {
  if (marker && bundle.includes(marker)) {
    console.log(`Already patched: ${label}`);
    return false;
  }
  const occurrences = bundle.split(search).length - 1;
  if (occurrences === 0) {
    throw new Error(`${label}: signature not found`);
  }
  if (occurrences !== 1) {
    throw new Error(`${label}: expected exactly one match, found ${occurrences}`);
  }
  bundle = bundle.replace(search, replacement);
  return true;
}

let changed = false;

/**
 * 外部节点（special-<id>）与原生命题共用一张表，
 * 行内的开关与操作菜单必须走外部节点自己的接口，避免误删原生节点。
 */
changed = replaceOnce(
  'function F5t({node:e,refetch:t}){const[n,i]=H.useState(Boolean(e.show));return Q.jsx(oZt,{checked:n,onCheckedChange:async n=>{i(n),XL({id:e.id,type:e.type,show:n?1:0}).catch(()=>{i(!n),t()})},style:{backgroundColor:n?sqt[e.type]:void 0}})}',
  'function F5t({node:e,refetch:t}){const[n,i]=H.useState(Boolean(e.show));return Q.jsx(oZt,{checked:n,onCheckedChange:async n=>{if(e.is_special){i(n),window.__xboardNodeDialog.toggleSpecial(e,n,t);return}i(n),XL({id:e.id,type:e.type,show:n?1:0}).catch(()=>{i(!n),t()})},style:{backgroundColor:n?sqt[e.type]:void 0}})}',
  'node row visibility switch',
  'if(e.is_special){i(n),window.__xboardNodeDialog.toggleSpecial',
) || changed;

const externalRowActions = 'const xndRowActions=({node:e,refetch:t,t:n})=>Q.jsx("div",{className:"flex justify-center",children:Q.jsxs(Vst,{modal:!1,children:[Q.jsx(Wst,{asChild:!0,children:Q.jsx(Lf,{variant:"ghost",className:"h-8 w-8 p-0 hover:bg-muted","aria-label":n("columns.actions"),children:Q.jsx(Fat,{className:"size-4"})})}),Q.jsxs(Ust,{align:"end",className:"w-44",children:[Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.editSpecial(e,t),children:Q.jsxs("div",{className:"flex w-full items-center",children:[Q.jsx(YXt,{icon:"ion:create-outline",className:"mr-2 size-4"}),"编辑外部节点"]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.assignUsers(e,t),children:Q.jsxs("div",{className:"flex w-full items-center",children:[Q.jsx(YXt,{icon:"ion:person-add-outline",className:"mr-2 size-4"}),"分配给个人"]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.toggleSpecial(e,!e.show,t),children:Q.jsxs("div",{className:"flex w-full items-center",children:[Q.jsx(YXt,{icon:e.show?"ion:pause-outline":"ion:play-outline",className:"mr-2 size-4"}),e.show?"暂停下发":"恢复下发"]})}),Q.jsx($st,{className:"cursor-pointer text-destructive focus:text-destructive",onClick:()=>window.__xboardNodeDialog.deleteSpecial(e,t),children:Q.jsxs("div",{className:"flex w-full items-center",children:[Q.jsx(YXt,{icon:"ion:trash-outline",className:"mr-2 size-4"}),"删除"]})})]})]})});';

changed = replaceOnce(
  'function B5t({node:e,refetch:t,t:n}){const{setIsOpen:i,setEditingServer:r,setServerType:s}=u4t();return Q.jsx("div",{className:"flex justify-center",children:Q.jsxs(Vst,{modal:!1,children:[',
  `${externalRowActions}function B5t({node:e,refetch:t,t:n}){if(e.is_special)return Q.jsx(xndRowActions,{node:e,refetch:t,t:n});const{setIsOpen:i,setEditingServer:r,setServerType:s}=u4t();return Q.jsx("div",{className:"flex justify-center",children:Q.jsxs(Vst,{modal:!1,children:[`,
  'node row actions menu',
  'if(e.is_special)return Q.jsx(xndRowActions',
) || changed;

changed = replaceOnce(
  'n("columns.actions_dropdown.copy")]}),Q.jsx($st,{className:"cursor-pointer",onSelect:e=>e.preventDefault(),children:Q.jsx(hQt,{title:n("columns.actions_dropdown.reset_traffic.title")',
  'n("columns.actions_dropdown.copy")]}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.assignUsers(e,t),children:Q.jsxs("div",{className:"flex w-full items-center",children:[Q.jsx(YXt,{icon:"ion:person-add-outline",className:"mr-2 size-4"}),"分配给个人"]})}),Q.jsx($st,{className:"cursor-pointer",onSelect:e=>e.preventDefault(),children:Q.jsx(hQt,{title:n("columns.actions_dropdown.reset_traffic.title")',
  'assign users to native node',
  'n("columns.actions_dropdown.copy")]}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.assignUsers(e,t)',
) || changed;

changed = replaceOnce(
  '"编辑外部节点"]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.assignUsers(e,t)',
  '"编辑外部节点"]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.editRouting(e,t),children:Q.jsxs("div",{className:"flex w-full items-center",children:[Q.jsx(YXt,{icon:"ion:git-branch-outline",className:"mr-2 size-4"}),"Clash 分流用途"]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.assignUsers(e,t)',
  'external node manual Clash routing action',
  '"编辑外部节点"]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.editRouting(e,t)',
) || changed;

changed = replaceOnce(
  'n("columns.actions_dropdown.edit")]})}),Q.jsxs($st,{className:"cursor-pointer",onClick:async()=>{YL',
  'n("columns.actions_dropdown.edit")]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.editRouting(e,t),children:Q.jsxs("div",{className:"flex w-full items-center",children:[Q.jsx(YXt,{icon:"ion:git-branch-outline",className:"mr-2 size-4"}),"Clash 分流用途"]})}),Q.jsxs($st,{className:"cursor-pointer",onClick:async()=>{YL',
  'native node manual Clash routing action',
  'n("columns.actions_dropdown.edit")]})}),Q.jsx($st,{className:"cursor-pointer",onClick:()=>window.__xboardNodeDialog.editRouting(e,t)',
) || changed;

changed = replaceOnce(
  'children:e.name})]})),$5t=',
  'children:e.name}),e.is_special?Q.jsx(nKt,{variant:"outline",className:"ml-1.5 border-primary/40 px-1.5 py-0 text-[10px] font-normal text-primary",children:"外部导入"}):null]})),$5t=',
  'external node badge',
  'children:"外部导入"}',
) || changed;

changed = replaceOnce(
  'cell:({row:e})=>{const n=e.original.groups||[];return Q.jsxs("div",{className:"flex flex-wrap gap-1.5",children:[n.map(',
  'cell:({row:e})=>{const n=[...(e.original.groups||[]),...((e.original.users||[]).map(u=>({id:"user-"+u.id,name:"@"+String(u.email||"").split("@")[0]})))];return Q.jsxs("div",{className:"flex flex-wrap gap-1.5",children:[n.map(',
  'group column shows individually assigned users',
  '...(e.original.groups||[]),...((e.original.users||[])',
) || changed;

changed = replaceOnce(
  'o({"drag-handle":g,show:!g,host:!g,online:!g,rate:!g,group_ids:!g,type:!1,actions:!g})',
  'o({"drag-handle":g,show:!g,host:!g,online:!g,rate:!g,group_ids:!1,type:!1,actions:!g})',
  'group column visible by default',
  'group_ids:!1,type:!1,actions:!g',
) || changed;

changed = replaceOnce(
  'children:[Q.jsxs(Lf,{variant:"outline",size:"sm",className:"space-x-2",onClick:u,children:[Q.jsx(YXt,{icon:"ion:add"}),Q.jsx("div",{children:e("form.add_node")})]}),Q.jsxs(ptt,',
  'children:[Q.jsxs("div",{className:"flex items-center gap-2",children:[Q.jsxs(Lf,{variant:"outline",size:"sm",className:"space-x-2",onClick:u,children:[Q.jsx(YXt,{icon:"ion:add"}),Q.jsx("div",{children:e("form.add_node")})]}),Q.jsxs(Lf,{variant:"outline",size:"sm",className:"space-x-2",onClick:()=>window.__xboardOpenSpecialImport&&window.__xboardOpenSpecialImport(h),children:[Q.jsx(YXt,{icon:"ion:link-outline"}),Q.jsx("div",{children:"导入外部节点"})]})]}),Q.jsxs(ptt,',
  'import external node button',
  'window.__xboardOpenSpecialImport&&window.__xboardOpenSpecialImport(h)',
) || changed;

if (changed) {
  writeFileSync(bundlePath, bundle);
  console.log(`Patched ${bundlePath}`);
} else {
  console.log(`Already patched ${bundlePath}`);
}
