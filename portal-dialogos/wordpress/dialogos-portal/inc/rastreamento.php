<?php
/**
 * dataLayer, Google Tag Manager e nível de anúncios de cada página.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tipo da página atual, no vocabulário do rastreamento.
 */
function dp_tipo_pagina() {
	if ( is_front_page() ) return 'home';
	if ( is_singular( 'post' ) ) {
		$m = dp_campo( 'dp_modelo' ) ?: 'materia';
		return array( 'materia' => 'materia', 'materia_video' => 'materia', 'explicador' => 'explicador', 'opiniao' => 'opiniao', 'parceiro' => 'parceiro' )[ $m ] ?? 'materia';
	}
	if ( is_singular( 'episodio' ) ) return 'episodio';
	if ( is_singular( 'edicao' ) ) return 'edicao';
	if ( is_post_type_archive( 'episodio' ) ) return 'podcast';
	if ( is_post_type_archive( 'edicao' ) ) return 'revista';
	if ( is_post_type_archive( 'evento' ) || is_singular( 'evento' ) ) return 'agenda';
	if ( get_query_var( 'dp_formato' ) ) return 'formato';
	if ( is_category() ) return 'editoria';
	if ( is_tag() ) return 'tema';
	if ( is_author() ) return 'autor';
	if ( is_search() ) return 'busca';
	if ( is_404() ) return '404';
	if ( is_page() ) {
		$t = get_page_template_slug();
		if ( str_contains( (string) $t, 'newsletter' ) ) return 'newsletter';
		return 'institucional';
	}
	return 'outro';
}

/**
 * Nível de anúncios da página: leve, equilibrada, intensiva, hard ou nenhum.
 */
function dp_nivel_anuncios() {
	if ( dp_config( 'anuncios_modo' ) === 'desligado' ) return 'nenhum';
	$tipo = dp_tipo_pagina();
	if ( in_array( $tipo, array( 'parceiro', 'newsletter', 'institucional' ), true ) ) return 'nenhum';
	if ( in_array( $tipo, array( 'busca', '404' ), true ) ) return 'leve';
	if ( $tipo === 'home' ) return dp_config( 'nivel_home' );
	if ( is_singular( array( 'post', 'episodio' ) ) ) {
		$n = dp_campo( 'dp_nivel' );
		if ( $n && $n !== 'padrao' ) return $n;
	}
	return dp_config( 'nivel_padrao' );
}

function dp_slug_editoria( $post_id = null ) {
	$cats = get_the_category( $post_id );
	return $cats ? $cats[0]->slug : '';
}

function dp_dados_pagina() {
	$d = array(
		'tipo_pagina'     => dp_tipo_pagina(),
		'pacote_anuncios' => dp_nivel_anuncios(),
	);
	if ( is_singular() ) {
		$id = get_queried_object_id();
		$d['conteudo_id'] = get_post_field( 'post_name', $id );
		$d['editoria']    = dp_slug_editoria( $id );
		$tags             = wp_get_post_tags( $id, array( 'fields' => 'slugs' ) );
		$d['tema']        = $tags ? $tags[0] : '';
		$d['autor']       = get_the_author_meta( 'user_nicename', get_post_field( 'post_author', $id ) );
		if ( is_singular( 'post' ) ) {
			$m = dp_campo( 'dp_modelo', $id );
			$d['formato'] = $m === 'materia_video' ? 'video' : 'foto';
			if ( $m === 'parceiro' ) {
				$d['venda_anuncios'] = 'patrocinado';
				$d['patrocinador']   = sanitize_title( (string) dp_campo( 'dp_patrocinador', $id ) );
			}
		}
	} elseif ( is_category() || is_tag() ) {
		$t = get_queried_object();
		$d[ is_category() ? 'editoria' : 'tema' ] = $t->slug;
	} elseif ( is_author() ) {
		$d['autor'] = get_queried_object()->user_nicename;
	} elseif ( is_search() ) {
		$d['termo_busca'] = get_search_query();
		global $wp_query;
		$d['resultados'] = (int) $wp_query->found_posts;
	} elseif ( is_front_page() ) {
		$d['versao_layout'] = dp_config( 'versao_home' );
	}
	return apply_filters( 'dp_dados_pagina', $d );
}

add_action( 'wp_head', function () {
	echo "<script>window.dataLayer=window.dataLayer||[];dataLayer.push(" . wp_json_encode( dp_dados_pagina() ) . ");</script>\n";
	$gtm = dp_config( 'gtm_id' );
	if ( $gtm ) {
		// Consent Mode v2: tudo negado até o banner de cookies registrar a escolha do leitor.
		echo "<script>function gtag(){dataLayer.push(arguments);}gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});</script>\n";
		printf( "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','%s');</script>\n", esc_js( $gtm ) );
	}
}, 1 );

add_action( 'wp_body_open', function () {
	$gtm = dp_config( 'gtm_id' );
	if ( $gtm ) {
		printf( '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>', esc_attr( $gtm ) );
	}
} );
