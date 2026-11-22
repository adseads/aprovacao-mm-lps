<?php
/**
 * Lista de matérias com destaque, anúncio no meio e paginação.
 * Usado por editoria, tema, autor, Opinião e Entenda.
 */
$lista_html = '';
$i = 0;
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$i++;
		if ( $i === 1 && ! is_paged() && empty( $sem_destaque ) ) {
			$p = get_post();
			$lista_html .= sprintf( '<a class="lead-ed" href="%s" data-componente="destaque-lista" data-posicao="1" data-conteudo-id="%s">%s<span><span class="chapeu">%s</span><span class="h2 t" style="display:block">%s</span><p class="resumo">%s</p><span class="assina" style="margin-top:12px">%s</span></span></a>',
				esc_url( get_permalink() ), esc_attr( $p->post_name ), dt_img( $p, 'dt-capa', '', true ), esc_html( dt_rotulo( $p ) ), esc_html( get_the_title() ), esc_html( dt_linha_fina( $p ) ), esc_html( get_the_author() ) );
			continue;
		}
		$lista_html .= dt_item( get_post(), 'lista-' . $i );
		if ( $i === 4 ) {
			$lista_html .= '<div class="nv-int" style="padding:22px 0;border-bottom:1px solid var(--linha)">' . dt_ad( 728, 90, 'entre-lista' ) . '</div>';
		}
	}
	$lista_html .= dt_paginacao();
} else {
	$lista_html = '<p class="meta" style="padding:24px 0">Ainda não há publicações aqui.</p>';
}
echo $lista_html; // phpcs:ignore
