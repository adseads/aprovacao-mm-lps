<?php
/**
 * Tema acompanhado (ex.: Pacto Brasil pela Infraestrutura). A descrição do tema vira o texto do topo.
 */
get_header();
$termo = get_queried_object();
echo dt_ad_topo(); // phpcs:ignore
?>
<section class="cab-tema"><div class="wrap">
	<div><p class="migalhas"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Início</a> / Tema</p>
	<span class="chapeu">Tema acompanhado</span><h1 class="h1"><?php echo esc_html( $termo->name ); ?></h1>
	<?php if ( $termo->description ) : ?><p><?php echo esc_html( $termo->description ); ?></p><?php endif; ?>
	<a class="btn btn-laranja" href="<?php echo esc_url( dt_url_newsletter() ); ?>" data-componente="seguir-tema" data-posicao="cabecalho">Receber novidades na newsletter</a></div>
	<div class="numeros-tema"><div><b><?php echo (int) $termo->count; ?></b><span>publicações sobre o tema</span></div></div>
</div></section>
<div class="wrap com-lateral bloco" style="padding-top:44px">
	<div><h2 class="h2" style="border-top:3px solid var(--marinho);padding-top:20px">Cobertura do tema</h2><?php $sem_destaque = true; include locate_template( 'partes/lista.php' ); ?></div>
	<aside class="lateral"><?php echo dt_ad_lat(); // phpcs:ignore ?><div class="gruda"><?php echo dt_form_minuta( 'lateral-tema', true ) . dt_ad_half( 'half-page', 'int' ); // phpcs:ignore ?></div></aside>
</div>
<?php
echo dt_ad_faixa( 'rodape', 'eq' ); // phpcs:ignore
get_footer();
