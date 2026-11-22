"""Gera os arquivos de importação (WXR) do portal: 7ª edição e teste da Minuta."""
import re, sys, html
from datetime import datetime, timedelta

BASE_IMG = sys.argv[1] if len(sys.argv) > 1 else 'https://adseads.github.io/aprovacao-mm-lps/portal-dialogos/templates/assets/img/'
SUFIXO = sys.argv[2] if len(sys.argv) > 2 else ''
DIR = '/tmp/claude-0/-home-claude/3395977f-6b5f-5edf-8670-4fccbe34792b/scratchpad/ed7/'

def secoes(arq):
    d = {}
    for bloco in ('\n' + open(DIR + arq).read()).split('\n=== ')[1:]:
        k, _, corpo = bloco.partition('\n')
        d[k.strip()] = corpo.strip()
    return d

TXT = secoes('art_limpo.md')
MIN = secoes('minuta.md')

def esc(s):
    return html.escape(s, quote=False)

def inline(s):
    s = esc(s)
    return s

def blocos(texto, citacao=None, depois=4):
    out, n = [], 0
    entrevista = '\nDE - ' in texto
    def cit():
        q, c = citacao
        out.append('<!-- wp:quote -->\n<blockquote class="wp-block-quote"><!-- wp:paragraph -->\n'
                   f'<p>{esc(q)}</p>\n<!-- /wp:paragraph --><cite>{esc(c)}</cite></blockquote>\n<!-- /wp:quote -->')
    for p in [x.strip() for x in texto.split('\n\n') if x.strip()]:
        if citacao and n >= depois and not p.startswith(('## ', 'BOX: ')) and (not entrevista or p.startswith('DE - ')):
            cit(); citacao = None
        if p.startswith('DEK:'):
            continue
        if p.startswith('## '):
            h = esc(p[3:])
            out.append(f'<!-- wp:heading -->\n<h2 class="wp-block-heading">{h}</h2>\n<!-- /wp:heading -->')
            continue
        if p.startswith('BOX: '):
            corpo = p[5:]
            if ' — ' in corpo[:60]:
                tit, corpo = corpo.split(' — ', 1)
                corpo = corpo[0].upper() + corpo[1:]
                h = f'<strong>{esc(tit)}</strong>{esc(corpo)}'
            elif corpo.startswith('Fique de olho! '):
                h = f'<strong>Fique de olho!</strong>{esc(corpo[15:])}'
            else:
                h = esc(corpo)
            out.append(f'<!-- wp:paragraph {{"className":"quadro"}} -->\n<p class="quadro">{h}</p>\n<!-- /wp:paragraph -->')
            continue
        if p.startswith('FONTE: '):
            out.append(f'<!-- wp:paragraph -->\n<p><em>Fonte: {esc(p[7:])}</em></p>\n<!-- /wp:paragraph -->')
            continue
        if p.startswith('DE - '):
            h = f'<strong>Diálogos Estruturantes – {esc(p[5:])}</strong>'
        elif p.startswith('Diumar Bueno – '):
            h = f'<strong>Diumar Bueno –</strong> {esc(p[15:])}'
        else:
            h = esc(p)
        out.append(f'<!-- wp:paragraph -->\n<p>{h}</p>\n<!-- /wp:paragraph -->')
        n += 1
    return '\n\n'.join(out)

def dek(k):
    m = re.search(r'^DEK: (.*)$', TXT.get(k, ''), re.M)
    return m.group(1) if m else ''

# ---------- autores ----------
AUTORES = {}
def autor(nome):
    login = re.sub(r'[^a-z0-9]+', '-', nome.lower().translate(str.maketrans('áàâãéêíóôõúçÁÉÍÓÚÂÊÔÃÕÇ', 'aaaaeeiooouc' + 'aeiouaeoaoc'))).strip('-')
    AUTORES[login] = nome
    return login

