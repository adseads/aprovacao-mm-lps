<?php
/**
 * Peças reutilizadas pelos modelos de página.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dt_campo( $nome, $id = null ) {
	if ( function_exists( 'dp_campo' ) ) {
		return dp_campo( $nome, $id );
	}
	return get_post_meta( $id ?: get_the_ID(), $nome, true );
}

function dt_ed_mapa() {
	return array(
		'portos'     => array( 'Portos', 'portos', 'portos' ),
		'infra'      => array( 'Infra', 'infra', 'infra' ),
		'agro'       => array( 'Agro', 'agro', 'agro' ),
		'regulacao'  => array( 'Regulação', 'regulacao', 'regulacao' ),
		'tributos'   => array( 'Radar Tributário', 'tributos', 'tributos' ),
		'no-direito' => array( 'No Direito', 'direito', 'direito' ),
		'direito'    => array( 'No Direito', 'direito', 'direito' ),
		'eventos'    => array( 'Eventos', 'eventos', 'eventos' ),
		'podcast'    => array( 'Podcast', 'podcast', 'podcast' ),
	);
}

/** Slug da editoria do post (a primeira editoria conhecida). */
function dt_ed( $post = null ) {
	$cats = get_the_category( $post ? ( is_object( $post ) ? $post->ID : $post ) : null );
	$mapa = dt_ed_mapa();
	foreach ( (array) $cats as $c ) {
		if ( isset( $mapa[ $c->slug ] ) ) return $c->slug;
	}
	return $cats ? $cats[0]->slug : 'regulacao';
}
function dt_ed_info( $slug ) {
	$m = dt_ed_mapa();
	if ( isset( $m[ $slug ] ) ) return $m[ $slug ];
	$t = get_term_by( 'slug', $slug, 'category' );
	return array( $t ? $t->name : 'Notícias', 'regulacao', 'regulacao' );
}
function dt_ed_classe( $slug ) {
	return 'ed-' . dt_ed_info( $slug )[2];
}

function dt_icone( $nome ) {
	$i = array(
		'portos'    => '<path d="M2 14h20l-3 6H5zM6 13V8h5V4h2v4h5v5z"/>',
		'infra'     => '<path d="M2 17h20v2.5H2zM4 16a8 8 0 0 1 16 0zM10.5 5h3v5h-3z"/>',
		'agro'      => '<path d="M11 21v-7.2C6.5 13.6 4 10.6 4 6c4.6 0 7 2.4 7.4 6.2C12 7.3 15 4 20 4c0 5.2-3 8.4-7 8.9V21z"/>',
		'regulacao' => '<path d="M12 2l10 5.5H2zM4.5 9.5h2.5v8H4.5zm6.25 0h2.5v8h-2.5zm6.25 0h2.5v8H17zM2 19.5h20V22H2z"/>',
		'tributos'  => '<path d="M7 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm10 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6zM5 19.5 17.6 3.6l1.9 1.5L6.9 21z"/>',
		'direito'   => '<path d="M11 3h2v15h5v3H6v-3h5zM3 7h18v2H3zM5.5 9 2 15.5h7zm13 0L15 15.5h7z"/>',
		'podcast'   => '<path d="M12 2a3.5 3.5 0 0 0-3.5 3.5v6a3.5 3.5 0 0 0 7 0v-6A3.5 3.5 0 0 0 12 2zM5 11h2a5 5 0 0 0 10 0h2a7 7 0 0 1-6 6.9V20h3v2H8v-2h3v-2.1A7 7 0 0 1 5 11z"/>',
		'eventos'   => '<path d="M7 2h2v2h6V2h2v2h4v18H3V4h4zM5 9v11h14V9z"/>',
		'cal'       => '<path d="M7 2h2v2h6V2h2v2h4v18H3V4h4zM5 9v11h14V9z"/>',
		'busca'     => '<path d="M10 3a7 7 0 0 1 5.6 11.2l5.1 5.1-1.4 1.4-5.1-5.1A7 7 0 1 1 10 3zm0 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10z" fill="currentColor"/>',
		'menu'      => '<path d="M3 6h18v2H3zm0 5h18v2H3zm0 5h18v2H3z" fill="currentColor"/>',
		'play'      => '<path d="M7 4v16l13-8z"/>',
		'wpp'       => '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-3.3-.8-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.9s.7-2.1 1-2.4c.3-.3.6-.3.8-.3h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.7-.1l2 1c.3.1.5.2.5.4.1.1.1.7-.1 1.3z"/>',
		'in'        => '<path d="M4.5 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM3 8.5h3V21H3zm5.5 0h2.9v1.7c.4-.8 1.5-1.9 3.4-1.9 3.6 0 4.2 2.4 4.2 5.4V21h-3v-6.3c0-1.5 0-3.4-2.1-3.4s-2.4 1.6-2.4 3.3V21h-3z"/>',
		'x'         => '<path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/>',
		'link'      => '<path d="M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0zM8.5 19.5a4.5 4.5 0 0 1-3.2-7.7l2.1-2.1 1.4 1.4-2.1 2.1a2.5 2.5 0 0 0 3.5 3.5l2.1-2.1 1.4 1.4-2.1 2.1a4.5 4.5 0 0 1-3.1 1.4zm8.1-5.2-1.4-1.4 2.1-2.1a2.5 2.5 0 0 0-3.5-3.5l-2.1 2.1-1.4-1.4 2.1-2.1a4.5 4.5 0 0 1 6.4 6.4z"/>',
	);
	return '<svg viewBox="0 0 24 24" aria-hidden="true">' . ( $i[ $nome ] ?? '' ) . '</svg>';
}

