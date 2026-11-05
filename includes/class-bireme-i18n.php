<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Traduções com o Polylang:
 *  - a interface (botões, títulos de seção, header e rodapé) já tem espanhol e inglês prontos;
 *  - ao clonar a página, esses textos são gravados no idioma novo;
 *  - no editor, os rótulos do builder seguem o idioma da página (es/en);
 *  - uma tradução feita em Idiomas > Traduções de strings tem prioridade;
 *  - o conteúdo das publicações não é traduzido aqui.
 */
class Bireme_I18n {

    const DOMAIN         = 'bireme-builder';
    const GROUP_FIXED    = 'Bireme Builder - Textos fixos';
    const GROUP_SECTIONS = 'Bireme Builder - Elementos';

    /** Traduz os rótulos do builder só na tela de edição de uma página es/en. */
    private static $editor_ui = false;

    /** @var string|null */
    private static $editor_slug = null;

    public function __construct() {
        add_filter( 'gettext_' . self::DOMAIN, array( $this, 'translate' ), 10, 2 );
        add_filter( 'ngettext_' . self::DOMAIN, array( $this, 'translate_plural' ), 10, 4 );
        add_action( 'pll_language_defined', array( 'Bireme_Sections', 'reset' ) );
        add_action( 'current_screen', array( $this, 'prepare_editor' ) );
        add_action( 'admin_init', array( $this, 'register_strings' ) );
        add_filter( 'pll_copy_post_metas', array( $this, 'copy_post_metas' ), 10, 2 );
        add_filter( 'pll_translate_post_meta', array( $this, 'translate_post_meta' ), 10, 3 );
        add_filter( 'bireme_builder_section_data', array( $this, 'translate_section_data' ) );
    }

    /**
     * Na edição de uma página em espanhol ou inglês, os rótulos do builder usam esse idioma.
     *
     * @param WP_Screen $screen
     */
    public function prepare_editor( $screen ) {
        if ( ! $screen || ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
            return;
        }
        if ( ! in_array( $screen->post_type, Bireme_Plugin::post_types(), true ) ) {
            return;
        }
        if ( '' === self::editor_lang() ) {
            return;
        }
        self::$editor_ui = true;
        Bireme_Sections::reset();
    }