# ---------- itens ----------
ITENS = []   # dicts
IMGS = {}    # arquivo -> id
_id = [1000]
def novo_id():
    _id[0] += 1
    return _id[0]

def img(arq):
    if arq not in IMGS:
        IMGS[arq] = novo_id()
    return IMGS[arq]

def slugify(t, n=75):
    import unicodedata
    t = unicodedata.normalize('NFKD', t).encode('ascii', 'ignore').decode().lower()
    t = re.sub(r'[^a-z0-9]+', '-', t).strip('-')
    while len(t) > n and '-' in t:
        t = t.rsplit('-', 1)[0]
    return t

def item(**kw):
    kw.setdefault('id', novo_id())
    kw.setdefault('tipo', 'post')
    kw.setdefault('status', 'publish')
    kw.setdefault('meta', {})
    kw.setdefault('cats', [])
    kw.setdefault('tags', [])
    kw.setdefault('conteudo', '')
    kw.setdefault('resumo', '')
    ITENS.append(kw)
    return kw

ED = {'portos': 'Portos', 'infra': 'Infra', 'agro': 'Agro', 'regulacao': 'Regulação', 'tributos': 'Tributos', 'no-direito': 'No Direito'}

def materia(k, titulo, quem, cats, data, imagem, modelo='materia', tags=(), chapeu='', citacao=None, extra=None, legenda=''):
    meta = {'dp_modelo': modelo, 'dp_linha_fina': dek(k), 'dp_nivel': 'padrao'}
    if chapeu: meta['dp_chapeu'] = chapeu
    if legenda: meta['dp_legenda'] = legenda
    meta.update(extra or {})
    return item(slug=slugify(titulo), titulo=titulo, autor=autor(quem), cats=cats, tags=list(tags), data=data,
                conteudo=blocos(TXT[k], citacao), resumo=dek(k), thumb=img(imagem), meta=meta)

