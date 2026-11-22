
/* Portal Diálogos Estruturantes: comportamento das páginas. */
(function(){
  var dl = window.dataLayer = window.dataLayer || [];
  var CFG = window.DT || {};

  // Cliques rastreados: qualquer elemento com data-componente
  document.addEventListener('click', function(e){
    var el = e.target.closest('[data-componente]');
    if (el && el.dataset.componente !== 'anuncio') {
      dl.push({event:'clique_conteudo', componente:el.dataset.componente, posicao:el.dataset.posicao||''});
    }
    var cp = e.target.closest('[data-copiar]');
    if (cp) { e.preventDefault(); try { navigator.clipboard.writeText(cp.href); cp.setAttribute('aria-label','Link copiado'); } catch(x){} }
    var ck = e.target.closest('[data-preferencias-cookies]');
    if (ck) { e.preventDefault(); dl.push({event:'abrir_preferencias_cookies'}); }
  });

  // Newsletter: envia ao WordPress, que repassa ao Brevo
  document.querySelectorAll('form.minuta').forEach(function(f){
    f.addEventListener('submit', function(e){
      e.preventDefault();
      var out = f.querySelector('.minuta-msg');
      var dados = new FormData(f);
      dados.append('action','dp_newsletter'); dados.append('nonce', CFG.nonce||''); dados.append('posicao', f.dataset.posicao||'');
      f.querySelectorAll('button').forEach(function(b){ b.disabled = true; });
      fetch(CFG.ajax, {method:'POST', body:dados, credentials:'same-origin'}).then(function(r){ return r.json(); }).then(function(r){
        if (out) out.textContent = (r.data && r.data.msg) || 'Pronto. Confira seu e-mail para confirmar a inscrição.';
        if (r.success) { dl.push({event:'minuta_inscricao', posicao:f.dataset.posicao}); f.querySelectorAll('input').forEach(function(x){ x.disabled = true; }); }
        else f.querySelectorAll('button').forEach(function(b){ b.disabled = false; });
      }).catch(function(){ if (out) out.textContent = 'Não foi possível enviar agora. Tente de novo em instantes.'; f.querySelectorAll('button').forEach(function(b){ b.disabled = false; }); });
    });
  });

  // Vídeo leve: só carrega o YouTube no clique (sem cookies antes do play)
  document.querySelectorAll('div[data-video]').forEach(function(v){
    function abrir(){
      var id = v.dataset.video; if (!id) return;
      dl.push({event:'podcast_play', plataforma:'youtube'});
      v.classList.add('is-on');
      v.innerHTML = '<iframe src="https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1&rel=0" title="Vídeo" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe>';
    }
    v.addEventListener('click', abrir, {once:true});
    v.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); abrir(); } });
  });

  // Contagem de leitura para "Mais lidas"
  if (CFG.post) { setTimeout(function(){ var d = new FormData(); d.append('action','dp_leitura'); d.append('id', CFG.post); try { navigator.sendBeacon(CFG.ajax, d); } catch(x){} }, 5000); }

  // Menu no celular
  var t = document.querySelector('[data-menu-toggle]');
  if (t) t.addEventListener('click', function(){ var open = document.body.classList.toggle('menu-open'); t.setAttribute('aria-expanded', open); });

  // Prévia dos níveis de anúncio (só aparece para quem edita o portal)
  var niveis = ['leve','equilibrada','intensiva','hard'];
  window.dtNivel = function(n){
    niveis.forEach(function(k){ document.body.classList.remove('ads-' + k); });
    document.body.classList.add('ads-' + n);
    document.querySelectorAll('[data-nivel]').forEach(function(b){ b.setAttribute('aria-pressed', b.dataset.nivel === n); });
    document.dispatchEvent(new CustomEvent('dt:nivel'));
  };
  var atual = niveis.filter(function(k){ return document.body.classList.contains('ads-' + k); })[0] || 'leve';
  var q = new URLSearchParams(location.search).get('anuncios');
  document.querySelectorAll('[data-nivel]').forEach(function(b){ b.addEventListener('click', function(){ window.dtNivel(b.dataset.nivel); }); });
  if (q && niveis.indexOf(q) > -1 && document.querySelector('.nivel-bar')) window.dtNivel(q); else window.dtNivel(atual);
})();

