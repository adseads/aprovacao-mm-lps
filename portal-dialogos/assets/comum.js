
(function(){
  var dl = window.dataLayer = window.dataLayer || [];
  document.addEventListener('click', function(e){
    var el = e.target.closest('[data-componente]');
    if (el && el.dataset.componente !== 'anuncio') {
      dl.push({event:'clique_conteudo', componente:el.dataset.componente, posicao:el.dataset.posicao||''});
    }
  });
  document.querySelectorAll('form.minuta').forEach(function(f){
    f.addEventListener('submit', function(e){
      e.preventDefault();
      var out = f.querySelector('.minuta-msg');
      dl.push({event:'minuta_inscricao', posicao:f.dataset.posicao});
      if (out) out.textContent = 'Pronto. Confira seu e-mail para confirmar a inscrição.';
      f.querySelectorAll('input,button').forEach(function(x){ x.disabled = true; });
    });
  });
  document.querySelectorAll('[data-video]').forEach(function(v){
    v.addEventListener('click', function(){
      dl.push({event:'podcast_play', plataforma:'youtube'});
      v.classList.add('is-on');
      v.innerHTML = '<p class="video-note">No portal, o vídeo do YouTube carrega aqui quando o leitor clica, sem cookies antes do play.</p>';
    }, {once:true});
  });
  var niveis = ['leve','equilibrada','intensiva'];
  var q = new URLSearchParams(location.search).get('anuncios');
  function setNivel(n){
    if (niveis.indexOf(n) < 0) n = 'equilibrada';
    niveis.forEach(function(k){ document.body.classList.remove('ads-' + k); });
    document.body.classList.add('ads-' + n);
    document.querySelectorAll('[data-nivel]').forEach(function(b){ b.setAttribute('aria-pressed', b.dataset.nivel === n); });
    document.querySelectorAll('a[href$=".html"],a[href*=".html#"]').forEach(function(a){
      try { var u = new URL(a.getAttribute('href'), location.href); if (u.origin === location.origin) { u.searchParams.set('anuncios', n); a.setAttribute('href', u.pathname.split('/').pop() + u.search + u.hash); } } catch(e){}
    });
    dl.push({event:'nivel_anuncios', pacote_anuncios:n});
  }
  document.querySelectorAll('[data-nivel]').forEach(function(b){
    b.addEventListener('click', function(){
      setNivel(b.dataset.nivel);
      var u = new URL(location.href); u.searchParams.set('anuncios', b.dataset.nivel); history.replaceState(null, '', u);
    });
  });
  setNivel(q);
  var t = document.querySelector('[data-menu-toggle]');
  if (t) t.addEventListener('click', function(){
    var open = document.body.classList.toggle('menu-open');
    t.setAttribute('aria-expanded', open);
  });
})();
