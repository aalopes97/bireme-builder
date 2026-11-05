<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'           => __( 'Conteúdo do post', 'bireme-builder' ),
    'description'     => __( 'Título, informações e texto do post atual (use na página modelo dos posts).', 'bireme-builder' ),
    'icon'            => 'dashicons-media-text',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap',
    ),
    'fields'          => array(
        'show_edition' => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir a edição acima do título', 'bireme-builder' ),
            'default' => true,
        ),
        'show_date'    => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir data', 'bireme-builder' ),
            'default' => true,
        ),
        'show_reading' => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir tempo de leitura', 'bireme-builder' ),
            'default' => true,
        ),
        'show_topic'   => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir tema', 'bireme-builder' ),
            'default' => true,
        ),
        'show_listen'  => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir "Ouvir transcrição" (leitura em voz alta pelo navegador)', 'bireme-builder' ),
            'default' => true,
        ),
        'listen_text'  => array(
            'type'      => 'text',
            'label'     => __( 'Texto do botão de áudio', 'bireme-builder' ),
            'default'   => __( 'Ouvir transcrição', 'bireme-builder' ),
            'condition' => array( 'show_listen' => true ),
        ),
        'show_image'   => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir a imagem destacada no topo do texto', 'bireme-builder' ),
            'default' => false,
        ),
    ),
);
