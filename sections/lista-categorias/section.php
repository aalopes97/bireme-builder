<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$taxonomies = array_filter( Bireme_Settings::post_taxonomy_options(), 'strlen', ARRAY_FILTER_USE_KEY );

return array(
    'label'           => __( 'Lista de categorias', 'bireme-builder' ),
    'description'     => __( 'Categorias (ou outra taxonomia) com link, quantidade de publicações e descrição.', 'bireme-builder' ),
    'icon'            => 'dashicons-category',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
    ),
    'fields'          => array(
        'title'            => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Categorias', 'bireme-builder' ),
        ),
        'style'            => array(
            'type'    => 'select',
            'label'   => __( 'Estilo', 'bireme-builder' ),
            'default' => 'cards',
            'options' => array(
                'cards' => __( 'Cards em grade', 'bireme-builder' ),
                'list'  => __( 'Lista (ideal para barra lateral)', 'bireme-builder' ),
                'chips' => __( 'Etiquetas', 'bireme-builder' ),
            ),
        ),
        'columns'          => array(
            'type'      => 'select',
            'label'     => __( 'Cards por linha', 'bireme-builder' ),
            'default'   => '3',
            'options'   => array(
                '2' => '2',
                '3' => '3',
                '4' => '4',
            ),
            'condition' => array( 'style' => 'cards' ),
        ),
        'taxonomy'         => array(
            'type'    => 'select',
            'label'   => __( 'Taxonomia', 'bireme-builder' ),
            'default' => 'category',
            'options' => $taxonomies ? $taxonomies : array( 'category' => __( 'Categoria', 'bireme-builder' ) ),
        ),
        'orderby'          => array(
            'type'    => 'select',
            'label'   => __( 'Ordem', 'bireme-builder' ),
            'default' => 'name',
            'options' => array(
                'name'  => __( 'Nome (A-Z)', 'bireme-builder' ),
                'count' => __( 'Mais publicações primeiro', 'bireme-builder' ),
            ),
        ),
        'number'           => array(
            'type'    => 'select',
            'label'   => __( 'Quantidade', 'bireme-builder' ),
            'default' => '0',
            'options' => array(
                '0'  => __( 'Todas', 'bireme-builder' ),
                '4'  => '4',
                '6'  => '6',
                '8'  => '8',
                '9'  => '9',
                '12' => '12',
                '16' => '16',
                '24' => '24',
            ),
        ),
        'parent_only'      => array(
            'type'    => 'checkbox',
            'label'   => __( 'Somente categorias principais (sem subcategorias)', 'bireme-builder' ),
            'default' => false,
        ),
        'hide_empty'       => array(
            'type'    => 'checkbox',
            'label'   => __( 'Ocultar categorias sem publicações', 'bireme-builder' ),
            'default' => true,
        ),
        'show_count'       => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir quantidade de publicações', 'bireme-builder' ),
            'default' => true,
        ),
        'show_description' => array(
            'type'      => 'checkbox',
            'label'     => __( 'Exibir descrição', 'bireme-builder' ),
            'default'   => true,
            'condition' => array( 'style' => 'cards' ),
        ),
        'more_link'        => array(
            'type'    => 'link',
            'label'   => __( 'Link "ver todas" (opcional)', 'bireme-builder' ),
            'default' => array(
                'url'    => '',
                'text'   => __( 'Ver todas', 'bireme-builder' ),
                'target' => '',
            ),
        ),
    ),
);
