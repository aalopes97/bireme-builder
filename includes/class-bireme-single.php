<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Página interna dos posts no layout Bireme: título da edição + breadcrumb e as seções
 * de uma página modelo montada no builder.
 *
 * O tema pode sobrescrever <tema>/bireme-builder/single-builder.php.
 */
class Bireme_Single {

    const CONTENT_SECTION = 'post-conteudo';
    const RELATED_SECTION = 'posts-relacionados';

    public static function post_types() {
        return (array) apply_filters( 'bireme_builder_single_post_types', array( 'post' ) );
    }

    public static function is_context() {
        if ( is_admin() || ! is_singular( self::post_types() ) ) {
            return false;
        }
        $settings = Bireme_Settings::get();
        return ! empty( $settings['single_enabled'] );
    }

    /**
     * Post exibido pelas seções: o post atual ou, fora da página de post (ex.: na própria
     * página modelo), o mais recente, para servir de pré-visualização.
     *
     * @return WP_Post|null
     */
    public static function current_post() {
        if ( self::is_context() ) {
            return get_post( get_queried_object_id() );
        }
        $latest = get_posts( array(
            'post_type'      => 'post',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        ) );
        return $latest ? $latest[0] : null;
    }

    public static function template_page_id() {
        $settings = Bireme_Settings::get();
        $page_id  = absint( $settings['single_page'] );
        if ( $page_id && function_exists( 'pll_get_post' ) ) {
            $translated = pll_get_post( $page_id );
            $page_id    = $translated ? (int) $translated : $page_id;
        }
        return ( $page_id && 'publish' === get_post_status( $page_id ) ) ? $page_id : 0;
    }

    public static function items() {
        $page_id = self::template_page_id();
        $items   = $page_id ? Bireme_Render::get_active_items( $page_id ) : array();
        if ( $items ) {
            return $items;
        }
        return Bireme_Layout::with_default_sidebar( array(
            array( 'id' => 'post-content', 'type' => self::CONTENT_SECTION, 'enabled' => true, 'data' => array() ),
            array( 'id' => 'post-related', 'type' => self::RELATED_SECTION, 'enabled' => true, 'data' => array() ),
        ) );
    }

    /**
     * Primeiro termo do post na taxonomia configurada ('single_edition_tax' ou 'single_topic_tax').
     *
     * @return WP_Term|null
     */
    public static function term( $post, $setting ) {
        $settings = Bireme_Settings::get();
        $taxonomy = isset( $settings[ $setting ] ) ? $settings[ $setting ] : '';
        if ( ! $post || ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
            return null;
        }
        $terms = get_the_terms( $post, $taxonomy );
        return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
    }

    /**
     * Edição do post: termo da "Taxonomia da edição" ou, sem ele, o da "Taxonomia do tema"
     * (o mesmo exibido na linha de informações do conteúdo).
     *
     * @return WP_Term|null
     */
    public static function edition_term( $post ) {
        $term = self::term( $post, 'single_edition_tax' );
        return $term ? $term : self::term( $post, 'single_topic_tax' );
    }

    public static function term_name( $term ) {
        return $term ? html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ) : '';
    }

    /**
     * Minutos de leitura (mínimo 1).
     */
    public static function reading_time( WP_Post $post, $words_per_minute = 200 ) {
        $text  = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
        $words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
        return max( 1, (int) ceil( $words / max( 50, (int) $words_per_minute ) ) );
    }

    /**
     * Título do topo: a edição do post ou, sem ela, o nome da listagem.
     */
    public static function title() {
        $edition = self::edition_term( get_post( get_queried_object_id() ) );
        if ( $edition ) {
            return self::term_name( $edition );
        }
        $label = Bireme_Settings::text( 'archive_label' );
        return '' !== $label ? $label : get_the_title( get_queried_object_id() );
    }

    /**
     * @return array Lista de [ text, url ].
     */
    public static function breadcrumb() {
        $crumbs = array(
            array( 'text' => __( 'Início', 'bireme-builder' ), 'url' => Bireme_Layout::home_url() ),
        );

        $label     = Bireme_Settings::text( 'archive_label' );
        $posts_url = Bireme_Archive::label_url();
        if ( '' !== $label ) {
            $crumbs[] = array( 'text' => $label, 'url' => $posts_url );
        }

        $edition = self::edition_term( get_post( get_queried_object_id() ) );
        if ( $edition ) {
            $link     = get_term_link( $edition );
            $crumbs[] = array( 'text' => self::term_name( $edition ), 'url' => is_wp_error( $link ) ? '' : $link );
        }
        $crumbs[] = array( 'text' => get_the_title( get_queried_object_id() ), 'url' => '' );

        return $crumbs;
    }
}