def gerar_edicao():
    D = lambda d, h=8: f'2026-11-{d:02d} {h:02d}:00:00'
    materia('pacto', 'Um ano depois, o Pacto ainda espera pelo futuro', 'Luiz Roberto Marinho', ['infra'], D(21), 'pacto-cidade.jpg',
            tags=['Pacto Brasil pela Infraestrutura', 'Investimento público'], chapeu='Capa da 7ª edição',
            citacao=('Projetos estruturantes atravessam governos. Ferrovias, rodovias, sistemas de saneamento, hidrovias e redes de energia não obedecem ao calendário eleitoral.', 'Claudio Medeiros, presidente do Sinicon'))['fixo'] = True
    materia('iss', 'STF tira o ISS da conta do PIS e da Cofins; prestadoras de serviço correm contra o relógio', 'Redação', ['tributos'], D(20, 9), 'brasilia.jpg',
            modelo='explicador', tags=['Reforma tributária', 'STF'], chapeu='Radar Tributário',
            extra={'dp_em_resumo': 'O STF decidiu, por 6 a 5, que o ISS não entra na base de cálculo do PIS e da Cofins.\nConstrução pesada, engenharia consultiva e operação portuária são as mais beneficiadas.\nO alcance retroativo ainda será definido: empresas sem ação podem perder até cinco anos de créditos.\nPIS e Cofins deixam de existir em janeiro de 2027; o ganho está no que foi pago até lá.'})
    materia('frete', '“A conta do frete ainda não fecha”', 'Redação', ['infra'], D(19), 'caminhoes.jpg',
            tags=['Piso do frete', 'Transporte rodoviário'], chapeu='Entrevista · Diumar Bueno, CNTA',
            citacao=('Se a legislação tivesse sido respeitada desde o início, o setor já teria se ajustado e o ambiente seria mais competitivo para todos.', 'Diumar Bueno, presidente da CNTA'))
    materia('zpe', 'ZPE em Mato Grosso do Sul será a 5ª a operar no país', 'Luiz Roberto Marinho', ['portos'], D(18), 'porto-guindastes.jpg',
            tags=['ZPEs'], citacao=('A ZPE abriu uma janela brutal como instrumento de desenvolvimento.', 'Helson Braga, presidente da Abrazpe'))
    materia('ormuz', 'De Ormuz aos portos brasileiros: os custos de uma crise distante', 'Marília Fernanda de Souza Bernardes', ['portos', 'agro'], D(17), 'ormuz.jpg',
            modelo='opiniao', tags=['Fertilizantes', 'Logística portuária'],
            citacao=('O problema não termina no cais: alcança toda a conexão entre porto e interior.', 'Marília Fernanda de Souza Bernardes'))
    materia('datacenter', 'Data centers e ZPEs: o nexo virtuoso', 'Helson Braga e Raul Gouveia', ['portos'], D(16), 'data-center.jpg',
            modelo='opiniao', tags=['ZPEs', 'Data centers'],
            citacao=('A oportunidade para o Brasil consiste em transformar a vantagem comparativa natural em vantagem competitiva sustentável.', 'Helson Braga e Raul Gouveia'))
    materia('elnino', 'El Niño pode aumentar custos do agronegócio brasileiro', 'Luiz Roberto Marinho', ['agro'], D(15), 'campo.jpg',
            tags=['El Niño', 'Clima'])
    materia('seguro', 'Seguro rural: proteção para produzir em tempos de incerteza', 'Tânia Zanella', ['agro'], D(14), 'soja.jpg',
            modelo='opiniao', tags=['Seguro rural', 'El Niño'],
            citacao=('Proteger o produtor significa proteger renda, empregos, abastecimento, investimentos e a própria competitividade do agro.', 'Tânia Zanella'))
    materia('quemdecide', 'Quem decide o futuro da infraestrutura?', 'Luciana Dutra', ['infra'], D(13), 'engenheira.jpg',
            modelo='opiniao', tags=['Liderança feminina'],
            citacao=('As mulheres que ajudam a construir o país precisam também decidir o seu futuro.', 'Luciana Dutra, presidente da Anetrams'))
    materia('secex', 'SecexConsenso, Compor e CCAF: as portas e os limites da consensualidade nas concessões', 'Murilo Jacoby Fernandes', ['no-direito', 'regulacao'], D(12), 'predio-tcu.jpg',
            modelo='opiniao', tags=['Concessões', 'TCU'],
            citacao=('Blindagem plena ainda não existe: o que há é segurança decorrente da solidez da solução construída.', 'Murilo Jacoby Fernandes'))
    materia('reforma', 'A reforma tributária e o preço dos serviços públicos', 'Waleska Lemos Morais', ['no-direito', 'tributos'], D(11), 'documentos.jpg',
            modelo='opiniao', tags=['Reforma tributária', 'Concessões'],
            citacao=('O sucesso da mudança não será medido apenas pela substituição de impostos.', 'Waleska Lemos Morais'))
    materia('namidia', 'Informação também é estrada para o progresso', 'Gustavo Uribe', ['infra'], D(10), 'predios.jpg',
            modelo='opiniao', chapeu='Na mídia',
            citacao=('Nunca tivemos tanto acesso à informação, o que não significa que estamos mais bem informados.', 'Gustavo Uribe'))
    materia('caravana', 'Caravana portuária discute aplicação da reforma tributária no setor', 'Redação', ['portos', 'tributos'], D(9), 'santos.jpg',
            tags=['Reforma tributária', 'Portos'], chapeu='Eventos')

    # podcast
    eps = [
        ('imbituba', 4, 'Modelo de parceria em Santa Catarina pode ser replicado em outras ZPEs', 'portos', 'podcast-ep04.jpg', 'Renato Lacerda', 'Diretor técnico da ZPE de Imbituba (SC)', D(7, 10)),
        ('ep05', 5, 'Investimento em infraestrutura deve ser política de Estado', 'infra', 'podcast-ep05.jpg', 'Rafael Sacchi', 'Presidente do Sicepot-RS', D(14, 10)),
        ('embrapa', 6, 'Embrapa elabora pacote de medidas para reduzir efeitos do El Niño', 'agro', 'podcast-estudio.jpg', 'Ary Fortes de Oliveira', 'Pesquisador da Embrapa', D(20, 10)),
    ]
    for k, n, t, ed, im, conv, cargo, data in eps:
        item(tipo='episodio', slug=f'episodio-{n:02d}', titulo=t, autor=autor('Luiz Roberto Marinho'), cats=[ed], data=data,
             conteudo=blocos(TXT[k]), resumo=dek(k), thumb=img(im),
             meta={'dp_numero': str(n), 'dp_linha_fina': dek(k), 'dp_convidado': conv, 'dp_convidado_cargo': cargo, 'dp_nivel': 'padrao',
                   'dp_video': '', 'dp_duracao': ''})

    # revista
    item(tipo='edicao', slug='edicao-07', titulo='Plano que atravessa governos', autor=autor('Redação'), data='2026-11-05 09:00:00',
         conteudo='<!-- wp:heading -->\n<h2 class="wp-block-heading">Editorial: Compromisso com o futuro</h2>\n<!-- /wp:heading -->\n\n' + blocos(TXT['editorial']),
         resumo='Pacto Brasil pela Infraestrutura completa 12 meses e pressiona por planejamento de longo prazo no setor.', thumb=img('revista-ed07-capa.jpg'),
         meta={'dp_numero': '7', 'dp_mes': 'Novembro 2026', 'dp_linha_fina': 'Pacto Brasil pela Infraestrutura completa 12 meses e pressiona por planejamento de longo prazo no setor. Entidades se unem para acelerar investimentos em 2027.'})
    ed6 = ('Diante da escalada dos conflitos no Leste Europeu e no Oriente Médio, a 6ª edição da Diálogos Estruturantes analisou como as crises internacionais impactam a economia brasileira.\n\n'
           '## Agro\n\nA vulnerabilidade na importação de fertilizantes e a pressão sobre as margens do produtor. Bruno Lucchi (CNA) debate soluções científicas e a mitigação dos custos logísticos para proteger as próximas safras.\n\n'
           '## Infra\n\nA disparada de mais de 20% no asfalto (CAP) e o desequilíbrio nos contratos de obras viárias. A Aneor defende no DNIT e no Ministério dos Transportes a concessão do reequilíbrio econômico-financeiro cautelar.\n\n'
           '## Portos\n\nO impacto do frete marítimo global e a tramitação do Marco Legal dos Portos (PL 733/2025). Fenop e ABTP analisam a descentralização de outorgas para a ANTAQ e a modernização das relações de trabalho.')
    item(tipo='edicao', slug='edicao-06', titulo='O impacto dos conflitos internacionais no desenvolvimento do país', autor=autor('Redação'), data='2026-10-05 09:00:00',
         conteudo=blocos(ed6), resumo='Como as crises internacionais impactam a economia brasileira.', thumb=img('revista-ed06.jpg'),
         meta={'dp_numero': '6', 'dp_mes': 'Outubro 2026', 'dp_linha_fina': 'Como as crises internacionais impactam a economia brasileira.'})

    # agenda
    evs = [
        ('premio', 'II Prêmio Melhores do Ano Diálogos Estruturantes', '20261211', '20261211', 'B Hotel, Brasília (DF)', 'Organização: M&M Lexis', 'institucional', ['infra', 'portos', 'agro'],
         'Um momento para celebrar e homenagear lideranças, empresas e agentes dos setores estratégicos que contribuem para o desenvolvimento do país. 11 de dezembro, 10h30.'),
        ('congresso-ppps', '1º Congresso Nacional de Inovação em PPPs e Concessões', '20261125', '20261126', 'Park Shopping Canoas, Canoas (RS)', 'Organização: IBPM e Conipc', 'destaque', ['infra', 'regulacao'], ''),
        ('expolog-2026', 'Expolog 2026', '20261123', '20261124', 'Centro de Eventos do Ceará, Fortaleza', 'Setores: logística, agronegócio, infraestrutura, comércio exterior e inovação', 'normal', ['portos', 'agro', 'infra'], ''),
        ('tendencias-construcao', 'Tendências no Mercado da Construção', '20261125', '20261125', 'Apas, São Paulo', 'Organização: Sobratema', 'normal', ['infra'], ''),
        ('circuito-reforma', 'Circuito Reforma Tributária pelo Brasil', '20261105', '20261127', 'Ceará, Sergipe, Alagoas, Campinas, Vale do Taquari, Maranhão, Piauí, Espírito Santo e Goiás', 'Organização: Fenacon, CFC, CRCs, Sebrae, OAB e universidades', 'normal', ['tributos'], ''),
        ('fenatran-2026', 'Fenatran 2026', '20261109', '20261113', 'São Paulo Expo, São Paulo', 'Setores: transporte rodoviário de cargas, implementos, intralogística e tecnologia', 'normal', ['infra'], ''),
    ]
    eventos(evs, 'publish')

