<?php
/**
 * Edição da revista: capa, editorial e sumário (o texto da edição no editor).
 */
get_header();
the_post();
$num = dt_campo( 'dp_numero' );
$pdf = dt_campo( 'dp_pdf' );
$folhear = dt_campo( 'dp_folhear' );
echo dt_ad_topo(); // phpcs:ignore
?>
<div class="wrap">
<section class="ed-cab"><?php the_post_thumbnail( 'large', array( 'fetchpriority' => 'high' ) ); ?>
<div><p class="migalhas"><a href="<?php echo esc_url( get_post_type_archive_link( 'edicao' ) ); ?>">Revista</a> / <?php echo esc_html( $num ? $num . 'ª edição' : get_the_title() ); ?></p>
	<?php if ( dt_campo( 'dp_mes' ) ) : ?><span class="chapeu laranja"><?php echo esc_html( dt_campo( 'dp_mes' ) ); ?></span><?php endif; ?>
	<h1 class="h1"><?php echo esc_html( $num ? $num . 'ª edição' : get_the_title() ); ?></h1>
	<?php if ( $num ) : ?><p class="fina"><?php the_title(); ?></p><?php endif; ?>
	<div style="display:flex;gap:10px;flex-wrap:wrap">
		<?php if ( $folhear ) : ?><a class="btn btn-laranja" href="<?php echo esc_url( $folhear ); ?>" target="_blank" rel="noopener" data-componente="edicao" data-posicao="folhear">Folhear a revista</a><?php endif; ?>
		<?php if ( $pdf ) : ?><a class="btn btn-linha" href="<?php echo esc_url( $pdf ); ?>" data-componente="edicao" data-posicao="pdf">Baixar PDF</a><?php endif; ?>
	</div></div></section>
</div>
<div class="wrap com-lateral bloco"><div class="editorial texto"><?php the_content(); ?></div>
<aside class="lateral"><?php echo dt_ad_lat(); // phpcs:ignore ?><div class="gruda"><?php echo dt_form_minuta( 'lateral-edicao', true ) . dt_ad_half( 'half-page', 'int' ); // phpcs:ignore ?></div></aside></div>
<?php get_footer();
