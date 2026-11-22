<?php
/**
 * Matérias: o campo "Modelo da página" escolhe entre matéria, matéria com vídeo,
 * Entenda (explicador), opinião e conteúdo de parceiro.
 */
get_header();
the_post();

$post_id = get_the_ID();
$modelo  = dt_modelo();
$ed      = dt_ed();
$ed_cls  = dt_ed_classe( $ed );
$autor   = (int) get_the_author_meta( 'ID' );
$nome    = get_the_author();
$bio     = get_the_author_meta( 'description' ) ?: dt_cargo_autor( $autor );
$chapeu  = dt_campo( 'dp_chapeu' );
$fina    = dt_linha_fina();
$palavras = str_word_count( wp_strip_all_tags( get_the_content() ) );
$leitura = max( 1, (int) round( $palavras / 200 ) );

$conteudo = apply_filters( 'the_content', get_the_content() );
$conteudo = preg_replace( '~<p(?: class="([^"]*)")?>(?=\s*[A-Za-zÀ-ú“"])~u', '<p class="$1 abre">', $conteudo, 1 );

// Entenda: numera os intertítulos e monta o índice
$indice = '';
if ( $modelo === 'explicador' ) {
	$n = 0;
	$conteudo = preg_replace_callback( '~<h2([^>]*)>(.*?)</h2>~s', function ( $m ) use ( &$n, &$indice ) {
		$n++;
		$id = 'p' . $n;
		$indice .= '<li><a href="#' . $id . '" data-componente="indice-explicador" data-posicao="' . $n . '">' . wp_strip_all_tags( $m[2] ) . '</a></li>';
		return '<h2 id="' . $id . '" class="pergunta-t"><span>' . $n . '</span>' . $m[2] . '</h2>';
	}, $conteudo );
}
if ( $modelo !== 'parceiro' ) {
	$conteudo = dt_conteudo_com_anuncios( $conteudo );
}

$meta = function () use ( $leitura, $modelo ) {
	$atualizado = get_the_modified_time( 'U' ) - get_the_time( 'U' ) > 600 ? ' | Atualizado em ' . get_the_modified_date( 'd/m' ) . ' às ' . get_the_modified_time( 'H\hi' ) : '';
	$extra = $modelo === 'materia_video' && dt_campo( 'dp_duracao' ) ? ' | Vídeo de ' . dt_campo( 'dp_duracao' ) : ' | Leitura de ' . $leitura . ' min';
	return '<span class="meta">Publicado em <b>' . esc_html( get_the_date( 'd M Y' ) ) . '</b>' . esc_html( $atualizado . $extra ) . '</span>' . dt_compartilhar();
};

$etiquetas = '';
$tags = get_the_tags();
$etq = '';
foreach ( (array) $tags as $t ) {
	if ( $t ) $etq .= '<li><a href="' . esc_url( get_tag_link( $t ) ) . '" data-componente="tema" data-posicao="fim">' . esc_html( $t->name ) . '</a></li>';
}
foreach ( get_the_category() as $c ) {
	$etq .= '<li><a href="' . esc_url( get_category_link( $c ) ) . '" data-componente="editoria" data-posicao="fim">' . esc_html( $c->name ) . '</a></li>';
}
if ( $etq ) $etiquetas = '<div class="fim-artigo"><ul class="etiquetas ' . esc_attr( $ed_cls ) . '">' . $etq . '</ul></div>';

$caixa_autor = '<div class="autor-box">' . dt_retrato( $autor, $ed ) . '<div><b>' . esc_html( $nome ) . '</b><p>' . esc_html( $bio ) . '</p><a href="' . esc_url( get_author_posts_url( $autor ) ) . '" data-componente="autor" data-posicao="fim">Ver todos os textos</a></div></div>';

$lateral = '<aside class="lateral" aria-label="Mais conteúdo e publicidade">'
	. ( $indice ? '<nav class="indice caixa" aria-labelledby="ix-h"><h3 id="ix-h">Nesta página</h3><ol>' . $indice . '</ol></nav>' : '' )
	. dt_ad_lat() . ( $indice ? '' : dt_mais_lidas() )
	. '<div class="gruda">' . dt_form_minuta( 'lateral-materia', true ) . dt_ad_half() . '</div></aside>';

