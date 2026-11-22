<?php
/**
 * Agenda completa: institucional no topo, depois destaques, depois os demais por mês.
 */
get_header();
$ev = dt_eventos();
$inst = array_values( array_filter( $ev, fn( $e ) => $e['prioridade'] === 'institucional' ) );
$dest = array_values( array_filter( $ev, fn( $e ) => $e['prioridade'] === 'destaque' ) );
$normais = array_values( array_filter( $ev, fn( $e ) => $e['prioridade'] === 'normal' ) );
usort( $normais, fn( $a, $b ) => strcmp( $a['ini'], $b['ini'] ) );
$meses = array();
foreach ( $ev as $e ) if ( $e['mes_num'] ) $meses[ substr( $e['ini'], 0, 6 ) ] = $e['mes_nome'];
ksort( $meses );

$linha = function ( $e, $pos ) {
	$tags = '';
	foreach ( $e['setores'] as $s ) {
		$info = dt_ed_info( $s );
		$tags .= '<span class="ed-' . esc_attr( $info[2] ) . '">' . esc_html( $info[0] ) . '</span>';
	}
	$selo = $e['prioridade'] === 'destaque' ? '<span class="ev-tag-d">Destaque</span>' : ( $e['andamento'] ? '<span class="ev-tag-a">Acontecendo</span>' : '' );
	return '<article class="linha-ev ev-' . esc_attr( $e['prioridade'] ) . '" data-setores="' . esc_attr( implode( ' ', $e['setores'] ) ) . '" data-mes="' . esc_attr( substr( $e['ini'], 0, 6 ) ) . '" data-componente="evento" data-posicao="' . esc_attr( $pos ) . '" data-conteudo-id="' . esc_attr( $e['id'] ) . '" data-prioridade="' . esc_attr( $e['prioridade'] ) . '">'
		. '<div class="d"><b>' . esc_html( $e['dia'] ) . '</b><span>' . esc_html( $e['mes'] ) . '</span></div>'
		. '<div>' . $selo . '<h3>' . esc_html( $e['nome'] ) . '</h3><p><b style="color:var(--marinho)">' . esc_html( $e['data'] ) . '</b>' . ( $e['local'] ? ' | ' . esc_html( $e['local'] ) : '' ) . '</p>'
		. ( $e['info'] ? '<p>' . esc_html( $e['info'] ) . '</p>' : '' ) . '<div class="setor-tags">' . $tags . '</div></div>'
		. '<div class="acoes-ev">' . ( $e['link'] ? '<a href="' . esc_url( $e['link'] ) . '" target="_blank" rel="noopener" data-componente="evento-site" data-posicao="' . esc_attr( $e['id'] ) . '">Site do evento</a>' : '' ) . '</div></article>';
};
echo dt_ad_topo(); // phpcs:ignore
?>
<section class="cab-agenda"><div class="wrap">
	<p class="migalhas"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Início</a> / Agenda</p>
	<h1 class="titulo-secao">Agenda</h1>
	<p class="desc">Congressos, feiras e audiências que importam para infraestrutura, portos, agro e tributos.</p>
	<div class="filtros-ag" role="group" aria-label="Filtrar eventos">
		<button type="button" data-filtro-setor="todos" aria-pressed="true">Todos</button>
		<?php foreach ( array( 'portos', 'infra', 'agro', 'regulacao', 'tributos' ) as $s ) : ?><button type="button" data-filtro-setor="<?php echo esc_attr( $s ); ?>" aria-pressed="false"><?php echo esc_html( dt_ed_info( $s )[0] ); ?></button><?php endforeach; ?>
		<label class="sr" for="f-mes">Mês</label><select id="f-mes"><option value="todos">Todos os meses</option><?php foreach ( $meses as $k => $n ) : ?><option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $n ); ?></option><?php endforeach; ?></select>
	</div>
</div></section>
<div class="wrap com-lateral bloco">
	<div>
		<?php foreach ( $inst as $e ) echo dt_ev_institucional( $e, 'agenda-institucional' ); // phpcs:ignore ?>
		<?php if ( $dest ) { echo '<h2 class="mes">Em destaque</h2>'; foreach ( $dest as $i => $e ) echo $linha( $e, 'destaque-' . ( $i + 1 ) ); } // phpcs:ignore ?>
		<?php
		$atual = '';
		foreach ( $normais as $i => $e ) {
			$m = substr( $e['ini'], 0, 6 );
			if ( $m !== $atual ) { $atual = $m; echo '<h2 class="mes">' . esc_html( $e['mes_nome'] ) . '</h2>'; }
			echo $linha( $e, $m . '-' . ( $i + 1 ) ); // phpcs:ignore
		}
		if ( ! $ev ) echo '<p class="meta" style="padding:24px 0">Nenhum evento agendado no momento.</p>';
		?>
		<p class="vazio">Nenhum evento para esse filtro. Escolha outro setor ou mês.</p>
		<div class="nv-int" style="padding:22px 0"><?php echo dt_ad( 728, 90, 'entre-lista' ); // phpcs:ignore ?></div>
	</div>
	<aside class="lateral"><?php echo dt_ad_lat(); // phpcs:ignore ?>
		<section class="caixa envie"><h3>Envie seu evento</h3><p>Organiza um evento do setor? Mande os dados para a redação avaliar a inclusão na agenda.</p><a class="btn btn-marinho" href="<?php echo esc_url( home_url( '/quem-somos/#contato' ) ); ?>" data-componente="agenda-envie" data-posicao="lateral">Enviar evento</a></section>
		<div class="gruda"><?php echo dt_form_minuta( 'lateral-agenda', true ) . dt_ad_half( 'half-page', 'int' ); // phpcs:ignore ?></div></aside>
</div>
<?php
echo dt_ad_faixa( 'rodape', 'eq' ); // phpcs:ignore
get_footer();
