<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Explorar artigos', 'bireme-builder' ),
    'description'     => __( 'Grid de cards de artigos (manual ou últimos posts).', 'bireme-builder' ),
    'icon'            => 'dashicons-grid-view',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'title'      => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Explore mais artigos recentes', 'bireme-builder' ),
        ),
        'more_link'  => array(
            'type'    => 'link',
            'label'   => __( 'Link "ver todas"', 'bireme-builder' ),
            'default' => array(
                'url'    => '',
                'text'   => __( 'Ver todas as notícias', 'bireme-builder' ),
                'target' => '',
            ),
        ),
        'columns'    => array(
            'type'    => 'select',
            'label'   => __( 'Cards por linha', 'bireme-builder' ),
            'default' => '3',
            'options' => array(
                '2' => '2',
                '3' => '3',
                '4' => '4',
            ),
        ),
        'source'     => array(
            'type'    => 'select',
            'label'   => __( 'Conteúdo', 'bireme-builder' ),
            'default' => 'auto',
            'options' => array(
                'auto'   => __( 'Últimos posts do boletim (automático)', 'bireme-builder' ),
                'manual' => __( 'Inserir manualmente', 'bireme-builder' ),
            ),
        ),

        'post_type'  => array(
            'type'      => 'select',
            'label'     => __( 'Tipo de conteúdo', 'bireme-builder' ),
            'options'   => Bireme_Posts::post_type_options(),
            'condition' => array( 'source' => 'auto' ),
        ),
        'category'   => array(
            'type'      => 'select',
            'label'     => __( 'Categoria', 'bireme-builder' ),
            'help'      => __( 'Vale para tipos de conteúdo que usam Categorias (ex.: Posts).', 'bireme-builder' ),
            'options'   => Bireme_Posts::category_options(),
            'condition' => array( 'source' => 'auto' ),
        ),
        'count'      => array(
            'type'      => 'select',
            'label'     => __( 'Quantidade', 'bireme-builder' ),
            'default'   => '6',
            'options'   => Bireme_Posts::count_options( 2, 12 ),
            'condition' => array( 'source' => 'auto' ),
        ),
        'skip_shown' => array(
            'type'      => 'checkbox',
            'label'     => __( 'Não repetir posts já exibidos acima nesta página (ex.: no Slide Boletim)', 'bireme-builder' ),
            'default'   => true,
            'condition' => array( 'source' => 'auto' ),
        ),

        'items'      => array(
            'type'         => 'repeater',
            'label'        => __( 'Artigos', 'bireme-builder' ),
            'button_label' => __( 'Adicionar artigo', 'bireme-builder' ),
            'row_label'    => 'title',
            'max'          => 12,
            'condition'    => array( 'source' => 'manual' ),
            'fields'       => array(
                'image'    => array(
                    'type'  => 'image',
                    'label' => __( 'Imagem', 'bireme-builder' ),
                ),
                'category' => array(
                    'type'  => 'text',
                    'label' => __( 'Categoria', 'bireme-builder' ),
                ),
                'title'    => array(
                    'type'  => 'text',
                    'label' => __( 'Título', 'bireme-builder' ),
                ),
                'text'     => array(
                    'type'  => 'textarea',
                    'label' => __( 'Resumo', 'bireme-builder' ),
                ),
                'date'     => array(
                    'type'        => 'text',
                    'label'       => __( 'Data', 'bireme-builder' ),
                    'placeholder' => __( '12 de junho de 2025', 'bireme-builder' ),
                ),
                'link'     => array(
                    'type'  => 'link',
                    'label' => __( 'Link', 'bireme-builder' ),
                ),
            ),
        ),
    ),
);