function dt_selo( $slug, $grande = false, $link = false ) {
	$info = dt_ed_info( $slug );
	$html = dt_icone( $info[1] ) . esc_html( $info[0] );
	$cls  = 'selo ed-' . $info[2] . ( $grande ? ' grande' : '' );
	if ( $link ) {
		$t = get_term_by( 'slug', $slug, 'category' );
		if ( $t ) {
			return sprintf( '<a class="%s" href="%s" data-componente="selo" data-posicao="%s">%s</a>', $cls, esc_url( get_term_link( $t ) ), esc_attr( $slug ), $html );
		}
	}
	return sprintf( '<span class="%s">%s</span>', $cls, $html );
}

/** Imagem de destaque; sem foto, um bloco neutro do mesmo tamanho. */
function dt_img( $post, $tamanho = 'dt-card', $cls = '', $prioridade = false ) {
	$id = is_object( $post ) ? $post->ID : $post;
	if ( has_post_thumbnail( $id ) ) {
		return get_the_post_thumbnail( $id, $tamanho, array(
			'class'         => $cls,
			'loading'       => $prioridade ? 'eager' : 'lazy',
			'fetchpriority' => $prioridade ? 'high' : 'auto',
			'alt'           => trim( wp_strip_all_tags( get_post_meta( get_post_thumbnail_id( $id ), '_wp_attachment_image_alt', true ) ) ),
		) );
	}
	return '<span class="' . esc_attr( $cls ) . ' sem-foto" style="display:block;aspect-ratio:3/2;background:var(--nevoa)"></span>';
}

function dt_linha_fina( $post = null ) {
	$post = get_post( $post );
	$lf = dt_campo( 'dp_linha_fina', $post->ID );
	return $lf ?: ( has_excerpt( $post ) ? get_the_excerpt( $post ) : '' );
}

function dt_modelo( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type === 'episodio' ) return 'episodio';
	return dt_campo( 'dp_modelo', $post->ID ) ?: 'materia';
}

