<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Configurações globais do header (Configurações > Bireme Builder).
 * Os textos são registrados no Polylang (Idiomas > Traduções de strings).
 */
class Bireme_Settings {

    const OPTION    = 'bireme_builder_header';
    const PAGE_SLUG = 'bireme-builder';
    const PLL_GROUP = 'Bireme Builder';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_page' ) );
        add_action( 'admin_init', array( $this, 'register' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
    }

    public static function defaults() {
        return array(
            'header_style'       => 'classic',
            'header_logo'        => '',
            'header_logo_link'   => '',
            'title'              => 'Boletim BIREME/OPAS/OMS',
            'subtitle'           => 'Informação e conhecimento para a saúde das Américas',
            'search_placeholder' => 'Buscar no boletim...',
            'show_search'        => true,
            'show_languages'     => true,
            'top_links'          => "Portal BIREME|https://bvsalud.org\nOPAS/OMS|https://www.paho.org",
            'language_links'     => "Português|/\nEnglish|/en/\nEspañol|/es/",
            'footer_logo'        => '',
            'footer_logo_link'   => 'https://www.paho.org/pt/bireme',
            'footer_title'       => 'BIREME/OPAS/OMS',
            'footer_text'        => 'Centro Latino-Americano e do Caribe de Informação em Ciências da Saúde',
            'footer_copyright'   => '©{ano} BIREME/OPAS/OMS. Todos os direitos reservados.',
            'footer_links'       => "Contato|\nMapa do Site|\nPrivacidade|",
            'footer_style'       => 'classic',
            'footer_brand_logo'  => '',
            'footer_col1_title'  => 'Explorar',
            'footer_col1_links'  => "Início|/\nEdições|\nSobre|",
            'footer_col2_title'  => 'Institucional',
            'footer_col2_links'  => "BIREME|https://www.paho.org/pt/bireme\nOPAS|https://www.paho.org/pt\nContato|https://contacto.bvsalud.org/",
            'footer_col3_title'  => 'Projetos institucionais',
            'footer_col3_links'  => "Portal Regional da BVS|https://bvsalud.org/\nLILACS|https://lilacs.bvsalud.org/\nDeCS/MeSH|https://decs.bvsalud.org/\ne-BlueInfo|https://e-blueinfo.bvsalud.org/",
            'social_facebook'    => '',
            'social_twitter'     => '',
            'social_linkedin'    => '',
            'social_youtube'     => '',
            'social_instagram'   => '',
            'footer_i18n'        => array(),
            'archive_enabled'    => true,
            'archive_page'       => 0,
            'archive_title'      => 'Todas as Edições',
            'archive_label'      => 'Edições',
            'archive_label_page' => 0,
            'single_enabled'     => true,
            'single_page'        => 0,
            'single_edition_tax' => 'post_tag',
            'single_topic_tax'   => 'category',
            'global_chrome'      => true,
            'notfound_enabled'   => true,
            'notfound_page'      => 0,
            'notfound_title'     => 'Página não encontrada',
            'notfound_text'      => 'O endereço pode ter mudado ou a página não existe mais. Use a busca ou siga um dos atalhos abaixo.',
            'logos'              => array(),
            'header_bg_color'    => '#0D4EA6',
            'header_bg_image'    => '',
            'header_bg_none'     => false,
            'header_bar_color'   => '#FFFFFF',
            'footer_bg_color'    => '#FFFFFF',
            'footer_band_color'  => '#002857',
            'footer_band_image'  => '',
            'footer_band_none'   => false,
        );
    }

    /**
     * Logos que podem ser trocados em cada idioma: [ chave => [ rótulo, ajuda ] ].
     */
    public static function logo_kinds() {
        return array(
            'header' => array( __( 'Logo do topo', 'bireme-builder' ), __( 'Modelo de header "Faixa branca com logo".', 'bireme-builder' ) ),
            'footer' => array( __( 'Logo do rodapé', 'bireme-builder' ), __( 'Modelo de rodapé "Clássico".', 'bireme-builder' ) ),
            'band'   => array( __( 'Logo da faixa azul do rodapé', 'bireme-builder' ), __( 'Modelo "Fundo azul com triângulos". O logo padrão aparece em branco.', 'bireme-builder' ) ),
        );
    }

    /**
     * Idiomas com logo próprio: [ slug => nome ]. Sem Polylang, um único idioma.
     */
    public static function logo_languages() {
        if ( function_exists( 'pll_languages_list' ) ) {
            $slugs = (array) pll_languages_list( array( 'fields' => 'slug' ) );
            $names = (array) pll_languages_list( array( 'fields' => 'name' ) );
            if ( $slugs ) {
                $languages = array();
                foreach ( $slugs as $i => $slug ) {
                    $languages[ $slug ] = isset( $names[ $i ] ) ? $names[ $i ] : $slug;
                }
                return $languages;
            }
        }
        return array( 'default' => __( 'Todos os idiomas', 'bireme-builder' ) );
    }

    /**
     * Logo oficial da BIREME no idioma (pt, en ou es).
     */
    public static function default_logo_url( $lang ) {
        $lang = strtolower( (string) $lang );
        if ( 'default' === $lang || '' === $lang ) {
            $lang = (string) get_locale();
        }
        $code = 'pt';
        if ( 0 === strpos( $lang, 'es' ) ) {
            $code = 'es';
        } elseif ( 0 === strpos( $lang, 'en' ) ) {
            $code = 'en';
        }
        return 'https://logos.bireme.org/img/' . $code . '/v_bir_color.svg';
    }

    private static function logo_language() {
        if ( function_exists( 'pll_current_language' ) && function_exists( 'pll_languages_list' ) && pll_languages_list() ) {
            $lang = (string) pll_current_language( 'slug' );
            if ( '' !== $lang ) {
                return $lang;
            }
            return function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : 'default';
        }
        return 'default';
    }

    /**
     * Logo salvo antes da tabela por idioma (campos únicos antigos), ou ''.
     */
    private static function legacy_logo( $kind, $lang ) {
        $settings = self::get();
        if ( 'footer' === $kind && ! empty( $settings['footer_i18n'][ $lang ]['footer_logo'] ) ) {
            return (string) $settings['footer_i18n'][ $lang ]['footer_logo'];
        }
        $default = function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : '';
        if ( 'default' !== $lang && '' !== $default && $lang !== $default ) {
            return '';
        }
        $keys = array( 'header' => 'header_logo', 'footer' => 'footer_logo', 'band' => 'footer_brand_logo' );
        return isset( $keys[ $kind ], $settings[ $keys[ $kind ] ] ) ? (string) $settings[ $keys[ $kind ] ] : '';
    }

    /**
     * Logo escolhido para o idioma, sem o padrão: o da tabela ou o dos campos antigos.
     */
    private static function saved_logo( $kind, $lang ) {
        $settings = self::get();
        if ( ! empty( $settings['logos'][ $lang ][ $kind ] ) ) {
            return (string) $settings['logos'][ $lang ][ $kind ];
        }
        return self::legacy_logo( $kind, $lang );
    }

    /**
     * Logo no idioma atual: [ url, is_default ].
     */
    public static function logo( $kind ) {
        $lang  = self::logo_language();
        $saved = self::saved_logo( $kind, $lang );
        if ( '' !== $saved ) {
            return array( $saved, false );
        }
        return array( self::default_logo_url( $lang ), true );
    }

    /**
     * Logo usado na prévia de compartilhamento: precisa ser uma imagem PNG/JPG local.
     */
    public static function share_logo() {
        $logo = self::header_logo();
        $path = (string) wp_parse_url( $logo, PHP_URL_PATH );
        if ( preg_match( '/\.(png|jpe?g|gif|webp)$/i', $path ) && 0 === strpos( set_url_scheme( $logo, 'https' ), set_url_scheme( home_url( '/' ), 'https' ) ) ) {
            return $logo;
        }
        return BIREME_BUILDER_URL . 'assets/img/' . rawurlencode( 'h_bir_color 1.png' );
    }

    /**
     * Cores e imagens de fundo do header e do rodapé, como variáveis CSS.
     */
    public static function style_vars() {
        $settings = self::get();
        $defaults = self::defaults();
        $color    = static function ( $key ) use ( $settings, $defaults ) {
            $value = sanitize_hex_color( (string) $settings[ $key ] );
            // O azul antigo da faixa do rodapé passou a ser o azul-marinho do fundo com triângulos.
            if ( 'footer_band_color' === $key && $value && in_array( strtoupper( $value ), array( '#0D4EA6', '#012650' ), true ) ) {
                $value = '';
            }
            return $value ? $value : $defaults[ $key ];
        };
        $image = static function ( $key, $none, $file ) use ( $settings ) {
            if ( ! empty( $settings[ $none ] ) ) {
                return 'none';
            }
            $url = esc_url_raw( (string) $settings[ $key ] );
            if ( '' === $url ) {
                $url = BIREME_BUILDER_URL . 'assets/img/' . $file;
            }
            return 'url("' . str_replace( array( '"', '\\', ')' ), array( '%22', '%5C', '%29' ), $url ) . '")';
        };

        $header = array(
            '--bh-bg'       => $color( 'header_bg_color' ),
            '--bh-bg-image' => $image( 'header_bg_image', 'header_bg_none', 'fundo_header_v3.jpg' ),
            '--bh-bar'      => $color( 'header_bar_color' ),
        );
        // Modelo "Faixa branca com logo": fundo próprio, trocado só por uma imagem escolhida ou "sem imagem".
        if ( ! empty( $settings['header_bg_none'] ) ) {
            $header['--bh-v2-bg'] = $color( 'header_bg_color' );
        } elseif ( '' !== esc_url_raw( (string) $settings['header_bg_image'] ) ) {
            $header['--bh-v2-bg'] = $color( 'header_bg_color' ) . ' ' . $image( 'header_bg_image', 'header_bg_none', 'fundo_header.png' ) . ' center / cover no-repeat';
        }
        $footer = array(
            '--bf-bg'         => $color( 'footer_bg_color' ),
            '--bf-band'       => $color( 'footer_band_color' ),
            '--bf-band-image' => $image( 'footer_band_image', 'footer_band_none', 'fundo_footer.jpg' ),
        );

        $css = '';
        foreach ( array( '.bireme-header' => $header, '.bireme-footer' => $footer ) as $selector => $vars ) {
            $css .= $selector . '{';
            foreach ( $vars as $name => $value ) {
                $css .= $name . ':' . $value . ';';
            }
            $css .= '}';
        }
        return $css;
    }

    /**
     * Taxonomias públicas dos posts: [ slug => nome ].
     */
    public static function post_taxonomy_options() {
        $options = array( '' => __( '— Nenhuma —', 'bireme-builder' ) );
        foreach ( get_object_taxonomies( 'post', 'objects' ) as $taxonomy ) {
            if ( $taxonomy->public && 'post_format' !== $taxonomy->name ) {
                $options[ $taxonomy->name ] = $taxonomy->labels->singular_name;
            }
        }
        return $options;
    }

    /**
     * Redes sociais do rodapé: [ rede => rótulo ].
     */
    public static function social_networks() {
        return array(
            'facebook'  => 'Facebook',
            'twitter'   => 'X (Twitter)',
            'linkedin'  => 'LinkedIn',
            'youtube'   => 'YouTube',
            'instagram' => 'Instagram',
        );
    }

    /**
     * Modelos de header disponíveis: [ chave => [ nome, descrição ] ].
     */
    public static function header_styles() {
        return array(
            'classic' => array(
                __( 'Clássico', 'bireme-builder' ),
                __( 'Faixa azul com barra superior de links, título, busca e menu.', 'bireme-builder' ),
            ),
            'brand'   => array(
                __( 'Faixa branca com logo', 'bireme-builder' ),
                __( 'Faixa branca com logo, idiomas e busca; abaixo, faixa azul com título, subtítulo e menu.', 'bireme-builder' ),
            ),
        );
    }

    /**
     * URL do logo do header no modelo "Faixa branca com logo".
     */
    public static function header_logo() {
        $logo = self::logo( 'header' );
        return $logo[0];
    }

    public function render_header_style_field() {
        $previews = array(
            'classic' => '<span style="display:block;height:10px;background:#0A3B80;"></span><span style="display:block;height:44px;background:#0D4EA6;padding:10px;"><span style="display:block;width:55%;height:8px;background:#fff;border-radius:2px;"></span><span style="display:block;width:35%;height:5px;margin-top:6px;background:rgba(255,255,255,.7);border-radius:2px;"></span></span>',
            'brand'   => '<span style="display:flex;align-items:center;justify-content:space-between;height:16px;padding:0 8px;background:#fff;border-bottom:1px solid #E6E8EC;"><span style="width:40%;height:6px;background:#0D4EA6;border-radius:2px;"></span><span style="width:28%;height:7px;background:#F0F0F1;border:1px solid #DCDCDE;border-radius:3px;"></span></span><span style="display:block;height:38px;background:#0D4EA6;padding:8px 10px;"><span style="display:block;width:55%;height:8px;background:#fff;border-radius:2px;"></span><span style="display:block;width:40%;height:3px;margin-top:6px;background:linear-gradient(90deg,#29B6F6 0 30%,#FE5604 30% 60%,rgba(255,255,255,.7) 60%);border-radius:2px;"></span></span>',
        );
        self::render_style_cards( 'header_style', self::header_styles(), $previews );
        echo '<p class="description">' . esc_html__( 'Os dois modelos usam o título, o subtítulo, a busca, os idiomas e o menu definidos abaixo. A barra superior de links aparece só no modelo Clássico.', 'bireme-builder' ) . '</p>';
    }

    /**
     * Modelos de rodapé disponíveis: [ chave => [ nome, descrição ] ].
     */
    public static function footer_styles() {
        return array(
            'classic' => array(
                __( 'Clássico', 'bireme-builder' ),
                __( 'Faixa branca com logo, textos, links e redes sociais.', 'bireme-builder' ),
            ),
            'brand'   => array(
                __( 'Fundo azul com triângulos', 'bireme-builder' ),
                __( 'Fundo azul-marinho com triângulos nas laterais: colunas de links e projetos institucionais; abaixo, logo, copyright e redes sociais.', 'bireme-builder' ),
            ),
        );
    }

    public function render_footer_style_field() {
        $previews = array(
            'classic' => '<span style="display:flex;align-items:center;justify-content:space-between;height:54px;padding:0 10px;background:#fff;"><span style="width:45%;height:6px;background:#0D4EA6;border-radius:2px;"></span><span style="width:25%;height:5px;background:#0D4EA6;opacity:.6;border-radius:2px;"></span></span>',
            'brand'   => '<span style="display:grid;grid-template-rows:38px 22px;height:60px;background:#002857;"><span style="display:flex;align-items:center;gap:10px;padding:0 22px;background:#002857 url(' . esc_url( BIREME_BUILDER_URL . 'assets/img/fundo_footer.jpg' ) . ') center / 100% 100% no-repeat;"><span style="width:22%;height:14px;border-left:1px solid rgba(255,255,255,.5);"></span><span style="width:22%;height:14px;border-left:1px solid rgba(255,255,255,.5);"></span><span style="width:22%;height:14px;border-left:1px solid rgba(255,255,255,.5);"></span></span><span style="display:flex;align-items:center;justify-content:space-between;padding:0 22px;border-top:1px solid rgba(255,255,255,.25);"><span style="width:28%;height:5px;background:#fff;border-radius:2px;"></span><span style="width:22%;height:4px;background:rgba(255,255,255,.7);border-radius:2px;"></span></span></span>',
        );
        self::render_style_cards( 'footer_style', self::footer_styles(), $previews );
        echo '<p class="description">' . esc_html__( 'No modelo "Fundo azul com triângulos", as colunas usam os links e os projetos institucionais definidos abaixo; a parte de baixo usa o logo, o copyright e as redes sociais.', 'bireme-builder' ) . '</p>';
    }

    private static function render_style_cards( $setting, array $styles, array $previews ) {
        $settings = self::get();
        echo '<style>.bireme-style-cards label:has(input:checked){border-color:#2271B1!important;box-shadow:0 0 0 1px #2271B1;}</style>';
        echo '<fieldset class="bireme-style-cards" style="display:flex;flex-wrap:wrap;gap:16px;">';
        foreach ( $styles as $key => $style ) {
            $checked = $settings[ $setting ] === $key;
            printf(
                '<label style="display:block;width:260px;padding:12px;border:2px solid %1$s;border-radius:8px;background:#fff;cursor:pointer;">
                    <span style="display:block;overflow:hidden;margin-bottom:10px;border-radius:4px;box-shadow:0 0 0 1px #DCDCDE;">%2$s</span>
                    <input type="radio" name="%3$s[%8$s]" value="%4$s"%5$s> <strong>%6$s</strong>
                    <span style="display:block;margin-top:4px;color:#646970;font-size:12px;line-height:1.4;">%7$s</span>
                </label>',
                '#DCDCDE',
                $previews[ $key ], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML fixo acima.
                esc_attr( self::OPTION ),
                esc_attr( $key ),
                checked( $checked, true, false ),
                esc_html( $style[0] ),
                esc_html( $style[1] ),
                esc_attr( $setting )
            );
        }
        echo '</fieldset>';
    }

    /**
     * Campos do rodapé que podem ser personalizados por idioma: [ chave => [ rótulo, tipo ] ].
     */
    public static function footer_i18n_fields() {
        $fields = array(
            'footer_title'     => array( __( 'Título', 'bireme-builder' ), 'text' ),
            'footer_text'      => array( __( 'Texto', 'bireme-builder' ), 'text' ),
            'footer_copyright' => array( __( 'Copyright', 'bireme-builder' ), 'text' ),
            'footer_links'     => array( __( 'Links do rodapé (Texto|URL)', 'bireme-builder' ), 'links' ),
            'footer_col1_title' => array( __( 'Coluna 1: título', 'bireme-builder' ), 'text' ),
            'footer_col1_links' => array( __( 'Coluna 1: links (Texto|URL)', 'bireme-builder' ), 'links' ),
            'footer_col2_title' => array( __( 'Coluna 2: título', 'bireme-builder' ), 'text' ),
            'footer_col2_links' => array( __( 'Coluna 2: links (Texto|URL)', 'bireme-builder' ), 'links' ),
            'footer_col3_title' => array( __( 'Projetos institucionais: título', 'bireme-builder' ), 'text' ),
            'footer_col3_links' => array( __( 'Projetos institucionais: links (Texto|URL)', 'bireme-builder' ), 'links' ),
            'footer_logo_link' => array( __( 'Link do logo', 'bireme-builder' ), 'url' ),
        );
        foreach ( self::social_networks() as $network => $label ) {
            $fields[ 'social_' . $network ] = array( $label, 'url' );
        }
        return $fields;
    }

    /**
     * Idiomas do Polylang além do padrão: [ slug => nome ].
     */
    public static function extra_languages() {
        if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_default_language' ) ) {
            return array();
        }
        $default   = pll_default_language( 'slug' );
        $slugs     = (array) pll_languages_list( array( 'fields' => 'slug' ) );
        $names     = (array) pll_languages_list( array( 'fields' => 'name' ) );
        $languages = array();
        foreach ( $slugs as $i => $slug ) {
            if ( $slug !== $default ) {
                $languages[ $slug ] = isset( $names[ $i ] ) ? $names[ $i ] : $slug;
            }
        }
        return $languages;
    }

