<?php
/**
 * Editoria (Portos, Infra, Agro…).
 */
get_header();
$termo = get_queried_object();
$slug  = $termo->slug;
$cls   = dt_ed_classe( $slug );

// Temas mais usados nesta editoria, para os filtros
$temas = array();
$ids = get_posts( array( 'cat' => $termo->term_id, 'numberposts' => 60, 'fields' => 'ids' ) );
if ( $ids ) {
	$temas = wp_get_object_terms( $ids, 'post_tag', array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 5 ) );
}
echo dt_ad_topo(); // phpcs:ignore
?>
<section class="cab-ed <?php echo esc_attr( $cls ); ?>"><div class="wrap">
	<p class="migalhas"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Início</a> / <?php echo esc_html( $termo->name ); ?></p>
	<h1 style="margin:0 0 22px"><?php echo dt_selo( $slug, true ); // phpcs:ignore ?></h1>
	<?php if ( $temas && ! is_wp_error( $temas ) ) : ?>
		<ul class="etiquetas <?php echo esc_attr( $cls ); ?>"><li><a href="<?php echo esc_url( get_category_link( $termo ) ); ?>" aria-current="true">Tudo</a></li>
			<?php foreach ( $temas as $t ) : ?><li><a href="<?php echo esc_url( get_tag_link( $t ) ); ?>" data-componente="filtro-tema" data-posicao="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $t->name ); ?></a></li><?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div></section>
<div class="wrap com-lateral bloco <?php echo esc_attr( $cls ); ?>">
	<div><?php get_template_part( 'partes/lista' ); ?></div>
	<aside class="lateral"><?php echo dt_ad_lat() . dt_mais_lidas( function_exists( 'dp_mais_lidas' ) ? dp_mais_lidas( 5, $termo->term_id ) : null ); // phpcs:ignore ?><div class="gruda"><?php echo dt_form_minuta( 'lateral-editoria', true ) . dt_ad_half( 'half-page', 'int' ); // phpcs:ignore ?></div></aside>
</div>
<?php
echo dt_ad_faixa( 'rodape', 'eq' ); // phpcs:ignore
get_footer();
