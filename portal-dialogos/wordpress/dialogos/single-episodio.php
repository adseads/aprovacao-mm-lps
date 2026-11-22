<?php
/**
 * Episódio do podcast.
 */
get_header();
require_once get_template_directory() . '/partes/blocos-home.php';
the_post();
$ep = get_post();
$ed = dt_ed( $ep );
$conteudo = apply_filters( 'the_content', get_the_content() );
$conteudo = preg_replace( '~<p(?: class="([^"]*)")?>(?=\s*[A-Za-zÀ-ú“"])~u', '<p class="$1 abre">', $conteudo, 1 );
$conteudo = dt_conteudo_com_anuncios( $conteudo );
$spotify = dt_campo( 'dp_spotify' );
$apple = dt_campo( 'dp_apple' );
echo dt_ad_topo(); // phpcs:ignore
?>
<article class="ed-podcast"><div class="wrap">
<div class="ep-cab">
	<?php echo dt_video( dt_campo( 'dp_video' ), $ep, (string) dt_campo( 'dp_duracao' ), 'Assistir ao episódio' ); // phpcs:ignore ?>
	<div class="pessoas">
		<?php $c = dt_convidado( $ep ); if ( $c ) : ?><div><p class="papel">Convidado</p><?php echo $c; // phpcs:ignore ?></div><?php endif; ?>
		<?php if ( $spotify || $apple ) : ?><div class="plataformas" style="display:flex;gap:8px;flex-wrap:wrap">
			<?php if ( $spotify ) : ?><a class="btn btn-linha" href="<?php echo esc_url( $spotify ); ?>" target="_blank" rel="noopener" data-componente="plataforma" data-posicao="spotify">Ouvir no Spotify</a><?php endif; ?>
			<?php if ( $apple ) : ?><a class="btn btn-linha" href="<?php echo esc_url( $apple ); ?>" target="_blank" rel="noopener" data-componente="plataforma" data-posicao="apple">Apple Podcasts</a><?php endif; ?>
		</div><?php endif; ?>
	</div>
</div>
<header class="art-cab" style="text-align:left;padding-top:24px"><p style="margin:0 0 14px"><?php echo dt_selo( $ed !== 'uncategorized' && $ed !== 'sem-categoria' ? $ed : 'podcast', false, true ); // phpcs:ignore ?></p><span class="seta-rotulo"><span class="s">→</span><span class="r"><?php echo esc_html( dt_ep_numero( $ep ) ); ?></span></span>
	<h1 class="h1" style="margin:14px 0"><?php the_title(); ?></h1><?php $f = dt_linha_fina( $ep ); if ( $f ) : ?><p class="fina" style="margin-inline:0"><?php echo esc_html( $f ); ?></p><?php endif; ?></header>
<div class="art-meta"><span class="meta">Publicado em <b><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></b><?php echo dt_campo( 'dp_duracao' ) ? ' | ' . esc_html( dt_campo( 'dp_duracao' ) ) : ''; ?></span><?php echo dt_compartilhar(); // phpcs:ignore ?></div></div>
<div class="wrap com-lateral"><div class="texto"><?php echo $conteudo; // phpcs:ignore ?></div>
<aside class="lateral"><?php echo dt_ad_lat() . dt_mais_lidas(); // phpcs:ignore ?><div class="gruda"><?php echo dt_form_minuta( 'lateral-episodio', true ) . dt_ad_half(); // phpcs:ignore ?></div></aside></div>
</article>
<?php
$outros = get_posts( array( 'post_type' => 'episodio', 'numberposts' => 3, 'post__not_in' => array( $ep->ID ) ) );
if ( $outros ) {
	echo '<div class="wrap"><section class="relacionadas"><h2 class="h2" style="margin-bottom:20px">Outros episódios</h2><div class="grade-3">';
	foreach ( $outros as $i => $o ) echo dt_card( $o, 'outro-episodio-' . ( $i + 1 ), 'h4' ); // phpcs:ignore
	echo '</div></section></div>';
}
echo dt_ad_faixa( 'rodape', 'int' ); // phpcs:ignore
get_footer();
