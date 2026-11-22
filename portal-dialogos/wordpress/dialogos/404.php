<?php
/**
 * Página não encontrada.
 */
get_header();
echo dt_ad_topo(); // phpcs:ignore
?>
<div class="wrap">
<section class="e404"><p class="num" aria-hidden="true">4<span>0</span>4</p>
<div><h1 class="h1">Não encontramos esta página</h1><p>O endereço pode ter mudado ou o conteúdo foi retirado do ar. Busque pelo assunto ou siga por uma editoria.</p>
<form class="campo" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label class="sr" for="q404">Buscar no portal</label><input id="q404" name="s" type="search" placeholder="Ex.: ZPE, reforma tributária, frete"><button class="btn btn-marinho" type="submit">Buscar</button></form>
<div class="caminhos"><?php foreach ( array( 'portos', 'infra', 'agro', 'regulacao', 'tributos' ) as $s ) echo dt_selo( $s, false, true ); // phpcs:ignore ?></div></div></section>
<?php $ml = function_exists( 'dp_mais_lidas' ) ? dp_mais_lidas( 3 ) : get_posts( array( 'numberposts' => 3 ) ); if ( $ml ) : ?>
<section style="border-top:3px solid var(--marinho);padding:28px 0 48px"><h2 class="h2" style="margin-bottom:20px">Mais lidas agora</h2>
<div class="grade-3"><?php foreach ( $ml as $i => $p ) echo dt_card( $p, '404-' . ( $i + 1 ), 'h4' ); // phpcs:ignore ?></div></section>
<?php endif; ?>
</div>
<?php get_footer();
