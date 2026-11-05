<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'label'       => __( 'Colunas', 'bireme-builder' ),
    'description' => __( 'Container: arraste outras seções para dentro das colunas.', 'bireme-builder' ),
    'icon'        => 'dashicons-columns',
    'container'   => true,
    'fields'      => array(
        'layout' => array(
            'type'    => 'select',
            'label'   => __( 'Layout', 'bireme-builder' ),
            'default' => '75-25',
            'options' => array(
                '75-25'    => __( '2 colunas: 75% + 25% (conteúdo + barra lateral)', 'bireme-builder' ),
                '66-33'    => __( '2 colunas: 2/3 + 1/3', 'bireme-builder' ),
                '50-50'    => __( '2 colunas iguais', 'bireme-builder' ),
                '33-66'    => __( '2 colunas: 1/3 + 2/3', 'bireme-builder' ),
                '25-75'    => __( '2 colunas: 25% + 75% (barra lateral + conteúdo)', 'bireme-builder' ),
                '33-33-33' => __( '3 colunas iguais', 'bireme-builder' ),
                '25-50-25' => __( '3 colunas: 25% + 50% + 25%', 'bireme-builder' ),
            ),
        ),
        'gap'    => array(
            'type'    => 'select',
            'label'   => __( 'Espaço entre colunas', 'bireme-builder' ),
            'default' => '24',
            'options' => array(
                '16' => '16px',
                '24' => '24px',
                '32' => '32px',
                '48' => '48px',
            ),
        ),
    ),
);
