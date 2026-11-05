<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$interval_options = array();
foreach ( array( 6, 8, 10, 12, 15, 20, 30, 45, 60 ) as $seconds ) {
    /* translators: %d: segundos */
    $interval_options[ (string) $seconds ] = sprintf( __( '%d segundos', 'bireme-builder' ), $seconds );
}

return array(
    'label'           => __( 'TV Boletim', 'bireme-builder' ),
    'description'     => __( 'Tela de TV (1900x1080) com notícias em rotação, lista e relógio. Use com o modelo de página "TV - Boletim".', 'bireme-builder' ),
    'icon'            => 'dashicons-desktop',
    'external_styles' => array(
        'bireme-font-inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
    ),
    'fields'          => array(
        'source'        => array(
            'type'    => 'select',
            'label'   => __( 'Notícias', 'bireme-builder' ),
            'default' => 'auto',
            'options' => array(
                'auto'   => __( 'Últimos posts (automático)', 'bireme-builder' ),
                'manual' => __( 'Inserir manualmente', 'bireme-builder' ),
            ),
        ),
        'post_type'     => array(
            'type'      => 'select',
            'label'     => __( 'Tipo de conteúdo', 'bireme-builder' ),
            'options'   => Bireme_Posts::post_type_options(),
            'condition' => array( 'source' => 'auto' ),
        ),
        'category'      => array(
            'type'      => 'select',
            'label'     => __( 'Categoria', 'bireme-builder' ),
            'options'   => Bireme_Posts::category_options(),
            'condition' => array( 'source' => 'auto' ),
        ),
        'count'         => array(
            'type'      => 'select',
            'label'     => __( 'Quantidade', 'bireme-builder' ),
            'default'   => '8',
            'options'   => Bireme_Posts::count_options( 3, 15 ),
            'condition' => array( 'source' => 'auto' ),
        ),
        'items'         => array(
            'type'         => 'repeater',
            'label'        => __( 'Notícias', 'bireme-builder' ),
            'button_label' => __( 'Adicionar notícia', 'bireme-builder' ),
            'row_label'    => 'title',
            'max'          => 15,
            'condition'    => array( 'source' => 'manual' ),
            'fields'       => array(
                'image' => array(
                    'type'  => 'image',
                    'label' => __( 'Imagem', 'bireme-builder' ),
                ),
                'title' => array(
                    'type'  => 'text',
                    'label' => __( 'Título', 'bireme-builder' ),
                ),
                'link'  => array(
                    'type'  => 'link',
                    'label' => __( 'Link (usado no QR Code)', 'bireme-builder' ),
                ),
            ),
        ),
        'interval'      => array(
            'type'    => 'select',
            'label'   => __( 'Tempo de cada notícia', 'bireme-builder' ),
            'default' => '12',
            'options' => $interval_options,
        ),
        'sidebar_title' => array(
            'type'    => 'text',
            'label'   => __( 'Título da lista', 'bireme-builder' ),
            'default' => __( 'Próximas Notícias', 'bireme-builder' ),
        ),
        'logo'          => array(
            'type'  => 'image',
            'label' => __( 'Imagem do topo', 'bireme-builder' ),
            'help'  => __( 'Imagem de fundo do topo, com o logo já aplicado (1920x100 px). Sem imagem, usa a faixa padrão OPS | BIREME.', 'bireme-builder' ),
        ),
        'qr'            => array(
            'type'    => 'select',
            'label'   => __( 'QR Code', 'bireme-builder' ),
            'default' => 'current',
            'options' => array(
                'current' => __( 'Link da notícia atual (gerado automaticamente)', 'bireme-builder' ),
                'image'   => __( 'Imagem fixa', 'bireme-builder' ),
                'none'    => __( 'Não exibir', 'bireme-builder' ),
            ),
        ),
        'qr_image'      => array(
            'type'      => 'image',
            'label'     => __( 'Imagem do QR Code', 'bireme-builder' ),
            'condition' => array( 'qr' => 'image' ),
        ),
        'show_clock'    => array(
            'type'    => 'checkbox',
            'label'   => __( 'Exibir relógio', 'bireme-builder' ),
            'default' => true,
        ),
        'rotate_langs'  => array(
            'type'    => 'select',
            'label'   => __( 'Alternar idiomas', 'bireme-builder' ),
            'help'    => __( 'Mostra a tela em cada idioma do Polylang, em sequência, com as notícias e os textos daquele idioma. Funciona com esta página sozinha; se ela tiver traduções com o modelo "TV - Boletim", a TV passa por elas. Cada troca recarrega a tela.', 'bireme-builder' ),
            'default' => 'cycle',
            'options' => array(
                'cycle' => __( 'Ao terminar todas as notícias', 'bireme-builder' ),
                '5'     => __( 'A cada 5 minutos', 'bireme-builder' ),
                '10'    => __( 'A cada 10 minutos', 'bireme-builder' ),
                '15'    => __( 'A cada 15 minutos', 'bireme-builder' ),
                '30'    => __( 'A cada 30 minutos', 'bireme-builder' ),
                'none'  => __( 'Não alternar', 'bireme-builder' ),
            ),
        ),
        'refresh'       => array(
            'type'    => 'select',
            'label'   => __( 'Recarregar a tela para buscar novas notícias', 'bireme-builder' ),
            'help'    => __( 'Usado quando os idiomas não se alternam (a troca de idioma já recarrega a tela).', 'bireme-builder' ),
            'default' => '30',
            'options' => array(
                '0'   => __( 'Nunca', 'bireme-builder' ),
                '15'  => __( 'A cada 15 minutos', 'bireme-builder' ),
                '30'  => __( 'A cada 30 minutos', 'bireme-builder' ),
                '60'  => __( 'A cada 1 hora', 'bireme-builder' ),
                '180' => __( 'A cada 3 horas', 'bireme-builder' ),
            ),
        ),
    ),
);