/** Texto do chapéu nos cartões. */
function dt_rotulo( $post = null ) {
	$post = get_post( $post );
	$m = dt_modelo( $post );
	$r = array( 'episodio' => 'Podcast', 'opiniao' => 'Opinião', 'explicador' => 'Entenda', 'parceiro' => 'Conteúdo de parceiro', 'materia_video' => 'Vídeo' );
	if ( isset( $r[ $m ] ) ) return $r[ $m ];
	$ch = dt_campo( 'dp_chapeu', $post->ID );
	return $ch ?: dt_ed_info( dt_ed( $post ) )[0];
}

function dt_data( $post = null ) {
	return get_the_date( 'd M Y', $post );
}

function dt_card( $post, $pos, $tam = 'h3', $resumo = false, $imagem = true ) {
	$post = get_post( $post );
	$ed = dt_ed( $post );
	return sprintf(
		'<a class="card %s" href="%s" data-componente="card" data-posicao="%s" data-conteudo-id="%s">%s<span class="chapeu">%s</span><h3 class="%s t">%s</h3>%s</a>',
		dt_ed_classe( $ed ), esc_url( get_permalink( $post ) ), esc_attr( $pos ), esc_attr( $post->post_name ),
		$imagem ? dt_img( $post, 'dt-card', 'img' ) : '',
		esc_html( dt_rotulo( $post ) ), esc_attr( $tam ), esc_html( get_the_title( $post ) ),
		$resumo ? '<p class="resumo">' . esc_html( dt_linha_fina( $post ) ) . '</p>' : ''
	);
}

function dt_linha( $post, $pos ) {
	$post = get_post( $post );
	return sprintf(
		'<a class="lista-linha %s" href="%s" data-componente="lista" data-posicao="%s" data-conteudo-id="%s">%s<span><span class="chapeu">%s</span><span class="h4 t">%s</span></span></a>',
		dt_ed_classe( dt_ed( $post ) ), esc_url( get_permalink( $post ) ), esc_attr( $pos ), esc_attr( $post->post_name ),
		dt_img( $post, 'dt-card' ), esc_html( dt_rotulo( $post ) ), esc_html( get_the_title( $post ) )
	);
}

function dt_item( $post, $pos, $comp = 'lista' ) {
	$post = get_post( $post );
	return sprintf(
		'<a class="item %s" href="%s" data-componente="%s" data-posicao="%s" data-conteudo-id="%s">%s<span><span class="chapeu">%s</span><span class="h3 t">%s</span><span class="resumo" style="display:block">%s</span><span class="meta">%s | %s</span></span></a>',
		dt_ed_classe( dt_ed( $post ) ), esc_url( get_permalink( $post ) ), esc_attr( $comp ), esc_attr( $pos ), esc_attr( $post->post_name ),
		dt_img( $post, 'dt-card' ), esc_html( dt_rotulo( $post ) ), esc_html( get_the_title( $post ) ),
		esc_html( dt_linha_fina( $post ) ), esc_html( get_the_author_meta( 'display_name', $post->post_author ) ), esc_html( dt_data( $post ) )
	);
}

/* ---------- autores ---------- */
function dt_foto_autor_id( $user_id ) {
	$f = function_exists( 'get_field' ) ? get_field( 'dp_foto', 'user_' . $user_id ) : get_user_meta( $user_id, 'dp_foto', true );
	return is_array( $f ) ? ( $f['ID'] ?? 0 ) : (int) $f;
}
function dt_foto_html( $img_id, $nome, $tamanho = 'dt-retrato' ) {
	if ( $img_id ) {
		return wp_get_attachment_image( $img_id, $tamanho, false, array( 'alt' => 'Foto de ' . $nome, 'loading' => 'lazy' ) );
	}
	return '<img src="' . esc_url( DT_URI . '/assets/autor.svg' ) . '" alt="Foto de ' . esc_attr( $nome ) . '" loading="lazy">';
}
function dt_retrato( $user_id, $ed = 'regulacao', $cls = '' ) {
	$nome = get_the_author_meta( 'display_name', $user_id );
	return '<span class="retrato ed-' . esc_attr( dt_ed_info( $ed )[2] ) . ' ' . esc_attr( $cls ) . '">' . dt_foto_html( dt_foto_autor_id( $user_id ), $nome ) . '</span>';
}
function dt_avatar_img( $img_id, $nome, $ed = 'podcast', $grande = false ) {
	return '<span class="avatar ed-' . esc_attr( dt_ed_info( $ed )[2] ) . ( $grande ? ' g' : '' ) . '">' . dt_foto_html( $img_id, $nome, 'dt-quadrada' ) . '</span>';
}
function dt_cargo_autor( $user_id ) {
	return function_exists( 'get_field' ) ? (string) get_field( 'dp_cargo', 'user_' . $user_id ) : (string) get_user_meta( $user_id, 'dp_cargo', true );
}

