<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Contador de visualizações dos posts (usado pela seção "Mais Lidas").
 *
 * A contagem é feita pelo navegador (sendBeacon para a REST API) para funcionar
 * mesmo com cache de página; cada post conta uma vez por sessão do visitante.
 */
class Bireme_Views {

    const META_KEY = '_bireme_views';

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_route' ) );
        add_action( 'wp_footer', array( $this, 'print_tracker' ), 99 );
    }

    public static function post_types() {
        return (array) apply_filters( 'bireme_builder_views_post_types', array( 'post' ) );
    }

    public static function get( $post_id ) {
        return (int) get_post_meta( $post_id, self::META_KEY, true );
    }

    public function register_route() {
        register_rest_route( 'bireme/v1', '/view/(?P<id>\d+)', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'track' ),
            'permission_callback' => '__return_true',
            'args'                => array(
                'id' => array(
                    'validate_callback' => static function ( $value ) {
                        return is_numeric( $value ) && (int) $value > 0;
                    },
                ),
            ),
        ) );
    }

    public function track( WP_REST_Request $request ) {
        $post = get_post( (int) $request['id'] );
        if ( ! $post || 'publish' !== $post->post_status || ! in_array( $post->post_type, self::post_types(), true ) ) {
            return new WP_Error( 'bireme_invalid_post', 'Invalid post.', array( 'status' => 404 ) );
        }

        $views = self::get( $post->ID ) + 1;
        update_post_meta( $post->ID, self::META_KEY, $views );

        return rest_ensure_response( array( 'views' => $views ) );
    }

    public function print_tracker() {
        if ( ! is_singular( self::post_types() ) || is_preview() ) {
            return;
        }

        $post_id = (int) get_queried_object_id();
        $url     = rest_url( 'bireme/v1/view/' . $post_id );

        $js = sprintf(
            '(function(){if(navigator.webdriver)return;var k="bireme_view_%1$d";try{if(sessionStorage.getItem(k))return;sessionStorage.setItem(k,"1");}catch(e){}var u=%2$s;if(navigator.sendBeacon){navigator.sendBeacon(u);}else if(window.fetch){fetch(u,{method:"POST",keepalive:true,credentials:"omit"});}})();',
            $post_id,
            wp_json_encode( $url )
        );

        if ( function_exists( 'wp_print_inline_script_tag' ) ) {
            wp_print_inline_script_tag( $js, array( 'id' => 'bireme-views' ) );
        } else {
            echo '<script id="bireme-views">' . $js . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
}