(function(){
  if (document.body.classList.contains('sem-anuncios')) return;
  function bloco(w, h, pos, cls){
    var d = document.createElement('div');
    d.className = 'ad nv-hard ' + (cls||''); d.dataset.componente = 'anuncio'; d.dataset.posicao = pos; d.dataset.hard = '1';
    d.innerHTML = '<span class="ad-label">Publicidade</span>' + (w >= 728
      ? '<div class="ad-box ad-wide" style="--ar:'+w+'/'+h+';--mw:'+w+'px"><span class="d">'+w+' × '+h+'</span><span class="m">320 × 100</span></div>'
      : '<div class="ad-box" style="aspect-ratio:'+w+'/'+h+';max-width:'+w+'px">'+w+' × '+h+'</div>');
    return d;
  }
  // 1. texto: um anúncio a cada 2 parágrafos no computador e a cada 5 no celular (limite de 30% de densidade), sem encostar em outro anúncio
  document.querySelectorAll('.texto').forEach(function(t){
    var passo = window.innerWidth <= 820 ? 5 : 2, n = 0, k = 0;
    Array.prototype.slice.call(t.querySelectorAll(':scope > p, :scope > section > p')).forEach(function(el){
      n++;
      var prox = el.nextElementSibling;
      if (n % passo === 0 && prox && !prox.classList.contains('ad') && !(el.previousElementSibling||{classList:{contains:function(){return false}}}).classList.contains('ad'))
        el.after(bloco(300, 250, 'hard-inline-' + (++k), 'ad-inline'));
    });
  });
  // 2. lateral: mais um 300x250 depois do primeiro bloco e um 300x600 no fim
  document.querySelectorAll('.com-lateral > aside, .trio > aside').forEach(function(a, i){
    var primeiro = a.firstElementChild;
    if (primeiro) primeiro.after(bloco(300, 250, 'hard-lateral-' + (i+1)));
    (a.querySelector('.gruda') || a).appendChild(bloco(300, 600, 'hard-half-' + (i+1)));
  });
  // 3. listas: um anúncio a cada 2 itens
  ['.item', '.linha-ev', '.ep', '.lista-linha', '.capa'].forEach(function(sel){
    var itens = document.querySelectorAll('main ' + sel);
    itens.forEach(function(it, i){
      if ((i + 1) % 2 === 0 && i < itens.length - 1) { var b = bloco(728, 90, 'hard-lista-' + (i+1), 'ad-hard-lista'); it.after(b); }
    });
  });
  // 4. home e páginas de vitrine: faixa entre as seções principais
  if (!document.querySelector('.texto')) {
    var secoes = document.querySelectorAll('main > section');
    secoes.forEach(function(sec, i){
      var prox = sec.nextElementSibling;
      if (i > 0 && i < secoes.length - 1 && prox && !prox.classList.contains('ad-faixa')) {
        var w = document.createElement('div'); w.className = 'ad-faixa wrap nv-hard'; w.appendChild(bloco(970, 90, 'hard-entre-' + i)); sec.after(w);
      }
    });
  }
  var f = document.querySelector('.fecha-ancora');
  if (f) f.addEventListener('click', function(){ f.parentNode.classList.add('fechado'); (window.dataLayer=window.dataLayer||[]).push({event:'anuncio_ancora_fechado'}); });
})();

(function(){
  // A coluna lateral nunca fica maior que o conteúdo: blocos do fim que não cabem são ocultados
  // (no WordPress, o espaço de anúncio oculto não é solicitado ao Ad Manager).
  var FOLGA = 130;
  function fundo(el){ var t = el.getBoundingClientRect().top, m = t;
    Array.prototype.forEach.call(el.children, function(c){ if (c.offsetParent !== null) m = Math.max(m, c.getBoundingClientRect().bottom); });
    return m - t; }
  function ajustar(){
    document.querySelectorAll('.com-lateral, .trio').forEach(function(g){
      var aside = g.querySelector(':scope > aside'); if (!aside) return;
      aside.querySelectorAll('.nao-cabe').forEach(function(e){ e.classList.remove('nao-cabe'); });
      if (window.innerWidth <= 980) return;
      var alvo = 0;
      Array.prototype.forEach.call(g.children, function(c){ if (c !== aside) alvo = Math.max(alvo, fundo(c)); });
      var itens = [];
      Array.prototype.forEach.call(aside.children, function(c){
        if (c.classList.contains('gruda')) Array.prototype.forEach.call(c.children, function(x){ itens.push(x); });
        else itens.push(c);
      });
      itens = itens.filter(function(x){ return x.offsetParent !== null; });
      for (var i = itens.length - 1; i > 0 && fundo(aside) > alvo + FOLGA; i--) itens[i].classList.add('nao-cabe');
    });
  }
  window.addEventListener('load', ajustar);
  window.addEventListener('resize', function(){ clearTimeout(window.__aj); window.__aj = setTimeout(ajustar, 150); });
  document.addEventListener('dt:nivel', function(){ setTimeout(ajustar, 30); });
  ajustar();
})();

