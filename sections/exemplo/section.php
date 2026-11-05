<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'       => __( 'Exemplo: texto + imagem', 'bireme-builder' ),
    'description' => __( 'Seção de referência com todos os tipos de campo.', 'bireme-builder' ),
    'icon'        => 'dashicons-align-pull-left',
    'fields'      => array(
        'title'          => array(
            'type'  => 'text',
            'label' => __( 'Título', 'bireme-builder' ),
        ),
        'subtitle'       => array(
            'type'  => 'textarea',
            'label' => __( 'Subtítulo', 'bireme-builder' ),
        ),
        'text'           => array(
            'type'  => 'wysiwyg',
            'label' => __( 'Texto', 'bireme-builder' ),
        ),
        'image'          => array(
            'type'  => 'image',
            'label' => __( 'Imagem', 'bireme-builder' ),
        ),
        'image_position' => array(
            'type'    => 'select',
            'label'   => __( 'Posição da imagem', 'bireme-builder' ),
            'options' => array(
                'right' => __( 'Direita', 'bireme-builder' ),
                'left'  => __( 'Esquerda', 'bireme-builder' ),
            ),
        ),
        'bg_color'       => array(
            'type'  => 'color',
            'label' => __( 'Cor de fundo', 'bireme-builder' ),
        ),
        'button'         => array(
            'type'  => 'link',
            'label' => __( 'Botão', 'bireme-builder' ),
        ),
        'show_items'     => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir lista de destaques', 'bireme-builder' ),
            'default' => true,
        ),
        'items'          => array(
            'type'         => 'repeater',
            'label'        => __( 'Destaques', 'bireme-builder' ),
            'button_label' => __( 'Adicionar destaque', 'bireme-builder' ),
            'row_label'    => 'title',
            'max'          => 6,
            'fields'       => array(
                'title' => array(
                    'type'  => 'text',
                    'label' => __( 'Título', 'bireme-builder' ),
                ),
                'text'  => array(
                    'type'  => 'textarea',
                    'label' => __( 'Texto', 'bireme-builder' ),
                ),
            ),
        ),
    ),
);
