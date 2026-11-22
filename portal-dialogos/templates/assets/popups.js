/* Popups do portal Diálogos Estruturantes
   Os textos, imagens, links e regras vêm do painel (no WordPress: tipo de conteúdo "Popup").
   Aqui o painel é simulado por window.POPUPS. Só um popup aparece por página. */
(function () {
  var dl = window.dataLayer = window.dataLayer || [];
  var LS = { get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
             set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} } };
  var aberto = null;

  function esc(t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

  function html(p) {
    var botao = p.botao_texto ? '<a class="btn btn-laranja pp-cta" href="' + esc(p.botao_link || '#') + '"' + (p.nova_aba ? ' target="_blank" rel="noopener"' : '') + '>' + esc(p.botao_texto) + '</a>' : '';
    var fechar = '<button type="button" class="pp-fechar" aria-label="Fechar">×</button>';
    var chapeu = p.chapeu ? '<span class="pp-chapeu">' + esc(p.chapeu) + '</span>' : '';
    if (p.modelo === 'modal') {
      return '<div class="pp-fundo" data-fecha></div><div class="pp pp-modal pp-' + esc(p.cor || 'marinho') + '" role="dialog" aria-modal="true" aria-labelledby="pp-t">' + fechar +
        (p.imagem ? '<div class="pp-img"><img src="' + esc(p.imagem) + '" alt=""></div>' : '') +
        '<div class="pp-corpo">' + chapeu + '<h2 id="pp-t">' + esc(p.titulo) + '</h2><p>' + esc(p.texto) + '</p>' + botao +
        '<button type="button" class="pp-agora-nao" data-fecha>Agora não</button></div></div>';
    }
    if (p.modelo === 'canto') {
      return '<div class="pp pp-canto pp-' + esc(p.cor || 'marinho') + '" role="dialog" aria-labelledby="pp-t">' + fechar +
        (p.imagem ? '<img class="pp-mini" src="' + esc(p.imagem) + '" alt="">' : '') +
        '<div>' + chapeu + '<h2 id="pp-t">' + esc(p.titulo) + '</h2><p>' + esc(p.texto) + '</p>' + botao + '</div></div>';
    }
    return '<div class="pp pp-faixa pp-' + esc(p.cor || 'marinho') + ' pp-' + (p.posicao === 'topo' ? 'topo' : 'base') + '" role="region" aria-label="Aviso">' +
      '<div class="pp-faixa-in">' + chapeu + '<p><b>' + esc(p.titulo) + '</b> ' + esc(p.texto) + '</p>' + botao + '</div>' + fechar + '</div>';
  }

  function fechar(p, motivo) {
    if (!aberto) return;
    aberto.remove(); aberto = null;
    document.removeEventListener('keydown', esc_);
    LS.set('popup_' + p.id, String(Date.now()));
    dl.push({ event: 'popup_fechado', popup_id: p.id, popup_modelo: p.modelo, motivo: motivo });
  }
  var esc_ = null;

  function mostrar(p, forcar) {
    if (aberto) { aberto.remove(); aberto = null; }
    var w = document.createElement('div'); w.className = 'pp-raiz'; w.innerHTML = html(p);
    document.body.appendChild(w); aberto = w;
    requestAnimationFrame(function () { w.classList.add('pp-visivel'); });
    dl.push({ event: 'popup_exibido', popup_id: p.id, popup_modelo: p.modelo, gatilho: forcar ? 'previa' : p.gatilho });
    w.querySelectorAll('.pp-fechar,[data-fecha]').forEach(function (b) { b.addEventListener('click', function () { fechar(p, 'botao'); }); });
    var cta = w.querySelector('.pp-cta');
    if (cta) cta.addEventListener('click', function () { dl.push({ event: 'popup_clique', popup_id: p.id, popup_modelo: p.modelo }); LS.set('popup_' + p.id, String(Date.now())); });
    esc_ = function (e) { if (e.key === 'Escape') fechar(p, 'esc'); };
    document.addEventListener('keydown', esc_);
    if (p.modelo === 'modal') { var f = w.querySelector('.pp-cta') || w.querySelector('.pp-fechar'); if (f) f.focus(); }
  }

  function pode(p) {
    var hoje = new Date().toISOString().slice(0, 10);
    if (p.ativo === false) return false;
    if (p.inicio && hoje < p.inicio) return false;
    if (p.fim && hoje > p.fim) return false;
    var cel = window.innerWidth <= 820;
    if (p.dispositivo === 'desktop' && cel) return false;
    if (p.dispositivo === 'celular' && !cel) return false;
    if (p.paginas && p.paginas.length && p.paginas.indexOf(document.body.dataset.tipoPagina || '') < 0 && p.paginas.indexOf('todas') < 0) return false;
    var visto = +LS.get('popup_' + p.id) || 0;
    if (visto && Date.now() - visto < (p.repetir_dias || 7) * 864e5) return false;
    // regra do Google: no celular, modal só depois de rolar a página, nunca ao abrir
    if (cel && p.modelo === 'modal' && p.gatilho === 'tempo') p = Object.assign({}, p, { gatilho: 'rolagem', valor: 40 });
    return p;
  }

  function armar(p) {
    var feito = false;
    function vai() { if (!feito && !aberto) { feito = true; mostrar(p); } }
    if (p.gatilho === 'tempo') setTimeout(vai, (p.valor || 8) * 1000);
    else if (p.gatilho === 'rolagem') window.addEventListener('scroll', function () {
      var h = document.documentElement; if ((h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100 >= (p.valor || 50)) vai();
    }, { passive: true });
    else if (p.gatilho === 'saida') document.addEventListener('mouseout', function (e) { if (!e.relatedTarget && e.clientY <= 0) vai(); });
  }

  window.Popups = { mostrar: function (p) { mostrar(p, true); }, iniciar: function (lista) {
    var ord = (lista || []).slice().sort(function (a, b) { return (b.prioridade || 0) - (a.prioridade || 0); });
    for (var i = 0; i < ord.length; i++) { var ok = pode(ord[i]); if (ok) { armar(ok); break; } }
  } };
  if (window.POPUPS) window.Popups.iniciar(window.POPUPS);
})();