/* ===================== conteúdo de parceiro (sem anúncios) ===================== */
if ( $modelo === 'parceiro' ) :
	$parc = dt_campo( 'dp_patrocinador' ) ?: 'Parceiro';
	$logo = dt_campo( 'dp_patrocinador_logo' );
	$link = dt_campo( 'dp_patrocinador_link' );
	?>
	<div class="parc-faixa"><div class="wrap"><strong>Conteúdo de parceiro</strong><div class="parc-logo">Oferecido por <?php
		$marca = $logo ? '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $parc ) . '" style="max-height:44px;width:auto">' : '<b>' . esc_html( $parc ) . '</b>';
		echo $link ? '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener sponsored" data-componente="parceiro-logo" data-posicao="topo">' . $marca . '</a>' : $marca; // phpcs:ignore
	?></div></div></div>
	<article class="<?php echo esc_attr( $ed_cls ); ?>">
		<div class="wrap">
			<?php if ( has_post_thumbnail() ) : ?><figure class="hero"><?php echo dt_img( $post_id, 'dt-capa', '', true ); // phpcs:ignore ?><figcaption><?php echo esc_html( (string) dt_campo( 'dp_legenda' ) ); ?></figcaption></figure><?php endif; ?>
			<header class="art-cab"><span class="chapeu laranja">Conteúdo de parceiro</span><h1 class="h1"><?php the_title(); ?></h1><?php if ( $fina ) : ?><p class="fina"><?php echo esc_html( $fina ); ?></p><?php endif; ?><span class="assina"><?php echo esc_html( $parc ); ?></span></header>
			<div class="art-meta"><?php echo $meta(); // phpcs:ignore ?></div>
		</div>
		<div class="wrap" style="display:flex;justify-content:center"><div class="texto">
			<p class="transparencia">Este conteúdo foi produzido em parceria comercial e não tem participação da redação da Diálogos Estruturantes.</p>
			<?php echo $conteudo; // phpcs:ignore ?>
		</div></div>
	</article>
	<?php
	get_footer();
	return;
endif;

echo dt_ad_topo(); // phpcs:ignore

/* ===================== opinião ===================== */
if ( $modelo === 'opiniao' ) :
	$outros = get_posts( array( 'numberposts' => 4, 'meta_key' => 'dp_modelo', 'meta_value' => 'opiniao', 'post__not_in' => array( $post_id ) ) );
	$lista_outros = '';
	foreach ( $outros as $i => $o ) {
		$lista_outros .= sprintf( '<a class="%s" href="%s" data-componente="outros-artigos" data-posicao="%d">%s<span><span class="h4 t">%s</span><small>%s</small></span></a>',
			esc_attr( dt_ed_classe( dt_ed( $o ) ) ), esc_url( get_permalink( $o ) ), $i + 1, dt_retrato( $o->post_author, dt_ed( $o ) ), esc_html( get_the_title( $o ) ), esc_html( get_the_author_meta( 'display_name', $o->post_author ) ) );
	}
	?>
	<article class="<?php echo esc_attr( $ed_cls ); ?>"><div class="wrap">
		<div class="op-cab">
			<div class="autor-op"><?php echo dt_retrato( $autor, $ed ); // phpcs:ignore ?><div><b><?php echo esc_html( $nome ); ?></b><p><?php echo esc_html( $bio ); ?></p></div></div>
			<div><p style="margin:0 0 18px"><?php echo dt_selo( $ed ); // phpcs:ignore ?></p><span class="chapeu laranja">Opinião</span><h1 class="h1"><?php the_title(); ?></h1><?php if ( $fina ) : ?><p class="fina" style="margin:0"><?php echo esc_html( $fina ); ?></p><?php endif; ?><span class="por">Por <?php echo esc_html( $nome ); ?></span></div>
		</div>
		<div class="art-meta" style="margin-top:0;border-top:0"><?php echo $meta(); // phpcs:ignore ?></div>
	</div>
	<div class="wrap com-lateral"><div><div class="texto"><?php echo $conteudo; // phpcs:ignore ?></div>
		<p class="aviso-op">Os artigos assinados são de responsabilidade dos autores e não refletem necessariamente a opinião da Diálogos Estruturantes.</p><?php echo $etiquetas; // phpcs:ignore ?></div>
		<aside class="lateral"><?php echo dt_ad_lat(); // phpcs:ignore ?><?php if ( $lista_outros ) : ?><section class="outros-op"><h3>Outros artigos</h3><?php echo $lista_outros; // phpcs:ignore ?></section><?php endif; ?><div class="gruda"><?php echo dt_ad_half(); // phpcs:ignore ?></div></aside>
	</div>
	</article>
	<div class="wrap"><?php echo dt_relacionadas( $post_id ); // phpcs:ignore ?></div>
	<?php
	echo dt_ad_faixa( 'rodape', 'int' ); // phpcs:ignore
	get_footer();
	return;
