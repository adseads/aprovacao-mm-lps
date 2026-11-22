<?php
/**
 * Template Name: Newsletter (inscrição)
 */
get_header();
the_post();
$nome = dt_nome_newsletter();
$temas = '';
foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) {
	if ( in_array( $c->slug, array( 'uncategorized', 'sem-categoria' ), true ) ) continue;
	$temas .= '<label><input type="checkbox" name="temas[]" value="' . esc_attr( $c->slug ) . '" checked> ' . esc_html( $c->name ) . '</label>';
}
?>
<section class="min-hero"><div class="wrap">
	<div><span class="chapeu laranja">Newsletter diária e gratuita</span><h1><?php echo esc_html( $nome ); ?></h1>
	<p class="fina">As decisões que movem infraestrutura, portos, agro e tributos, resumidas para ler em cinco minutos.</p>
	<ul class="lista-check"><li>De segunda a sexta, às 7h</li><li>Prazos, consultas públicas e votações que pedem atenção</li><li>Links para as matérias completas e para os documentos oficiais</li></ul></div>
	<form class="minuta form-grande" data-posicao="pagina-newsletter">
		<label class="campo" for="nm">Nome</label><input id="nm" name="nome" type="text" autocomplete="name">
		<label class="campo" for="em">E-mail profissional</label><input id="em" name="email" type="email" required autocomplete="email" placeholder="seu@empresa.com.br">
		<input type="text" name="site" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
		<fieldset><legend>Assuntos que você quer receber</legend><div class="temas"><?php echo $temas; // phpcs:ignore ?></div></fieldset>
		<button class="btn btn-laranja" type="submit">Receber a newsletter</button>
		<label class="consent"><input type="checkbox" required> Concordo em receber a newsletter e com a <a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/' ) ); ?>" style="text-decoration:underline">política de privacidade</a>. Dá para cancelar a qualquer momento.</label>
		<p class="minuta-msg" role="status"></p>
	</form>
</div></section>
<?php if ( trim( get_the_content() ) ) : ?><section class="bloco"><div class="wrap texto" style="margin:auto"><?php the_content(); ?></div></section><?php endif; ?>
<section class="bloco"><div class="wrap faq"><h2 class="h2">Perguntas frequentes</h2>
	<details><summary>Quanto custa?</summary><p>Nada. A newsletter é gratuita.</p></details>
	<details><summary>Posso escolher só alguns assuntos?</summary><p>Sim. Marque os assuntos no cadastro ou altere depois pelo link no rodapé de cada edição.</p></details>
	<details><summary>Como cancelo?</summary><p>Clique em “Cancelar inscrição” no fim de qualquer edição. O cancelamento vale na hora.</p></details>
</div></section>
<?php get_footer();
