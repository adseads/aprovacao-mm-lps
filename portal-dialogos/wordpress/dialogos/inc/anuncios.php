<?php
/**
 * Espaços de anúncio. Cada espaço tem um nível mínimo:
 * todos (leve em diante), eq (equilibrada em diante), int (intensiva em diante), hard.
 * O CSS mostra ou esconde conforme a classe ads-* do <body>.
 * Os anúncios extras do Hard (a cada 2 parágrafos, listas e lateral) entram pelo assets/portal.js.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dt_ad( $w, $h, $pos, $cls = '', $nivel = 'todos' ) {
	if ( dt_nivel() === 'nenhum' ) {
		return '';
	}
	$nv = array( 'todos' => '', 'eq' => ' nv-eq', 'int' => ' nv-int', 'hard' => ' nv-hard' )[ $nivel ];
	$caixa = $w >= 728
		? sprintf( '<div class="ad-box ad-wide" style="--ar:%1$d/%2$d;--mw:%1$dpx"><span class="d">%1$d × %2$d</span><span class="m">320 × 100</span></div>', $w, $h )
		: sprintf( '<div class="ad-box" style="aspect-ratio:%1$d/%2$d;max-width:%1$dpx">%1$d × %2$d</div>', $w, $h );
	return sprintf( '<div class="ad %s%s" data-componente="anuncio" data-posicao="%s" data-tamanho="%dx%d"><span class="ad-label">Publicidade</span>%s</div>', esc_attr( $cls ), $nv, esc_attr( $pos ), $w, $h, $caixa );
}

function dt_ad_topo() {
	$t = dt_ad( 970, 90, 'topo' );
	return $t ? '<div class="ad-topo">' . $t . '</div><div class="ad-faixa wrap nv-hard">' . dt_ad( 970, 250, 'billboard' ) . '</div>' : '';
}
function dt_ad_faixa( $pos, $nivel ) {
	$a = dt_ad( 970, 90, $pos );
	return $a ? '<div class="ad-faixa wrap nv-' . ( $nivel === 'eq' ? 'eq' : 'int' ) . '">' . $a . '</div>' : '';
}
function dt_ad_lat( $pos = 'lateral' ) { return dt_ad( 300, 250, $pos, '', 'eq' ); }
function dt_ad_half( $pos = 'half-page', $nivel = 'eq' ) { return dt_ad( 300, 600, $pos, '', $nivel ); }
function dt_ad_inline( $pos, $nivel = 'eq' ) { return dt_ad( 300, 250, $pos, 'ad-inline', $nivel ); }
function dt_ad_mobile( $pos = 'mobile' ) { return dt_ad( 320, 100, $pos, 'ad-inline only-mobile', 'eq' ); }

function dt_ad_rodape_hard() {
	$a = dt_ad( 970, 250, 'rodape-2' );
	return $a ? '<div class="ad-faixa wrap nv-hard">' . $a . '</div>' : '';
}
function dt_ad_ancora() {
	if ( dt_nivel() === 'nenhum' ) return '';
	return '<div class="ad-ancora nv-hard" data-componente="anuncio" data-posicao="ancora">'
		. '<button type="button" class="fecha-ancora" aria-label="Fechar anúncio">×</button>'
		. '<span class="ad-label">Publicidade</span><div class="ad-box"><span class="d">728 × 90</span><span class="m">320 × 50</span></div></div>';
}

/** Barra de prévia dos níveis: só para quem edita o portal. */
function dt_barra_niveis() {
	if ( ! current_user_can( 'edit_posts' ) || dt_nivel() === 'nenhum' ) return '';
	$b = '';
	foreach ( array( 'leve' => 'Leve', 'equilibrada' => 'Equilibrada', 'intensiva' => 'Intensiva', 'hard' => 'Hard' ) as $k => $n ) {
		$b .= '<button type="button" data-nivel="' . $k . '">' . $n . '</button>';
	}
	return '<div class="nivel-bar" role="group" aria-label="Prévia do nível de anúncios"><span>Prévia (só editores)</span>' . $b . '</div>';
}

/**
 * Anúncios dentro do texto: equilibrada após o 2º parágrafo; intensiva também após o 6º.
 */
function dt_conteudo_com_anuncios( $html ) {
	if ( dt_nivel() === 'nenhum' ) return $html;
	$partes = preg_split( '~(</p>)~i', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$out = '';
	$n = 0;
	for ( $i = 0; $i < count( $partes ); $i++ ) {
		$out .= $partes[ $i ];
		if ( strtolower( $partes[ $i ] ) === '</p>' ) {
			$n++;
			if ( $n === 2 ) $out .= dt_ad_inline( 'inline-1' ) . dt_ad_mobile();
			if ( $n === 6 ) $out .= dt_ad_inline( 'inline-2', 'int' );
		}
	}
	return $out;
}