    /**
     * Slug do Polylang da página em edição (ex.: es, es-ar, en). Vazio fora do editor.
     */
    private static function editor_polylang_slug() {
        if ( null !== self::$editor_slug ) {
            return self::$editor_slug;
        }
        self::$editor_slug = '';
        if ( ! is_admin() ) {
            return self::$editor_slug;
        }

        $lang = '';
        if ( ! empty( $_GET['new_lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $lang = sanitize_key( wp_unslash( $_GET['new_lang'] ) );
        } elseif ( function_exists( 'pll_get_post_language' ) ) {
            $post_id = 0;
            if ( ! empty( $_REQUEST['pll_post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $post_id = (int) $_REQUEST['pll_post_id'];
            } elseif ( ! empty( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $post_id = (int) $_GET['post'];
            } elseif ( ! empty( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $post_id = (int) $_POST['post_ID'];
            }
            if ( $post_id ) {
                $lang = (string) pll_get_post_language( $post_id, 'slug' );
            }
        }
        if ( '' === $lang && function_exists( 'pll_current_language' ) && function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
                $lang = (string) pll_current_language( 'slug' );
            }
        }
        self::$editor_slug = $lang;
        return self::$editor_slug;
    }

    /**
     * es ou en da página em edição. Vazio em português e fora do editor.
     */
    private static function editor_lang() {
        $slug = self::editor_polylang_slug();
        return '' === $slug ? '' : self::lang_slug( $slug );
    }

    /**
     * Rótulo do builder no idioma da página. A tradução manual do Polylang prevalece.
     */
    private static function editor_label( $text ) {
        $catalog_lang = self::editor_lang();
        $pll_lang     = self::editor_polylang_slug();
        if ( '' === $catalog_lang || ! is_string( $text ) || '' === $text ) {
            return $text;
        }
        if ( '' !== $pll_lang && function_exists( 'pll_translate_string' ) ) {
            $custom = pll_translate_string( $text, $pll_lang );
            if ( is_string( $custom ) && '' !== $custom && $custom !== $text ) {
                return $custom;
            }
        }
        return self::string( $text, $catalog_lang );
    }

    private static function active() {
        return function_exists( 'pll__' ) && ( ! is_admin() || wp_doing_ajax() );
    }

    /**
     * Tradução do Polylang, se existir; senão, a predefinida de es/en.
     */
    public static function preferred( $text ) {
        if ( ! is_string( $text ) || '' === $text ) {
            return $text;
        }
        if ( self::active() ) {
            $translated = pll__( $text );
            if ( is_string( $translated ) && '' !== $translated && $translated !== $text ) {
                return $translated;
            }
        }
        return self::string( $text );
    }

    public function translate( $translation, $text ) {
        if ( self::$editor_ui ) {
            $source = is_string( $text ) && '' !== $text ? $text : $translation;
            $label  = self::editor_label( $source );
            return $label !== $source ? $label : $translation;
        }
        if ( is_admin() && ! wp_doing_ajax() ) {
            return $translation;
        }
        return self::preferred( $translation );
    }

    public function translate_plural( $translation, $single, $plural, $number ) {
        if ( self::$editor_ui ) {
            $source = 1 === (int) $number ? $single : $plural;
            $label  = self::editor_label( $source );
            return $label !== $source ? $label : $translation;
        }
        if ( is_admin() && ! wp_doing_ajax() ) {
            return $translation;
        }
        return self::preferred( $translation );
    }

    /**
     * Texto da interface no idioma pedido. Português devolve o original.
     */
    public static function string( $text, $lang = null ) {
        if ( ! is_string( $text ) || '' === trim( $text ) ) {
            return $text;
        }
        $slug = self::lang_slug( $lang );
        if ( '' === $slug ) {
            return $text;
        }
        $catalog = self::catalog();
        $trim    = trim( $text );
        if ( isset( $catalog[ $trim ][ $slug ] ) ) {
            return $catalog[ $trim ][ $slug ];
        }
        $plain = trim( wp_strip_all_tags( $trim ) );
        if ( $plain !== $trim && isset( $catalog[ $plain ][ $slug ] ) ) {
            return $catalog[ $plain ][ $slug ];
        }
        return $text;
    }

    /**
     * Slug es ou en. Vazio para português e idiomas sem catálogo.
     */
    public static function lang_slug( $lang = null ) {
        if ( null === $lang || '' === $lang ) {
            $lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
            if ( '' === $lang ) {
                $lang = substr( (string) get_locale(), 0, 2 );
            }
        }
        $lang = strtolower( str_replace( '_', '-', (string) $lang ) );
        if ( 0 === strpos( $lang, 'es' ) ) {
            return 'es';
        }
        if ( 0 === strpos( $lang, 'en' ) ) {
            return 'en';
        }
        return '';
    }

    /**
     * @return array<string, array{es:string, en:string}>
     */
    public static function catalog() {
        static $catalog = null;
        if ( null === $catalog ) {
            $loaded  = include BIREME_BUILDER_PATH . 'includes/translations.php';
            $admin   = include BIREME_BUILDER_PATH . 'includes/admin-translations.php';
            $catalog = array_merge( is_array( $loaded ) ? $loaded : array(), is_array( $admin ) ? $admin : array() );
        }
        return $catalog;
    }

    /**
     * Traduz os textos conhecidos da interface dentro dos dados de uma seção.
     */
    public function translate_section_data( $data ) {
        if ( is_admin() ) {
            return $data;
        }
        return self::translate_data( $data );
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public static function translate_data( $data, $lang = null ) {
        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {
                $data[ $key ] = self::translate_data( $value, $lang );
            }
            return $data;
        }
        return is_string( $data ) ? self::string( $data, $lang ) : $data;
    }

    /**
     * Textos fixos exibidos no site (templates, breadcrumb, busca, acessibilidade).
     */
    public static function fixed_strings() {
        return array(
            'Início',
            'Resultados para "%s"',
            'Você está em',
            'Pular para o conteúdo',
            'Links institucionais',
            'Idiomas',
            'Buscar',
            'Menu',
            'Menu principal',
            'Rodapé',
            'OPAS | BIREME',
            'Paginação',
            'Anterior',
            'Próxima',
            'Carregando...',
            '%d min de leitura',
            'Pausar leitura',
            'Continuar leitura',
            'Parar leitura',
            'Destaques',
            'Ler agora',
            'Ir para o slide %d',
            'Slide anterior',
            'Próximo slide',
            'Ver todos',
            'Ver todos os Projetos',
            'Ver todas as notícias',
            'Ver todas',
            '%d publicação',
            '%d publicações',
            '%d edição',
            '%d edições',
            '%d artigo',
            '%d artigos',
            'Anos',
            'Informe um e-mail válido.',
            'Muitas tentativas. Tente novamente mais tarde.',
            'Não foi possível concluir a inscrição. Tente novamente.',
            'Não preencha este campo',
            'QR Code da notícia',
            'Leia no celular',
            'Aponte a câmera para o código e acesse a notícia completa.',
        );
    }

    public function register_strings() {
        if ( ! function_exists( 'pll_register_string' ) ) {
            return;
        }

        foreach ( self::fixed_strings() as $text ) {
            pll_register_string( 'bireme_fixed_' . md5( $text ), $text, self::GROUP_FIXED );
        }

        foreach ( Bireme_Sections::all() as $slug => $section ) {
            foreach ( self::default_texts( $section['fields'] ) as $text ) {
                pll_register_string( 'bireme_' . $slug . '_' . md5( $text ), $text, self::GROUP_SECTIONS, false !== strpos( $text, "\n" ) );
            }
        }
    }

    /**
     * Textos padrão dos campos (usados quando o elemento não foi salvo, ex.: barra lateral padrão).
     */
    private static function default_texts( array $fields ) {
        $texts = array();
        foreach ( $fields as $field ) {
            $type    = isset( $field['type'] ) ? $field['type'] : '';
            $default = isset( $field['default'] ) ? $field['default'] : '';

            if ( in_array( $type, array( 'text', 'textarea', 'editor' ), true ) && is_string( $default ) && '' !== trim( $default ) ) {
                $texts[] = $default;
            } elseif ( 'link' === $type && is_array( $default ) && ! empty( $default['text'] ) ) {
                $texts[] = (string) $default['text'];
            }

            if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
                $texts = array_merge( $texts, self::default_texts( $field['fields'] ) );
            }
        }
        return array_unique( $texts );
    }

    /**
     * Copia o builder e os temas em destaque ao criar a tradução, sem sincronizar edições.
     */
    public function copy_post_metas( $keys, $sync ) {
        $ours = array( Bireme_Plugin::META_KEY, Bireme_Topics::META_KEY );
        $keys = array_values( array_diff( (array) $keys, $ours ) );
        return $sync ? $keys : array_merge( $keys, $ours );
    }

    /**
     * Os temas em destaque copiados apontam para as traduções dos posts escolhidos.
     */
    public function translate_post_meta( $value, $key, $lang ) {
        if ( Bireme_Plugin::META_KEY === $key ) {
            return self::translate_items( $value, $lang );
        }
        if ( Bireme_Topics::META_KEY !== $key || ! function_exists( 'pll_get_post' ) ) {
            return $value;
        }
        $ids = array();
        foreach ( (array) $value as $id ) {
            $translated = pll_get_post( (int) $id, $lang );
            $ids[]      = $translated ? (int) $translated : (int) $id;
        }
        return $ids;
    }

    /**
     * Grava a interface já traduzida na página clonada. Textos fora do catálogo permanecem.
     *
     * @param mixed $items
     * @return mixed
     */
    private static function translate_items( $items, $lang ) {
        if ( ! is_array( $items ) || '' === self::lang_slug( $lang ) ) {
            return $items;
        }
        foreach ( $items as $index => $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            if ( isset( $item['data'] ) ) {
                $item['data'] = self::translate_data( $item['data'], $lang );
            }
            if ( ! empty( $item['columns'] ) && is_array( $item['columns'] ) ) {
                foreach ( $item['columns'] as $column => $children ) {
                    $item['columns'][ $column ] = self::translate_items( $children, $lang );
                }
            }
            $items[ $index ] = $item;
        }
        return $items;
    }
}
