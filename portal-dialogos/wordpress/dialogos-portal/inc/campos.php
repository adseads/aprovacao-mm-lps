<?php
/**
 * Campos do painel (Advanced Custom Fields, versão gratuita).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_notices', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p><strong>Portal Diálogos:</strong> instale e ative o plugin gratuito <em>Advanced Custom Fields</em> (Plugins › Adicionar novo). Sem ele, os campos do painel não aparecem.</p></div>';
	}
} );

function dp_f( $nome, $rotulo, $tipo = 'text', $extra = array() ) {
	return array_merge( array(
		'key'   => 'field_' . $nome,
		'name'  => $nome,
		'label' => $rotulo,
		'type'  => $tipo,
	), $extra );
}

function dp_niveis( $com_padrao = true ) {
	$n = array();
	if ( $com_padrao ) {
		$n['padrao'] = 'Padrão do portal';
	}
	return $n + array( 'leve' => 'Leve', 'equilibrada' => 'Equilibrada', 'intensiva' => 'Intensiva', 'hard' => 'Hard' );
}

add_action( 'acf/init', function () {
	$se = function ( $valores ) {
		$r = array();
		foreach ( (array) $valores as $v ) {
			$r[] = array( array( 'field' => 'field_dp_modelo', 'operator' => '==', 'value' => $v ) );
		}
		return $r;
	};

	// Matérias: modelo e anúncios na barra lateral do editor; o resto abaixo do texto
	acf_add_local_field_group( array(
		'key'      => 'group_dp_post_lado',
		'title'    => 'Modelo e anúncios',
		'position' => 'side',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
		'fields'   => array(
			dp_f( 'dp_modelo', 'Modelo da página', 'select', array(
				'choices'       => array( 'materia' => 'Matéria', 'materia_video' => 'Matéria com vídeo', 'explicador' => 'Entenda (explicador)', 'opiniao' => 'Opinião', 'parceiro' => 'Conteúdo de parceiro' ),
				'default_value' => 'materia',
			) ),
			dp_f( 'dp_nivel', 'Nível de anúncios', 'select', array(
				'choices' => dp_niveis(), 'default_value' => 'padrao',
				'instructions' => 'Intensiva: matérias em alta. Hard: só em picos, por poucos dias. Conteúdo de parceiro nunca tem anúncios.',
			) ),
			dp_f( 'dp_chapeu', 'Chapéu', 'text', array( 'instructions' => 'Palavra curta acima do título.' ) ),
		),
	) );
	acf_add_local_field_group( array(
		'key'      => 'group_dp_post',
		'title'    => 'Detalhes da publicação',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
		'fields'   => array(
			dp_f( 'dp_linha_fina', 'Linha fina', 'textarea', array( 'rows' => 2, 'maxlength' => 220, 'instructions' => 'Até 2 linhas, logo abaixo do título.' ) ),
			dp_f( 'dp_legenda', 'Legenda e crédito da foto de capa' ),
			dp_f( 'dp_video', 'Link do vídeo no YouTube', 'url', array( 'instructions' => 'Só para o modelo "Matéria com vídeo".' ) ),
			dp_f( 'dp_em_resumo', 'Em resumo', 'textarea', array( 'rows' => 4, 'instructions' => 'Só para o modelo "Entenda". Um item por linha.' ) ),
			dp_f( 'dp_patrocinador', 'Nome do parceiro', 'text', array( 'instructions' => 'Só para "Conteúdo de parceiro".', 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_patrocinador_link', 'Site do parceiro', 'url', array( 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_patrocinador_logo', 'Logo do parceiro', 'image', array( 'return_format' => 'url', 'preview_size' => 'medium' ) ),
		),
	) );

	// Autores
	acf_add_local_field_group( array(
		'key'      => 'group_dp_autor',
		'title'    => 'Perfil no portal',
		'location' => array( array( array( 'param' => 'user_form', 'operator' => '==', 'value' => 'all' ) ) ),
		'fields'   => array(
			dp_f( 'dp_foto', 'Foto do autor', 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Vertical (3:4), no mínimo 900 × 1200 px, fundo neutro.' ) ),
			dp_f( 'dp_cargo', 'Cargo ou função', 'text', array( 'instructions' => 'Ex.: Repórter, Diretora de Operações da Superservice.' ) ),
		),
	) );

	// Episódios
	acf_add_local_field_group( array(
		'key'      => 'group_dp_episodio',
		'title'    => 'Episódio',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'episodio' ) ) ),
		'fields'   => array(
			dp_f( 'dp_numero', 'Número do episódio', 'number', array( 'wrapper' => array( 'width' => 25 ) ) ),
			dp_f( 'dp_duracao', 'Duração', 'text', array( 'placeholder' => '42 min', 'wrapper' => array( 'width' => 25 ) ) ),
			dp_f( 'dp_nivel', 'Nível de anúncios', 'select', array( 'choices' => dp_niveis(), 'default_value' => 'padrao', 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_linha_fina', 'Linha fina', 'textarea', array( 'rows' => 2 ) ),
			dp_f( 'dp_video', 'Link do vídeo no YouTube', 'url' ),
			dp_f( 'dp_convidado', 'Convidado', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_convidado_cargo', 'Cargo do convidado', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_convidado_foto', 'Foto do convidado', 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail' ) ),
			dp_f( 'dp_spotify', 'Link no Spotify', 'url', array( 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_apple', 'Link no Apple Podcasts', 'url', array( 'wrapper' => array( 'width' => 50 ) ) ),
		),
	) );

	// Edições da revista
	acf_add_local_field_group( array(
		'key'      => 'group_dp_edicao',
		'title'    => 'Edição da revista',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'edicao' ) ) ),
		'fields'   => array(
			dp_f( 'dp_numero', 'Número da edição', 'number', array( 'wrapper' => array( 'width' => 33 ) ) ),
			dp_f( 'dp_mes', 'Mês e ano', 'text', array( 'placeholder' => 'Novembro 2026', 'wrapper' => array( 'width' => 33 ) ) ),
			dp_f( 'dp_pdf', 'PDF da edição', 'file', array( 'return_format' => 'url', 'wrapper' => array( 'width' => 34 ) ) ),
			dp_f( 'dp_folhear', 'Link para folhear (opcional)', 'url' ),
			dp_f( 'dp_linha_fina', 'Chamada da capa', 'textarea', array( 'rows' => 2 ) ),
		),
	) );

	// Eventos
	acf_add_local_field_group( array(
		'key'      => 'group_dp_evento',
		'title'    => 'Evento',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'evento' ) ) ),
		'fields'   => array(
			dp_f( 'dp_prioridade', 'Prioridade', 'select', array(
				'choices' => array( 'normal' => 'Normal', 'destaque' => 'Destaque', 'institucional' => 'Institucional (evento da Diálogos)' ),
				'default_value' => 'normal', 'instructions' => 'Institucional fica sempre no topo. Destaque vem logo depois, com etiqueta.',
			) ),
			dp_f( 'dp_inicio', 'Data de início', 'date_picker', array( 'display_format' => 'd/m/Y', 'return_format' => 'Ymd', 'required' => 1, 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_fim', 'Data de fim', 'date_picker', array( 'display_format' => 'd/m/Y', 'return_format' => 'Ymd', 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_local', 'Local', 'text', array( 'placeholder' => 'B Hotel, Brasília (DF)' ) ),
			dp_f( 'dp_info', 'Organização ou setores', 'text', array( 'placeholder' => 'Organização: Sobratema' ) ),
			dp_f( 'dp_chamada', 'Chamada (só para eventos institucionais)', 'textarea', array( 'rows' => 2 ) ),
			dp_f( 'dp_link', 'Link do evento', 'url' ),
		),
	) );

	// Popups
	acf_add_local_field_group( array(
		'key'      => 'group_dp_popup',
		'title'    => 'Popup',
		'position' => 'acf_after_title',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'popup' ) ) ),
		'fields'   => array(
			dp_f( 'dp_ativo', 'Ativo', 'true_false', array( 'ui' => 1, 'default_value' => 1, 'wrapper' => array( 'width' => 20 ) ) ),
			dp_f( 'dp_pp_modelo', 'Modelo', 'select', array( 'choices' => array( 'modal' => 'Modal central', 'canto' => 'Aviso no canto', 'faixa' => 'Faixa no topo ou rodapé' ), 'wrapper' => array( 'width' => 40 ) ) ),
			dp_f( 'dp_cor', 'Cor', 'select', array( 'choices' => array( 'marinho' => 'Marinho', 'laranja' => 'Laranja' ), 'wrapper' => array( 'width' => 20 ) ) ),
			dp_f( 'dp_prioridade', 'Prioridade', 'number', array( 'default_value' => 1, 'wrapper' => array( 'width' => 20 ), 'instructions' => 'Maior aparece primeiro.' ) ),
			dp_f( 'dp_chapeu', 'Chapéu', 'text', array( 'placeholder' => 'Evento, Podcast, Ao vivo…' ) ),
			dp_f( 'dp_texto', 'Texto', 'textarea', array( 'rows' => 3, 'maxlength' => 160, 'instructions' => 'O título é o título do popup, no topo desta tela (até 70 caracteres).' ) ),
			dp_f( 'dp_imagem', 'Imagem', 'image', array( 'return_format' => 'url', 'preview_size' => 'medium', 'instructions' => 'Modal: 600 × 800 px. Canto: quadrada, 300 × 300 px. A faixa não usa imagem.' ) ),
			dp_f( 'dp_botao_texto', 'Texto do botão', 'text', array( 'wrapper' => array( 'width' => 40 ) ) ),
			dp_f( 'dp_botao_link', 'Link do botão', 'url', array( 'wrapper' => array( 'width' => 40 ) ) ),
			dp_f( 'dp_nova_aba', 'Abrir em nova aba', 'true_false', array( 'ui' => 1, 'wrapper' => array( 'width' => 20 ) ) ),
			dp_f( 'dp_posicao', 'Posição da faixa', 'select', array( 'choices' => array( 'topo' => 'Topo', 'base' => 'Rodapé' ),
				'conditional_logic' => array( array( array( 'field' => 'field_dp_pp_modelo', 'operator' => '==', 'value' => 'faixa' ) ) ) ) ),
			dp_f( 'dp_gatilho', 'Quando aparece', 'select', array( 'choices' => array( 'tempo' => 'Após alguns segundos', 'rolagem' => 'Após rolar parte da página', 'saida' => 'Ao tentar sair (computador)' ), 'wrapper' => array( 'width' => 50 ) ) ),
			dp_f( 'dp_valor', 'Segundos ou % de rolagem', 'number', array( 'default_value' => 8, 'wrapper' => array( 'width' => 25 ) ) ),
			dp_f( 'dp_repetir_dias', 'Mostrar de novo após (dias)', 'number', array( 'default_value' => 7, 'wrapper' => array( 'width' => 25 ) ) ),
			dp_f( 'dp_paginas', 'Onde aparece', 'checkbox', array( 'choices' => array( 'todas' => 'Todas as páginas', 'home' => 'Home', 'materia' => 'Matérias', 'editoria' => 'Editorias', 'agenda' => 'Agenda', 'podcast' => 'Podcast' ), 'default_value' => array( 'todas' ) ) ),
			dp_f( 'dp_dispositivo', 'Dispositivos', 'select', array( 'choices' => array( 'todos' => 'Todos', 'desktop' => 'Só computador', 'celular' => 'Só celular' ), 'wrapper' => array( 'width' => 34 ) ) ),
			dp_f( 'dp_inicio', 'Início', 'date_picker', array( 'display_format' => 'd/m/Y', 'return_format' => 'Y-m-d', 'wrapper' => array( 'width' => 33 ) ) ),
			dp_f( 'dp_fim', 'Fim', 'date_picker', array( 'display_format' => 'd/m/Y', 'return_format' => 'Y-m-d', 'wrapper' => array( 'width' => 33 ) ) ),
		),
	) );
} );
