<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Slide Boletim', 'bireme-builder' ),
    'description'     => __( 'Slide de destaques com cards vinculados abaixo.', 'bireme-builder' ),
    'icon'            => 'dashicons-images-alt2',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'source'      => array(
            'type'    => 'select',
            'label'   => __( 'Conteúdo', 'bireme-builder' ),
            'options' => array(
                'manual' => __( 'Inserir manualmente', 'bireme-builder' ),
                'auto'   => __( 'Últimos posts do boletim (automático)', 'bireme-builder' ),
            ),
        ),

        'post_type'   => array(
            'type'      => 'select',
            'label'     => __( 'Tipo de conteúdo', 'bireme-builder' ),
            'options'   => Bireme_Posts::post_type_options(),
            'condition' => array( 'source' => 'auto' ),
        ),
        'category'    => array(
            'type'      => 'select',
            'label'     => __( 'Categoria', 'bireme-builder' ),
            'help'      => __( 'Vale para tipos de conteúdo que usam Categorias (ex.: Posts).', 'bireme-builder' ),
            'options'   => Bireme_Posts::category_options(),
            'condition' => array( 'source' => 'auto' ),
        ),
        'count'       => array(
            'type'      => 'select',
            'label'     => __( 'Quantidade', 'bireme-builder' ),
            'default'   => '6',
            'options'   => Bireme_Posts::count_options( 2, 8 ),
            'condition' => array( 'source' => 'auto' ),
        ),
        'button_text' => array(
            'type'      => 'text',
            'label'     => __( 'Texto do botão', 'bireme-builder' ),
            'default'   => __( 'Ler agora', 'bireme-builder' ),
            'condition' => array( 'source' => 'auto' ),
        ),

        'items'       => array(
            'type'         => 'repeater',
            'label'        => __( 'Slides', 'bireme-builder' ),
            'help'         => __( 'Cada slide gera também o card correspondente abaixo do slide principal.', 'bireme-builder' ),
            'button_label' => __( 'Adicionar slide', 'bireme-builder' ),
            'row_label'    => 'title',
            'max'          => 8,
            'condition'    => array( 'source' => 'manual' ),
            'fields'       => array(
                'image'    => array(
                    'type'  => 'image',
                    'label' => __( 'Imagem', 'bireme-builder' ),
                    'help'  => __( 'Recomendado: 1600 x 500 px ou maior.', 'bireme-builder' ),
                ),
                'category' => array(
                    'type'        => 'text',
                    'label'       => __( 'Edição (banner)', 'bireme-builder' ),
                    'help'        => __( 'Número ou nome da edição, exibido no banner principal. Não aparece nos cards abaixo.', 'bireme-builder' ),
                    'placeholder' => __( 'Boletim BIREME n. 113', 'bireme-builder' ),
                ),
                'title'    => array(
                    'type'  => 'text',
                    'label' => __( 'Título', 'bireme-builder' ),
                ),
                'text'     => array(
                    'type'  => 'textarea',
                    'label' => __( 'Resumo (slide)', 'bireme-builder' ),
                ),
                'date'     => array(
                    'type'        => 'text',
                    'label'       => __( 'Data (card)', 'bireme-builder' ),
                    'placeholder' => __( '12 de junho de 2025', 'bireme-builder' ),
                ),
                'link'     => array(
                    'type'    => 'link',
                    'label'   => __( 'Botão', 'bireme-builder' ),
                    'default' => array(
                        'url'    => '',
                        'text'   => __( 'Ler agora', 'bireme-builder' ),
                        'target' => '',
                    ),
                ),
            ),
        ),

        'autoplay'    => array(
            'type'    => 'checkbox',
            'label'   => __( 'Troca automática dos slides', 'bireme-builder' ),
            'default' => true,
        ),
        'interval'    => array(
            'type'    => 'select',
            'label'   => __( 'Tempo de cada slide', 'bireme-builder' ),
            'default' => '6',
            'options' => array(
                '4'  => __( '4 segundos', 'bireme-builder' ),
                '6'  => __( '6 segundos', 'bireme-builder' ),
                '8'  => __( '8 segundos', 'bireme-builder' ),
                '10' => __( '10 segundos', 'bireme-builder' ),
            ),
        ),
    ),
);
