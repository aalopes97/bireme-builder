<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Lista de edições', 'bireme-builder' ),
    'description'     => __( 'Todas as edições organizadas por ano e mês (taxonomia da edição definida em Configurações > Bireme Builder).', 'bireme-builder' ),
    'icon'            => 'dashicons-calendar-alt',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
    ),
    'fields'          => array(
        'title'      => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Todas as Edições', 'bireme-builder' ),
        ),
        'page_head'  => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir como topo da página (título + breadcrumb, igual à página de arquivo)', 'bireme-builder' ),
            'default' => true,
        ),
        'text'       => array(
            'type'  => 'textarea',
            'label' => __( 'Texto de introdução (opcional)', 'bireme-builder' ),
        ),
        'year_nav'   => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir navegação por ano no topo', 'bireme-builder' ),
            'default' => true,
        ),
        'collapse'   => array(
            'type'    => 'checkbox',
            'label'   => __( 'Recolher anos anteriores (clique para abrir)', 'bireme-builder' ),
            'default' => false,
        ),
        'show_count' => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir quantidade de artigos de cada edição', 'bireme-builder' ),
            'default' => true,
        ),
        'years'      => array(
            'type'    => 'select',
            'label'   => __( 'Anos exibidos', 'bireme-builder' ),
            'default' => '0',
            'options' => array(
                '0'  => __( 'Todos', 'bireme-builder' ),
                '1'  => '1',
                '2'  => '2',
                '3'  => '3',
                '5'  => '5',
                '10' => '10',
            ),
        ),
        'empty_text' => array(
            'type'    => 'text',
            'label'   => __( 'Texto sem edições', 'bireme-builder' ),
            'default' => __( 'Nenhuma edição publicada ainda.', 'bireme-builder' ),
        ),
    ),
);