endif;

/* ===================== matéria, matéria com vídeo e Entenda ===================== */
$rotulo = $modelo === 'explicador' ? 'Entenda' : ( $modelo === 'materia_video' ? ( $chapeu ?: 'Vídeo' ) : $chapeu );
?>
<article class="<?php echo esc_attr( $ed_cls ); ?>">
<div class="wrap">
	<?php if ( $modelo === 'materia_video' && dt_campo( 'dp_video' ) ) : ?>
		<div class="video-topo"><?php echo dt_video( dt_campo( 'dp_video' ), $post_id, (string) dt_campo( 'dp_duracao' ), 'Assistir ao vídeo: ' . get_the_title() ); // phpcs:ignore ?></div>
		<header class="art-cab"><p style="margin:0 0 18px"><?php echo dt_selo( $ed, false, true ); // phpcs:ignore ?></p>
	<?php elseif ( $modelo !== 'explicador' && has_post_thumbnail() ) : ?>
		<p class="selo-topo"><?php echo dt_selo( $ed, false, true ); // phpcs:ignore ?></p>
		<figure class="hero"><?php echo dt_img( $post_id, 'dt-capa', '', true ); // phpcs:ignore ?><figcaption><?php echo esc_html( (string) dt_campo( 'dp_legenda' ) ); ?></figcaption></figure>
		<header class="art-cab">
	<?php else : ?>
		<header class="art-cab"><p style="margin:0 0 18px"><?php echo dt_selo( $ed, false, true ); // phpcs:ignore ?></p>
	<?php endif; ?>
		<?php if ( $rotulo ) : ?><span class="chapeu laranja"><?php echo esc_html( $rotulo ); ?></span><?php endif; ?>
		<h1 class="h1"><?php the_title(); ?></h1>
		<?php if ( $fina ) : ?><p class="fina"><?php echo esc_html( $fina ); ?></p><?php endif; ?>
		<a class="assina" href="<?php echo esc_url( get_author_posts_url( $autor ) ); ?>"><?php echo esc_html( $nome ); ?></a>
	</header>
	<div class="art-meta"><?php echo $meta(); // phpcs:ignore ?></div>
</div>
<div class="wrap com-lateral"><div><div class="texto">
	<?php
	if ( $modelo === 'explicador' && dt_campo( 'dp_em_resumo' ) ) {
		$itens = array_filter( array_map( 'trim', preg_split( '~\R~', (string) dt_campo( 'dp_em_resumo' ) ) ) );
		echo '<div class="em-resumo"><h2>Em resumo</h2><ul><li>' . implode( '</li><li>', array_map( 'esc_html', $itens ) ) . '</li></ul></div>'; // phpcs:ignore
	}
	echo $conteudo; // phpcs:ignore
	?>
</div><?php echo $etiquetas . $caixa_autor; // phpcs:ignore ?></div>
<?php echo $lateral; // phpcs:ignore ?>
</div>
</article>
<div class="wrap"><?php echo dt_relacionadas( $post_id ); // phpcs:ignore ?></div>
<?php
echo dt_ad_faixa( 'rodape', 'int' ); // phpcs:ignore
get_footer();