def eventos(evs, status):
    for slug, nome, ini, fim, local, info, pri, cats, chamada in evs:
        item(tipo='evento', slug=slug, titulo=nome, autor=autor('Redação'), cats=cats, data='2026-11-01 09:00:00', status=status,
             meta={'dp_prioridade': pri, 'dp_inicio': ini, 'dp_fim': fim, 'dp_local': local, 'dp_info': info, 'dp_chamada': chamada, 'dp_link': ''})

def gerar_minuta():
    D = '2026-11-20 07:00:00'
    def nota(k, titulo, cats, im, linha, tags, modelo='materia', extra=None):
        meta = {'dp_modelo': modelo, 'dp_linha_fina': linha, 'dp_nivel': 'padrao', 'dp_chapeu': 'Minuta ao Leitor · 20/11'}
        meta.update(extra or {})
        item(slug=slugify(titulo), titulo=titulo, autor=autor('Redação'), cats=cats, tags=tags, data=D, status='draft',
             conteudo=blocos(MIN[k]), resumo=linha, thumb=img(im), meta=meta)
    nota('ato8', 'IBS/CBS: produtor rural e transportador autônomo ficam dispensados de CNPJ em 2027 e 2028', ['tributos', 'agro', 'infra'], 'caminhoes.jpg',
         'Ato Conjunto RFB/CGIBS nº 8 abre janela cadastral para dois públicos, mas não elimina registros setoriais nem a adaptação de documentos e sistemas.',
         ['Reforma tributária', 'IBS e CBS'], modelo='explicador',
         extra={'dp_em_resumo': 'Produtor rural PF e TAC não contribuintes ficam dispensados de CNPJ em 2027 e 2028.\nA dispensa não elimina o RNTRC nem outros registros setoriais.\nQuem compra do produtor ou contrata o TAC deve revisar ERP, documento fiscal e crédito presumido.'})
    nota('debentures', 'Projetos portuários no Amazonas e no Paraná podem captar R$ 1,9 bilhão em debêntures', ['portos'], 'navio-porto.jpg',
         'Carteira inclui terminal de grãos e acesso aquaviário a Paranaguá; habilitação ainda não é emissão concluída nem obra entregue.', ['Debêntures incentivadas', 'Portos'])
    nota('ponte', 'Nova Ponte do Arranha-Céu, em Itaipava, é entregue quatro anos antes do prazo', ['infra'], 'obra-rodovia.jpg',
         'Obra concluída em seis meses melhora a ligação entre a BR-040 e a Estrada União e Indústria.', ['Concessões', 'Rodovias'])
    nota('vbp', 'VBP do agro chega a R$ 1,46 trilhão; Mapa projeta recuperação em 2027', ['agro'], 'campo-por-do-sol.jpg',
         'Lavouras recuam 3,9%; bovinos e soja sustentam parte do resultado.', ['VBP', 'Agro'])
    evs = [
        ('antaq-624', '624ª reunião ordinária da ANTAQ', '20261123', '20261125', 'Virtual', 'Organização: ANTAQ', 'normal', ['portos', 'regulacao'], ''),
        ('dialogos-hidroviaveis', 'Diálogos Hidroviáveis', '20261125', '20261126', 'CREA-DF, Brasília (DF)', 'Organização: Adecon e parceiros · navegação interior', 'normal', ['portos', 'infra'], ''),
        ('unctad-rmt-2026', 'Lançamento do Review of Maritime Transport 2026 (UNCTAD)', '20261126', '20261126', 'Online', 'Organização: UNCTAD · fretes, portos, custos e regulação', 'normal', ['portos'], ''),
        ('consulta-pnd', 'Fim da consulta pública do PND', '20261207', '20261207', 'Brasil Participativo (online)', 'Organização: MPor · prazo para contribuições', 'normal', ['portos'], ''),
        ('leiloes-iqi16-muc04', 'Leilões de arrendamento IQI16 e MUC04', '20261209', '20261209', 'B3, São Paulo', 'Organização: ANTAQ e MPor', 'normal', ['portos'], ''),
        ('consulta-portaria-574', 'Fim da consulta sobre a revisão da Portaria 574 (gestão dos portos públicos)', '20261226', '20261226', 'Brasil Participativo (online)', 'Organização: MPor · prazo para contribuições', 'normal', ['portos', 'regulacao'], ''),
    ]
    eventos(evs, 'draft')

