<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Caixa de mídia', 'bireme-builder' ),
    'description'     => __( 'Imagem ou ícone com título, descrição e link.', 'bireme-builder' ),
    'icon'            => 'dashicons-format-image',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'media'       => array(
            'type'    => 'select',
            'label'   => __( 'Mídia', 'bireme-builder' ),
            'default' => 'image',
            'options' => array(
                'image' => __( 'Imagem', 'bireme-builder' ),
                'icon'  => __( 'Ícone', 'bireme-builder' ),
                'none'  => __( 'Sem mídia', 'bireme-builder' ),
            ),
        ),
        'image'       => array(
            'type'      => 'image',
            'label'     => __( 'Imagem', 'bireme-builder' ),
            'condition' => array( 'media' => 'image' ),
        ),
        'image_ratio' => array(
            'type'      => 'select',
            'label'     => __( 'Formato da imagem', 'bireme-builder' ),
            'default'   => '16-9',
            'options'   => array(
                '16-9'     => __( 'Paisagem (16:9)', 'bireme-builder' ),
                '4-3'      => __( 'Paisagem (4:3)', 'bireme-builder' ),
                '1-1'      => __( 'Quadrado', 'bireme-builder' ),
                'original' => __( 'Original (sem cortar)', 'bireme-builder' ),
            ),
            'condition' => array( 'media' => 'image' ),
        ),
        'icon'        => array(
            'type'      => 'select',
            'label'     => __( 'Ícone', 'bireme-builder' ),
            'default'   => 'info',
            'options'   => array(
                'info'      => __( 'Informação', 'bireme-builder' ),
                'document'  => __( 'Documento', 'bireme-builder' ),
                'book'      => __( 'Livro', 'bireme-builder' ),
                'search'    => __( 'Busca', 'bireme-builder' ),
                'globe'     => __( 'Globo', 'bireme-builder' ),
                'health'    => __( 'Saúde', 'bireme-builder' ),
                'chart'     => __( 'Gráfico', 'bireme-builder' ),
                'database'  => __( 'Base de dados', 'bireme-builder' ),
                'people'    => __( 'Pessoas', 'bireme-builder' ),
                'mail'      => __( 'E-mail', 'bireme-builder' ),
                'calendar'  => __( 'Calendário', 'bireme-builder' ),
                'video'     => __( 'Vídeo', 'bireme-builder' ),
                'idea'      => __( 'Ideia', 'bireme-builder' ),
                'star'      => __( 'Estrela', 'bireme-builder' ),
                'link'      => __( 'Link', 'bireme-builder' ),
                'custom'    => __( 'Enviar meu ícone (imagem)', 'bireme-builder' ),
            ),
            'condition' => array( 'media' => 'icon' ),
        ),
        'icon_image'  => array(
            'type'      => 'image',
            'label'     => __( 'Meu ícone', 'bireme-builder' ),
            'help'      => __( 'SVG ou PNG quadrado.', 'bireme-builder' ),
            'condition' => array( 'icon' => 'custom' ),
        ),
        'icon_color'  => array(
            'type'      => 'color',
            'label'     => __( 'Cor do ícone', 'bireme-builder' ),
            'default'   => '#014EA3',
            'condition' => array( 'media' => 'icon' ),
        ),
        'layout'      => array(
            'type'    => 'select',
            'label'   => __( 'Posição da mídia', 'bireme-builder' ),
            'default' => 'top',
            'options' => array(
                'top'  => __( 'Em cima do texto', 'bireme-builder' ),
                'left' => __( 'À esquerda do texto', 'bireme-builder' ),
            ),
        ),
        'align'       => array(
            'type'    => 'select',
            'label'   => __( 'Alinhamento do texto', 'bireme-builder' ),
            'default' => 'left',
            'options' => array(
                'left'   => __( 'Esquerda', 'bireme-builder' ),
                'center' => __( 'Centro', 'bireme-builder' ),
            ),
        ),
        'title'       => array(
            'type'  => 'text',
            'label' => __( 'Título', 'bireme-builder' ),
        ),
        'text'        => array(
            'type'  => 'textarea',
            'label' => __( 'Descrição', 'bireme-builder' ),
        ),
        'link'        => array(
            'type'    => 'link',
            'label'   => __( 'Link', 'bireme-builder' ),
            'default' => array(
                'url'    => '',
                'text'   => __( 'Saiba mais', 'bireme-builder' ),
                'target' => '',
            ),
        ),
        'link_style'  => array(
            'type'    => 'select',
            'label'   => __( 'Estilo do link', 'bireme-builder' ),
            'default' => 'button',
            'options' => array(
                'button' => __( 'Botão', 'bireme-builder' ),
                'text'   => __( 'Texto com seta', 'bireme-builder' ),
                'card'   => __( 'Caixa inteira clicável (sem botão)', 'bireme-builder' ),
            ),
        ),
        'style'       => array(
            'type'    => 'select',
            'label'   => __( 'Aparência', 'bireme-builder' ),
            'default' => 'card',
            'options' => array(
                'card'  => __( 'Cartão branco com sombra', 'bireme-builder' ),
                'plain' => __( 'Sem fundo', 'bireme-builder' ),
            ),
        ),
        'bg_color'    => array(
            'type'      => 'color',
            'label'     => __( 'Cor de fundo (opcional)', 'bireme-builder' ),
            'condition' => array( 'style' => 'card' ),
        ),
    ),
);
