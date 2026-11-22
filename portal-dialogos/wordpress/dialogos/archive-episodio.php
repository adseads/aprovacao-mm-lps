<?php
/**
 * Podcast: lista de episódios.
 */
get_header();
require_once get_template_directory() . '/partes/blocos-home.php';
echo dt_ad_topo(); // phpcs:ignore
$ultimo = get_posts( array( 'post_type' => 'episodio', 'numberposts' => 1 ) );
?>
<section class="pod-hero"><div class="wrap">
	<div><span class="selo ed-podcast"><?php echo dt_icone( 'podcast' ); // phpcs:ignore ?>Podcast</span><h1>Diálogos Estruturantes</h1>
	<p>Conversas com quem decide e executa a infraestrutura do país: empresários, gestores públicos e especialistas.</p></div>
	<?php if ( $ultimo ) echo str_replace( array( '<div class="video"', '</div>' ), array( '<a href="' . esc_url( get_permalink( $ultimo[0] ) ) . '" data-componente="episodio-destaque" data-posicao="hero" class="video"', '</a>' ), dt_video( '', $ultimo[0] ) ); // phpcs:ignore ?>
</div></section>
<div class="wrap com-lateral bloco">
	<div><h2 class="titulo-secao" style="font-size:30px">Episódios</h2>
	<?php
	$i = 0;
	while ( have_posts() ) :
		the_post();
		$i++;
		$ep = get_post();
		?>
		<article class="ep ed-podcast">
			<?php echo str_replace( array( '<div class="video"', '</div>' ), array( '<a href="' . esc_url( get_permalink() ) . '" data-componente="episodio" data-posicao="' . $i . '" class="video"', '</a>' ), dt_video( '', $ep, (string) dt_campo( 'dp_duracao' ) ) ); // phpcs:ignore ?>
			<div><span class="seta-rotulo"><span class="s">→</span><span class="r"><?php echo esc_html( dt_ep_numero( $ep ) ); ?></span></span>
				<h2 class="h2" style="margin-top:12px"><a class="t" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<p class="resumo" style="font-size:16px"><?php echo esc_html( dt_linha_fina( $ep ) ); ?></p>
				<div class="pessoas"><?php echo dt_convidado( $ep ); // phpcs:ignore ?></div></div>
		</article>
		<?php
		if ( $i === 2 ) echo '<div class="nv-int" style="padding:22px 0">' . dt_ad( 728, 90, 'entre-episodios' ) . '</div>'; // phpcs:ignore
	endwhile;
	if ( ! $i ) echo '<p class="meta" style="padding:24px 0">Os episódios publicados aparecem aqui.</p>';
	echo dt_paginacao(); // phpcs:ignore
	?>
	</div>
	<aside class="lateral"><?php echo dt_ad_lat() . dt_form_minuta( 'lateral-podcast', true ); // phpcs:ignore ?></aside>
</div>
<?php
echo dt_ad_faixa( 'rodape', 'int' ); // phpcs:ignore
get_footer();
