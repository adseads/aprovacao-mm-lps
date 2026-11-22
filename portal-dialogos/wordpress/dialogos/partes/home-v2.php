<?php
/**
 * Home v2: todos os setores juntos logo após a manchete, com escolha do setor.
 */
require_once __DIR__ . '/blocos-home.php';

$ultimas = $buscar( array( 'numberposts' => 4 ) );
echo dt_ad_topo(); // phpcs:ignore
?>
<div class="wrap">
<section class="manchete2 <?php echo $manchete ? esc_attr( dt_ed_classe( dt_ed( $manchete ) ) ) : ''; ?>" aria-label="Manchete">
	<?php echo dt_bloco_manchete( $manchete ); // phpcs:ignore ?>
	<div class="ultimas"><h2>Últimas</h2>
		<?php foreach ( $ultimas as $i => $p ) :
			$ed = dt_ed( $p );
			$quando = get_the_date( 'Ymd', $p ) === wp_date( 'Ymd' ) ? get_the_time( 'H\hi', $p ) : ( get_the_date( 'Ymd', $p ) === wp_date( 'Ymd', strtotime( '-1 day' ) ) ? 'ontem' : get_the_date( 'd/m', $p ) );
			printf( '<a href="%s" data-componente="ultimas" data-posicao="%d" data-conteudo-id="%s"><span class="chapeu %s">%s</span><span class="h4 t"><span class="hora">%s</span>%s</span></a>',
				esc_url( get_permalink( $p ) ), $i + 1, esc_attr( $p->post_name ), esc_attr( dt_ed_classe( $ed ) ), esc_html( dt_ed_info( $ed )[0] ), esc_html( $quando ), esc_html( get_the_title( $p ) ) );
		endforeach; ?>
	</div>
</section>
</div>

<section class="setores" aria-labelledby="h-set"><div class="wrap">
	<div class="setores-cab"><div><h2 id="h-set">Notícias por setor</h2><p>Escolha o seu setor para ele aparecer primeiro.</p></div>
		<div class="chips" role="group" aria-label="Escolher setor">
			<?php foreach ( $editorias as $slug => $t ) {
				$info = dt_ed_info( $slug );
				printf( '<button type="button" class="chip ed-%s" data-setor="%s" aria-pressed="false">%s%s</button>', esc_attr( $info[2] ), esc_attr( $slug ), dt_icone( $info[1] ), esc_html( $info[0] ) ); // phpcs:ignore
			} ?>
		</div>
	</div>
	<div class="grade-setores">
		<?php foreach ( $editorias as $slug => $t ) :
			// Os setores podem repetir as "Últimas", mas não a manchete
			$posts = get_posts( array( 'numberposts' => 3, 'cat' => $t->term_id, 'post__not_in' => $manchete ? array( $manchete->ID ) : array(), 'ignore_sticky_posts' => true ) );
			$info = dt_ed_info( $slug );
			?>
			<section class="painel ed-<?php echo esc_attr( $info[2] ); ?>" data-setor="<?php echo esc_attr( $slug ); ?>" aria-labelledby="st-<?php echo esc_attr( $slug ); ?>">
				<div class="topo-p"><h3 class="nome" id="st-<?php echo esc_attr( $slug ); ?>"><?php echo dt_icone( $info[1] ) . esc_html( $info[0] ); // phpcs:ignore ?></h3><span class="seu">Seu setor</span><a class="mais" href="<?php echo esc_url( get_category_link( $t ) ); ?>" data-componente="setor-ver-tudo" data-posicao="<?php echo esc_attr( $slug ); ?>">Ver tudo</a></div>
				<?php if ( $posts ) :
					$p = $posts[0];
					printf( '<a class="lead" href="%s" data-componente="setor-destaque" data-posicao="%s-1" data-conteudo-id="%s">%s<span><span class="chapeu">%s</span><span class="h4 t">%s</span></span></a>',
						esc_url( get_permalink( $p ) ), esc_attr( $slug ), esc_attr( $p->post_name ), dt_img( $p, 'dt-quadrada' ), esc_html( dt_rotulo( $p ) ), esc_html( get_the_title( $p ) ) ); // phpcs:ignore
					echo '<ul>';
					foreach ( array_slice( $posts, 1 ) as $k => $o ) {
						printf( '<li><a class="t" href="%s" data-componente="setor-lista" data-posicao="%s-%d" data-conteudo-id="%s">%s</a></li>', esc_url( get_permalink( $o ) ), esc_attr( $slug ), $k + 2, esc_attr( $o->post_name ), esc_html( get_the_title( $o ) ) );
					}
					echo '</ul>';
				else : ?>
					<p class="meta" style="padding:14px 16px">Ainda sem matérias nesta editoria.</p>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
