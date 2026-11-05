<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Temas em Destaque', 'bireme-builder' ),
    'description'     => __( 'Lista de temas com ícone, título, subtítulo e link.', 'bireme-builder' ),
    'icon'            => 'dashicons-info-outline',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'title' => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Temas em Destaque', 'bireme-builder' ),
        ),
        'source' => array(
            'type'    => 'select',
            'label'   => __( 'Conteúdo', 'bireme-builder' ),
            'default' => 'manual',
            'options' => array(
                'manual' => __( 'Inserir manualmente', 'bireme-builder' ),
                'post'   => __( 'Escolhidos em cada post (sem seleção: posts mais recentes)', 'bireme-builder' ),
                'auto'   => __( 'Categorias com mais posts (automático)', 'bireme-builder' ),
            ),
        ),
        'count'  => array(
            'type'      => 'select',
            'label'     => __( 'Quantidade', 'bireme-builder' ),
            'default'   => '6',
            'options'   => array_combine( array_map( 'strval', range( 3, 12 ) ), array_map( 'strval', range( 3, 12 ) ) ),
            'help'      => __( 'Posts: o subtítulo é o resumo. Categorias: o subtítulo é a descrição. Na página de arquivo e na interna dos posts são exibidos no máximo 4 itens.', 'bireme-builder' ),
            'condition' => array( 'source' => array( 'post', 'auto' ) ),
        ),
        'items' => array(
            'condition'    => array( 'source' => 'manual' ),
            'type'         => 'repeater',
            'label'        => __( 'Temas', 'bireme-builder' ),
            'button_label' => __( 'Adicionar tema', 'bireme-builder' ),
            'row_label'    => 'title',
            'max'          => 12,
            'fields'       => array(
                'icon'     => array(
                    'type'  => 'image',
                    'label' => __( 'Ícone (opcional)', 'bireme-builder' ),
                    'help'  => __( 'Sem imagem, usa o ícone padrão de informação.', 'bireme-builder' ),
                ),
                'title'    => array(
                    'type'  => 'text',
                    'label' => __( 'Título', 'bireme-builder' ),
                ),
                'subtitle' => array(
                    'type'  => 'text',
                    'label' => __( 'Subtítulo', 'bireme-builder' ),
                ),
                'link'     => array(
                    'type'  => 'link',
                    'label' => __( 'Link', 'bireme-builder' ),
                ),
            ),
        ),
    ),
);
