<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Posts relacionados', 'bireme-builder' ),
    'description'     => __( 'Posts com o mesmo tema/edição do post atual (use na página modelo dos posts).', 'bireme-builder' ),
    'icon'            => 'dashicons-networking',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
        'bireme-cards'      => Bireme_Posts::cards_style_url(),
    ),
    'fields'          => array(
        'title'     => array(
            'type'    => 'text',
            'label'   => __( 'Título', 'bireme-builder' ),
            'default' => __( 'Papers relacionados', 'bireme-builder' ),
        ),
        'more_link' => array(
            'type'    => 'link',
            'label'   => __( 'Link "ver todos"', 'bireme-builder' ),
            'help'    => __( 'Sem URL, aponta para a página do tema do post.', 'bireme-builder' ),
            'default' => array(
                'url'    => '',
                'text'   => __( 'Ver todos', 'bireme-builder' ),
                'target' => '',
            ),
        ),
        'relation'  => array(
            'type'    => 'select',
            'label'   => __( 'Relacionar por', 'bireme-builder' ),
            'default' => 'both',
            'options' => array(
                'both'    => __( 'Tema ou edição em comum', 'bireme-builder' ),
                'topic'   => __( 'Mesmo tema', 'bireme-builder' ),
                'edition' => __( 'Mesma edição', 'bireme-builder' ),
            ),
        ),
        'count'     => array(
            'type'    => 'select',
            'label'   => __( 'Quantidade', 'bireme-builder' ),
            'default' => '4',
            'options' => Bireme_Posts::count_options( 2, 8 ),
        ),
        'fill'      => array(
            'type'    => 'checkbox',
            'label'   => __( 'Completar com os posts mais recentes quando faltar', 'bireme-builder' ),
            'default' => true,
        ),
    ),
);
