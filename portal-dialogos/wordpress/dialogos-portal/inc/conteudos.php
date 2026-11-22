<?php
/**
 * Tipos de conteúdo, temas e rotas do portal.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dp_registrar_conteudos() {
	$base = array(
		'public'       => true,
		'show_in_rest' => true,
		'menu_position'=> 5,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
	);

	register_post_type( 'episodio', $base + array(
		'labels'      => dp_rotulos( 'Episódio', 'Podcast', 'Episódios' ),
		'menu_icon'   => 'dashicons-microphone',
		'has_archive' => 'podcast',
		'rewrite'     => array( 'slug' => 'podcast', 'with_front' => false ),
		'taxonomies'  => array( 'category', 'post_tag' ),
	) );

	register_post_type( 'edicao', $base + array(
		'labels'      => dp_rotulos( 'Edição', 'Revista', 'Edições' ),
		'menu_icon'   => 'dashicons-book-alt',
		'has_archive' => 'revista',
		'rewrite'     => array( 'slug' => 'revista', 'with_front' => false ),
	) );

	register_post_type( 'evento', array_merge( $base, array(
		'labels'      => dp_rotulos( 'Evento', 'Agenda', 'Eventos' ),
		'menu_icon'   => 'dashicons-calendar-alt',
		'has_archive' => 'agenda',
		'rewrite'     => array( 'slug' => 'agenda', 'with_front' => false ),
		'taxonomies'  => array( 'category' ),
		'supports'    => array( 'title', 'editor', 'thumbnail' ),
	) ) );

	register_post_type( 'popup', array(
		'labels'       => dp_rotulos( 'Popup', 'Popups', 'Popups' ),
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => false,
		'menu_icon'    => 'dashicons-format-status',
		'menu_position'=> 25,
		'supports'     => array( 'title' ),
	) );
}
add_action( 'init', 'dp_registrar_conteudos' );

function dp_rotulos( $um, $menu, $varios ) {
	return array(
		'name'          => $varios,
		'singular_name' => $um,
		'menu_name'     => $menu,
		'add_new'       => 'Adicionar',
		'add_new_item'  => 'Adicionar ' . strtolower( $um ),
		'edit_item'     => 'Editar ' . strtolower( $um ),
		'all_items'     => 'Todos',
		'search_items'  => 'Buscar',
		'not_found'     => 'Nada encontrado',
	);
}

// "Tags" passam a se chamar "Temas"; "Categorias" passam a se chamar "Editorias".
add_action( 'init', function () {
	global $wp_taxonomies;
	if ( isset( $wp_taxonomies['post_tag'] ) ) {
		$l = $wp_taxonomies['post_tag']->labels;
		$l->name = $l->menu_name = 'Temas';
		$l->singular_name = 'Tema';
		$l->add_new_item  = 'Adicionar tema';
		$l->search_items  = 'Buscar temas';
		$l->all_items     = 'Todos os temas';
	}
	if ( isset( $wp_taxonomies['category'] ) ) {
		$l = $wp_taxonomies['category']->labels;
		$l->name = $l->menu_name = 'Editorias';
		$l->singular_name = 'Editoria';
		$l->add_new_item  = 'Adicionar editoria';
		$l->all_items     = 'Todas as editorias';
	}
}, 20 );

/**
 * Rotas /opiniao/ e /entenda/: listam as matérias por modelo.
 */
function dp_rotas() {
	add_rewrite_rule( '^opiniao/?$', 'index.php?dp_formato=opiniao', 'top' );
	add_rewrite_rule( '^opiniao/page/([0-9]+)/?$', 'index.php?dp_formato=opiniao&paged=$matches[1]', 'top' );
	add_rewrite_rule( '^entenda/?$', 'index.php?dp_formato=explicador', 'top' );
	add_rewrite_rule( '^entenda/page/([0-9]+)/?$', 'index.php?dp_formato=explicador&paged=$matches[1]', 'top' );
}
add_action( 'init', 'dp_rotas' );
add_filter( 'query_vars', function ( $v ) {
	$v[] = 'dp_formato';
	return $v;
} );

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	$formato = $q->get( 'dp_formato' );
	if ( $formato ) {
		$q->set( 'post_type', 'post' );
		$q->set( 'meta_query', array( array( 'key' => 'dp_modelo', 'value' => $formato ) ) );
		$q->is_home = false;
		$q->is_archive = true;
	}
	if ( $q->is_post_type_archive( 'evento' ) ) {
		$q->set( 'posts_per_page', 100 );
		$q->set( 'meta_key', 'dp_inicio' );
		$q->set( 'orderby', 'meta_value' );
		$q->set( 'order', 'ASC' );
		$q->set( 'meta_query', array(
			'relation' => 'OR',
			array( 'key' => 'dp_fim', 'value' => wp_date( 'Ymd' ), 'compare' => '>=' ),
			array( 'key' => 'dp_inicio', 'value' => wp_date( 'Ymd' ), 'compare' => '>=' ),
		) );
	}
	if ( $q->is_search() ) {
		$q->set( 'post_type', array( 'post', 'episodio', 'edicao' ) );
	}
} );

/**
 * Contador de leituras para "Mais lidas". Funciona mesmo com o cache de página,
 * porque a contagem é enviada depois que a página abre.
 */
add_action( 'wp_ajax_dp_leitura', 'dp_contar_leitura' );
add_action( 'wp_ajax_nopriv_dp_leitura', 'dp_contar_leitura' );
function dp_contar_leitura() {
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	if ( $id && in_array( get_post_type( $id ), array( 'post', 'episodio' ), true ) && get_post_status( $id ) === 'publish' ) {
		update_post_meta( $id, 'dp_leituras', (int) get_post_meta( $id, 'dp_leituras', true ) + 1 );
	}
	wp_send_json_success();
}

function dp_mais_lidas( $quantidade = 5, $categoria = 0 ) {
	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $quantidade,
		'ignore_sticky_posts' => true,
		'date_query'          => array( array( 'after' => '30 days ago' ) ),
		'meta_key'            => 'dp_leituras',
		'orderby'             => 'meta_value_num',
		'order'               => 'DESC',
	);
	if ( $categoria ) {
		$args['cat'] = $categoria;
	}
	$q = new WP_Query( $args );
	if ( $q->post_count < $quantidade ) {
		unset( $args['meta_key'], $args['orderby'], $args['order'], $args['date_query'] );
		$args['post__not_in'] = wp_list_pluck( $q->posts, 'ID' );
		$args['posts_per_page'] = $quantidade - $q->post_count;
		$q->posts = array_merge( $q->posts, get_posts( $args ) );
	}
	return $q->posts;
}

// Eventos e popups usam o editor simples: os campos aparecem logo abaixo do título.
add_filter( 'use_block_editor_for_post_type', function ( $usar, $tipo ) {
	return in_array( $tipo, array( 'evento', 'popup' ), true ) ? false : $usar;
}, 10, 2 );