/* ---------- blocos ---------- */
function dt_mais_lidas( $posts = null ) {
	$posts = $posts ?? ( function_exists( 'dp_mais_lidas' ) ? dp_mais_lidas( 5 ) : get_posts( array( 'numberposts' => 5 ) ) );
	if ( ! $posts ) return '';
	$li = '';
	foreach ( $posts as $i => $p ) {
		$li .= sprintf( '<li><a class="t" href="%s" data-componente="mais-lidas" data-posicao="%d">%s</a></li>', esc_url( get_permalink( $p ) ), $i + 1, esc_html( get_the_title( $p ) ) );
	}
	return '<section class="mais-lidas" aria-labelledby="ml-h"><h3 id="ml-h">Mais lidas</h3><ol>' . $li . '</ol></section>';
}

function dt_nome_newsletter() {
	return function_exists( 'dp_config' ) ? dp_config( 'nome_newsletter' ) : 'Newsletter';
}
function dt_url_newsletter() {
	$u = function_exists( 'dp_url_pagina' ) ? dp_url_pagina( 'page-newsletter.php' ) : '';
	return $u ?: home_url( '/newsletter/' );
}

function dt_form_minuta( $pos, $compacto = false ) {
	$isca = '<input type="text" name="site" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">';
	if ( $compacto ) {
		return '<section class="caixa-minuta" aria-labelledby="mn-' . esc_attr( $pos ) . '"><h3 id="mn-' . esc_attr( $pos ) . '">Newsletter diária</h3>'
			. '<p>As decisões do dia em infraestrutura, portos, agro e tributos. De segunda a sexta, às 7h.</p>'
			. '<form class="minuta" data-posicao="' . esc_attr( $pos ) . '"><label class="sr" for="em-' . esc_attr( $pos ) . '">Seu e-mail</label>'
			. '<input id="em-' . esc_attr( $pos ) . '" type="email" name="email" required placeholder="seu@email.com.br" autocomplete="email">' . $isca
			. '<button class="btn btn-laranja" type="submit">Receber a newsletter</button><p class="minuta-msg" role="status"></p></form></section>';
	}
	return '<form class="minuta form-minuta" data-posicao="' . esc_attr( $pos ) . '"><label class="sr" for="em-' . esc_attr( $pos ) . '">Seu e-mail</label>'
		. '<input id="em-' . esc_attr( $pos ) . '" type="email" name="email" required placeholder="seu@email.com.br" autocomplete="email">' . $isca
		. '<button class="btn btn-laranja" type="submit">Receber a newsletter</button>'
		. '<label class="consent"><input type="checkbox" required> Concordo em receber a newsletter e com a <a href="' . esc_url( get_privacy_policy_url() ?: home_url( '/' ) ) . '" style="text-decoration:underline">política de privacidade</a>.</label>'
		. '<p class="minuta-msg" role="status"></p></form>';
}

