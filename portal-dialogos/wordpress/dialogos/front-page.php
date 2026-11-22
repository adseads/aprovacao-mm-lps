<?php
/**
 * Página inicial. A versão (v1 ou v2) é escolhida em Configurações › Portal.
 */
get_header();

$usados = array();
/** Busca posts que ainda não apareceram na página. */
$buscar = function ( $args ) use ( &$usados ) {
	$args = wp_parse_args( $args, array( 'post_type' => 'post', 'numberposts' => 3, 'ignore_sticky_posts' => true ) );
	$args['post__not_in'] = $usados;
	$r = get_posts( $args );
	$usados = array_merge( $usados, wp_list_pluck( $r, 'ID' ) );
	return $r;
};

$fixos = get_option( 'sticky_posts' );
$manchete = $fixos ? $buscar( array( 'numberposts' => 1, 'post__in' => $fixos ) ) : array();
if ( ! $manchete ) {
	$manchete = $buscar( array( 'numberposts' => 1 ) );
}
$manchete = $manchete ? $manchete[0] : null;

$editorias = array();
foreach ( array( 'portos', 'infra', 'agro', 'regulacao', 'tributos', 'no-direito' ) as $slug ) {
	$t = get_term_by( 'slug', $slug, 'category' );
	if ( $t ) $editorias[ $slug ] = $t;
}
$episodio = get_posts( array( 'post_type' => 'episodio', 'numberposts' => 1 ) );
$episodio = $episodio ? $episodio[0] : null;
$edicao = dt_edicao_atual();
$url_opiniao = home_url( '/opiniao/' );
$url_agenda = get_post_type_archive_link( 'evento' ) ?: home_url( '/agenda/' );
$url_podcast = get_post_type_archive_link( 'episodio' ) ?: home_url( '/podcast/' );

$versao = function_exists( 'dp_config' ) ? dp_config( 'versao_home' ) : 'v2';
include locate_template( 'partes/home-' . ( $versao === 'v1' ? 'v1' : 'v2' ) . '.php' );

get_footer();
