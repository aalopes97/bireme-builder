<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Layout próprio das páginas com builder: template limpo (sem header/footer do tema),
 * header Bireme e menus.
 *
 * O tema pode sobrescrever os arquivos em:
 *  - <tema>/bireme-builder/page-builder.php
 *  - <tema>/bireme-builder/header.php
 *  - <tema>/bireme-builder/footer.php
 *  - <tema>/bireme-builder/archive-builder.php
 *  - <tema>/bireme-builder/single-builder.php
 *  - <tema>/bireme-builder/404-builder.php
 *  - <tema>/bireme-builder/tv-boletim.php
 *  - <tema>/bireme-builder/breadcrumb.php
 */
class Bireme_Layout {

    const MENU_TOP         = 'bireme-topbar';
    const MENU_PRIMARY     = 'bireme-primary';
    const MENU_FOOTER      = 'bireme-footer';
    const MENU_FOOTER_COL1 = 'bireme-footer-col1';
    const MENU_FOOTER_COL2 = 'bireme-footer-col2';
    const MENU_FOOTER_COL3 = 'bireme-footer-col3';

    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'register_menus' ), 20 );
        add_filter( 'template_include', array( $this, 'template_include' ), 99 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
        // Prioridade alta: carrega depois do CSS das seções, para os efeitos prevalecerem.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_effects' ), 100 );
        add_filter( 'nav_menu_item_title', array( $this, 'menu_item_title' ), 10, 4 );
    }

    public function register_menus() {
        register_nav_menus( array(
            self::MENU_TOP     => __( 'Bireme Builder: barra superior', 'bireme-builder' ),
            self::MENU_PRIMARY => __( 'Bireme Builder: menu principal', 'bireme-builder' ),
            self::MENU_FOOTER  => __( 'Bireme Builder: rodapé', 'bireme-builder' ),
            self::MENU_FOOTER_COL1 => __( 'Bireme Builder: rodapé — coluna 1', 'bireme-builder' ),
            self::MENU_FOOTER_COL2 => __( 'Bireme Builder: rodapé — coluna 2', 'bireme-builder' ),
            self::MENU_FOOTER_COL3 => __( 'Bireme Builder: rodapé — projetos institucionais', 'bireme-builder' ),
        ) );
    }

    public static function is_builder_page() {
        static $result = null;
        if ( null !== $result ) {
            return $result;
        }

        if ( Bireme_TV::is_context() ) {
            $result = false;
            return $result;
        }

        $result = Bireme_404::is_context() || Bireme_Archive::is_context() || Bireme_Single::is_context() || (
            is_singular( Bireme_Plugin::post_types() )
            && ! empty( Bireme_Render::get_active_items( get_queried_object_id() ) )
        );

        $result = (bool) apply_filters( 'bireme_builder_use_layout', $result );
        return $result;
    }

    public function template_include( $template ) {
        if ( Bireme_TV::is_context() ) {
            $file = self::locate( 'tv-boletim.php' );
            return $file ? $file : $template;
        }
        if ( ! self::is_builder_page() ) {
            return $template;
        }
        if ( Bireme_404::is_context() ) {
            $file = self::locate( '404-builder.php' );
        } elseif ( Bireme_Archive::is_context() ) {
            $file = self::locate( 'archive-builder.php' );
        } elseif ( Bireme_Single::is_context() ) {
            $file = self::locate( 'single-builder.php' );
        } else {
            $file = self::locate( 'page-builder.php' );
        }
        return $file ? $file : $template;
    }

    public function enqueue() {
        if ( ! self::is_builder_page() && ! Bireme_Chrome::applies() ) {
            return;
        }

        $css = BIREME_BUILDER_PATH . 'assets/front/layout.css';
        $js  = BIREME_BUILDER_PATH . 'assets/front/layout.js';

        wp_enqueue_style( 'bireme-font-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap', array(), null );
        wp_enqueue_style( 'bireme-builder-layout', BIREME_BUILDER_URL . 'assets/front/layout.css', array( 'bireme-font-inter' ), BIREME_BUILDER_VERSION . '-' . filemtime( $css ) );
        wp_add_inline_style( 'bireme-builder-layout', Bireme_Settings::style_vars() );
        wp_enqueue_script( 'bireme-builder-layout', BIREME_BUILDER_URL . 'assets/front/layout.js', array(), BIREME_BUILDER_VERSION . '-' . filemtime( $js ), true );
    }

    public function enqueue_effects() {
        if ( ! self::is_builder_page() ) {
            return;
        }
        $file = BIREME_BUILDER_PATH . 'assets/front/effects.css';
        wp_enqueue_style( 'bireme-builder-effects', BIREME_BUILDER_URL . 'assets/front/effects.css', array( 'bireme-builder-layout' ), BIREME_BUILDER_VERSION . '-' . filemtime( $file ) );
    }

    /**
     * Layout padrão (sem página modelo): Colunas 75-25 com as seções principais à esquerda
     * e a barra lateral (Temas em Destaque escolhidos no post, Mais Lidas e Newsletter) à direita.
     *
     * @param array $main Itens da coluna principal.
     */
    public static function with_default_sidebar( array $main ) {
        $sidebar = array(
            array( 'id' => 'sidebar-temas', 'type' => 'temas-destaque', 'enabled' => true, 'data' => array( 'source' => 'post', 'count' => '4' ) ),
            array( 'id' => 'sidebar-mais-lidas', 'type' => 'mais-lidas', 'enabled' => true, 'data' => array() ),
            array( 'id' => 'sidebar-newsletter', 'type' => 'newsletter', 'enabled' => true, 'data' => array() ),
        );

        return array(
            array(
                'id'      => 'default-layout',
                'type'    => 'colunas',
                'enabled' => true,
                'data'    => array( 'layout' => '75-25', 'gap' => '24' ),
                'columns' => array( $main, (array) apply_filters( 'bireme_builder_default_sidebar', $sidebar ) ),
            ),
        );
    }

    public static function locate( $file ) {
        $theme_file = locate_template( 'bireme-builder/' . $file );
        if ( $theme_file ) {
            return $theme_file;
        }
        $plugin_file = BIREME_BUILDER_PATH . 'templates/' . $file;
        return file_exists( $plugin_file ) ? $plugin_file : '';
    }

    public static function render_header() {
        $settings = Bireme_Settings::get();
        $file     = 'brand' === $settings['header_style'] ? self::locate( 'header-brand.php' ) : '';
        $file     = $file ? $file : self::locate( 'header.php' );
        if ( $file ) {
            include $file;
        }
    }

    /**
     * @param array|null $crumbs Lista de [ text, url ]; sem ela, usa o contexto (post ou arquivo).
     */
    public static function render_breadcrumb( $crumbs = null ) {
        $file = self::locate( 'breadcrumb.php' );
        if ( $file ) {
            $bireme_crumbs = is_array( $crumbs ) ? $crumbs : null;
            include $file;
        }
    }

    public static function render_footer() {
        $settings = Bireme_Settings::get();
        $file     = 'brand' === $settings['footer_style'] ? self::locate( 'footer-brand.php' ) : '';
        $file     = $file ? $file : self::locate( 'footer.php' );
        if ( $file ) {
            include $file;
        }
    }

    /**
     * Redes sociais preenchidas nos ajustes: lista de [ network, label, url ].
     */
    public static function social_links() {
        $links = array();
        foreach ( Bireme_Settings::social_networks() as $network => $label ) {
            $url = Bireme_Settings::localized( 'social_' . $network );
            if ( '' !== $url ) {
                $links[] = array(
                    'network' => $network,
                    'label'   => $label,
                    'url'     => $url,
                );
            }
        }
        return $links;
    }

    public static function social_icon( $network ) {
        $icons = array(
            'facebook'  => '<circle cx="12" cy="12" r="12" fill="currentColor"/><path d="M13.4 19.5v-6.3h2.1l.3-2.5h-2.4V9.2c0-.7.2-1.2 1.2-1.2h1.3V5.8c-.2 0-1-.1-1.9-.1-1.9 0-3.2 1.2-3.2 3.3v1.8H8.7v2.5h2.1v6.3z" fill="#fff"/>',
            'twitter'   => '<circle cx="12" cy="12" r="12" fill="currentColor"/><path d="M6.8 6.8h3.3l2.5 3.5 3-3.5h1.4l-3.8 4.4 4.1 5.9h-3.3l-2.7-3.8-3.3 3.8H6.6l4-4.6z" fill="#fff"/>',
            'linkedin'  => '<circle cx="12" cy="12" r="12" fill="currentColor"/><circle cx="8.4" cy="7.9" r="1.3" fill="#fff"/><path d="M7.3 10.1h2.2v6.9H7.3zM11.1 10.1h2.1v1c.4-.7 1.3-1.2 2.4-1.2 1.9 0 2.8 1.2 2.8 3.3V17h-2.2v-3.4c0-1-.4-1.7-1.3-1.7-1 0-1.6.7-1.6 1.8V17h-2.2z" fill="#fff"/>',
            'youtube'   => '<rect x="0.5" y="3.5" width="23" height="17" rx="5" fill="currentColor"/><path d="m9.8 8.3 6 3.7-6 3.7z" fill="#fff"/>',
            'instagram' => '<circle cx="12" cy="12" r="12" fill="currentColor"/><rect x="6.8" y="6.8" width="10.4" height="10.4" rx="3" fill="none" stroke="#fff" stroke-width="1.6"/><circle cx="12" cy="12" r="2.5" fill="none" stroke="#fff" stroke-width="1.6"/><circle cx="15.1" cy="8.9" r=".8" fill="#fff"/>',
        );
        if ( ! isset( $icons[ $network ] ) ) {
            return '';
        }
        return '<svg class="bireme-social-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">' . $icons[ $network ] . '</svg>';
    }

    public static function home_url() {
        return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
    }

    /**
     * Idiomas do Polylang: lista de [ name, url, slug, locale, current ].
     */
    public static function languages() {
        $languages = array();

        if ( function_exists( 'pll_the_languages' ) ) {
            $raw = pll_the_languages( array(
                'raw'                    => 1,
                'hide_if_empty'          => 0,
                'hide_if_no_translation' => 0,
            ) );

            foreach ( (array) $raw as $lang ) {
                $languages[] = array(
                    'name'    => isset( $lang['name'] ) ? $lang['name'] : '',
                    'url'     => isset( $lang['url'] ) ? $lang['url'] : '',
                    'slug'    => isset( $lang['slug'] ) ? $lang['slug'] : '',
                    'locale'  => isset( $lang['locale'] ) ? str_replace( '_', '-', $lang['locale'] ) : '',
                    'current' => ! empty( $lang['current_lang'] ),
                );
            }
        }

        return count( $languages ) >= 2 ? $languages : self::fallback_languages();
    }

    /**
     * Idiomas definidos nos ajustes; o atual é o de caminho mais longo que prefixa a URL da página.
     */
    private static function fallback_languages() {
        $links = Bireme_Settings::language_links();
        if ( ! $links ) {
            return array();
        }

        $request = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/';
        $request = trailingslashit( (string) $request );

        $current = -1;
        $longest = -1;
        foreach ( $links as $i => $link ) {
            $path = trailingslashit( (string) wp_parse_url( $link['url'], PHP_URL_PATH ) );
            if ( 0 === strpos( $request, $path ) && strlen( $path ) > $longest ) {
                $current = $i;
                $longest = strlen( $path );
            }
        }

        $languages = array();
        foreach ( $links as $i => $link ) {
            $languages[] = array(
                'name'    => $link['text'],
                'url'     => $link['url'],
                'slug'    => '',
                'locale'  => '',
                'current' => $i === $current,
            );
        }
        return $languages;
    }

    /**
     * Ícone de casa no item que aponta para a home e seta nos itens com submenu.
     */
    public function menu_item_title( $title, $item, $args, $depth ) {
        if ( ! isset( $args->theme_location ) || self::MENU_PRIMARY !== $args->theme_location || $depth > 0 ) {
            return $title;
        }

        $classes = is_array( $item->classes ) ? $item->classes : array();
        $is_home = in_array( 'menu-home', $classes, true )
            || untrailingslashit( $item->url ) === untrailingslashit( self::home_url() )
            || untrailingslashit( $item->url ) === untrailingslashit( home_url( '/' ) );

        if ( $is_home ) {
            $title = self::icon( 'home' ) . '<span>' . $title . '</span>';
        }

        if ( in_array( 'menu-item-has-children', $classes, true ) ) {
            $title .= self::icon( 'chevron' );
        }

        return $title;
    }

    public static function menu_fallback() {
        printf(
            '<ul class="bireme-header__menu"><li class="menu-item current-menu-item"><a href="%s">%s<span>%s</span></a></li></ul>',
            esc_url( self::home_url() ),
            self::icon( 'home' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            esc_html__( 'Início', 'bireme-builder' )
        );
    }

    public static function icon( $name ) {
        $icons = array(
            'home'    => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
            'chevron' => '<path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>',
            'search'  => '<circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2.5"/><path d="m20 20-3.5-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>',
        );
        if ( ! isset( $icons[ $name ] ) ) {
            return '';
        }
        return '<svg class="bireme-icon bireme-icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
    }
}
