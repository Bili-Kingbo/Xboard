(function () {
  'use strict';

  const id = 'arkon-client-routing-entry';
  const styleId = 'arkon-client-routing-style';
  const ids = ['codex', 'claude', 'domestic', 'international', 'shedio'];
  const labels = { codex: 'OpenAI / Codex', claude: 'Claude', domestic: '国内', international: '国外', shedio: 'Shedio' };
  let documentState = null;

  function securePath() {
    const rawBase = String(window.settings?.base_url || '').trim();
    const base = rawBase === '/' ? '' : rawBase.replace(/\/+$/g, '');
    const path = String(window.settings?.secure_path || '').replace(/^\/+|\/+$/g, '');
    return `${base}/api/v2/${path}`;
  }

  function authHeaders() {
    let token = '';
    try { token = JSON.parse(localStorage.getItem('XBOARD_ACCESS_TOKEN') || '{}').value || ''; } catch (_) {}
    return { Authorization: token, 'Content-Type': 'application/json', 'Content-Language': localStorage.getItem('i18nextLng') || 'zh-CN' };
  }

  async function request(path, options = {}) {
    const response = await fetch(`${securePath()}${path}`, { ...options, headers: { ...authHeaders(), ...(options.headers || {}) } });
    const body = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(body.message || `请求失败（HTTP ${response.status}）`);
    return body.data;
  }

  function addStyle() {
    if (document.getElementById(styleId)) return;
    const style = document.createElement('style');
    style.id = styleId;
    style.textContent = `
      .arkon-routing-overlay{position:fixed;inset:0;z-index:100;display:flex;align-items:flex-start;justify-content:center;padding:6vh 16px;background:rgba(15,23,42,.42);backdrop-filter:blur(4px)}
      .arkon-routing-dialog{width:min(860px,100%);max-height:88vh;overflow:auto;border:1px solid hsl(var(--border));border-radius:18px;background:hsl(var(--background));color:hsl(var(--foreground));box-shadow:0 26px 80px rgba(15,23,42,.24)}
      .arkon-routing-head{display:flex;align-items:flex-start;justify-content:space-between;padding:20px 24px;border-bottom:1px solid hsl(var(--border))}.arkon-routing-head h2{margin:0;font-size:18px}.arkon-routing-head p{margin:6px 0 0;color:hsl(var(--muted-foreground));font-size:12px}.arkon-routing-close{border:0;background:transparent;color:hsl(var(--muted-foreground));font-size:22px;cursor:pointer}
      .arkon-routing-body{display:grid;grid-template-columns:180px 1fr;gap:16px;padding:18px 24px}.arkon-routing-tabs{display:flex;flex-direction:column;gap:5px}.arkon-routing-tab{border:1px solid transparent;border-radius:10px;background:transparent;color:hsl(var(--muted-foreground));padding:11px 12px;text-align:left;cursor:pointer}.arkon-routing-tab.active{border-color:hsl(var(--primary)/.3);background:hsl(var(--primary)/.1);color:hsl(var(--foreground));font-weight:600}
      .arkon-routing-editor{display:flex;flex-direction:column;gap:12px}.arkon-routing-editor label{font-size:12px;font-weight:600}.arkon-routing-editor select,.arkon-routing-editor textarea{width:100%;box-sizing:border-box;border:1px solid hsl(var(--border));border-radius:9px;background:hsl(var(--background));color:inherit;padding:9px 10px;font:12px/1.6 ui-monospace,SFMono-Regular,Menlo,monospace}.arkon-routing-editor textarea{min-height:260px;resize:vertical}.arkon-routing-hint{color:hsl(var(--muted-foreground));font-size:11px;line-height:1.6}.arkon-routing-preview-panel{grid-column:1/-1;padding:14px;border:1px solid hsl(var(--border));border-radius:10px}.arkon-routing-preview-panel[hidden]{display:none}.arkon-routing-preview-controls{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.arkon-routing-preview-user{width:120px;padding:8px;border:1px solid hsl(var(--border));border-radius:8px;background:hsl(var(--background));color:inherit}.arkon-routing-preview-yaml{max-height:250px;overflow:auto;margin:10px 0 0;padding:12px;border-radius:8px;background:hsl(var(--muted)/.45);white-space:pre;font:11px/1.6 ui-monospace,SFMono-Regular,Menlo,monospace}.arkon-routing-foot{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 24px;border-top:1px solid hsl(var(--border))}.arkon-routing-foot-actions{display:flex;gap:8px;flex-wrap:wrap}.arkon-routing-save,.arkon-routing-preview,.arkon-routing-preview-run{border:0;border-radius:9px;background:hsl(var(--primary));color:hsl(var(--primary-foreground));padding:9px 16px;font-weight:600;cursor:pointer}.arkon-routing-preview{border:1px solid hsl(var(--border));background:transparent;color:hsl(var(--foreground))}.arkon-routing-save:disabled,.arkon-routing-preview-run:disabled{opacity:.55;cursor:not-allowed}@media(max-width:680px){.arkon-routing-body{grid-template-columns:1fr}.arkon-routing-tabs{display:grid;grid-template-columns:repeat(3,1fr)}.arkon-routing-foot{align-items:flex-start;flex-direction:column}}`;
    document.head.appendChild(style);
  }

  function toast(message, error = false) {
    const item = document.createElement('div');
    item.textContent = message;
    item.style.cssText = `position:fixed;left:50%;bottom:26px;z-index:110;transform:translateX(-50%);padding:10px 16px;border-radius:999px;background:${error ? '#991b1b' : '#0f766e'};color:white;font-size:12px;box-shadow:0 10px 28px rgba(0,0,0,.2)`;
    document.body.appendChild(item);
    setTimeout(() => item.remove(), 2600);
  }

  function activeProfile(id) { return documentState.profiles[id] || { target: 'direct', rules_text: '' }; }

  function open() {
    if (document.querySelector('.arkon-routing-overlay')) return;
    addStyle();
    const overlay = document.createElement('div'); overlay.className = 'arkon-routing-overlay';
    const dialog = document.createElement('div'); dialog.className = 'arkon-routing-dialog';
    dialog.innerHTML = `<div class="arkon-routing-head"><div><h2>客户端分流</h2><p>为每类流量选择直连或节点，并维护域名、IP、CIDR 与正则规则。</p></div><button class="arkon-routing-close" aria-label="关闭">×</button></div><div class="arkon-routing-body"><div class="arkon-routing-tabs"></div><div class="arkon-routing-editor"></div><div class="arkon-routing-preview-panel" hidden><div class="arkon-routing-preview-controls"><label for="arkon-preview-user">按用户预览</label><input id="arkon-preview-user" class="arkon-routing-preview-user" type="number" min="1" placeholder="用户 ID"><button class="arkon-routing-preview-run">生成预览</button><span class="arkon-routing-preview-status arkon-routing-hint">展示已保存规则和该用户可见的节点，连接凭据已隐藏。</span></div><pre class="arkon-routing-preview-yaml" hidden></pre></div></div><div class="arkon-routing-foot"><span class="arkon-routing-hint">支持 DOMAIN、DOMAIN-SUFFIX、DOMAIN-KEYWORD、DOMAIN-REGEX、IP-CIDR、IP-ASN、RULE-SET；也可每行直接填写域名或 CIDR。</span><div class="arkon-routing-foot-actions"><button class="arkon-routing-preview">预览 Clash 下发</button><button class="arkon-routing-save">保存规则</button></div></div>`;
    overlay.appendChild(dialog); document.body.appendChild(overlay);
    const tabs = dialog.querySelector('.arkon-routing-tabs'); const editor = dialog.querySelector('.arkon-routing-editor'); let selected = ids[0];
    function render() {
      tabs.innerHTML = ids.map((id) => `<button class="arkon-routing-tab ${id === selected ? 'active' : ''}" data-id="${id}">${labels[id]}</button>`).join('');
      const profile = activeProfile(selected);
      editor.innerHTML = `<label>出口方式</label><select class="arkon-routing-target"><option value="direct" ${profile.target === 'direct' ? 'selected' : ''}>直连</option><option value="proxy" ${profile.target === 'proxy' ? 'selected' : ''}>代理节点（由客户端选择）</option></select><label>规则</label><textarea class="arkon-routing-text"></textarea><div class="arkon-routing-hint">国内默认使用 geosite-cn / geoip-cn；国外是除 OpenAI、Claude、Shedio、国内之外的剩余流量。Clash Meta 订阅会生成同名策略组，节点在管理端勾选用途后可由用户手动选择。</div>`;
      editor.querySelector('.arkon-routing-text').value = profile.rules_text || '';
      tabs.querySelectorAll('button').forEach((button) => button.onclick = () => { documentState.profiles[selected].target = editor.querySelector('.arkon-routing-target').value; documentState.profiles[selected].rules_text = editor.querySelector('.arkon-routing-text').value; selected = button.dataset.id; render(); });
    }
    dialog.querySelector('.arkon-routing-close').onclick = () => overlay.remove();
    dialog.querySelector('.arkon-routing-preview').onclick = () => {
      const panel = dialog.querySelector('.arkon-routing-preview-panel');
      panel.hidden = !panel.hidden;
    };
    dialog.querySelector('.arkon-routing-preview-run').onclick = async () => {
      const userId = Number(dialog.querySelector('.arkon-routing-preview-user').value);
      const status = dialog.querySelector('.arkon-routing-preview-status');
      const output = dialog.querySelector('.arkon-routing-preview-yaml');
      if (!Number.isInteger(userId) || userId < 1) { status.textContent = '请填写用户管理中的用户 ID。'; return; }
      const button = dialog.querySelector('.arkon-routing-preview-run');
      button.disabled = true;
      status.textContent = '正在生成…';
      try {
        const data = await request('/routing/preview?user_id=' + encodeURIComponent(userId));
        status.textContent = `${data.email} · ${data.node_count} 个节点 · 已隐藏连接凭据`;
        output.textContent = data.yaml;
        output.hidden = false;
      } catch (error) {
        status.textContent = error.message;
        output.hidden = true;
      } finally {
        button.disabled = false;
      }
    };
    dialog.querySelector('.arkon-routing-save').onclick = async () => { const save = dialog.querySelector('.arkon-routing-save'); save.disabled = true; documentState.profiles[selected].target = editor.querySelector('.arkon-routing-target').value; documentState.profiles[selected].rules_text = editor.querySelector('.arkon-routing-text').value; try { documentState = await request('/routing/save', { method: 'POST', body: JSON.stringify(documentState) }); toast('分流规则已保存'); render(); } catch (error) { toast(error.message, true); } finally { save.disabled = false; } };
    render();
  }

  async function load() { documentState = await request('/routing/fetch'); open(); }

  function installEntry() {
    if (document.getElementById(id)) return;
    const settingLink = [...document.querySelectorAll('a')].find((item) => String(item.getAttribute('href') || '').includes('/config/system'));
    const list = settingLink?.parentElement?.parentElement;
    if (!settingLink || list?.tagName !== 'UL') return;
    const item = document.createElement('li');
    item.className = settingLink.parentElement.className;
    const entry = document.createElement('button');
    entry.id = id;
    entry.type = 'button';
    entry.className = settingLink.className;
    const routeLink = [...document.querySelectorAll('a')].find((link) => String(link.getAttribute('href') || '').includes('/server/route'));
    entry.append((routeLink?.firstElementChild || settingLink.firstElementChild)?.cloneNode(true) || document.createElement('span'), document.createTextNode('客户端分流'));
    entry.onclick = () => load().catch((error) => toast(error.message, true));
    item.appendChild(entry);
    list.appendChild(item);
  }
  new MutationObserver(installEntry).observe(document.documentElement, { childList: true, subtree: true });
  setTimeout(installEntry, 900);
})();