function dt_faixa_minuta( $pos = 'faixa-minuta' ) {
	return '<section class="minuta-faixa" aria-labelledby="mf-h"><div class="wrap"><div>'
		. '<span class="chapeu laranja">Newsletter diária</span>'
		. '<h2 id="mf-h" class="h1" style="margin-bottom:8px">Comece o dia informado</h2>'
		. '<p class="fina" style="margin:0">As decisões que movem infraestrutura, portos, agro e tributos, resumidas para ler em cinco minutos. De segunda a sexta, às 7h.</p>'
		. '</div><div>' . dt_form_minuta( $pos ) . '</div></div></section>';
}

function dt_compartilhar() {
	$u = rawurlencode( get_permalink() );
	$t = rawurlencode( get_the_title() );
	return '<div class="compartilhar" aria-label="Compartilhar">'
		. '<a href="https://api.whatsapp.com/send?text=' . $t . '%20' . $u . '" target="_blank" rel="noopener" data-componente="compartilhar" data-posicao="whatsapp" aria-label="Compartilhar no WhatsApp">' . dt_icone( 'wpp' ) . '</a>'
		. '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' . $u . '" target="_blank" rel="noopener" data-componente="compartilhar" data-posicao="linkedin" aria-label="Compartilhar no LinkedIn">' . dt_icone( 'in' ) . '</a>'
		. '<a href="https://x.com/intent/post?url=' . $u . '&text=' . $t . '" target="_blank" rel="noopener" data-componente="compartilhar" data-posicao="x" aria-label="Compartilhar no X">' . dt_icone( 'x' ) . '</a>'
		. '<a href="' . esc_url( get_permalink() ) . '" data-copiar data-componente="compartilhar" data-posicao="copiar" aria-label="Copiar link">' . dt_icone( 'link' ) . '</a></div>';
}