    /**
     * Valor personalizado para o idioma atual (Polylang), ou '' quando não houver.
     */
    private static function override( $key ) {
        if ( ! function_exists( 'pll_current_language' ) ) {
            return '';
        }
        $lang     = pll_current_language( 'slug' );
        $settings = self::get();
        if ( ! $lang || empty( $settings['footer_i18n'][ $lang ][ $key ] ) ) {
            return '';
        }
        return (string) $settings['footer_i18n'][ $lang ][ $key ];
    }

    /**
     * Valor no idioma atual: o personalizado para o idioma ou o principal.
     */
    public static function localized( $key ) {
        $value = self::override( $key );
        if ( '' !== $value ) {
            return $value;
        }
        $settings = self::get();
        return isset( $settings[ $key ] ) ? (string) $settings[ $key ] : '';
    }

    /**
     * Links do rodapé (Texto|URL por linha), usados quando não há menu na posição "Bireme Builder: rodapé".
     *
     * @return array Lista de [ text, url ].
     */
    public static function footer_links() {
        return self::parse_links( 'footer_links' );
    }

    /**
     * Links de uma coluna do rodapé no modelo "Faixa branca" (1 ou 2). URLs começando com / são relativas à home do idioma.
     *
     * @return array Lista de [ text, url ].
     */
    public static function footer_column_links( $column ) {
        $links = array();
        foreach ( self::parse_links( 'footer_col' . absint( $column ) . '_links' ) as $link ) {
            if ( '' === $link['url'] ) {
                $link['url'] = self::auto_link_url( array( $link['source'], $link['text'] ) );
            }
            if ( '' !== $link['url'] && 0 === strpos( $link['url'], '/' ) && 0 !== strpos( $link['url'], '//' ) ) {
                $link['url'] = untrailingslashit( Bireme_Layout::home_url() ) . $link['url'];
            }
            $link['url'] = self::localize_url( $link['url'] );
            $links[] = $link;
        }
        return $links;
    }