/* Trava de densidade: retira anúncios do fim até a densidade ficar abaixo do limite do Google (com margem). */
(function(){
  if (!(window.DT && window.DT.trava)) return;
  function visivel(a){ return a.offsetParent !== null && !a.parentElement.closest('.ad'); }
  function medir(){
    var nav = document.querySelector('.nav'), fimEl = document.querySelector('.relacionadas') || document.querySelector('.rodape');
    if (!nav || !fimEl) return null;
    var topo = nav.getBoundingClientRect().bottom + scrollY, fim = fimEl.getBoundingClientRect().top + scrollY, soma = 0, lista = [];
    document.querySelectorAll('.ad').forEach(function(a){
      if (!visivel(a)) return;
      var r = a.getBoundingClientRect(), y = r.top + scrollY;
      if (y >= topo && y < fim) { soma += r.height; lista.push(a); }
    });
    return {dens: soma / Math.max(1, fim - topo), lista: lista};
  }
  function ajustar(){
    document.querySelectorAll('.dens-oculto').forEach(function(a){ a.classList.remove('dens-oculto'); });
    var limite = window.innerWidth <= 820 ? 0.28 : 0.45;
    var ordem = ['nv-hard','nv-int','nv-eq'];
    for (var g = 0; g < ordem.length; g++) {
      var m = medir(); if (!m || m.dens <= limite) return;
      var cand = m.lista.filter(function(a){ return a.classList.contains(ordem[g]) || (a.closest('.' + ordem[g]) && !a.closest('.ad-topo')); });
      for (var i = cand.length - 1; i >= 0; i--) {
        (cand[i].closest('.ad-faixa') || cand[i]).classList.add('dens-oculto');
        var m2 = medir(); if (!m2 || m2.dens <= limite) return;
      }
    }
  }
  window.dtTrava = ajustar;
  window.addEventListener('load', function(){ setTimeout(ajustar, 60); });
  document.addEventListener('dt:nivel', function(){ setTimeout(ajustar, 90); });
})();

(function(){
  var grade = document.querySelector('.grade-setores'); if (!grade) return;
  var chips = document.querySelectorAll('.chip[data-setor]');
  function escolher(s, salvar){
    chips.forEach(function(c){ c.setAttribute('aria-pressed', c.dataset.setor === s); });
    grade.querySelectorAll('.painel').forEach(function(p){ p.classList.toggle('meu', p.dataset.setor === s); });
    grade.classList.toggle('filtrado', !!s);
    if (salvar){
      try { localStorage.setItem('setor_preferido', s); } catch(e){}
      (window.dataLayer = window.dataLayer || []).push({event:'setor_preferido', setor:s});
    }
  }
  chips.forEach(function(c){ c.addEventListener('click', function(){
    var s = c.getAttribute('aria-pressed') === 'true' ? '' : c.dataset.setor;
    escolher(s, true);
  }); });
  var salvo = ''; try { salvo = localStorage.getItem('setor_preferido') || ''; } catch(e){}
  if (salvo) escolher(salvo, false);
})();

(function(){
  if (!document.getElementById('f-mes')) return;
  var btns = document.querySelectorAll('[data-filtro-setor]'), sel = document.getElementById('f-mes');
  function aplicar(){
    var s = (document.querySelector('[data-filtro-setor][aria-pressed=true]')||{}).dataset; s = s ? s.filtroSetor : 'todos';
    var m = sel.value, vis = 0;
    document.querySelectorAll('.linha-ev').forEach(function(l){
      var ok = (s === 'todos' || l.dataset.setores.split(' ').indexOf(s) > -1) && (m === 'todos' || l.dataset.mes === m);
      l.hidden = !ok; if (ok) vis++;
    });
    document.querySelectorAll('.mes').forEach(function(h){
      var n = h.nextElementSibling, tem = false;
      while (n && !n.classList.contains('mes')) { if (n.classList.contains('linha-ev') && !n.hidden) tem = true; n = n.nextElementSibling; }
      h.hidden = !tem;
    });
    document.querySelector('.vazio').style.display = vis ? 'none' : 'block';
    (window.dataLayer = window.dataLayer || []).push({event:'filtro_agenda', setor:s, mes:m});
  }
  btns.forEach(function(b){ b.addEventListener('click', function(){ btns.forEach(function(x){ x.setAttribute('aria-pressed', x === b); }); aplicar(); }); });
  sel.addEventListener('change', aplicar);
})();
