/**
 * ARQUIVO 11 — PORTAL
 * Entrega ao portal (WordPress) os autores da revista: nome, cargo, mini currículo e foto.
 * Só responde a quem mandar a chave guardada em Configurações do projeto › Propriedades do script › PORTAL_CHAVE.
 * Não altera nada no sistema: só lê. Não entrega e-mails, usuários, métricas nem financeiro.
 *
 * Endereços (implantação "Portal", acesso "Qualquer pessoa"):
 *   …/exec?portal=autores&chave=…   → lista de autores
 *   …/exec?portal=foto&id=…&chave=… → uma foto (só fotos das pastas do sistema)
 */

function portalResposta_(e) {
  const p = (e && e.parameter) || {};
  const chave = PropertiesService.getScriptProperties().getProperty('PORTAL_CHAVE') || '';
  if (chave.length < 32 || !portalIguais_(String(p.chave || ''), chave)) return portalJson_({ ok: false, erro: 'chave' });
  try {
    if (p.portal === 'autores') return portalJson_({ ok: true, autores: portalAutores_() });
    if (p.portal === 'foto') return portalJson_(portalFoto_(String(p.id || '')));
    return portalJson_({ ok: false, erro: 'pedido' });
  } catch (err) {
    console.error('Portal: ' + (err && err.message));
    return portalJson_({ ok: false, erro: String((err && err.message) || err) });
  }
}

/** Compara a chave sem revelar, pelo tempo de resposta, onde ela difere. */
function portalIguais_(a, b) {
  if (a.length !== b.length) return false;
  let d = 0;
  for (let i = 0; i < a.length; i++) d |= a.charCodeAt(i) ^ b.charCodeAt(i);
  return d === 0;
}

function portalJson_(o) {
  return ContentService.createTextOutput(JSON.stringify(o)).setMimeType(ContentService.MimeType.JSON);
}

function portalChaveNome_(s) {
  return String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
}

/**
 * Um registro por pessoa (pelo nome). Quando a pessoa aparece em várias edições,
 * cada dado vem da versão mais recente que o tem preenchido.
 */
function portalAutores_() {
  LINHAS_CACHE_ = null;
  const linhas = lerLinhas_();
  const edicoes = {};
  linhas.forEach(function (l) {
    if (!l.excluido && /^edicoes\/[^\/]+$/.test(l.caminho)) edicoes[l.caminho.split('/')[1]] = dadosDe_(l) || {};
  });
  const pessoas = {};
  linhas.forEach(function (l) {
    const m = l.caminho.match(/^edicoes\/([^\/]+)\/itens\/[^\/]+$/);
    if (!m || l.excluido || !edicoes[m[1]]) return;
    const it = dadosDe_(l);
    if (!it) return;
    const nome = String(it.autor || '').trim();
    if (!nome) return;
    const k = portalChaveNome_(nome);
    const p = pessoas[k] || (pessoas[k] = { chave: k, campos: {} });
    const pegar = function (campo, valor) {
      if (valor === null || valor === undefined || valor === '') return;
      const atual = p.campos[campo];
      if (!atual || atual.marca < l.marca) p.campos[campo] = { marca: l.marca, valor: valor };
    };
    pegar('nome', nome);
    pegar('tipo', String(it.tipo || ''));
    pegar('minicurriculo', String(it.minicurriculo || '').trim());
    pegar('cargo', String(it.cargo || it.autorCargo || it.cargoAutor || '').trim());
    pegar('edicao', String(edicoes[m[1]].nome || ''));
    if (it.foto && /^[A-Za-z0-9_-]{10,100}$/.test(String(it.foto.id || ''))) pegar('fotoId', String(it.foto.id));
  });
  return Object.keys(pessoas).map(function (k) {
    const c = pessoas[k].campos;
    const v = function (n) { return c[n] ? c[n].valor : ''; };
    let foto = null;
    if (v('fotoId')) {
      try {
        const f = DriveApp.getFileById(v('fotoId'));
        if (!f.isTrashed() && fotoDoSistema_(f)) foto = { id: f.getId(), versao: f.getLastUpdated().getTime() };
      } catch (err) { /* foto apagada ou sem acesso: segue sem foto */ }
    }
    return { chave: k, nome: v('nome'), tipo: v('tipo'), minicurriculo: v('minicurriculo'), cargo: v('cargo'), edicao: v('edicao'), foto: foto };
  });
}

function portalFoto_(id) {
  if (!/^[A-Za-z0-9_-]{10,100}$/.test(id)) return { ok: false, erro: 'foto inválida' };
  let f;
  try { f = DriveApp.getFileById(id); } catch (err) { return { ok: false, erro: 'foto não encontrada' }; }
  if (f.isTrashed() || !fotoDoSistema_(f)) return { ok: false, erro: 'foto não encontrada' };
  const blob = f.getBlob();
  if (TIPOS_FOTO.indexOf(blob.getContentType()) < 0) return { ok: false, erro: 'formato não aceito' };
  const bytes = blob.getBytes();
  if (bytes.length > 6 * 1024 * 1024) return { ok: false, erro: 'foto maior que 6 MB' };
  return { ok: true, mime: blob.getContentType(), nome: f.getName(), base64: Utilities.base64Encode(bytes) };
}
