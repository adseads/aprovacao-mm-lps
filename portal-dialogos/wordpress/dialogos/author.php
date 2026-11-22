<?php
/**
 * Página do autor.
 */
get_header();
$autor = get_queried_object();
$ed = 'portos';
$primeiro = get_posts( array( 'author' => $autor->ID, 'numberposts' => 1 ) );
if ( $primeiro ) $ed = dt_ed( $primeiro[0] );
echo dt_ad_topo(); // phpcs:ignore
?>
<section class="cab-autor <?php echo esc_attr( dt_ed_classe( $ed ) ); ?>"><div class="wrap">
	<?php echo dt_retrato( $autor->ID, $ed ); // phpcs:ignore ?>
	<div><?php $cargo = dt_cargo_autor( $autor->ID ); if ( $cargo ) : ?><span class="chapeu laranja"><?php echo esc_html( $cargo ); ?></span><?php endif; ?>
		<h1 class="h1" style="margin:0"><?php echo esc_html( $autor->display_name ); ?></h1>
		<?php if ( $autor->description ) : ?><p><?php echo esc_html( $autor->description ); ?></p><?php endif; ?></div>
	<div class="redes"></div>
</div></section>
<div class="wrap com-lateral bloco">
	<div><?php $sem_destaque = true; include locate_template( 'partes/lista.php' ); ?></div>
	<aside class="lateral"><?php echo dt_ad_lat() . dt_form_minuta( 'lateral-autor', true ); // phpcs:ignore ?></aside>
</div>
<?php
echo dt_ad_faixa( 'rodape', 'int' ); // phpcs:ignore
get_footer();