    /**
     * Endereço dos itens comuns do rodapé escritos sem URL (Edições, Sobre, Contato, Início).
     *
     * @param string[] $labels Texto original e traduzido do item.
     */
    private static function auto_link_url( array $labels ) {
        $keys = array();
        foreach ( $labels as $label ) {
            $keys[] = strtolower( remove_accents( trim( (string) $label ) ) );
        }
        $is = static function ( array $names ) use ( $keys ) {
            return (bool) array_intersect( $keys, $names );
        };

        if ( $is( array( 'inicio', 'home', 'pagina inicial', 'pagina de inicio' ) ) ) {
            return Bireme_Layout::home_url();
        }
        if ( $is( array( 'edicoes', 'ediciones', 'editions', 'todas as edicoes', 'todas las ediciones', 'all editions' ) ) ) {
            return Bireme_Archive::label_url();
        }
        if ( $is( array( 'sobre', 'acerca de', 'about', 'sobre o boletim', 'acerca del boletin', 'about the bulletin' ) ) ) {
            return self::menu_url( array( 'sobre', 'acerca', 'about' ) );
        }
        if ( $is( array( 'contato', 'contacto', 'contact', 'fale conosco', 'contactenos' ) ) ) {
            $url = self::menu_url( array( 'contato', 'contacto', 'contact' ) );
            return '' !== $url ? $url : 'https://contacto.bvsalud.org/';
        }
        return '';
    }

    /**
     * Primeiro item do menu principal ou da barra superior (no idioma atual) cujo título contém uma das palavras.
     */
    private static function menu_url( array $needles ) {
        $locations = get_nav_menu_locations();
        foreach ( array( Bireme_Layout::MENU_PRIMARY, Bireme_Layout::MENU_TOP ) as $location ) {
            if ( empty( $locations[ $location ] ) ) {
                continue;
            }
            $items = wp_get_nav_menu_items( $locations[ $location ] );
            foreach ( (array) $items as $item ) {
                $title = strtolower( remove_accents( wp_strip_all_tags( (string) $item->title ) ) );
                foreach ( $needles as $needle ) {
                    if ( false !== strpos( $title, $needle ) && ! empty( $item->url ) ) {
                        return (string) $item->url;
                    }
                }
            }
        }
        return '';
    }

    /**
     * Endereços do site da OPAS (paho.org) no idioma da página: /pt, /es ou /en.
     */
    public static function localize_url( $url ) {
        $url  = (string) $url;
        $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
        if ( 'www.paho.org' !== $host && 'paho.org' !== $host ) {
            return $url;
        }
        $code = Bireme_I18n::lang_slug();
        $code = '' !== $code ? $code : 'pt';
        $parts = wp_parse_url( $url );
        $path  = isset( $parts['path'] ) ? (string) $parts['path'] : '';
        if ( preg_match( '#^/(pt|es|en)(/|$)#', $path ) ) {
            $path = (string) preg_replace( '#^/(pt|es|en)(?=/|$)#', '/' . $code, $path );
        } elseif ( '' === $path || '/' === $path ) {
            $path = '/' . $code;
        } else {
            return $url;
        }
        $scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : 'https';
        $query  = isset( $parts['query'] ) ? '?' . $parts['query'] : '';
        $hash   = isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';
        return $scheme . '://' . $parts['host'] . $path . $query . $hash;
    }

