<?php echo dt_ad_rodape_hard(); // phpcs:ignore ?>
</main>
<footer class="rodape"><div class="wrap">
	<div class="topo">
		<div>
			<img src="<?php echo esc_url( DT_URI . '/assets/logo-branco.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="230" height="52" loading="lazy">
			<p class="lema">O ponto de encontro de quem faz o Brasil acontecer.</p>
		</div>
		<div><h4>Editorias</h4><ul>
			<?php foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) : if ( in_array( $c->slug, array( 'uncategorized', 'sem-categoria' ), true ) ) continue; ?>
				<li><a href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a></li>
			<?php endforeach; ?>
		</ul></div>
		<div><h4>Formatos</h4><ul>
			<li><a href="<?php echo esc_url( home_url( '/opiniao/' ) ); ?>">Opinião</a></li>
			<li><a href="<?php echo esc_url( home_url( '/entenda/' ) ); ?>">Entenda</a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'episodio' ) ?: home_url( '/podcast/' ) ); ?>">Podcast</a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'edicao' ) ?: home_url( '/revista/' ) ); ?>">Revista</a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'evento' ) ?: home_url( '/agenda/' ) ); ?>">Agenda</a></li>
			<li><a href="<?php echo esc_url( dt_url_newsletter() ); ?>">Newsletter</a></li>
		</ul></div>
		<div><h4>O portal</h4><ul>
			<li><a href="<?php echo esc_url( home_url( '/quem-somos/' ) ); ?>">Quem somos</a></li>
			<li><a href="<?php echo esc_url( home_url( '/quem-somos/#expediente' ) ); ?>">Expediente</a></li>
			<li><a href="<?php echo esc_url( home_url( '/quem-somos/#anuncie' ) ); ?>">Anuncie</a></li>
			<li><a href="<?php echo esc_url( home_url( '/quem-somos/#contato' ) ); ?>">Contato</a></li>
			<li><a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacidade-e-termos/' ) ); ?>">Privacidade e termos</a></li>
			<li><a href="#" class="cmplz-show-banner" data-preferencias-cookies>Preferências de cookies</a></li>
		</ul></div>
	</div>
	<div class="base"><span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <b>Diálogos Estruturantes</b>. Uma publicação da M&amp;M Lexis Editora.</span>
	<span>www.dialogosestruturantes.com.br | @dialogos.estruturantes</span></div>
</div></footer>
<?php echo dt_ad_ancora() . dt_barra_niveis(); // phpcs:ignore ?>
<?php wp_footer(); ?>
</body>
</html>
