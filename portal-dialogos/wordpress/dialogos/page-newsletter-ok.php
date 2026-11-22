<?php
/**
 * Template Name: Newsletter (inscrição confirmada)
 */
get_header();
?>
<div class="wrap"><section class="conf">
	<div class="marca" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg></div>
	<h1 class="h1">Inscrição confirmada</h1>
	<p class="fina">A próxima edição chega no próximo dia útil, às 7h. Enquanto isso:</p>
	<div class="passos"><div><b>Salve o remetente</b>Adicione o e-mail da newsletter aos seus contatos para a edição não cair no spam.</div><div><b>Ajuste os assuntos</b>Escolha só as editorias que importam para você, pelo link no rodapé da edição.</div><div><b>Indique a um colega</b>Envie o link de inscrição para quem acompanha o setor.</div></div>
	<a class="btn btn-marinho" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-componente="minuta-confirmacao" data-posicao="voltar">Ler as notícias de hoje</a>
</section></div>
<script>window.dataLayer=window.dataLayer||[];dataLayer.push({event:'minuta_confirmada'});</script>
<?php get_footer();
