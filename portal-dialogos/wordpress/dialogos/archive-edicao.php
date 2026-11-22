<?php
/**
 * Revista: edição atual e anteriores.
 */
get_header();
echo dt_ad_topo(); // phpcs:ignore
$edicoes = array();
while ( have_posts() ) { the_post(); $edicoes[] = get_post(); }
$atual = $edicoes ? array_shift( $edicoes ) : null;
?>
<?php if ( $atual ) : $num = dt_campo( 'dp_numero', $atual->ID ); $pdf = dt_campo( 'dp_pdf', $atual->ID ); ?>
<section class="rev-hero"><div class="wrap">
	<a href="<?php echo esc_url( get_permalink( $atual ) ); ?>" data-componente="edicao-atual" data-posicao="capa"><?php echo get_the_post_thumbnail( $atual, 'large', array( 'fetchpriority' => 'high' ) ); // phpcs:ignore ?></a>
	<div><span class="chapeu laranja">Revista<?php echo $num ? ' | ' . esc_html( $num ) . 'ª edição' : ''; ?><?php echo dt_campo( 'dp_mes', $atual->ID ) ? ' | ' . esc_html( dt_campo( 'dp_mes', $atual->ID ) ) : ''; ?></span>
		<h1 class="h-manchete"><?php echo esc_html( get_the_title( $atual ) ); ?></h1>
		<?php $f = dt_linha_fina( $atual ); if ( $f ) : ?><p class="fina"><?php echo esc_html( $f ); ?></p><?php endif; ?>
		<div class="acoes"><a class="btn btn-laranja" href="<?php echo esc_url( get_permalink( $atual ) ); ?>" data-componente="edicao-atual" data-posicao="ler">Ler a edição</a>
		<?php if ( $pdf ) : ?><a class="btn btn-linha" href="<?php echo esc_url( $pdf ); ?>" data-componente="edicao-atual" data-posicao="pdf">Baixar PDF</a><?php endif; ?></div></div>
</div></section>
<?php endif; ?>
<div class="wrap com-lateral bloco"><div><h2 class="titulo-secao" style="font-size:30px;margin-bottom:28px">Edições anteriores</h2>
	<div class="edicoes" style="grid-template-columns:repeat(2,1fr)">
	<?php foreach ( $edicoes as $e ) :
		$n = dt_campo( 'dp_numero', $e->ID ); ?>
		<a class="capa" href="<?php echo esc_url( get_permalink( $e ) ); ?>" data-componente="edicao" data-posicao="<?php echo esc_attr( (string) $n ); ?>"><div class="c"><?php echo has_post_thumbnail( $e ) ? get_the_post_thumbnail( $e, 'medium_large' ) : '<img class="vazia" src="' . esc_url( DT_URI . '/assets/logo-branco.png' ) . '" alt="">'; // phpcs:ignore ?></div>
		<b><?php echo esc_html( ( $n ? $n . 'ª edição' : '' ) . ( dt_campo( 'dp_mes', $e->ID ) ? ' | ' . dt_campo( 'dp_mes', $e->ID ) : '' ) ); ?></b><span><?php echo esc_html( get_the_title( $e ) ); ?></span></a>
	<?php endforeach; ?>
	</div>
	<?php if ( ! $edicoes ) echo '<p class="meta">As edições anteriores aparecem aqui.</p>'; echo dt_paginacao(); // phpcs:ignore ?>
	</div>
	<aside class="lateral"><?php echo dt_ad_lat(); // phpcs:ignore ?><div class="caixa"><h3>Receba a revista</h3><p style="font-size:14px;margin:0 0 12px">A edição impressa é distribuída a gestores e entidades do setor. Para entrar na lista, fale com a redação.</p><a class="btn btn-marinho" href="<?php echo esc_url( home_url( '/quem-somos/#contato' ) ); ?>">Falar com a redação</a></div><div class="gruda"><?php echo dt_ad_half( 'half-page', 'int' ); // phpcs:ignore ?></div></aside>
</div>
<?php
echo dt_ad_faixa( 'rodape', 'int' ); // phpcs:ignore
get_footer();
