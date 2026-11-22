<?php
/**
 * Páginas fixas (Quem somos, Privacidade e termos…): índice lateral montado com os intertítulos.
 */
get_header();
the_post();
$conteudo = apply_filters( 'the_content', get_the_content() );
$indice = '';
$n = 0;
$conteudo = preg_replace_callback( '~<h2([^>]*)>(.*?)</h2>~s', function ( $m ) use ( &$indice, &$n ) {
	$n++;
	$id = preg_match( '~id="([^"]+)"~', $m[1], $x ) ? $x[1] : 'secao-' . $n;
	$indice .= '<li><a href="#' . esc_attr( $id ) . '">' . wp_strip_all_tags( $m[2] ) . '</a></li>';
	return preg_match( '~id="~', $m[1] ) ? $m[0] : '<h2 id="' . esc_attr( $id ) . '"' . $m[1] . '>' . $m[2] . '</h2>';
}, $conteudo );
// Seções do texto legal já vêm com id; os títulos das seções entram no índice
if ( ! $indice && preg_match_all( '~<section id="([^"]+)"><h2>(.*?)</h2>~s', $conteudo, $mm, PREG_SET_ORDER ) ) {
	foreach ( $mm as $m ) $indice .= '<li><a href="#' . esc_attr( $m[1] ) . '">' . wp_strip_all_tags( $m[2] ) . '</a></li>';
}
?>
<section class="faixa-ed ed-regulacao" style="padding:40px 0"><div class="wrap"><h1 class="h1" style="margin:0"><?php the_title(); ?></h1>
	<p class="meta" style="margin:8px 0 0">Atualizado em <?php echo esc_html( get_the_modified_date( 'j \d\e F \d\e Y' ) ); ?></p></div></section>
<div class="wrap doc legal">
	<nav aria-label="Nesta página"><ul><?php echo $indice; // phpcs:ignore ?>
		<?php if ( (int) get_option( 'wp_page_for_privacy_policy' ) === get_the_ID() ) : ?><li style="margin-top:16px"><button class="btn btn-marinho" type="button" data-preferencias-cookies>Preferências de cookies</button></li><?php endif; ?>
	</ul></nav>
	<div class="texto"><?php echo $conteudo; // phpcs:ignore ?></div>
</div>
<?php get_footer();
