<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Inscrições da seção Newsletter: armazenamento (CPT privado), endpoint REST,
 * fallback sem JavaScript (admin-post) e exportação CSV.
 *
 * Integrações podem usar a action 'bireme_newsletter_subscribed' ( $email, $post_id, $lang ).
 */
class Bireme_Newsletter {

    const POST_TYPE   = 'bireme_subscriber';
    const ACTION      = 'bireme_newsletter';
    const EXPORT      = 'bireme_newsletter_export';
    const RATE_LIMIT  = 10;

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'rest_api_init', array( $this, 'register_route' ) );
        add_action( 'admin_post_nopriv_' . self::ACTION, array( $this, 'handle_form_post' ) );
        add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_form_post' ) );
        add_action( 'admin_post_' . self::EXPORT, array( $this, 'export_csv' ) );

        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
        add_action( 'manage_posts_extra_tablenav', array( $this, 'export_button' ) );
    }

    public function register_post_type() {
        register_post_type( self::POST_TYPE, array(
            'labels'          => array(
                'name'          => __( 'Newsletter', 'bireme-builder' ),
                'singular_name' => __( 'Inscrito', 'bireme-builder' ),
                'all_items'     => __( 'Inscritos', 'bireme-builder' ),
                'edit_item'     => __( 'Editar inscrito', 'bireme-builder' ),
                'search_items'  => __( 'Buscar inscritos', 'bireme-builder' ),
                'not_found'     => __( 'Nenhum inscrito ainda.', 'bireme-builder' ),
                'menu_name'     => __( 'Newsletter', 'bireme-builder' ),
            ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => false,
            'menu_icon'       => 'dashicons-email-alt',
            'supports'        => array( 'title' ),
            'capability_type' => 'post',
            'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
            'map_meta_cap'    => true,
        ) );
    }

    public function register_route() {
        register_rest_route( 'bireme/v1', '/newsletter', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_rest' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public function handle_rest( WP_REST_Request $request ) {
        $result = self::subscribe(
            (string) $request->get_param( 'email' ),
            (string) $request->get_param( 'lang' ),
            (string) $request->get_param( 'website' )
        );

        $status = array(
            'ok'      => 200,
            'invalid' => 400,
            'limited' => 429,
            'error'   => 500,
        );

        return new WP_REST_Response( array( 'status' => $result ), isset( $status[ $result ] ) ? $status[ $result ] : 500 );
    }

    public function handle_form_post() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- formulário público em páginas com cache; protegido por honeypot e limite por IP.
        $result = self::subscribe(
            isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            isset( $_POST['lang'] ) ? sanitize_key( wp_unslash( $_POST['lang'] ) ) : '',
            isset( $_POST['website'] ) ? sanitize_text_field( wp_unslash( $_POST['website'] ) ) : ''
        );

        $redirect = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : '';
        $anchor   = isset( $_POST['anchor'] ) ? sanitize_html_class( wp_unslash( $_POST['anchor'] ) ) : '';
        // phpcs:enable

        $redirect = wp_validate_redirect( $redirect, home_url( '/' ) );
        $redirect = add_query_arg( 'bireme_nl', $result, $redirect );
        if ( $anchor ) {
            $redirect .= '#' . $anchor;
        }

        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * @return string 'ok' | 'invalid' | 'limited' | 'error'
     */
    public static function subscribe( $email, $lang = '', $honeypot = '' ) {
        // Bots que preenchem o campo oculto recebem "ok" sem nada ser salvo.
        if ( '' !== trim( $honeypot ) ) {
            return 'ok';
        }

        $email = sanitize_email( trim( $email ) );
        if ( ! $email || ! is_email( $email ) ) {
            return 'invalid';
        }

        $rate_key = 'bireme_nl_' . md5( self::client_ip() );
        $attempts = (int) get_transient( $rate_key );
        if ( $attempts >= self::RATE_LIMIT ) {
            return 'limited';
        }
        set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

        $email = strtolower( $email );

        $existing = new WP_Query( array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'any',
            'title'          => $email,
            'fields'         => 'ids',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        ) );
        if ( $existing->posts ) {
            return 'ok';
        }

        $post_id = wp_insert_post( array(
            'post_type'   => self::POST_TYPE,
            'post_title'  => $email,
            'post_status' => 'publish',
        ), true );

        if ( is_wp_error( $post_id ) ) {
            return 'error';
        }

        $lang = sanitize_key( $lang );
        if ( $lang ) {
            update_post_meta( $post_id, '_bireme_lang', $lang );
        }

        do_action( 'bireme_newsletter_subscribed', $email, $post_id, $lang );

        return 'ok';
    }

    private static function client_ip() {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        return $ip ? $ip : 'unknown';
    }

    /* ------------------------------------------------------------------ *
     * Admin
     * ------------------------------------------------------------------ */

    public function columns( $columns ) {
        return array(
            'cb'          => isset( $columns['cb'] ) ? $columns['cb'] : '',
            'title'       => __( 'E-mail', 'bireme-builder' ),
            'bireme_lang' => __( 'Idioma', 'bireme-builder' ),
            'date'        => __( 'Data', 'bireme-builder' ),
        );
    }

    public function column_content( $column, $post_id ) {
        if ( 'bireme_lang' === $column ) {
            $lang = get_post_meta( $post_id, '_bireme_lang', true );
            echo esc_html( $lang ? strtoupper( $lang ) : '—' );
        }
    }

    public function export_button( $which ) {
        if ( 'top' !== $which || self::POST_TYPE !== get_current_screen()->post_type || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $url = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::EXPORT ), self::EXPORT );
        echo '<div class="alignleft actions"><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Exportar CSV', 'bireme-builder' ) . '</a></div>';
    }

    public function export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'bireme-builder' ), 403 );
        }
        check_admin_referer( self::EXPORT );

        $ids = get_posts( array(
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=newsletter-' . gmdate( 'Y-m-d' ) . '.csv' );

        $out = fopen( 'php://output', 'w' );
        fwrite( $out, "\xEF\xBB\xBF" );
        fputcsv( $out, array( 'email', 'idioma', 'data' ) );
        foreach ( $ids as $id ) {
            fputcsv( $out, array(
                get_the_title( $id ),
                (string) get_post_meta( $id, '_bireme_lang', true ),
                get_the_date( 'Y-m-d H:i', $id ),
            ) );
        }
        fclose( $out );
        exit;
    }
}
