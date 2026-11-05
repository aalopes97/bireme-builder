<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Mais Lidas', 'bireme-builder' ),
    'description'     => __( 'Ranking numerado dos posts mais visualizados.', 'bireme-builder' ),
    'icon'            => 'dashicons-chart-bar',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'title'     => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Mais Lidas', 'bireme-builder' ),
        ),
        'count'     => array(
            'type'    => 'select',
            'label'   => __( 'Quantidade', 'bireme-builder' ),
            'default' => '5',
            'options' => Bireme_Posts::count_options( 3, 10 ),
        ),
        'post_type' => array(
            'type'    => 'select',
            'label'   => __( 'Tipo de conteúdo', 'bireme-builder' ),
            'help'    => __( 'As visualizações são contadas nos Posts.', 'bireme-builder' ),
            'options' => Bireme_Posts::post_type_options(),
        ),
        'days'      => array(
            'type'    => 'select',
            'label'   => __( 'Considerar posts publicados', 'bireme-builder' ),
            'default' => '0',
            'options' => array(
                '0'   => __( 'Em qualquer data', 'bireme-builder' ),
                '7'   => __( 'Nos últimos 7 dias', 'bireme-builder' ),
                '30'  => __( 'Nos últimos 30 dias', 'bireme-builder' ),
                '90'  => __( 'Nos últimos 90 dias', 'bireme-builder' ),
                '365' => __( 'No último ano', 'bireme-builder' ),
            ),
        ),
        'fill'      => array(
            'type'    => 'checkbox',
            'label'   => __( 'Completar com os posts mais recentes enquanto não houver visualizações suficientes', 'bireme-builder' ),
            'default' => true,
        ),
    ),
);