    /**
     * Logo da faixa azul do rodapé no modelo "Faixa branca": [ url, is_default ].
     */
    public static function footer_brand_logo() {
        return self::logo( 'band' );
    }

    /**
     * URL do logo do rodapé no idioma atual.
     */
    public static function footer_logo() {
        $logo = self::logo( 'footer' );
        return $logo[0];
    }

    /**
     * Links da barra superior definidos na tela de ajustes (uma linha por link: Texto|URL).
     * Usados quando não há menu atribuído à posição "Bireme Builder: barra superior".
     *
     * @return array Lista de [ text, url ].
     */
    public static function top_links() {
        return self::parse_links( 'top_links' );
    }

    /**
     * Links de idioma usados quando o Polylang não fornece ao menos dois idiomas.
     * URLs relativas (ex.: /en/) são resolvidas a partir da home do site.
     *
     * @return array Lista de [ text, url ].
     */
    public static function language_links() {
        $links = array();
        foreach ( self::parse_links( 'language_links' ) as $link ) {
            if ( '' !== $link['url'] && 0 === strpos( $link['url'], '/' ) && 0 !== strpos( $link['url'], '//' ) ) {
                $link['url'] = home_url( $link['url'] );
            }
            $links[] = $link;
        }
        return $links;
    }

    private static function parse_links( $key ) {
        $override = self::override( $key );
        $settings = self::get();
        $value    = '' !== $override ? $override : (string) $settings[ $key ];
        $links    = array();
        foreach ( preg_split( '/\r\n|\r|\n/', $value ) as $line ) {
            $parts = array_map( 'trim', explode( '|', $line, 2 ) );
            if ( '' === $parts[0] ) {
                continue;
            }
            $text    = ( '' === $override ) ? Bireme_I18n::preferred( $parts[0] ) : $parts[0];
            $links[] = array(
                'source' => $parts[0],
                'text' => $text,
                'url'  => isset( $parts[1] ) ? $parts[1] : '',
            );
        }
        return $links;
    }

    public static function get() {
        $saved = get_option( self::OPTION, array() );
        return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
    }

    /**
     * Texto no idioma atual: o personalizado para o idioma ou a tradução do Polylang.
     */
    public static function text( $key ) {
        $override = self::override( $key );
        if ( '' !== $override ) {
            return $override;
        }
        $settings = self::get();
        $value    = isset( $settings[ $key ] ) ? (string) $settings[ $key ] : '';
        return Bireme_I18n::preferred( $value );
    }