function dt_youtube_id( $url ) {
	if ( preg_match( '~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{11})~', (string) $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/** Vídeo leve: mostra a capa e só carrega o YouTube no clique. */
function dt_video( $url, $post, $duracao = '', $rotulo = 'Assistir ao vídeo' ) {
	$id = dt_youtube_id( $url );
	$capa = has_post_thumbnail( $post ) ? dt_img( $post, 'dt-capa', '', true ) : ( $id ? '<img src="https://i.ytimg.com/vi/' . esc_attr( $id ) . '/hqdefault.jpg" alt="">' : '' );
	return '<div class="video" data-video="' . esc_attr( $id ) . '" role="button" tabindex="0" aria-label="' . esc_attr( $rotulo ) . '">' . $capa
		. '<span class="play">' . dt_icone( 'play' ) . '</span>' . ( $duracao ? '<span class="duracao">' . esc_html( $duracao ) . '</span>' : '' ) . '</div>';
}

function dt_relacionadas( $post_id ) {
	$q = get_posts( array( 'numberposts' => 3, 'post__not_in' => array( $post_id ), 'category__in' => wp_get_post_categories( $post_id ), 'ignore_sticky_posts' => true ) );
	if ( count( $q ) < 3 ) {
		$q = array_merge( $q, get_posts( array( 'numberposts' => 3 - count( $q ), 'post__not_in' => array_merge( array( $post_id ), wp_list_pluck( $q, 'ID' ) ) ) ) );
	}
	if ( ! $q ) return '';
	$c = '';
	foreach ( $q as $i => $p ) $c .= dt_card( $p, 'relacionada-' . ( $i + 1 ), 'h4' );
	return '<section class="relacionadas" aria-labelledby="rel-h"><h2 id="rel-h" class="h2" style="margin-bottom:20px">Leia também</h2><div class="grade-3">' . $c . '</div></section>';
}

function dt_paginacao() {
	$l = paginate_links( array( 'type' => 'array', 'prev_text' => 'Anterior', 'next_text' => 'Próxima', 'mid_size' => 1 ) );
	if ( ! $l ) return '';
	return '<nav class="paginacao" aria-label="Páginas">' . implode( '', $l ) . '</nav>';
}

/* ---------- eventos ---------- */
function dt_evento_dados( $p ) {
	$ini = (string) dt_campo( 'dp_inicio', $p->ID );
	$fim = (string) dt_campo( 'dp_fim', $p->ID );
	$ts_i = $ini ? strtotime( $ini ) : 0;
	$ts_f = $fim ? strtotime( $fim ) : $ts_i;
	$meses = array( 'jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez' );
	$mes_ext = array( 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro' );
	$data = '';
	if ( $ts_i ) {
		$mi = (int) gmdate( 'n', $ts_i ) - 1;
		$mf = (int) gmdate( 'n', $ts_f ) - 1;
		if ( $ts_f === $ts_i ) $data = gmdate( 'j', $ts_i ) . ' de ' . $mes_ext[ $mi ];
		elseif ( $mi === $mf ) $data = gmdate( 'j', $ts_i ) . ( (int) gmdate( 'j', $ts_f ) - (int) gmdate( 'j', $ts_i ) === 1 ? ' e ' : ' a ' ) . gmdate( 'j', $ts_f ) . ' de ' . $mes_ext[ $mf ];
		else $data = gmdate( 'j', $ts_i ) . ' de ' . $mes_ext[ $mi ] . ' a ' . gmdate( 'j', $ts_f ) . ' de ' . $mes_ext[ $mf ];
	}
	$hoje = wp_date( 'Ymd' );
	return array(
		'id'         => $p->post_name,
		'nome'       => get_the_title( $p ),
		'ini'        => $ini,
		'mes_num'    => $ts_i ? gmdate( 'm', $ts_i ) : '',
		'mes_nome'   => $ts_i ? ucfirst( $mes_ext[ (int) gmdate( 'n', $ts_i ) - 1 ] ) . ' ' . gmdate( 'Y', $ts_i ) : '',
		'dia'        => $ts_i ? gmdate( 'd', $ts_i ) : '',
		'mes'        => $ts_i ? $meses[ (int) gmdate( 'n', $ts_i ) - 1 ] : '',
		'data'       => $data,
		'local'      => (string) dt_campo( 'dp_local', $p->ID ),
		'info'       => (string) dt_campo( 'dp_info', $p->ID ),
		'chamada'    => (string) dt_campo( 'dp_chamada', $p->ID ),
		'link'       => (string) dt_campo( 'dp_link', $p->ID ),
		'prioridade' => dt_campo( 'dp_prioridade', $p->ID ) ?: 'normal',
		'andamento'  => $ini && $ini < $hoje && ( $fim ?: $ini ) >= $hoje,
		'setores'    => wp_list_pluck( get_the_category( $p->ID ), 'slug' ),
	);
}

/** Eventos futuros ou em andamento: institucional, destaque e normais por data. */
function dt_eventos() {
	$hoje = wp_date( 'Ymd' );
	$posts = get_posts( array(
		'post_type' => 'evento', 'numberposts' => 100, 'meta_key' => 'dp_inicio', 'orderby' => 'meta_value', 'order' => 'ASC',
		'meta_query' => array( 'relation' => 'OR',
			array( 'key' => 'dp_fim', 'value' => $hoje, 'compare' => '>=' ),
			array( 'key' => 'dp_inicio', 'value' => $hoje, 'compare' => '>=' ) ),
	) );
	$ev = array_map( 'dt_evento_dados', $posts );
	$rank = array( 'institucional' => 0, 'destaque' => 1, 'normal' => 2 );
	usort( $ev, fn( $a, $b ) => array( $rank[ $a['prioridade'] ] ?? 2, $a['ini'] ) <=> array( $rank[ $b['prioridade'] ] ?? 2, $b['ini'] ) );
	return $ev;
}

function dt_ev_institucional( $e, $pos ) {
	return '<article class="ev-inst" data-componente="evento" data-posicao="' . esc_attr( $pos ) . '" data-conteudo-id="' . esc_attr( $e['id'] ) . '" data-prioridade="institucional">'
		. '<div class="ev-inst-data"><b>' . esc_html( $e['dia'] ) . '</b><span>' . esc_html( $e['mes'] ) . '</span></div>'
		. '<div><span class="ev-tag">Evento Diálogos Estruturantes</span><h3>' . esc_html( $e['nome'] ) . '</h3>'
		. ( $e['chamada'] ? '<p class="ev-chamada">' . esc_html( $e['chamada'] ) . '</p>' : '' )
		. '<p class="ev-local">' . dt_icone( 'cal' ) . esc_html( $e['data'] . ( $e['local'] ? ' | ' . $e['local'] : '' ) ) . '</p>'
		. ( $e['link'] ? '<a class="btn btn-laranja" href="' . esc_url( $e['link'] ) . '" data-componente="evento-cta" data-posicao="' . esc_attr( $pos ) . '">Saiba mais</a>' : '' )
		. '</div></article>';
}

function dt_ev_card( $e, $pos ) {
	$tag = $e['prioridade'] === 'destaque' ? '<span class="ev-tag-d">Destaque</span>' : ( $e['andamento'] ? '<span class="ev-tag-a">Acontecendo</span>' : '' );
	return '<article class="evento ev-' . esc_attr( $e['prioridade'] ) . '" data-componente="evento" data-posicao="' . esc_attr( $pos ) . '" data-conteudo-id="' . esc_attr( $e['id'] ) . '" data-prioridade="' . esc_attr( $e['prioridade'] ) . '">'
		. $tag . '<h4>' . esc_html( $e['nome'] ) . '</h4><span class="data">' . dt_icone( 'cal' ) . esc_html( $e['data'] ) . '</span><p>' . esc_html( $e['local'] ) . '</p></article>';
}

function dt_agenda_resumo( $pos, $n = 3 ) {
	$ev = dt_eventos();
	if ( ! $ev ) return '<p class="meta">Nenhum evento agendado.</p>';
	$inst = array_slice( array_values( array_filter( $ev, fn( $e ) => $e['prioridade'] === 'institucional' ) ), 0, 1 );
	$resto = array_slice( array_values( array_filter( $ev, fn( $e ) => $e['prioridade'] !== 'institucional' && ! $e['andamento'] ) ), 0, $n );
	$h = '';
	foreach ( $inst as $e ) $h .= dt_ev_institucional( $e, $pos . '-institucional' );
	$g = '';
	foreach ( $resto as $i => $e ) $g .= dt_ev_card( $e, $pos . '-' . ( $i + 1 ) );
	return $h . ( $g ? '<div class="ev-grade">' . $g . '</div>' : '' );
}

/** Edição mais recente da revista. */
function dt_edicao_atual() {
	$e = get_posts( array( 'post_type' => 'edicao', 'numberposts' => 1 ) );
	return $e ? $e[0] : null;
}

/** Menu principal com a cor de cada editoria no sublinhado. */
class DT_Menu extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$cls = 'ed-regulacao';
		if ( $item->object === 'category' ) {
			$t = get_term( (int) $item->object_id, 'category' );
			if ( $t && ! is_wp_error( $t ) ) $cls = dt_ed_classe( $t->slug );
		} else {
			$tit = sanitize_title( $item->title );
			$cls = array( 'podcast' => 'ed-podcast', 'revista' => 'ed-revista', 'agenda' => 'ed-eventos' )[ $tit ] ?? 'ed-regulacao';
		}
		$atual = in_array( 'current-menu-item', (array) $item->classes, true ) ? ' aria-current="page"' : '';
		$output .= sprintf( '<li class="%s"><a href="%s" data-componente="menu" data-posicao="%s"%s>%s</a>', esc_attr( $cls ), esc_url( $item->url ), esc_attr( sanitize_title( $item->title ) ), $atual, esc_html( $item->title ) );
	}
}
