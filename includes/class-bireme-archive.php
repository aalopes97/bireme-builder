<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Páginas de arquivo (blog, categorias, tags, datas, autores e busca) no layout Bireme:
 * título + breadcrumb e as seções de uma página modelo montada no builder.
 *
 * O tema pode sobrescrever <tema>/bireme-builder/archive-builder.php.
 */
class Bireme_Archive {

    const LIST_SECTION     = 'arquivo-posts';
    const DEFAULT_PER_PAGE = 9;

    /** @var WP_Post|null Edição aberta pelo endereço próprio (ex.: /edition/boletim-bireme-n-114/). */
    private static $edition_post = null;

    public function __construct() {
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
        add_filter( 'request', array( $this, 'edition_request' ) );
        add_filter( 'redirect_canonical', array( $this, 'keep_edition_url' ) );
        add_filter( 'pll_translation_url', array( $this, 'edition_translation_url' ), 10, 2 );
    }

    /**
     * Seletor de idiomas na página da edição: leva à mesma edição no outro idioma.
     */
    public function edition_translation_url( $url, $lang ) {
        if ( ! self::$edition_post || ! function_exists( 'pll_get_post' ) ) {
            return $url;
        }
        $translated = pll_get_post( self::$edition_post->ID, $lang );
        if ( $translated && 'publish' === get_post_status( $translated ) ) {
            return get_permalink( $translated );
        }
        return $url;
    }

    /**
     * A página de uma edição lista os artigos da categoria da edição no layout Bireme,
     * mantendo o endereço da edição.
     */
    public function edition_request( $vars ) {
        $post_type = Bireme_Editions::post_type();
        if ( is_admin() || ! $post_type || ! self::enabled() || ! is_array( $vars ) ) {
            return $vars;
        }
        if ( ! empty( $vars['feed'] ) || ! empty( $vars['embed'] ) || ! empty( $vars['preview'] ) ) {
            return $vars;
        }
        $slug = '';
        if ( ! empty( $vars[ $post_type ] ) ) {
            $slug = (string) $vars[ $post_type ];
        } elseif ( isset( $vars['post_type'] ) && $post_type === $vars['post_type'] && ! empty( $vars['name'] ) ) {
            $slug = (string) $vars['name'];
        }
        $slug = sanitize_title( basename( $slug ) );
        if ( '' === $slug ) {
            return $vars;
        }

        $args = array(
            'post_type'        => $post_type,
            'name'             => $slug,
            'post_status'      => 'publish',
            'posts_per_page'   => 1,
            'no_found_rows'    => true,
            'suppress_filters' => false,
        );
        if ( ! empty( $vars['lang'] ) ) {
            $args['lang'] = $vars['lang'];
        }
        $editions = get_posts( $args );
        if ( ! $editions ) {
            return $vars;
        }

        $category = self::edition_category( $editions[0] );
        if ( ! $category ) {
            return $vars;
        }

        self::$edition_post = $editions[0];
        $new = array( 'cat' => $category->term_id );
        if ( ! empty( $vars['lang'] ) ) {
            $new['lang'] = $vars['lang'];
        }
        $page = ! empty( $vars['paged'] ) ? (int) $vars['paged'] : ( ! empty( $vars['page'] ) ? (int) trim( (string) $vars['page'], '/' ) : 0 );
        if ( $page > 1 ) {
            $new['paged'] = $page;
        }
        return $new;
    }

    public function keep_edition_url( $redirect ) {
        return self::$edition_post ? false : $redirect;
    }

