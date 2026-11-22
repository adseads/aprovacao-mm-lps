<?php
/**
 * Listas gerais: Opinião (/opiniao/), Entenda (/entenda/) e qualquer outro arquivo.
 */
get_header();
$formato = get_query_var( 'dp_formato' );
$titulos = array( 'opiniao' => array( 'Opinião', 'Artigos assinados por especialistas, gestores e lideranças do setor.' ), 'explicador' => array( 'Entenda', 'Perguntas e respostas para entender as decisões que mudam o setor.' ) );
$titulo = $titulos[ $formato ][0] ?? wp_strip_all_tags( get_the_archive_title() );
$desc = $titulos[ $formato ][1] ?? '';
echo dt_ad_topo(); // phpcs:ignore
?>
<section class="cab-ed ed-regulacao"><div class="wrap">
	<p class="migalhas"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Início</a> / <?php echo esc_html( $titulo ); ?></p>
	<h1 class="titulo-secao"><?php echo esc_html( $titulo ); ?></h1>
	<?php if ( $desc ) : ?><p class="fina" style="margin:16px 0 0"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
</div></section>
<div class="wrap com-lateral bloco">
	<div><?php get_template_part( 'partes/lista' ); ?></div>
	<aside class="lateral"><?php echo dt_ad_lat() . dt_mais_lidas(); // phpcs:ignore ?><div class="gruda"><?php echo dt_form_minuta( 'lateral-lista', true ) . dt_ad_half( 'half-page', 'int' ); // phpcs:ignore ?></div></aside>
</div>
<?php
echo dt_ad_faixa( 'rodape', 'eq' ); // phpcs:ignore
get_footer();
