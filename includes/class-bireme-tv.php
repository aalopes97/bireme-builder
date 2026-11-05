<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Modelo de página "TV - Boletim": tela fixa de 1900x1080 para exibição em TV,
 * sem header/rodapé, com as seções da página (ou a seção TV Boletim padrão).
 *
 * O tema pode sobrescrever <tema>/bireme-builder/tv-boletim.php.
 */
class Bireme_TV {

    const TEMPLATE = 'bireme-tv-boletim';
    const SECTION  = 'tv-boletim';

    public function __construct() {
        add_filter( 'theme_page_templates', array( $this, 'register_template' ) );
        add_action( 'wp', array( $this, 'hide_admin_bar' ) );
    }

    public function register_template( $templates ) {
        $templates[ self::TEMPLATE ] = __( 'TV - Boletim', 'bireme-builder' );
        return $templates;
    }

    public static function is_context() {
        return ! is_admin() && is_page() && self::TEMPLATE === get_page_template_slug( get_queried_object_id() );
    }

    public function hide_admin_bar() {
        if ( self::is_context() ) {
            add_filter( 'show_admin_bar', '__return_false' );
        }
    }

    public static function items() {
        $items = Bireme_Render::get_active_items( get_queried_object_id() );
        if ( $items ) {
            return $items;
        }
        return array(
            array( 'id' => 'tv', 'type' => self::SECTION, 'enabled' => true, 'data' => array() ),
        );
    }
}