# ---------- WXR ----------
def cdata(s):
    return '<![CDATA[' + s.replace(']]>', ']]]]><![CDATA[>') + ']]>'

def wxr(nome_arq, titulo):
    TAGS = sorted({t for i in ITENS for t in i['tags']})
    slug = lambda t: re.sub(r'[^a-z0-9]+', '-', t.lower().translate(str.maketrans('áàâãéêíóôõúç', 'aaaaeeiooouc'))).strip('-')
    L = ['<?xml version="1.0" encoding="UTF-8" ?>',
         '<rss version="2.0" xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/" xmlns:content="http://purl.org/rss/1.0/modules/content/" '
         'xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:wp="http://wordpress.org/export/1.2/">',
         '<channel>', f'<title>{esc(titulo)}</title>', '<link>https://dialogosestruturantes.com.br</link>', '<language>pt-BR</language>',
         '<wp:wxr_version>1.2</wp:wxr_version>', '<wp:base_site_url>https://dialogosestruturantes.com.br</wp:base_site_url>', '<wp:base_blog_url>https://dialogosestruturantes.com.br</wp:base_blog_url>']
    for i, (login, nome) in enumerate(sorted(AUTORES.items()), 2):
        L.append(f'<wp:author><wp:author_id>{i}</wp:author_id><wp:author_login>{cdata(login)}</wp:author_login><wp:author_email></wp:author_email>'
                 f'<wp:author_display_name>{cdata(nome)}</wp:author_display_name><wp:author_first_name></wp:author_first_name><wp:author_last_name></wp:author_last_name></wp:author>')
    for s, n in ED.items():
        L.append(f'<wp:category><wp:category_nicename>{s}</wp:category_nicename><wp:category_parent></wp:category_parent><wp:cat_name>{cdata(n)}</wp:cat_name></wp:category>')
    for t in TAGS:
        L.append(f'<wp:tag><wp:tag_slug>{slug(t)}</wp:tag_slug><wp:tag_name>{cdata(t)}</wp:tag_name></wp:tag>')

    def bloco(i, tipo, status, titulo, slg, data, autor_login, conteudo, resumo, cats=(), tags=(), meta=None, parent=0, anexo=None):
        x = ['<item>', f'<title>{cdata(titulo)}</title>', f'<dc:creator>{cdata(autor_login)}</dc:creator>',
             f'<guid isPermaLink="false">https://dialogosestruturantes.com.br/?p={i}</guid>', '<description></description>',
             f'<content:encoded>{cdata(conteudo)}</content:encoded>', f'<excerpt:encoded>{cdata(resumo)}</excerpt:encoded>',
             f'<wp:post_id>{i}</wp:post_id>', f'<wp:post_date>{cdata(data)}</wp:post_date>', f'<wp:post_date_gmt>{cdata(gmt(data))}</wp:post_date_gmt>',
             '<wp:comment_status>closed</wp:comment_status><wp:ping_status>closed</wp:ping_status>',
             f'<wp:post_name>{cdata(slg)}</wp:post_name>', f'<wp:status>{status}</wp:status>', f'<wp:post_parent>{parent}</wp:post_parent>',
             '<wp:menu_order>0</wp:menu_order>', f'<wp:post_type>{tipo}</wp:post_type>', '<wp:post_password></wp:post_password>',
             f'<wp:is_sticky>{1 if (meta or {}).pop("_fixo", False) else 0}</wp:is_sticky>']
        if anexo:
            x.append(f'<wp:attachment_url>{cdata(anexo)}</wp:attachment_url>')
        for c in cats:
            x.append(f'<category domain="category" nicename="{c}">{cdata(ED[c])}</category>')
        for t in tags:
            x.append(f'<category domain="post_tag" nicename="{slug(t)}">{cdata(t)}</category>')
        for k, v in (meta or {}).items():
            x.append(f'<wp:postmeta><wp:meta_key>{cdata(k)}</wp:meta_key><wp:meta_value>{cdata(str(v))}</wp:meta_value></wp:postmeta>')
            if k.startswith('dp_'):
                x.append(f'<wp:postmeta><wp:meta_key>{cdata("_" + k)}</wp:meta_key><wp:meta_value>{cdata("field_" + k)}</wp:meta_value></wp:postmeta>')
        x.append('</item>')
        L.append('\n'.join(x))

    # anexos primeiro (o post de cada um é o primeiro que usa a imagem)
    dono = {}
    for it in ITENS:
        if it.get('thumb'):
            dono.setdefault(it['thumb'], it['id'])
    for arq, aid in IMGS.items():
        nome = arq.rsplit('.', 1)[0].replace('-', ' ').capitalize()
        bloco(aid, 'attachment', 'inherit', nome, arq.rsplit('.', 1)[0], '2026-11-01 08:00:00', 'redacao', '', '', parent=dono.get(aid, 0), anexo=BASE_IMG + arq)
    for it in ITENS:
        meta = dict(it['meta'])
        if it.get('thumb'):
            meta['_thumbnail_id'] = it['thumb']
        if it.get('fixo'):
            meta['_fixo'] = True
        bloco(it['id'], it['tipo'], it['status'], it['titulo'], it['slug'], it['data'], it['autor'], it['conteudo'], it['resumo'],
              it['cats'], it['tags'], meta)
    L.append('</channel>\n</rss>\n')
    open(nome_arq, 'w').write('\n'.join(L))

def gmt(d):
    return (datetime.strptime(d, '%Y-%m-%d %H:%M:%S') + timedelta(hours=3)).strftime('%Y-%m-%d %H:%M:%S')

autor('Redação')
gerar_edicao()
wxr(DIR + f'dialogos-7a-edicao{SUFIXO}.xml', 'Diálogos Estruturantes — 7ª edição')
n_ed = len(ITENS)
ITENS.clear(); IMGS.clear(); AUTORES.clear(); _id[0] = 5000
autor('Redação')
gerar_minuta()
wxr(DIR + f'dialogos-teste-minuta-20-11{SUFIXO}.xml', 'Diálogos Estruturantes — teste da Minuta ao Leitor (20/11)')
print('edição:', n_ed, 'itens · minuta:', len(ITENS), 'itens')
