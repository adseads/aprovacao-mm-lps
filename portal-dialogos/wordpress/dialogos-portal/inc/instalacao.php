<?php
/**
 * Botão "Criar estrutura inicial" (Configurações › Portal).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dp_editorias_padrao() {
	return array(
		'portos'     => 'Portos',
		'infra'      => 'Infra',
		'agro'       => 'Agro',
		'regulacao'  => 'Regulação',
		'tributos'   => 'Tributos',
		'no-direito' => 'No Direito',
	);
}

add_action( 'admin_post_dp_instalar', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'dp_instalar' ) ) {
		wp_die( 'Sem permissão.' );
	}
	dp_instalar_estrutura();
	wp_safe_redirect( admin_url( 'options-general.php?page=dialogos-portal&instalado=1' ) );
	exit;
} );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['instalado'] ) && isset( $_GET['page'] ) && $_GET['page'] === 'dialogos-portal' ) {
		echo '<div class="notice notice-success"><p>Estrutura criada: editorias, páginas fixas, menu e links permanentes.</p></div>';
	}
} );

function dp_instalar_estrutura() {
	// Editorias
	foreach ( dp_editorias_padrao() as $slug => $nome ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $nome, 'category', array( 'slug' => $slug ) );
		}
	}
	// A categoria padrão "Sem categoria" passa a ser Infra
	$infra = get_term_by( 'slug', 'infra', 'category' );
	if ( $infra ) {
		update_option( 'default_category', $infra->term_id );
	}

	// Páginas fixas
	$legal = file_get_contents( DP_DIR . 'inc/legal.html' );
	$paginas = array(
		'newsletter'            => array( 'Newsletter', 'page-newsletter.php', '' ),
		'inscricao-confirmada'  => array( 'Inscrição confirmada', 'page-newsletter-ok.php', '' ),
		'quem-somos'            => array( 'Quem somos', '', dp_texto_quem_somos() ),
		'privacidade-e-termos'  => array( 'Privacidade e termos', '', $legal ),
	);
	foreach ( $paginas as $slug => $p ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $p[0], 'post_content' => $p[2] ) );
		if ( $id && $p[1] ) {
			update_post_meta( $id, '_wp_page_template', $p[1] );
		}
	}
	$priv = get_page_by_path( 'privacidade-e-termos' );
	if ( $priv ) {
		update_option( 'wp_page_for_privacy_policy', $priv->ID );
	}

	// Conteúdo de exemplo do WordPress vai para a lixeira
	foreach ( array( 'ola-mundo', 'hello-world' ) as $s ) {
		$p = get_page_by_path( $s, OBJECT, 'post' );
		if ( $p ) wp_trash_post( $p->ID );
	}
	foreach ( array( 'pagina-exemplo', 'sample-page' ) as $s ) {
		$p = get_page_by_path( $s );
		if ( $p ) wp_trash_post( $p->ID );
	}

	// Menu principal
	$nome_menu = 'Principal';
	$menu = wp_get_nav_menu_object( $nome_menu );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $nome_menu );
		foreach ( array_keys( dp_editorias_padrao() ) as $slug ) {
			$t = get_term_by( 'slug', $slug, 'category' );
			if ( $t ) {
				wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-type' => 'taxonomy', 'menu-item-object' => 'category', 'menu-item-object-id' => $t->term_id, 'menu-item-status' => 'publish' ) );
			}
		}
		foreach ( array( 'Opinião' => '/opiniao/', 'Podcast' => '/podcast/', 'Revista' => '/revista/', 'Agenda' => '/agenda/' ) as $rot => $url ) {
			wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $rot, 'menu-item-url' => home_url( $url ), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish' ) );
		}
		$locais = get_theme_mod( 'nav_menu_locations', array() );
		$locais['principal'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locais );
	}

	// Links permanentes legíveis, idioma e fuso
	update_option( 'permalink_structure', '/%category%/%postname%/' );
	update_option( 'timezone_string', 'America/Sao_Paulo' );
	update_option( 'date_format', 'j \d\e F \d\e Y' );
	update_option( 'posts_per_page', 12 );
	dp_registrar_conteudos();
	dp_rotas();
	flush_rewrite_rules();
}

function dp_texto_quem_somos() {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"quem-somos\">O ponto de encontro de quem faz o Brasil acontecer</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>A Diálogos Estruturantes cobre as decisões que definem a infraestrutura do país: portos, transportes, energia, agro, regulação e tributos. Começou como revista e chega ao portal com cobertura diária, podcast e newsletter.</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:paragraph -->\n<p>É uma publicação da M&amp;M Lexis Editora, com sede em Brasília.</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"principios\">Princípios editoriais</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>Separamos com clareza notícia, opinião e conteúdo de parceiro. Artigos assinados são de responsabilidade dos autores. Conteúdo pago sempre traz a marcação “Conteúdo de parceiro” e o nome de quem pagou. Erros são corrigidos no próprio texto, com nota de correção ao final.</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"expediente\">Expediente</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>Editora: M&amp;M Lexis Editora. Reportagem: Luiz Roberto Marinho. Demais nomes a definir.</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"anuncie\">Anuncie</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>Leve sua marca a gestores, empresários e especialistas da infraestrutura: display no portal, conteúdo de parceiro, revista, podcast e newsletter. Peça o mídia kit pelo e-mail abaixo.</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\" id=\"contato\">Contato</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:paragraph -->\n<p>Redação, comercial e assinaturas: <a href=\"mailto:contato@dialogosestruturantes.com.br\">contato@dialogosestruturantes.com.br</a></p>\n<!-- /wp:paragraph -->";
}
