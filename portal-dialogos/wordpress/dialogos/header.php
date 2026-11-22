<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-tipo-pagina="<?php echo esc_attr( dt_grupo_pagina() ); ?>">
<?php wp_body_open(); ?>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>
<div class="faixa-topo"><div class="wrap">
	<span><?php echo esc_html( ucfirst( wp_date( 'l, j \d\e F \d\e Y' ) ) ); ?></span>
	<span class="dir">
		<?php $ed = dt_edicao_atual(); if ( $ed ) : ?>
			<a href="<?php echo esc_url( get_permalink( $ed ) ); ?>" data-componente="topo" data-posicao="revista">Revista: <?php echo esc_html( dt_campo( 'dp_numero', $ed->ID ) ? dt_campo( 'dp_numero', $ed->ID ) . 'ª edição' : get_the_title( $ed ) ); ?></a>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/quem-somos/#anuncie' ) ); ?>" data-componente="topo" data-posicao="anuncie">Anuncie</a>
		<a href="https://www.instagram.com/dialogos.estruturantes/" target="_blank" rel="noopener" data-componente="topo" data-posicao="instagram">@dialogos.estruturantes</a>
	</span>
</div></div>
<header class="cab"><div class="wrap">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-componente="logo" data-posicao="cabecalho"><img src="<?php echo esc_url( DT_URI . '/assets/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="260" height="59"></a>
	<div class="acoes">
		<a class="btn-icone" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="Buscar no portal" data-componente="busca" data-posicao="cabecalho"><?php echo dt_icone( 'busca' ); // phpcs:ignore ?></a>
		<a class="btn btn-laranja" href="<?php echo esc_url( dt_url_newsletter() ); ?>" data-componente="minuta-cta" data-posicao="cabecalho">Receber a newsletter</a>
		<button class="btn-icone menu-btn" type="button" aria-label="Abrir menu" aria-expanded="false" data-menu-toggle><?php echo dt_icone( 'menu' ); // phpcs:ignore ?></button>
	</div>
</div></header>
<nav class="nav" aria-label="Editorias"><div class="wrap">
	<?php
	if ( has_nav_menu( 'principal' ) ) {
		wp_nav_menu( array( 'theme_location' => 'principal', 'container' => false, 'depth' => 1, 'walker' => new DT_Menu() ) );
	} else {
		echo '<ul>';
		foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) {
			if ( in_array( $c->slug, array( 'uncategorized', 'sem-categoria' ), true ) ) continue;
			printf( '<li class="%s"><a href="%s" data-componente="menu" data-posicao="%s">%s</a></li>', esc_attr( dt_ed_classe( $c->slug ) ), esc_url( get_category_link( $c ) ), esc_attr( $c->slug ), esc_html( $c->name ) );
		}
		echo '</ul>';
	}
	?>
</div></nav>
<main id="conteudo">
