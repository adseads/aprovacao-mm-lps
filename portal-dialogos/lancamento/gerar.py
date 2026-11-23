# -*- coding: utf-8 -*-
"""Gera index.html e manual.md a partir de checklist.py."""
import html, os
import checklist as C

AQUI = os.path.dirname(os.path.abspath(__file__))
e = lambda s: html.escape(s, quote=True)


def item_html(i, titulo, passos, conferir, errado):
    li = ''.join(f'<li>{e(p)}</li>' for p in passos)
    err = f'<div class="bloco errado"><b>Se der errado</b><p>{e(errado)}</p></div>' if errado else ''
    return f'''
<article class="item" id="{i}">
  <label class="topo"><input type="checkbox" data-id="{i}"><span class="t">{e(titulo)}</span></label>
  <div class="corpo">
    <ol>{li}</ol>
    <div class="bloco conferir"><b>Como conferir</b><p>{e(conferir)}</p></div>
    {err}
  </div>
</article>'''


def pagina():
    secoes = []
    for fid, nome, sub, itens in C.FASES:
        corpo = ''.join(item_html(*x) for x in itens)
        secoes.append(f'<section class="fase" id="{fid}"><h2>{e(nome)}</h2><p class="sub">{e(sub)}</p>{corpo}</section>')
    dns = ''.join(f'<tr><td>{e(a)}</td><td>{e(b)}</td><td>{e(c)}</td><td>{e(d)}</td></tr>' for a, b, c, d in C.DNS_HOJE)
    volta = ''.join(f'<li>{e(x)}</li>' for x in C.VOLTAR_ATRAS)
    mud = ''.join(f'<li><b>{e(d)}</b> {e(t)}</li>' for d, t in C.MUDANCAS)
    total = sum(len(f[3]) for f in C.FASES)
    nav = ''.join(f'<a href="#{f[0]}">{e(f[1].split(":")[0])}</a>' for f in C.FASES)
    return f'''<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Checklist de lançamento · Diálogos Estruturantes</title>
<style>
@font-face{{font-family:"Public Sans";src:url(../templates/assets/fontes/public-sans-latin-wght-normal.woff2) format("woff2");font-weight:100 900}}
@font-face{{font-family:"Source Serif 4";src:url(../templates/assets/fontes/source-serif-4-latin-wght-normal.woff2) format("woff2");font-weight:200 900}}
:root{{--marinho:#00054c;--laranja:#d95924;--tinta:#1d2033;--suave:#5b6072;--linha:#dfe2ea;--fundo:#f4f5f9;--ok:#209961;--alerta:#b3261e}}
*{{box-sizing:border-box}}
body{{margin:0;background:var(--fundo);color:var(--tinta);font:16px/1.55 "Public Sans",Arial,sans-serif}}
header{{background:var(--marinho);color:#fff;padding:28px 16px 22px}}
.wrap{{max-width:860px;margin:0 auto;padding:0 16px}}
header .wrap{{padding:0}}
h1{{font:700 clamp(26px,5vw,36px)/1.15 "Source Serif 4",Georgia,serif;margin:4px 0 8px}}
.meta{{color:#c9cbe0;font-size:14px;margin:0}}
.meta b{{color:#fff}}
.barra{{position:sticky;top:0;z-index:5;background:#fff;border-bottom:1px solid var(--linha)}}
.barra .wrap{{display:flex;flex-wrap:wrap;gap:6px 16px;align-items:center;padding:10px 16px}}
.barra a{{color:var(--marinho);font-weight:600;font-size:14px;text-decoration:none}}
.prog{{margin-left:auto;font-size:14px;color:var(--suave)}}
.prog b{{color:var(--laranja)}}
main{{padding:8px 0 40px}}
h2{{font:700 24px/1.2 "Source Serif 4",Georgia,serif;color:var(--marinho);margin:34px 0 4px}}
.sub{{color:var(--suave);margin:0 0 14px}}
.item{{background:#fff;border:1px solid var(--linha);border-left:4px solid var(--linha);margin:0 0 10px}}
.item.feito{{border-left-color:var(--ok)}}
.item.feito .t{{color:var(--suave);text-decoration:line-through}}
.topo{{display:flex;gap:12px;align-items:flex-start;padding:14px 16px;cursor:pointer;font-weight:700;color:var(--marinho)}}
.topo input{{width:20px;height:20px;margin:2px 0 0;accent-color:var(--ok);flex:none}}
.corpo{{padding:0 16px 14px 48px}}
.corpo ol{{margin:0 0 10px;padding-left:18px}}
.corpo li{{margin:2px 0}}
.bloco{{font-size:15px;padding:8px 12px;margin:8px 0 0;background:var(--fundo)}}
.bloco p{{margin:2px 0 0}}
.conferir{{border-left:3px solid var(--marinho)}}
.errado{{border-left:3px solid var(--laranja)}}
table{{width:100%;border-collapse:collapse;background:#fff;font-size:14px}}
th,td{{text-align:left;padding:8px 10px;border-bottom:1px solid var(--linha);vertical-align:top}}
th{{background:var(--marinho);color:#fff;font-weight:600}}
.tabela{{overflow-x:auto}}
.volta{{background:#fff;border:1px solid var(--linha);border-left:4px solid var(--alerta);padding:12px 16px 12px 36px}}
.mud li{{margin:4px 0}}
footer{{color:var(--suave);font-size:13px;padding:24px 0 40px}}
button{{font:600 14px "Public Sans",Arial,sans-serif;background:none;border:1px solid var(--linha);padding:6px 10px;cursor:pointer;color:var(--marinho)}}
:focus-visible{{outline:3px solid var(--laranja);outline-offset:2px}}
@media (max-width:600px){{.corpo{{padding-left:16px}}}}
@media print{{.barra,button{{display:none}}.item{{break-inside:avoid}}}}
</style></head>
<body>
<header><div class="wrap">
  <p class="meta">Portal Diálogos Estruturantes</p>
  <h1>Checklist de lançamento</h1>
  <p class="meta">Lançamento: <b>{e(C.LANCAMENTO)}</b> · Atualizado em <b>{e(C.ATUALIZADO)}</b></p>
</div></header>
<nav class="barra"><div class="wrap">{nav}<a href="#dns">DNS</a><a href="#voltar">Voltar atrás</a>
<span class="prog"><b id="feitos">0</b> de {total} concluídos <button type="button" id="zerar">Desmarcar tudo</button></span></div></nav>
<main class="wrap">
{''.join(secoes)}
<section id="dns"><h2>DNS de hoje</h2><p class="sub">Referência para conferir no dia 11. Os registros de e-mail nunca mudam.</p>
<div class="tabela"><table><thead><tr><th>Tipo</th><th>Nome</th><th>Valor</th><th>Para quê</th></tr></thead><tbody>{dns}</tbody></table></div></section>
<section id="voltar"><h2>Se for preciso voltar atrás</h2><p class="sub">Use só se o portal não abrir depois de 1 hora da troca. A LP volta ao ar em poucos minutos.</p>
<ol class="volta">{volta}</ol></section>
<section><h2>O que mudou neste checklist</h2><ul class="mud">{mud}</ul></section>
</main>
<footer class="wrap">As marcações ficam salvas só neste navegador. O manual do portal tem a mesma lista na seção "Dia do lançamento".</footer>
<script>
(function(){{
  var CH='dialogos-lancamento', s={{}};
  try{{s=JSON.parse(localStorage.getItem(CH)||'{{}}')}}catch(e){{}}
  var cx=[].slice.call(document.querySelectorAll('input[data-id]'));
  function conta(){{var n=0;cx.forEach(function(c){{c.closest('.item').classList.toggle('feito',c.checked);if(c.checked)n++}});document.getElementById('feitos').textContent=n}}
  function salva(){{try{{localStorage.setItem(CH,JSON.stringify(s))}}catch(e){{}}}}
  cx.forEach(function(c){{c.checked=!!s[c.dataset.id];c.addEventListener('change',function(){{s[c.dataset.id]=c.checked;salva();conta()}})}});
  document.getElementById('zerar').addEventListener('click',function(){{s={{}};salva();cx.forEach(function(c){{c.checked=false}});conta()}});
  conta();
}})();
</script>
</body></html>'''


def manual():
    L = [f'## Dia do lançamento ({C.LANCAMENTO.split(", ")[1]})', '',
         f'Checklist completo, com marcação de cada item, na página de lançamento. Atualizado em {C.ATUALIZADO}. Abaixo, o mesmo roteiro.', '']
    for fid, nome, sub, itens in C.FASES:
        L += [f'### {nome}', '', sub, '']
        for i, titulo, passos, conferir, errado in itens:
            L.append(f'- [ ] **{titulo}.** ' + ' '.join(passos) + f' *Como conferir:* {conferir}' + (f' *Se der errado:* {errado}' if errado else ''))
        L.append('')
    L += ['### Se for preciso voltar atrás', '', 'Use só se o portal não abrir depois de 1 hora da troca.', '']
    L += [f'{n}. {x}' for n, x in enumerate(C.VOLTAR_ATRAS, 1)]
    return '\n'.join(L)


open(os.path.join(AQUI, 'index.html'), 'w').write(pagina())
open(os.path.join(AQUI, 'manual.md'), 'w').write(manual())
print('ok')
