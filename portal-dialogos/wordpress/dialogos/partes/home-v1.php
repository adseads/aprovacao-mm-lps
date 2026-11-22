<?php
/**
 * Home v1: setores em faixas.
 */
require_once __DIR__ . '/blocos-home.php';

$secundarias = $buscar( array( 'numberposts' => 3 ) );
echo dt_ad_topo(); // phpcs:ignore
?>
<div class="wrap">
<section class="manchete <?php echo $manchete ? esc_attr( dt_ed_classe( dt_ed( $manchete ) ) ) : ''; ?>" aria-label="Manchete">
	<?php echo dt_bloco_manchete( $manchete ); // phpcs:ignore ?>
	<div class="secundarias"><?php foreach ( $secundarias as $i => $p ) echo dt_card( $p, 'secundaria-' . ( $i + 1 ), 'h4' ); // phpcs:ignore ?></div>
</section>
</div>
<?php
$ordem = array_keys( $editorias );
$i_ed = 0;
foreach ( $editorias as $slug => $termo ) :
	$posts = $buscar( array( 'numberposts' => $i_ed === 1 ? 4 : 3, 'cat' => $termo->term_id ) );
	$i_ed++;
	if ( ! $posts ) continue;
	$info = dt_ed_info( $slug );
	$link = get_category_link( $termo );
	if ( $i_ed === 2 ) : // segunda editoria: destaque grande + lista
		?>
		<section class="bloco <?php echo esc_attr( dt_ed_classe( $slug ) ); ?>" aria-labelledby="h-<?php echo esc_attr( $slug ); ?>"><div class="wrap">
			<div class="cab-bloco"><h2 id="h-<?php echo esc_attr( $slug ); ?>" class="sr"><?php echo esc_html( $info[0] ); ?></h2><?php echo dt_selo( $slug ); // phpcs:ignore ?><a class="ver-tudo" href="<?php echo esc_url( $link ); ?>" data-componente="ver-tudo" data-posicao="<?php echo esc_attr( $slug ); ?>">Ver tudo</a></div>
			<div class="dupla"><?php echo dt_card( $posts[0], $slug . '-1', 'h2', true ); // phpcs:ignore ?><div><?php foreach ( array_slice( $posts, 1 ) as $k => $p ) echo dt_linha( $p, $slug . '-' . ( $k + 2 ) ); // phpcs:ignore ?></div></div>
		</div></section>
		<?php
		// Depois da segunda editoria: podcast e opinião
		if ( $episodio ) :
			?>
			<section class="podcast-faixa" aria-labelledby="h-pod"><div class="wrap">
				<?php echo str_replace( '<div class="video"', '<a href="' . esc_url( get_permalink( $episodio ) ) . '" data-componente="podcast-destaque" data-posicao="home" class="video"', str_replace( '</div>', '</a>', dt_video( '', $episodio, (string) dt_campo( 'dp_duracao', $episodio->ID ) ) ) ); // phpcs:ignore ?>
				<div class="txt">
					<span class="seta-rotulo"><span class="s">→</span><span class="r">Podcast | <?php echo esc_html( dt_ep_numero( $episodio ) ); ?></span></span>
					<h2 id="h-pod" class="h2"><a class="t" href="<?php echo esc_url( get_permalink( $episodio ) ); ?>" data-componente="podcast-destaque" data-posicao="titulo"><?php echo esc_html( get_the_title( $episodio ) ); ?></a></h2>
					<p class="linha-fina"><?php echo esc_html( dt_linha_fina( $episodio ) ); ?></p>
					<?php echo dt_convidado( $episodio, 'Convidado | ' ); // phpcs:ignore ?>
					<a class="btn btn-laranja" href="<?php echo esc_url( $url_podcast ); ?>" data-componente="podcast-cta" data-posicao="home">Todos os episódios</a>
				</div>
			</div></section>
		<?php endif; ?>
		<section class="bloco" aria-labelledby="h-op"><div class="wrap com-lateral">
			<div><div class="cab-bloco"><h2 id="h-op" class="titulo-secao">Opinião</h2><a class="ver-tudo" href="<?php echo esc_url( $url_opiniao ); ?>" data-componente="ver-tudo" data-posicao="opiniao">Todos os artigos</a></div>
			<div class="opinioes"><?php echo dt_opinioes( 6, 'v1' ); // phpcs:ignore ?></div></div>
			<aside class="lateral" aria-label="Destaques e publicidade"><?php echo dt_ad_lat( 'lateral-1' ) . dt_mais_lidas( function_exists( 'dp_mais_lidas' ) ? dp_mais_lidas( 3 ) : null ); // phpcs:ignore ?></aside>
		</div></section>
		<?php
	else :
		?>
		<section class="faixa-ed <?php echo esc_attr( dt_ed_classe( $slug ) ); ?> bloco" aria-labelledby="h-<?php echo esc_attr( $slug ); ?>"><div class="wrap">
			<div class="cab-bloco"><h2 id="h-<?php echo esc_attr( $slug ); ?>" class="sr"><?php echo esc_html( $info[0] ); ?></h2><?php echo dt_selo( $slug ); // phpcs:ignore ?><span class="rodape-secao"><?php echo esc_html( $info[0] ); ?> |</span><a class="ver-tudo" href="<?php echo esc_url( $link ); ?>" data-componente="ver-tudo" data-posicao="<?php echo esc_attr( $slug ); ?>">Ver tudo</a></div>
			<div class="grade-3"><?php foreach ( $posts as $k => $p ) echo dt_card( $p, $slug . '-' . ( $k + 1 ), 'h3', true ); // phpcs:ignore ?></div>
		</div></section>
		<?php
		if ( $i_ed === 1 ) echo dt_ad_faixa( 'entre-blocos-1', 'int' ); // phpcs:ignore
	endif;
endforeach;
?>
<section class="bloco" id="agenda" aria-labelledby="h-ag"><div class="wrap revista-agenda">
	<?php echo dt_revista_box( $edicao ); // phpcs:ignore ?>
	<div>
		<div class="cab-bloco"><h2 id="h-ag" class="titulo-secao">Agenda</h2><a class="btn btn-marinho" href="<?php echo esc_url( $url_agenda ); ?>" data-componente="agenda-cta" data-posicao="home">Ver agenda completa</a></div>
		<?php echo dt_agenda_resumo( 'home' ); // phpcs:ignore ?>
	</div>
</div></section>
<?php
echo dt_ad_faixa( 'meio', 'int' ) . dt_faixa_minuta( 'home' ) . dt_ad_faixa( 'rodape', 'eq' ); // phpcs:ignore
