<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Página 404 no layout Bireme: mensagem com busca e atalhos, seguida das seções
 * de uma página modelo (ou dos artigos mais recentes).
 *
 * O tema pode sobrescrever <tema>/bireme-builder/404-builder.php.
 */
class Bireme_404 {

    public static function enabled() {
        $settings = Bireme_Settings::get();
        return ! empty( $settings['notfound_enabled'] );
    }

    public static function is_context() {
        return ! is_admin() && is_404() && self::enabled();
    }

    /**
     * Página modelo no idioma atual (Polylang), ou 0.
     */
    public static function template_page_id() {
        $settings = Bireme_Settings::get();
        $page_id  = absint( $settings['notfound_page'] );
        if ( $page_id && function_exists( 'pll_get_post' ) ) {
            $translated = pll_get_post( $page_id );
            $page_id    = $translated ? (int) $translated : $page_id;
        }
        return ( $page_id && 'publish' === get_post_status( $page_id ) ) ? $page_id : 0;
    }

    /**
     * Seções abaixo da mensagem: as da página modelo ou os artigos mais recentes.
     */
    public static function items() {
        $page_id = self::template_page_id();
        $items   = $page_id ? Bireme_Render::get_active_items( $page_id ) : array();
        if ( $items ) {
            return $items;
        }
        return (array) apply_filters( 'bireme_builder_404_items', array(
            array(
                'id'      => 'notfound-latest',
                'type'    => 'explorar-artigos',
                'enabled' => true,
                'data'    => array(
                    'title'      => __( 'Veja os artigos mais recentes', 'bireme-builder' ),
                    'more_link'  => array( 'url' => '', 'text' => '', 'target' => '' ),
                    'columns'    => '3',
                    'source'     => 'auto',
                    'count'      => '3',
                    'skip_shown' => false,
                ),
            ),
        ) );
    }

    /**
     * Atalhos da mensagem: início e todas as edições.
     *
     * @return array Lista de [ text, url, primary ].
     */
    public static function links() {
        $links = array(
            array(
                'text'    => __( 'Ir para o início', 'bireme-builder' ),
                'url'     => Bireme_Layout::home_url(),
                'primary' => true,
            ),
        );
        $editions = Bireme_Archive::label_url();
        if ( $editions ) {
            $links[] = array(
                'text'    => Bireme_Settings::text( 'archive_title' ),
                'url'     => $editions,
                'primary' => false,
            );
        }
        return $links;
    }

    public static function breadcrumb() {
        $title = Bireme_Settings::text( 'notfound_title' );
        return array(
            array( 'text' => __( 'Início', 'bireme-builder' ), 'url' => Bireme_Layout::home_url() ),
            array( 'text' => '' !== $title ? $title : __( 'Página não encontrada', 'bireme-builder' ), 'url' => '' ),
        );
    }
}
