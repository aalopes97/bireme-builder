<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Inicialização do Bireme Builder.
 *
 * @author  Toni Lopes (@toni) - 2WP
 * @link    https://2wp.com.br
 * @package Bireme_Builder
 */
class Bireme_Plugin {

    const META_KEY = '_bireme_builder_sections';

    private static $instance = null;

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if ( is_admin() ) {
            new Bireme_Metabox();
            new Bireme_Settings();
            new Bireme_Topics();
        }

        new Bireme_Render();
        new Bireme_Layout();
        new Bireme_Chrome();
        new Bireme_Views();
        new Bireme_Newsletter();
        new Bireme_Archive();
        new Bireme_TV();
        new Bireme_I18n();
        new Bireme_Editions();
        new Bireme_Share();
    }

    /**
     * Post types onde o builder fica disponível.
     *
     * @return string[]
     */
    public static function post_types() {
        return (array) apply_filters( 'bireme_builder_post_types', array( 'page' ) );
    }

    /**
     * Seções salvas no post: lista de { id, type, enabled, data }.
     *
     * @return array
     */
    public static function get_items( $post_id ) {
        $items = get_post_meta( $post_id, self::META_KEY, true );
        return is_array( $items ) ? array_values( $items ) : array();
    }
}
