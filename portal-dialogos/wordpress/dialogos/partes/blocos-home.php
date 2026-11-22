<?php
/**
 * Blocos comuns às duas versões da home.
 */

function dt_bloco_manchete( $manchete, $classe = 'manchete' ) {
	if ( ! $manchete ) {
		return '<p class="meta" style="padding:40px 0">Publique a primeira matéria para ela aparecer aqui.</p>';
	}
	$ed = dt_ed( $manchete );
	return sprintf(
		'<a href="%s" data-componente="manchete" data-posicao="1" data-conteudo-id="%s"><div class="foto">%s%s</div><div class="txt"><h1 class="h-manchete t">%s</h1><p class="fina">%s</p><span class="assina">%s</span></div></a>',
		esc_url( get_permalink( $manchete ) ), esc_attr( $manchete->post_name ), dt_img( $manchete, 'dt-capa', '', true ), dt_selo( $ed ),
		esc_html( get_the_title( $manchete ) ), esc_html( dt_linha_fina( $manchete ) ), esc_html( get_the_author_meta( 'display_name', $manchete->post_author ) )
	);
}

function dt_opinioes( $quantidade, $formato = 'v1' ) {
	$q = get_posts( array( 'numberposts' => $quantidade, 'meta_key' => 'dp_modelo', 'meta_value' => 'opiniao' ) );
	$h = '';
	foreach ( $q as $i => $p ) {
		$ed = dt_ed( $p );
		$nome = get_the_author_meta( 'display_name', $p->post_author );
		$h .= sprintf( '<a class="%s" href="%s" data-componente="opiniao" data-posicao="%d" data-conteudo-id="%s">%s<span>%s</span></a>',
			esc_attr( dt_ed_classe( $ed ) ), esc_url( get_permalink( $p ) ), $i + 1, esc_attr( $p->post_name ), dt_retrato( $p->post_author, $ed ),
			$formato === 'v1'
				? '<small>' . esc_html( $nome ) . '</small><span class="h4 t">' . esc_html( get_the_title( $p ) ) . '</span>'
				: '<span class="h4 t">' . esc_html( get_the_title( $p ) ) . '</span><small>' . esc_html( $nome ) . '</small>' );
	}
	return $h ?: '<p class="meta">Os artigos de opinião aparecem aqui.</p>';
}

function dt_ep_numero( $ep ) {
	$n = dt_campo( 'dp_numero', $ep->ID );
	return $n ? 'Episódio ' . str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) : 'Novo episódio';
}

function dt_convidado( $ep, $prefixo = '' ) {
	$nome = (string) dt_campo( 'dp_convidado', $ep->ID );
	if ( ! $nome ) return '';
	$foto = dt_campo( 'dp_convidado_foto', $ep->ID );
	$foto = is_array( $foto ) ? ( $foto['ID'] ?? 0 ) : (int) $foto;
	return '<div class="pessoa ed-podcast">' . dt_avatar_img( $foto, $nome ) . '<span><b>' . esc_html( $nome ) . '</b><small>' . esc_html( $prefixo . dt_campo( 'dp_convidado_cargo', $ep->ID ) ) . '</small></span></div>';
}

function dt_revista_box( $edicao ) {
	if ( ! $edicao ) return '';
	$num = dt_campo( 'dp_numero', $edicao->ID );
	return '<div class="revista-box ed-podcast"><a href="' . esc_url( get_permalink( $edicao ) ) . '" data-componente="revista" data-posicao="home">'
		. get_the_post_thumbnail( $edicao, 'medium_large', array( 'loading' => 'lazy' ) ) . '</a>'
		. '<span class="chapeu laranja">Revista | ' . esc_html( (string) dt_campo( 'dp_mes', $edicao->ID ) ) . '</span>'
		. '<h3 class="h3">' . esc_html( ( $num ? $num . 'ª edição: ' : '' ) . ( dt_campo( 'dp_linha_fina', $edicao->ID ) ?: get_the_title( $edicao ) ) ) . '</h3>'
		. '<a class="btn btn-marinho" href="' . esc_url( get_permalink( $edicao ) ) . '" data-componente="revista-cta" data-posicao="home" style="margin-top:10px">Ler a edição</a></div>';
}