</div></section>

<?php echo dt_ad_faixa( 'entre-blocos-1', 'int' ); // phpcs:ignore ?>

<div class="wrap trio">
	<?php if ( $episodio ) : ?>
	<article class="pod-mini ed-podcast">
		<?php echo str_replace( array( '<div class="video"', '</div>' ), array( '<a href="' . esc_url( get_permalink( $episodio ) ) . '" data-componente="podcast-destaque" data-posicao="home" class="video"', '</a>' ), dt_video( '', $episodio, (string) dt_campo( 'dp_duracao', $episodio->ID ) ) ); // phpcs:ignore ?>
		<div class="in"><span class="seta-rotulo"><span class="s">→</span><span class="r">Podcast | <?php echo esc_html( dt_ep_numero( $episodio ) ); ?></span></span>
			<h2 class="h3"><a class="t" href="<?php echo esc_url( get_permalink( $episodio ) ); ?>" data-componente="podcast-destaque" data-posicao="titulo"><?php echo esc_html( get_the_title( $episodio ) ); ?></a></h2>
			<?php $conv = dt_campo( 'dp_convidado', $episodio->ID ); if ( $conv ) : ?><p>Com <?php echo esc_html( $conv . ( dt_campo( 'dp_convidado_cargo', $episodio->ID ) ? ', ' . dt_campo( 'dp_convidado_cargo', $episodio->ID ) : '' ) ); ?></p><?php endif; ?>
			<a class="btn btn-laranja" href="<?php echo esc_url( $url_podcast ); ?>" data-componente="podcast-cta" data-posicao="home">Todos os episódios</a></div>
	</article>
	<?php else : ?><div></div><?php endif; ?>
	<div class="op-lista"><h2>Opinião</h2><?php echo dt_opinioes( 4, 'v2' ); // phpcs:ignore ?><p style="margin:14px 0 0"><a class="ver-tudo" href="<?php echo esc_url( $url_opiniao ); ?>" data-componente="ver-tudo" data-posicao="opiniao">Todos os artigos</a></p></div>
	<aside class="lateral"><?php echo dt_ad_lat( 'lateral-1' ) . dt_mais_lidas( function_exists( 'dp_mais_lidas' ) ? dp_mais_lidas( 3 ) : null ); // phpcs:ignore ?></aside>
</div>

<?php echo dt_ad_faixa( 'meio', 'int' ); // phpcs:ignore ?>

<section class="agenda-faixa" id="agenda" aria-labelledby="h-ag2"><div class="wrap">
	<div class="cab-bloco"><h2 id="h-ag2" class="titulo-secao" style="font-size:26px">Agenda</h2><a class="btn btn-marinho" href="<?php echo esc_url( $url_agenda ); ?>" data-componente="agenda-cta" data-posicao="home-v2">Ver agenda completa</a></div>
	<?php echo dt_agenda_resumo( 'home-v2' ); // phpcs:ignore ?>
</div></section>
<?php
echo dt_faixa_minuta( 'home-v2' ) . dt_ad_faixa( 'rodape', 'eq' ); // phpcs:ignore