    /**
     * Categoria dos artigos da edição (a primeira com posts, exceto a padrão).
     *
     * @return WP_Term|null
     */
    private static function edition_category( WP_Post $edition ) {
        $default = (int) get_option( 'default_category' );
        $terms   = is_object_in_taxonomy( $edition->post_type, 'category' ) ? get_the_terms( $edition, 'category' ) : array();
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                if ( (int) $term->term_id !== $default && (int) $term->count > 0 ) {
                    return $term;
                }
            }
        }
        $term = get_term_by( 'slug', $edition->post_name, 'category' );
        return ( $term instanceof WP_Term && (int) $term->term_id !== $default && (int) $term->count > 0 ) ? $term : null;
    }

    /**
     * Edição aberta pelo endereço próprio, ou null.
     *
     * @return WP_Post|null
     */
    public static function edition_post() {
        return self::$edition_post;
    }

    public static function enabled() {
        $settings = Bireme_Settings::get();
        return ! empty( $settings['archive_enabled'] );
    }

    /**
     * Arquivos atendidos pelo layout. Arquivos de post types personalizados ficam com o tema.
     */
    private static function matches( WP_Query $query ) {
        $match = $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_date() || $query->is_author() || $query->is_search() || self::is_editions_archive( $query );
        return (bool) apply_filters( 'bireme_builder_archive_context', $match, $query );
    }

    /**
     * Arquivo do tipo de post das edições (ex.: /editions/).
     */
    public static function is_editions_archive( $query = null ) {
        if ( null === $query ) {
            global $wp_query;
            $query = $wp_query;
        }
        $post_type = Bireme_Editions::post_type();
        return $post_type && $query instanceof WP_Query && $query->is_post_type_archive( $post_type );
    }

    /**
     * Página de uma edição (termo da taxonomia da edição), não a lista de todas as edições.
     */
    public static function is_edition_term() {
        if ( is_admin() || ! ( is_category() || is_tag() || is_tax() ) ) {
            return false;
        }
        if ( self::$edition_post ) {
            return true;
        }
        $term = get_queried_object();
        if ( ! $term instanceof WP_Term ) {
            return false;
        }
        $taxonomy = Bireme_Editions::taxonomy();
        if ( $taxonomy && $term->taxonomy === $taxonomy ) {
            return true;
        }
        $post_type = Bireme_Editions::post_type();
        if ( ! $post_type || 'category' !== $term->taxonomy ) {
            return false;
        }
        $ids = get_posts( array(
            'post_type'        => $post_type,
            'post_status'      => 'publish',
            'posts_per_page'   => 1,
            'fields'           => 'ids',
            'no_found_rows'    => true,
            'cat'              => $term->term_id,
            'suppress_filters' => false,
        ) );
        return ! empty( $ids );
    }

    public static function is_context() {
        global $wp_query;
        if ( is_admin() || ! self::enabled() || ! $wp_query instanceof WP_Query || is_singular() ) {
            return false;
        }
        return self::matches( $wp_query );
    }

    /**
     * Página modelo no idioma atual (Polylang), ou 0.
     */
    public static function template_page_id( $translate = true ) {
        $settings = Bireme_Settings::get();
        $page_id  = absint( $settings['archive_page'] );
        if ( $page_id && $translate && function_exists( 'pll_get_post' ) ) {
            $translated = pll_get_post( $page_id );
            $page_id    = $translated ? (int) $translated : $page_id;
        }
        return ( $page_id && 'publish' === get_post_status( $page_id ) ) ? $page_id : 0;
    }

    /**
     * Seções do arquivo: as da página modelo ou, sem ela, apenas a lista de publicações.
     */
    public static function items( $translate = true ) {
        if ( self::is_editions_archive() ) {
            return (array) apply_filters( 'bireme_builder_editions_archive_items', array(
                array(
                    'id'      => 'editions',
                    'type'    => 'lista-edicoes',
                    'enabled' => true,
                    'data'    => array(
                        'title'     => '',
                        'page_head' => false,
                    ),
                ),
            ) );
        }

        $page_id = self::template_page_id( $translate );
        $items   = $page_id ? Bireme_Render::get_active_items( $page_id ) : array();
        if ( $items ) {
            return $items;
        }
        return Bireme_Layout::with_default_sidebar( array(
            array(
                'id'      => 'archive',
                'type'    => self::LIST_SECTION,
                'enabled' => true,
                'data'    => array(),
            ),
        ) );
    }

    /**
     * Dados da primeira seção "Lista de publicações" (também dentro de colunas).
     */
    public static function list_section_data( array $items ) {
        foreach ( $items as $item ) {
            if ( isset( $item['type'] ) && self::LIST_SECTION === $item['type'] ) {
                return isset( $item['data'] ) ? (array) $item['data'] : array();
            }
            if ( ! empty( $item['columns'] ) && is_array( $item['columns'] ) ) {
                foreach ( $item['columns'] as $column ) {
                    $found = self::list_section_data( Bireme_Render::get_active_items( (array) $column ) );
                    if ( null !== $found ) {
                        return $found;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Posts por página definidos na seção da página modelo.
     */
    public function pre_get_posts( $query ) {
        if ( is_admin() || ! $query->is_main_query() || $query->is_singular() || ! self::enabled() || ! self::matches( $query ) ) {
            return;
        }
        if ( self::is_editions_archive( $query ) ) {
            $query->set( 'posts_per_page', 1 );
            return;
        }

        $data     = self::list_section_data( self::items( false ) );
        $per_page = ( $data && ! empty( $data['per_page'] ) ) ? absint( $data['per_page'] ) : self::DEFAULT_PER_PAGE;
        $query->set( 'posts_per_page', max( 1, $per_page ) );

        if ( $query->is_home() ) {
            $query->set( 'ignore_sticky_posts', true );
        }
    }

    public static function title() {
        if ( is_home() || self::is_editions_archive() ) {
            return Bireme_Settings::text( 'archive_title' );
        }
        if ( is_search() ) {
            /* translators: %s: termo buscado */
            return sprintf( __( 'Resultados para "%s"', 'bireme-builder' ), get_search_query( false ) );
        }
        if ( self::$edition_post ) {
            return html_entity_decode( wp_strip_all_tags( get_the_title( self::$edition_post ) ), ENT_QUOTES, get_bloginfo( 'charset' ) );
        }
        if ( is_category() || is_tag() || is_tax() ) {
            return single_term_title( '', false );
        }
        if ( is_author() ) {
            $author = get_queried_object();
            return $author instanceof WP_User ? $author->display_name : '';
        }
        return wp_strip_all_tags( get_the_archive_title() );
    }

    /**
     * URL da listagem geral de posts (página de posts do Polylang/WordPress), ou ''.
     */
    public static function posts_page_url() {
        $page_id = (int) get_option( 'page_for_posts' );
        if ( $page_id && function_exists( 'pll_get_post' ) ) {
            $translated = pll_get_post( $page_id );
            $page_id    = $translated ? (int) $translated : $page_id;
        }
        if ( $page_id ) {
            return get_permalink( $page_id );
        }
        return 'posts' === get_option( 'show_on_front' ) ? Bireme_Layout::home_url() : '';
    }

    /**
     * Link do nome da listagem no breadcrumb: a página escolhida nas configurações
     * (traduzida pelo Polylang) ou a página de posts.
     */
    public static function label_url() {
        $settings = Bireme_Settings::get();
        $page_id  = absint( $settings['archive_label_page'] );
        if ( $page_id && function_exists( 'pll_get_post' ) ) {
            $translated = pll_get_post( $page_id );
            $page_id    = $translated ? (int) $translated : $page_id;
        }
        if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
            return get_permalink( $page_id );
        }
        $editions_page = self::editions_page_id();
        if ( $editions_page ) {
            return get_permalink( $editions_page );
        }
        $post_type = Bireme_Editions::post_type();
        $archive   = $post_type ? get_post_type_archive_link( $post_type ) : false;
        return $archive ? $archive : self::posts_page_url();
    }

    /**
     * Página publicada (no idioma atual) que usa o elemento "Lista de edições", ou 0.
     */
    public static function editions_page_id() {
        static $cache = array();
        $lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
        if ( isset( $cache[ $lang ] ) ) {
            return $cache[ $lang ];
        }
        $ids = get_posts( array(
            'post_type'        => 'page',
            'post_status'      => 'publish',
            'posts_per_page'   => 1,
            'fields'           => 'ids',
            'orderby'          => 'date',
            'order'            => 'ASC',
            'suppress_filters' => false,
            'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key'     => Bireme_Plugin::META_KEY,
                    'value'   => '"lista-edicoes"',
                    'compare' => 'LIKE',
                ),
            ),
        ) );
        $cache[ $lang ] = $ids ? (int) $ids[0] : 0;
        return $cache[ $lang ];
    }

    /**
     * @return array Lista de [ text, url ]; o último item é a página atual (url vazia).
     */
    public static function breadcrumb() {
        $crumbs = array(
            array(
                'text' => __( 'Início', 'bireme-builder' ),
                'url'  => Bireme_Layout::home_url(),
            ),
        );
        $label  = Bireme_Settings::text( 'archive_label' );

        if ( is_home() || self::is_editions_archive() ) {
            $crumbs[] = array( 'text' => '' !== $label ? $label : self::title(), 'url' => '' );
            return $crumbs;
        }

        $posts_url = self::label_url();
        if ( '' !== $label && $posts_url ) {
            $crumbs[] = array( 'text' => $label, 'url' => $posts_url );
        }

        if ( is_category() ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term && $term->parent ) {
                foreach ( array_reverse( get_ancestors( $term->term_id, 'category', 'taxonomy' ) ) as $ancestor_id ) {
                    $ancestor = get_term( $ancestor_id, 'category' );
                    if ( $ancestor instanceof WP_Term ) {
                        $crumbs[] = array( 'text' => $ancestor->name, 'url' => get_term_link( $ancestor ) );
                    }
                }
            }
        }

        $crumbs[] = array( 'text' => self::title(), 'url' => '' );
        return $crumbs;
    }
}