    public function add_page() {
        add_options_page(
            __( 'Bireme Builder', 'bireme-builder' ),
            __( 'Bireme Builder', 'bireme-builder' ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register() {
        register_setting( self::PAGE_SLUG, self::OPTION, array(
            'type'              => 'array',
            'sanitize_callback' => array( $this, 'sanitize' ),
            'default'           => self::defaults(),
        ) );

        add_settings_section( 'bireme_header_style', __( 'Modelo do header', 'bireme-builder' ), '__return_false', self::PAGE_SLUG );

        add_settings_field( 'bireme_header_style', __( 'Modelo', 'bireme-builder' ), array( $this, 'render_header_style_field' ), self::PAGE_SLUG, 'bireme_header_style' );
        add_settings_field( 'bireme_global_chrome', __( 'Em todo o site', 'bireme-builder' ), function () {
            $settings = Bireme_Settings::get();
            printf(
                '<label><input type="checkbox" id="bireme_global_chrome" name="%1$s[global_chrome]" value="1"%2$s> %3$s</label><p class="description">%4$s</p>',
                esc_attr( Bireme_Settings::OPTION ),
                checked( ! empty( $settings['global_chrome'] ), true, false ),
                esc_html__( 'Usar o header e o rodapé Bireme também nas páginas feitas pelo tema', 'bireme-builder' ),
                esc_html__( 'O header e o rodapé do tema são trocados pelos do plugin em qualquer página (posts de outros tipos, páginas sem seções, busca, arquivos etc.). O conteúdo do tema entre eles continua igual.', 'bireme-builder' )
            );
        }, self::PAGE_SLUG, 'bireme_header_style' );
        $this->add_text_field( 'header_logo_link', __( 'Link do logo do topo', 'bireme-builder' ), 'bireme_header_style', __( 'Deixe vazio para levar à página inicial.', 'bireme-builder' ) );

        add_settings_section( 'bireme_brand', __( 'Logos e cores', 'bireme-builder' ), array( $this, 'brand_intro' ), self::PAGE_SLUG );

        add_settings_field( 'bireme_logos', __( 'Logos por idioma', 'bireme-builder' ), array( $this, 'render_logos_field' ), self::PAGE_SLUG, 'bireme_brand' );
        add_settings_field( 'bireme_header_bg', __( 'Fundo do header', 'bireme-builder' ), array( $this, 'render_background_field' ), self::PAGE_SLUG, 'bireme_brand', array(
            'color' => 'header_bg_color',
            'image' => 'header_bg_image',
            'none'  => 'header_bg_none',
            'help'  => __( 'Faixa azul do header (nos dois modelos). Sem imagem própria, usa o fundo com o triângulo ciano à esquerda e as faixas diagonais ciano e laranja à direita.', 'bireme-builder' ),
        ) );
        add_settings_field( 'bireme_header_bar', __( 'Faixa do logo', 'bireme-builder' ), array( $this, 'render_background_field' ), self::PAGE_SLUG, 'bireme_brand', array(
            'color' => 'header_bar_color',
            'help'  => __( 'Faixa de cima do modelo "Faixa branca com logo", onde ficam o logo, os idiomas e a busca.', 'bireme-builder' ),
        ) );
        add_settings_field( 'bireme_footer_bg', __( 'Fundo do rodapé', 'bireme-builder' ), array( $this, 'render_background_field' ), self::PAGE_SLUG, 'bireme_brand', array(
            'color' => 'footer_bg_color',
            'help'  => __( 'Rodapé "Clássico".', 'bireme-builder' ),
        ) );
        add_settings_field( 'bireme_footer_band', __( 'Fundo do rodapé azul', 'bireme-builder' ), array( $this, 'render_background_field' ), self::PAGE_SLUG, 'bireme_brand', array(
            'color' => 'footer_band_color',
            'image' => 'footer_band_image',
            'none'  => 'footer_band_none',
            'help'  => __( 'Modelo "Fundo azul com triângulos". Sem imagem própria, usa o fundo azul-marinho com triângulos ciano e laranja nas laterais.', 'bireme-builder' ),
        ) );

        add_settings_section( 'bireme_header', __( 'Header', 'bireme-builder' ), array( $this, 'section_intro' ), self::PAGE_SLUG );

        $this->add_text_field( 'title', __( 'Título', 'bireme-builder' ) );
        $this->add_text_field( 'subtitle', __( 'Subtítulo', 'bireme-builder' ) );
        $this->add_text_field( 'search_placeholder', __( 'Texto do campo de busca', 'bireme-builder' ) );
        $this->add_textarea_field( 'top_links', __( 'Links da barra superior', 'bireme-builder' ), __( 'Um link por linha, no formato Texto|URL. Ignorado se houver um menu na posição "Bireme Builder: barra superior".', 'bireme-builder' ) );
        $this->add_checkbox_field( 'show_search', __( 'Exibir busca', 'bireme-builder' ) );
        $this->add_checkbox_field( 'show_languages', __( 'Exibir seletor de idiomas (Polylang)', 'bireme-builder' ) );
        $this->add_textarea_field( 'language_links', __( 'Idiomas (alternativa)', 'bireme-builder' ), __( 'Um idioma por linha, no formato Nome|URL. Usado quando o Polylang não tem ao menos dois idiomas cadastrados. URLs começando com / são relativas à home.', 'bireme-builder' ) );

        add_settings_section( 'bireme_footer', __( 'Rodapé', 'bireme-builder' ), array( $this, 'footer_intro' ), self::PAGE_SLUG );

        add_settings_field( 'bireme_footer_style', __( 'Modelo do rodapé', 'bireme-builder' ), array( $this, 'render_footer_style_field' ), self::PAGE_SLUG, 'bireme_footer' );
        $this->add_text_field( 'footer_col1_title', __( 'Coluna 1: título', 'bireme-builder' ), 'bireme_footer', __( 'Modelo "Fundo azul com triângulos".', 'bireme-builder' ) );
        $this->add_textarea_field( 'footer_col1_links', __( 'Coluna 1: links', 'bireme-builder' ), __( 'Um link por linha, no formato Texto|URL. URLs começando com / são relativas à home de cada idioma (ex.: Início|/). Ignorado se houver um menu na posição "Bireme Builder: rodapé — coluna 1".', 'bireme-builder' ), 'bireme_footer' );
        $this->add_text_field( 'footer_col2_title', __( 'Coluna 2: título', 'bireme-builder' ), 'bireme_footer' );
        $this->add_textarea_field( 'footer_col2_links', __( 'Coluna 2: links', 'bireme-builder' ), __( 'Um link por linha, no formato Texto|URL. Ignorado se houver um menu na posição "Bireme Builder: rodapé — coluna 2".', 'bireme-builder' ), 'bireme_footer' );
        $this->add_text_field( 'footer_col3_title', __( 'Projetos institucionais: título', 'bireme-builder' ), 'bireme_footer' );
        $this->add_textarea_field( 'footer_col3_links', __( 'Projetos institucionais: links', 'bireme-builder' ), __( 'Um projeto por linha, no formato Nome|URL. Ignorado se houver um menu na posição "Bireme Builder: rodapé — projetos institucionais". Deixe vazio para esconder a coluna.', 'bireme-builder' ), 'bireme_footer' );

        $this->add_text_field( 'footer_logo_link', __( 'Link do logo', 'bireme-builder' ), 'bireme_footer', __( 'Os logos do rodapé são escolhidos em "Logos e cores".', 'bireme-builder' ) );
        $this->add_text_field( 'footer_title', __( 'Título', 'bireme-builder' ), 'bireme_footer' );
        $this->add_text_field( 'footer_text', __( 'Texto', 'bireme-builder' ), 'bireme_footer' );
        $this->add_text_field( 'footer_copyright', __( 'Copyright', 'bireme-builder' ), 'bireme_footer', __( 'Use {ano} para o ano atual.', 'bireme-builder' ) );
        $this->add_textarea_field( 'footer_links', __( 'Links do rodapé', 'bireme-builder' ), __( 'Um link por linha, no formato Texto|URL. Ignorado se houver um menu na posição "Bireme Builder: rodapé".', 'bireme-builder' ), 'bireme_footer' );
        foreach ( self::social_networks() as $network => $label ) {
            $this->add_text_field( 'social_' . $network, $label, 'bireme_footer', '' );
        }
        foreach ( self::extra_languages() as $slug => $name ) {
            /* translators: %s: nome do idioma */
            add_settings_field( 'bireme_footer_i18n_' . $slug, sprintf( __( 'Rodapé em %s', 'bireme-builder' ), $name ), array( $this, 'render_footer_language_field' ), self::PAGE_SLUG, 'bireme_footer', array( 'lang' => $slug ) );
        }

        add_settings_section( 'bireme_archive', __( 'Página de arquivo', 'bireme-builder' ), array( $this, 'archive_intro' ), self::PAGE_SLUG );

        $this->add_checkbox_field( 'archive_enabled', __( 'Usar o layout Bireme nos arquivos', 'bireme-builder' ), 'bireme_archive' );
        add_settings_field( 'bireme_archive_page', __( 'Página modelo', 'bireme-builder' ), array( $this, 'render_page_field' ), self::PAGE_SLUG, 'bireme_archive', array( 'label_for' => 'bireme_archive_page', 'key' => 'archive_page' ) );
        $this->add_text_field( 'archive_title', __( 'Título da listagem geral', 'bireme-builder' ), 'bireme_archive', __( 'Exibido na página de posts (blog). Categorias, tags e busca usam o próprio nome.', 'bireme-builder' ) );
        $this->add_text_field( 'archive_label', __( 'Nome no breadcrumb', 'bireme-builder' ), 'bireme_archive', __( 'Ex.: Início > Edições > Categoria.', 'bireme-builder' ) );
        add_settings_field( 'bireme_archive_label_page', __( 'Link do nome no breadcrumb', 'bireme-builder' ), array( $this, 'render_page_field' ), self::PAGE_SLUG, 'bireme_archive', array(
            'label_for' => 'bireme_archive_label_page',
            'key'       => 'archive_label_page',
            'none'      => __( '— Página de posts (padrão) —', 'bireme-builder' ),
            'help'      => __( 'Página aberta ao clicar em "Edições" no breadcrumb dos posts e dos arquivos. Com Polylang, a tradução da página é usada em cada idioma.', 'bireme-builder' ),
        ) );

        add_settings_section( 'bireme_single', __( 'Página do post', 'bireme-builder' ), array( $this, 'single_intro' ), self::PAGE_SLUG );

        $this->add_checkbox_field( 'single_enabled', __( 'Usar o layout Bireme nos posts', 'bireme-builder' ), 'bireme_single' );
        add_settings_field( 'bireme_single_page', __( 'Página modelo', 'bireme-builder' ), array( $this, 'render_page_field' ), self::PAGE_SLUG, 'bireme_single', array( 'label_for' => 'bireme_single_page', 'key' => 'single_page' ) );
        add_settings_field( 'bireme_single_edition_tax', __( 'Taxonomia da edição', 'bireme-builder' ), array( $this, 'render_taxonomy_field' ), self::PAGE_SLUG, 'bireme_single', array(
            'label_for' => 'bireme_single_edition_tax',
            'key'       => 'single_edition_tax',
            'help'      => __( 'Termo exibido como título do topo, no breadcrumb e acima do título do post (ex.: Boletim BIREME n. 112).', 'bireme-builder' ),
        ) );
        add_settings_field( 'bireme_single_topic_tax', __( 'Taxonomia do tema', 'bireme-builder' ), array( $this, 'render_taxonomy_field' ), self::PAGE_SLUG, 'bireme_single', array(
            'label_for' => 'bireme_single_topic_tax',
            'key'       => 'single_topic_tax',
            'help'      => __( 'Termo exibido na linha de informações do post (ex.: Cooperação Técnica).', 'bireme-builder' ),
        ) );

        add_settings_section( 'bireme_notfound', __( 'Página 404', 'bireme-builder' ), array( $this, 'notfound_intro' ), self::PAGE_SLUG );

        $this->add_checkbox_field( 'notfound_enabled', __( 'Usar o layout Bireme na página 404', 'bireme-builder' ), 'bireme_notfound' );
        $this->add_text_field( 'notfound_title', __( 'Título', 'bireme-builder' ), 'bireme_notfound' );
        $this->add_textarea_field( 'notfound_text', __( 'Texto', 'bireme-builder' ), '', 'bireme_notfound' );
        add_settings_field( 'bireme_notfound_page', __( 'Página modelo', 'bireme-builder' ), array( $this, 'render_page_field' ), self::PAGE_SLUG, 'bireme_notfound', array(
            'label_for' => 'bireme_notfound_page',
            'key'       => 'notfound_page',
            'none'      => __( '— Nenhuma (artigos mais recentes) —', 'bireme-builder' ),
            'help'      => __( 'Seções exibidas abaixo da mensagem. Monte uma página no builder (ela não precisa estar no menu). Com Polylang, a tradução da página é usada em cada idioma.', 'bireme-builder' ),
        ) );

        if ( function_exists( 'pll_register_string' ) ) {
            $settings = self::get();
            pll_register_string( 'bireme_notfound_title', $settings['notfound_title'], self::PLL_GROUP );
            pll_register_string( 'bireme_notfound_text', $settings['notfound_text'], self::PLL_GROUP, true );
            pll_register_string( 'bireme_header_title', $settings['title'], self::PLL_GROUP );
            pll_register_string( 'bireme_header_subtitle', $settings['subtitle'], self::PLL_GROUP );
            pll_register_string( 'bireme_header_search', $settings['search_placeholder'], self::PLL_GROUP );
            pll_register_string( 'bireme_footer_title', $settings['footer_title'], self::PLL_GROUP );
            pll_register_string( 'bireme_footer_text', $settings['footer_text'], self::PLL_GROUP );
            pll_register_string( 'bireme_footer_copyright', $settings['footer_copyright'], self::PLL_GROUP );
            pll_register_string( 'bireme_archive_title', $settings['archive_title'], self::PLL_GROUP );
            pll_register_string( 'bireme_archive_label', $settings['archive_label'], self::PLL_GROUP );
            pll_register_string( 'bireme_footer_col1_title', $settings['footer_col1_title'], self::PLL_GROUP );
            pll_register_string( 'bireme_footer_col2_title', $settings['footer_col2_title'], self::PLL_GROUP );
            pll_register_string( 'bireme_footer_col3_title', $settings['footer_col3_title'], self::PLL_GROUP );
            foreach ( array( 'top_links' => 'bireme_header_top_link_', 'footer_links' => 'bireme_footer_link_', 'footer_col1_links' => 'bireme_footer_col1_link_', 'footer_col2_links' => 'bireme_footer_col2_link_', 'footer_col3_links' => 'bireme_footer_col3_link_' ) as $key => $prefix ) {
                foreach ( preg_split( '/\r\n|\r|\n/', (string) $settings[ $key ] ) as $i => $line ) {
                    $parts = array_map( 'trim', explode( '|', $line, 2 ) );
                    if ( '' !== $parts[0] ) {
                        pll_register_string( $prefix . $i, $parts[0], self::PLL_GROUP );
                    }
                }
            }
        }
    }

    public function archive_intro() {
        echo '<p>' . esc_html__( 'Layout da página de posts (blog), categorias, tags, datas, autores e busca: header e rodapé Bireme, título com breadcrumb e as seções da página modelo.', 'bireme-builder' ) . '</p>';
        echo '<p>' . esc_html__( 'Monte a página modelo no builder com a seção "Lista de publicações" (ex.: Colunas 75-25 com a lista à esquerda e Temas em Destaque, Mais Lidas e Newsletter à direita). Sem página modelo, usa o layout padrão: a lista à esquerda e a barra lateral (Temas em Destaque com os posts escolhidos no post ou os mais recentes, Mais Lidas e Newsletter) à direita.', 'bireme-builder' ) . '</p>';
    }

    public function notfound_intro() {
        echo '<p>' . esc_html__( 'Página exibida quando um endereço não existe: header e rodapé Bireme, mensagem com busca e atalhos para o início e para todas as edições, e abaixo as seções da página modelo (sem ela, os artigos mais recentes). Espanhol e inglês já vêm traduzidos.', 'bireme-builder' ) . '</p>';
    }

    public function single_intro() {
        echo '<p>' . esc_html__( 'Layout dos posts: header e rodapé Bireme, título da edição com breadcrumb e as seções da página modelo.', 'bireme-builder' ) . '</p>';
        echo '<p>' . esc_html__( 'Monte a página modelo com as seções "Conteúdo do post" e "Posts relacionados" (ex.: Colunas 75-25 com elas à esquerda e Temas em Destaque, Mais Lidas e Newsletter à direita). Sem página modelo, usa o layout padrão: conteúdo e relacionados à esquerda e a barra lateral (Temas em Destaque com os posts escolhidos no post ou os mais recentes, Mais Lidas e Newsletter) à direita.', 'bireme-builder' ) . '</p>';
    }

    public function render_page_field( $args ) {
        $settings = self::get();
        $key      = $args['key'];
        wp_dropdown_pages( array(
            'id'                => 'bireme_' . $key,
            'name'              => self::OPTION . '[' . $key . ']',
            'selected'          => absint( $settings[ $key ] ),
            'show_option_none'  => isset( $args['none'] ) ? esc_html( $args['none'] ) : esc_html__( '— Nenhuma (layout padrão) —', 'bireme-builder' ),
            'option_none_value' => '0',
        ) );
        if ( ! empty( $args['help'] ) ) {
            echo '<p class="description">' . esc_html( $args['help'] ) . '</p>';
        } elseif ( function_exists( 'pll_get_post' ) ) {
            echo '<p class="description">' . esc_html__( 'Com Polylang, a tradução da página modelo é usada automaticamente em cada idioma.', 'bireme-builder' ) . '</p>';
        }
    }

    public function render_taxonomy_field( $args ) {
        $settings = self::get();
        $key      = $args['key'];
        printf( '<select id="bireme_%1$s" name="%2$s[%1$s]">', esc_attr( $key ), esc_attr( self::OPTION ) );
        foreach ( self::post_taxonomy_options() as $value => $label ) {
            printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $settings[ $key ], $value, false ), esc_html( $label ) );
        }
        echo '</select>';
        if ( ! empty( $args['help'] ) ) {
            echo '<p class="description">' . esc_html( $args['help'] ) . '</p>';
        }
    }

    public function footer_intro() {
        $menus_url = admin_url( 'nav-menus.php?action=locations' );
        echo '<p>' . esc_html__( 'Rodapé exibido nas páginas que usam o Bireme Builder. Redes sociais sem URL não são exibidas.', 'bireme-builder' ) . '</p>';
        echo '<p>' . sprintf(
            /* translators: %s: link para Aparência > Menus */
            esc_html__( 'Os menus do rodapé são editados em %s. Crie um menu e marque a posição: "Bireme Builder: rodapé" (links ao lado do logo), "Bireme Builder: rodapé — coluna 1" ou "Bireme Builder: rodapé — coluna 2". Com Polylang, cada idioma tem o seu menu.', 'bireme-builder' ),
            '<a href="' . esc_url( $menus_url ) . '">' . esc_html__( 'Aparência > Menus', 'bireme-builder' ) . '</a>'
        ) . '</p>';
        if ( self::extra_languages() ) {
            echo '<p>' . esc_html__( 'Os campos abaixo valem para o idioma padrão. Em "Rodapé em ..." personalize textos e links de cada idioma; campos vazios usam o valor do idioma padrão (com a tradução de Idiomas > Traduções de strings, quando houver). Um menu atribuído à posição substitui os links escritos no campo.', 'bireme-builder' ) . '</p>';
        }
    }

    public function brand_intro() {
        echo '<p>' . esc_html__( 'Campo de logo vazio usa o logo oficial da BIREME no idioma da página (logos.bireme.org/img/pt, /en ou /es). Para trocar, cole o endereço de outra imagem ou clique em "Escolher".', 'bireme-builder' ) . '</p>';
    }

    public function render_logos_field() {
        $settings = self::get();
        $kinds    = self::logo_kinds();
        echo '<table class="widefat striped bireme-logos" style="max-width:1100px;"><thead><tr><th style="width:120px;">' . esc_html__( 'Idioma', 'bireme-builder' ) . '</th>';
        foreach ( $kinds as $kind ) {
            echo '<th>' . esc_html( $kind[0] ) . '<br><span style="font-weight:400;color:#646970;font-size:12px;">' . esc_html( $kind[1] ) . '</span></th>';
        }
        echo '</tr></thead><tbody>';
        foreach ( self::logo_languages() as $lang => $name ) {
            $default = self::default_logo_url( $lang );
            echo '<tr><th scope="row"><strong>' . esc_html( $name ) . '</strong></th>';
            foreach ( array_keys( $kinds ) as $kind ) {
                $id    = 'bireme_logo_' . sanitize_key( $lang ) . '_' . $kind;
                $value = self::saved_logo( $kind, $lang );
                $shown = '' !== $value ? $value : $default;
                $band  = 'band' === $kind;
                echo '<td>';
                printf(
                    '<span class="bireme-logo-preview" style="display:flex;align-items:center;height:56px;margin-bottom:6px;padding:6px 8px;border-radius:4px;background:%1$s;"><img src="%2$s" alt="" data-default="%3$s" style="max-width:100%%;max-height:44px;%4$s"></span>',
                    $band ? '#0D4EA6' : '#F6F7F7',
                    esc_url( $shown ),
                    esc_url( $default ),
                    ( $band && '' === $value ) ? 'filter:brightness(0) invert(1);' : ''
                );
                printf(
                    '<input type="url" class="large-text bireme-logo-input" id="%1$s" name="%2$s[logos][%3$s][%4$s]" value="%5$s" placeholder="%6$s"%7$s>',
                    esc_attr( $id ),
                    esc_attr( self::OPTION ),
                    esc_attr( $lang ),
                    esc_attr( $kind ),
                    esc_attr( $value ),
                    esc_attr( $default ),
                    $band ? ' data-band="1"' : ''
                );
                printf(
                    '<p style="margin:6px 0 0;"><button type="button" class="button bireme-media-pick" data-target="%1$s">%2$s</button> <button type="button" class="button-link bireme-media-clear" data-target="%1$s">%3$s</button></p>',
                    esc_attr( $id ),
                    esc_html__( 'Escolher', 'bireme-builder' ),
                    esc_html__( 'Usar o padrão', 'bireme-builder' )
                );
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    public function render_background_field( $args ) {
        $settings = self::get();
        $defaults = self::defaults();
        $color    = $args['color'];
        $value    = sanitize_hex_color( (string) $settings[ $color ] );
        printf(
            '<input type="color" id="bireme_%1$s" name="%2$s[%1$s]" value="%3$s" style="width:64px;height:32px;padding:2px;vertical-align:middle;"> <button type="button" class="button-link bireme-color-reset" data-target="bireme_%1$s" data-default="%4$s">%5$s</button>',
            esc_attr( $color ),
            esc_attr( self::OPTION ),
            esc_attr( $value ? $value : $defaults[ $color ] ),
            esc_attr( $defaults[ $color ] ),
            esc_html__( 'Cor padrão', 'bireme-builder' )
        );
        if ( ! empty( $args['image'] ) ) {
            $image = $args['image'];
            $none  = $args['none'];
            printf(
                '<p style="margin:10px 0 0;"><label for="bireme_%1$s">%2$s</label><br><input type="url" class="regular-text" id="bireme_%1$s" name="%3$s[%1$s]" value="%4$s" placeholder="%5$s"> <button type="button" class="button bireme-media-pick" data-target="bireme_%1$s">%6$s</button></p>',
                esc_attr( $image ),
                esc_html__( 'Imagem de fundo (opcional)', 'bireme-builder' ),
                esc_attr( self::OPTION ),
                esc_attr( (string) $settings[ $image ] ),
                esc_attr__( 'Vazio: faixa padrão com triângulos', 'bireme-builder' ),
                esc_html__( 'Escolher', 'bireme-builder' )
            );
            printf(
                '<p style="margin:6px 0 0;"><label><input type="checkbox" name="%1$s[%2$s]" value="1"%3$s> %4$s</label></p>',
                esc_attr( self::OPTION ),
                esc_attr( $none ),
                checked( ! empty( $settings[ $none ] ), true, false ),
                esc_html__( 'Sem imagem de fundo (usar só a cor)', 'bireme-builder' )
            );
        }
        if ( ! empty( $args['help'] ) ) {
            echo '<p class="description">' . esc_html( $args['help'] ) . '</p>';
        }
    }

    /**
     * Seletor de mídia e prévia dos logos na tela de configurações.
     */
    public function enqueue_admin( $hook ) {
        if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }
        wp_enqueue_media();
        $js = <<<'JS'
jQuery(function ($) {
	function preview($input) {
		var $img = $input.closest('td').find('.bireme-logo-preview img');
		if (!$img.length) { return; }
		var custom = $.trim($input.val());
		$img.attr('src', custom || $img.data('default'));
		$img.css('filter', ($input.data('band') && !custom) ? 'brightness(0) invert(1)' : '');
	}
	$(document).on('input change', '.bireme-logo-input', function () { preview($(this)); });
	$(document).on('click', '.bireme-media-clear', function (e) {
		e.preventDefault();
		preview($('#' + $(this).data('target')).val(''));
	});
	$(document).on('click', '.bireme-color-reset', function (e) {
		e.preventDefault();
		$('#' + $(this).data('target')).val($(this).data('default'));
	});
	$(document).on('click', '.bireme-media-pick', function (e) {
		e.preventDefault();
		var $input = $('#' + $(this).data('target'));
		var frame = wp.media({ title: $(this).text(), library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var file = frame.state().get('selection').first().toJSON();
			$input.val(file.url).trigger('change');
		});
		frame.open();
	});
});
JS;
        wp_add_inline_script( 'media-editor', $js );
    }

    public function render_footer_language_field( $args ) {
        $lang     = $args['lang'];
        $settings = self::get();
        $values   = isset( $settings['footer_i18n'][ $lang ] ) ? (array) $settings['footer_i18n'][ $lang ] : array();
        echo '<details class="bireme-footer-lang"' . ( array_filter( $values ) ? ' open' : '' ) . '><summary style="cursor:pointer;font-weight:600;margin-bottom:8px;">' . esc_html__( 'Personalizar', 'bireme-builder' ) . '</summary>';
        echo '<table class="form-table" role="presentation" style="margin-top:0;"><tbody>';
        foreach ( self::footer_i18n_fields() as $key => $field ) {
            $id    = 'bireme_footer_i18n_' . $lang . '_' . $key;
            $name  = self::OPTION . '[footer_i18n][' . $lang . '][' . $key . ']';
            $value = isset( $values[ $key ] ) ? (string) $values[ $key ] : '';
            $main  = isset( $settings[ $key ] ) ? (string) $settings[ $key ] : '';
            echo '<tr><th scope="row" style="padding:8px 10px 8px 0;font-weight:400;"><label for="' . esc_attr( $id ) . '">' . esc_html( $field[0] ) . '</label></th><td style="padding:8px 0;">';
            if ( 'links' === $field[1] ) {
                printf( '<textarea class="large-text code" rows="4" id="%1$s" name="%2$s" placeholder="%3$s">%4$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_attr( $main ), esc_textarea( $value ) );
            } else {
                printf( '<input type="%1$s" class="regular-text" id="%2$s" name="%3$s" value="%4$s" placeholder="%5$s">', 'url' === $field[1] ? 'url' : 'text', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( $main ) );
            }
            echo '</td></tr>';
        }
        echo '</tbody></table></details>';
    }

    private static function sanitize_footer_i18n( $input ) {
        $output    = array();
        $languages = self::extra_languages();
        foreach ( is_array( $input ) ? $input : array() as $lang => $values ) {
            $lang = sanitize_key( $lang );
            if ( ! isset( $languages[ $lang ] ) || ! is_array( $values ) ) {
                continue;
            }
            foreach ( self::footer_i18n_fields() as $key => $field ) {
                $value = isset( $values[ $key ] ) ? (string) $values[ $key ] : '';
                if ( 'links' === $field[1] ) {
                    $value = self::sanitize_top_links( $value );
                } elseif ( 'url' === $field[1] ) {
                    $value = esc_url_raw( trim( $value ) );
                } else {
                    $value = sanitize_text_field( $value );
                }
                if ( '' !== $value ) {
                    $output[ $lang ][ $key ] = $value;
                }
            }
        }
        return $output;
    }

    public function sanitize( $input ) {
        $input  = is_array( $input ) ? $input : array();
        $output = array(
            'header_style'       => ( isset( $input['header_style'] ) && isset( self::header_styles()[ $input['header_style'] ] ) ) ? $input['header_style'] : 'classic',
            'header_logo_link'   => isset( $input['header_logo_link'] ) ? esc_url_raw( trim( $input['header_logo_link'] ) ) : '',
            'title'              => isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '',
            'subtitle'           => isset( $input['subtitle'] ) ? sanitize_text_field( $input['subtitle'] ) : '',
            'search_placeholder' => isset( $input['search_placeholder'] ) ? sanitize_text_field( $input['search_placeholder'] ) : '',
            'show_search'        => ! empty( $input['show_search'] ),
            'show_languages'     => ! empty( $input['show_languages'] ),
            'top_links'          => isset( $input['top_links'] ) ? self::sanitize_top_links( $input['top_links'] ) : '',
            'language_links'     => isset( $input['language_links'] ) ? self::sanitize_top_links( $input['language_links'] ) : '',
            'footer_logo_link'   => isset( $input['footer_logo_link'] ) ? esc_url_raw( trim( $input['footer_logo_link'] ) ) : '',
            'footer_title'       => isset( $input['footer_title'] ) ? sanitize_text_field( $input['footer_title'] ) : '',
            'footer_text'        => isset( $input['footer_text'] ) ? sanitize_text_field( $input['footer_text'] ) : '',
            'footer_copyright'   => isset( $input['footer_copyright'] ) ? sanitize_text_field( $input['footer_copyright'] ) : '',
            'footer_links'       => isset( $input['footer_links'] ) ? self::sanitize_top_links( $input['footer_links'] ) : '',
            'footer_style'       => ( isset( $input['footer_style'] ) && isset( self::footer_styles()[ $input['footer_style'] ] ) ) ? $input['footer_style'] : 'classic',
            'footer_col1_title'  => isset( $input['footer_col1_title'] ) ? sanitize_text_field( $input['footer_col1_title'] ) : '',
            'footer_col1_links'  => isset( $input['footer_col1_links'] ) ? self::sanitize_top_links( $input['footer_col1_links'] ) : '',
            'footer_col2_title'  => isset( $input['footer_col2_title'] ) ? sanitize_text_field( $input['footer_col2_title'] ) : '',
            'footer_col2_links'  => isset( $input['footer_col2_links'] ) ? self::sanitize_top_links( $input['footer_col2_links'] ) : '',
            'footer_col3_title'  => isset( $input['footer_col3_title'] ) ? sanitize_text_field( $input['footer_col3_title'] ) : '',
            'footer_col3_links'  => isset( $input['footer_col3_links'] ) ? self::sanitize_top_links( $input['footer_col3_links'] ) : '',
        );
        foreach ( array_keys( self::social_networks() ) as $network ) {
            $output[ 'social_' . $network ] = isset( $input[ 'social_' . $network ] ) ? esc_url_raw( trim( $input[ 'social_' . $network ] ) ) : '';
        }
        $output['footer_i18n']     = self::sanitize_footer_i18n( isset( $input['footer_i18n'] ) ? $input['footer_i18n'] : array() );
        $output['global_chrome']    = ! empty( $input['global_chrome'] );
        $output['notfound_enabled'] = ! empty( $input['notfound_enabled'] );
        $output['notfound_page']    = isset( $input['notfound_page'] ) ? absint( $input['notfound_page'] ) : 0;
        $output['notfound_title']   = isset( $input['notfound_title'] ) ? sanitize_text_field( $input['notfound_title'] ) : '';
        $output['notfound_text']    = isset( $input['notfound_text'] ) ? sanitize_textarea_field( $input['notfound_text'] ) : '';
        $output['logos']           = self::sanitize_logos( isset( $input['logos'] ) ? $input['logos'] : array() );
        $defaults                  = self::defaults();
        foreach ( array( 'header_bg_color', 'header_bar_color', 'footer_bg_color', 'footer_band_color' ) as $key ) {
            $color          = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
            $output[ $key ] = $color ? strtoupper( $color ) : $defaults[ $key ];
        }
        foreach ( array( 'header_bg_image', 'footer_band_image' ) as $key ) {
            $output[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( trim( $input[ $key ] ) ) : '';
        }
        $output['header_bg_none']  = ! empty( $input['header_bg_none'] );
        $output['footer_band_none'] = ! empty( $input['footer_band_none'] );
        $output['archive_enabled'] = ! empty( $input['archive_enabled'] );
        $output['archive_page']    = isset( $input['archive_page'] ) ? absint( $input['archive_page'] ) : 0;
        $output['archive_title']   = isset( $input['archive_title'] ) ? sanitize_text_field( $input['archive_title'] ) : '';
        $output['archive_label']   = isset( $input['archive_label'] ) ? sanitize_text_field( $input['archive_label'] ) : '';
        $output['archive_label_page'] = isset( $input['archive_label_page'] ) ? absint( $input['archive_label_page'] ) : 0;
        $output['single_enabled']  = ! empty( $input['single_enabled'] );
        $output['single_page']     = isset( $input['single_page'] ) ? absint( $input['single_page'] ) : 0;
        $taxonomies                = self::post_taxonomy_options();
        foreach ( array( 'single_edition_tax', 'single_topic_tax' ) as $key ) {
            $value          = isset( $input[ $key ] ) ? sanitize_key( $input[ $key ] ) : '';
            $output[ $key ] = isset( $taxonomies[ $value ] ) ? $value : '';
        }
        return $output;
    }

    private static function sanitize_logos( $input ) {
        $output    = array();
        $languages = self::logo_languages();
        $kinds     = self::logo_kinds();
        foreach ( is_array( $input ) ? $input : array() as $lang => $values ) {
            $lang = sanitize_key( $lang );
            if ( ! isset( $languages[ $lang ] ) || ! is_array( $values ) ) {
                continue;
            }
            foreach ( array_keys( $kinds ) as $kind ) {
                $url = isset( $values[ $kind ] ) ? esc_url_raw( trim( (string) $values[ $kind ] ) ) : '';
                if ( '' !== $url && $url !== self::default_logo_url( $lang ) ) {
                    $output[ $lang ][ $kind ] = $url;
                }
            }
        }
        return $output;
    }

    private static function sanitize_top_links( $value ) {
        $lines = array();
        foreach ( preg_split( '/\r\n|\r|\n/', (string) $value ) as $line ) {
            $parts = array_map( 'trim', explode( '|', $line, 2 ) );
            $text  = sanitize_text_field( $parts[0] );
            if ( '' === $text ) {
                continue;
            }
            $url     = isset( $parts[1] ) ? esc_url_raw( $parts[1] ) : '';
            $lines[] = $url ? $text . '|' . $url : $text;
        }
        return implode( "\n", $lines );
    }

    public function section_intro() {
        $menus_url = admin_url( 'nav-menus.php?action=locations' );
        echo '<p>' . esc_html__( 'Header exibido nas páginas que usam o Bireme Builder (substitui o header e o footer do tema).', 'bireme-builder' ) . '</p>';
        echo '<p>' . sprintf(
            /* translators: %s: link para Aparência > Menus */
            esc_html__( 'Os links da barra superior e o menu principal são definidos em %s, nas posições "Bireme Builder: barra superior" e "Bireme Builder: menu principal".', 'bireme-builder' ),
            '<a href="' . esc_url( $menus_url ) . '">' . esc_html__( 'Aparência > Menus', 'bireme-builder' ) . '</a>'
        ) . '</p>';
        if ( function_exists( 'pll_register_string' ) ) {
            echo '<p>' . esc_html__( 'Espanhol e inglês já trazem a interface traduzida (títulos das seções, botões, busca, header e rodapé). Ao clonar a página, esses textos são gravados no idioma novo. O conteúdo das publicações não é alterado. Uma tradução em Idiomas > Traduções de strings substitui o texto pronto.', 'bireme-builder' ) . '</p>';
        }
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Bireme Builder', 'bireme-builder' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( self::PAGE_SLUG );
                do_settings_sections( self::PAGE_SLUG );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private function add_text_field( $key, $label, $section = 'bireme_header', $help = '' ) {
        add_settings_field( 'bireme_' . $key, $label, function () use ( $key, $help ) {
            $settings = Bireme_Settings::get();
            printf(
                '<input type="text" class="regular-text" id="bireme_%1$s" name="%2$s[%1$s]" value="%3$s">',
                esc_attr( $key ),
                esc_attr( Bireme_Settings::OPTION ),
                esc_attr( $settings[ $key ] )
            );
            if ( $help ) {
                echo '<p class="description">' . esc_html( $help ) . '</p>';
            }
        }, self::PAGE_SLUG, $section, array( 'label_for' => 'bireme_' . $key ) );
    }

    private function add_textarea_field( $key, $label, $help = '', $section = 'bireme_header' ) {
        add_settings_field( 'bireme_' . $key, $label, function () use ( $key, $help ) {
            $settings = Bireme_Settings::get();
            printf(
                '<textarea class="large-text code" rows="4" id="bireme_%1$s" name="%2$s[%1$s]">%3$s</textarea>',
                esc_attr( $key ),
                esc_attr( Bireme_Settings::OPTION ),
                esc_textarea( $settings[ $key ] )
            );
            if ( $help ) {
                echo '<p class="description">' . esc_html( $help ) . '</p>';
            }
        }, self::PAGE_SLUG, $section, array( 'label_for' => 'bireme_' . $key ) );
    }

    private function add_checkbox_field( $key, $label, $section = 'bireme_header' ) {
        add_settings_field( 'bireme_' . $key, $label, function () use ( $key ) {
            $settings = Bireme_Settings::get();
            printf(
                '<input type="checkbox" id="bireme_%1$s" name="%2$s[%1$s]" value="1"%3$s>',
                esc_attr( $key ),
                esc_attr( Bireme_Settings::OPTION ),
                checked( ! empty( $settings[ $key ] ), true, false )
            );
        }, self::PAGE_SLUG, $section, array( 'label_for' => 'bireme_' . $key ) );
    }
}
