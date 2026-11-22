<?php
/**
 * Busca.
 */
get_header();
global $wp_query;
$termo = get_search_query();
echo dt_ad_topo(); // phpcs:ignore
?>
<section class="cab-busca"><div class="wrap">
	<h1 class="h2">Buscar no portal</h1>
	<form class="campo-busca" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label class="sr" for="q">Termo de busca</label><input id="q" name="s" type="search" value="<?php echo esc_attr( $termo ); ?>" placeholder="Ex.: ZPE, reforma tributária, frete"><button class="btn btn-marinho" type="submit">Buscar</button></form>
</div></section>
<div class="wrap" style="max-width:900px;padding-bottom:48px">
	<?php if ( $termo !== '' ) : ?>
		<p class="total"><b><?php echo (int) $wp_query->found_posts; ?> <?php echo $wp_query->found_posts === 1 ? 'resultado' : 'resultados'; ?></b> para “<?php echo esc_html( $termo ); ?>”</p>
		<?php
		$i = 0;
		while ( have_posts() ) {
			the_post();
			$i++;
			echo dt_item( get_post(), 'resultado-' . $i, 'resultado-busca' ); // phpcs:ignore
		}
		if ( ! $i ) echo '<p>Nada encontrado. Tente outra palavra ou siga por uma editoria no menu.</p>';
		echo dt_paginacao(); // phpcs:ignore
	endif;
	?>
</div>
<?php get_footer();
