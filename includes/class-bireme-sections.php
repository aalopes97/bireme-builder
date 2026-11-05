<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registro de seções. Cada seção é uma pasta em /sections/<slug>/ com:
 *  - section.php  (obrigatório) retorna o array de configuração/campos
 *  - template.php (obrigatório) HTML do front-end; o tema pode sobrescrever em
 *                 <tema>/bireme-builder/<slug>/template.php
 *  - style.css    (opcional) carregado só nas páginas que usam a seção
 *  - script.js    (opcional) idem, no rodapé
 *  - 'external_styles' em section.php (opcional) handle => URL, ex.: Google Fonts
 *  - 'container' => true em section.php: a seção recebe outras seções em colunas
 *    (campo 'layout' como "75-25"); o template recebe $columns com o HTML de cada coluna.
 *  - thumb.png|jpg|svg (opcional) miniatura exibida na biblioteca
 */
class Bireme_Sections {

    private static $sections = null;

    /**
     * @return array Mapa slug => configuração normalizada.
     */
    public static function all() {
        if ( null !== self::$sections ) {
            return self::$sections;
        }

        self::$sections = array();

        $dirs = glob( BIREME_BUILDER_PATH . 'sections/*', GLOB_ONLYDIR );
        if ( ! $dirs ) {
            return self::$sections;
        }

        sort( $dirs );

        foreach ( $dirs as $dir ) {
            $file = $dir . '/section.php';
            if ( ! file_exists( $file ) ) {
                continue;
            }

            $config = include $file;
            if ( ! is_array( $config ) ) {
                continue;
            }

            $folder = basename( $dir );
            $slug   = sanitize_key( $folder );
            if ( '' === $slug ) {
                continue;
            }

            self::$sections[ $slug ] = self::prepare( $slug, $config, $dir, BIREME_BUILDER_URL . 'sections/' . $folder . '/' );
        }

        return self::$sections;
    }

    /**
     * Descarta o cache para os textos padrão serem lidos no idioma definido pelo Polylang.
     */
    public static function reset() {
        self::$sections = null;
    }

    public static function get( $slug ) {
        $all = self::all();
        return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
    }

    public static function template_path( $slug ) {
        $section = self::get( $slug );
        if ( ! $section ) {
            return '';
        }

        $theme_file = locate_template( 'bireme-builder/' . $slug . '/template.php' );
        if ( $theme_file ) {
            return $theme_file;
        }

        $file = $section['dir'] . '/template.php';
        return file_exists( $file ) ? $file : '';
    }

    /**
     * Schema enxuto enviado ao JS do admin.
     */
    public static function admin_schema() {
        $schema = array();
        foreach ( self::all() as $slug => $section ) {
            $schema[ $slug ] = array(
                'label'       => $section['label'],
                'description' => $section['description'],
                'icon'        => $section['icon'],
                'thumb'       => $section['thumb'],
                'container'   => $section['container'],
                'fields'      => Bireme_Fields::to_js( $section['fields'] ),
            );
        }
        return $schema;
    }

    private static function prepare( $slug, array $config, $dir, $url ) {
        $config = wp_parse_args( $config, array(
            'label'       => $slug,
            'description' => '',
            'icon'        => 'dashicons-layout',
            'fields'      => array(),
        ) );

        $thumb = '';
        foreach ( array( 'thumb.png', 'thumb.jpg', 'thumb.svg' ) as $name ) {
            if ( file_exists( $dir . '/' . $name ) ) {
                $thumb = $url . $name;
                break;
            }
        }

        $style     = '';
        $style_ver = BIREME_BUILDER_VERSION;
        if ( file_exists( $dir . '/style.css' ) ) {
            $style     = $url . 'style.css';
            $style_ver = BIREME_BUILDER_VERSION . '-' . filemtime( $dir . '/style.css' );
        }

        $script     = '';
        $script_ver = BIREME_BUILDER_VERSION;
        if ( file_exists( $dir . '/script.js' ) ) {
            $script     = $url . 'script.js';
            $script_ver = BIREME_BUILDER_VERSION . '-' . filemtime( $dir . '/script.js' );
        }

        $external_styles = array();
        if ( ! empty( $config['external_styles'] ) && is_array( $config['external_styles'] ) ) {
            foreach ( $config['external_styles'] as $handle => $src ) {
                $external_styles[ sanitize_key( $handle ) ] = esc_url_raw( $src );
            }
        }

        return array(
            'slug'            => $slug,
            'label'           => (string) $config['label'],
            'description'     => (string) $config['description'],
            'icon'            => sanitize_html_class( $config['icon'] ),
            'fields'          => Bireme_Fields::normalize( $config['fields'] ),
            'dir'             => $dir,
            'url'             => $url,
            'thumb'           => $thumb,
            'style'           => $style,
            'style_ver'       => $style_ver,
            'script'          => $script,
            'script_ver'      => $script_ver,
            'external_styles' => $external_styles,
            'container'       => ! empty( $config['container'] ),
        );
    }

    /**
     * Número de colunas de um layout de container ("75-25" => 2, "33-33-33" => 3).
     */
    public static function column_count( $layout ) {
        $parts = array_filter( explode( '-', (string) $layout ), 'strlen' );
        return max( 1, count( $parts ) );
    }
}
