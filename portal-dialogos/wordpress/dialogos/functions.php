<?php
/**
 * Tema Diálogos Estruturantes.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DT_VERSAO', '0.1.2' );
define( 'DT_URI', get_template_directory_uri() );

require_once get_template_directory() . '/inc/ajudantes.php';
require_once get_template_directory() . '/inc/anuncios.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	register_nav_menus( array( 'principal' => 'Menu principal (editorias)' ) );
	add_image_size( 'dt-card', 640, 427, true );
	add_image_size( 'dt-capa', 1600, 686, true );
	add_image_size( 'dt-retrato', 450, 600, true );
	add_image_size( 'dt-quadrada', 240, 240, true );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'dialogos', DT_URI . '/assets/portal.css', array(), DT_VERSAO );
	wp_enqueue_script( 'dialogos', DT_URI . '/assets/portal.js', array(), DT_VERSAO, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script( 'dialogos', 'DT', array(
		'ajax'   => admin_url( 'admin-ajax.php' ),
		'nonce'  => wp_create_nonce( 'dp_newsletter' ),
		'post'   => is_singular( array( 'post', 'episodio' ) ) ? get_queried_object_id() : 0,
		'trava'  => function_exists( 'dp_config' ) && dp_config( 'trava_densidade' ) === '1',
		'previa' => current_user_can( 'edit_posts' ),
	) );
	// Sem jQuery, emojis e blocos de estilo que o tema não usa
	wp_dequeue_style( 'classic-theme-styles' );
} );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

add_action( 'wp_head', function () {
	foreach ( array( 'source-serif-4-latin-wght-normal', 'zalando-sans-expanded-latin-wght-normal' ) as $f ) {
		printf( '<link rel="preload" href="%s/assets/fontes/%s.woff2" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( DT_URI ), esc_attr( $f ) );
	}
	echo '<meta name="theme-color" content="#00054c">' . "\n";
}, 2 );

/**
 * Classes do <body>: estilo da página (pg-*) e nível de anúncios (ads-*).
 */
add_filter( 'body_class', function ( $c ) {
	$c[] = 'pg-' . dt_pagina();
	$nivel = dt_nivel();
	$c[] = 'ads-' . ( $nivel === 'nenhum' ? 'leve' : $nivel );
	if ( $nivel === 'nenhum' ) {
		$c[] = 'sem-anuncios';
	}
	return $c;
} );

/**
 * Nome do estilo da página, igual aos modelos aprovados.
 */
function dt_pagina() {
	if ( is_front_page() ) {
		return ( function_exists( 'dp_config' ) && dp_config( 'versao_home' ) === 'v1' ) ? 'home' : 'home-v2';
	}
	if ( is_singular( 'post' ) ) {
		$m = dt_campo( 'dp_modelo' ) ?: 'materia';
		return array( 'materia' => 'materia', 'materia_video' => 'materia-video', 'explicador' => 'explicador', 'opiniao' => 'opiniao', 'parceiro' => 'parceiro' )[ $m ] ?? 'materia';
	}
	if ( is_singular( 'episodio' ) ) return 'episodio';
	if ( is_singular( 'edicao' ) ) return 'edicao';
	if ( is_post_type_archive( 'episodio' ) ) return 'podcast';
	if ( is_post_type_archive( 'edicao' ) ) return 'revista';
	if ( is_post_type_archive( 'evento' ) || is_singular( 'evento' ) ) return 'agenda';
	if ( is_tag() ) return 'tema';
	if ( is_author() ) return 'autor';
	if ( is_search() ) return 'busca';
	if ( is_404() ) return '404';
	if ( is_page_template( 'page-newsletter.php' ) ) return 'newsletter';
	if ( is_page_template( 'page-newsletter-ok.php' ) ) return 'newsletter-ok';
	if ( is_page() ) return 'legal';
	return 'editoria';
}

function dt_nivel() {
	return function_exists( 'dp_nivel_anuncios' ) ? dp_nivel_anuncios() : 'nenhum';
}

/**
 * Grupo usado pelos popups ("Onde aparece").
 */
function dt_grupo_pagina() {
	if ( is_front_page() ) return 'home';
	if ( is_singular( 'post' ) ) return 'materia';
	if ( is_category() || is_tag() ) return 'editoria';
	if ( is_post_type_archive( 'evento' ) ) return 'agenda';
	if ( is_post_type_archive( 'episodio' ) || is_singular( 'episodio' ) ) return 'podcast';
	return 'outra';
}

add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more', fn() => '…' );

// Busca e listagens: 12 itens por página
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_category() || $q->is_tag() || $q->is_author() || $q->is_search() ) ) {
		$q->set( 'posts_per_page', 12 );
	}
} );
