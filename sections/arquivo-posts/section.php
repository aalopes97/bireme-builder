<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Lista de publicações', 'bireme-builder' ),
    'description'     => __( 'Lista paginada de posts. Nas páginas de arquivo, exibe os posts do arquivo atual.', 'bireme-builder' ),
    'icon'            => 'dashicons-list-view',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
        'bireme-cards'      => Bireme_Posts::cards_style_url(),
    ),
    'fields'          => array(
        'featured'       => array(
            'type'    => 'checkbox',
            'label'   => __( 'Primeiro post em destaque (card grande)', 'bireme-builder' ),
            'default' => true,
        ),
        'per_page'       => array(
            'type'    => 'select',
            'label'   => __( 'Posts por página', 'bireme-builder' ),
            'default' => '9',
            'options' => Bireme_Posts::count_options( 3, 30 ),
        ),
        'pagination'     => array(
            'type'    => 'select',
            'label'   => __( 'Paginação', 'bireme-builder' ),
            'default' => 'both',
            'options' => array(
                'both'     => __( 'Números + botão "Carregar mais"', 'bireme-builder' ),
                'numbers'  => __( 'Só números', 'bireme-builder' ),
                'loadmore' => __( 'Só botão "Carregar mais"', 'bireme-builder' ),
                'none'     => __( 'Sem paginação', 'bireme-builder' ),
            ),
        ),
        'load_more_text' => array(
            'type'      => 'text',
            'label'     => __( 'Texto do botão', 'bireme-builder' ),
            'default'   => __( 'Carregar mais notícias', 'bireme-builder' ),
            'condition' => array( 'pagination' => array( 'both', 'loadmore' ) ),
        ),
        'empty_text'     => array(
            'type'    => 'text',
            'label'   => __( 'Texto quando não há posts', 'bireme-builder' ),
            'default' => __( 'Nenhuma publicação encontrada.', 'bireme-builder' ),
        ),
        'post_type'      => array(
            'type'    => 'select',
            'label'   => __( 'Tipo de conteúdo (fora das páginas de arquivo)', 'bireme-builder' ),
            'help'    => __( 'Nas páginas de arquivo a lista sempre segue o arquivo atual (categoria, busca etc.).', 'bireme-builder' ),
            'options' => Bireme_Posts::post_type_options(),
        ),
    ),
);
