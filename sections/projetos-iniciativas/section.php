<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Projetos e iniciativas', 'bireme-builder' ),
    'description'     => __( 'Faixa de projetos com logo, título e descrição.', 'bireme-builder' ),
    'icon'            => 'dashicons-portfolio',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'title'     => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Projetos e iniciativas', 'bireme-builder' ),
        ),
        'more_link' => array(
            'type'    => 'link',
            'label'   => __( 'Link "ver todos"', 'bireme-builder' ),
            'default' => array(
                'url'    => '',
                'text'   => __( 'Ver todos os Projetos', 'bireme-builder' ),
                'target' => '',
            ),
        ),
        'columns'   => array(
            'type'    => 'select',
            'label'   => __( 'Projetos por linha', 'bireme-builder' ),
            'default' => '5',
            'options' => array(
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
            ),
        ),
        'items'     => array(
            'type'         => 'repeater',
            'label'        => __( 'Projetos', 'bireme-builder' ),
            'button_label' => __( 'Adicionar projeto', 'bireme-builder' ),
            'row_label'    => 'title',
            'max'          => 18,
            'fields'       => array(
                'logo'  => array(
                    'type'  => 'image',
                    'label' => __( 'Logo', 'bireme-builder' ),
                ),
                'title' => array(
                    'type'  => 'text',
                    'label' => __( 'Título', 'bireme-builder' ),
                ),
                'text'  => array(
                    'type'  => 'textarea',
                    'label' => __( 'Descrição', 'bireme-builder' ),
                ),
                'link'  => array(
                    'type'  => 'link',
                    'label' => __( 'Link', 'bireme-builder' ),
                ),
            ),
        ),
    ),
);
