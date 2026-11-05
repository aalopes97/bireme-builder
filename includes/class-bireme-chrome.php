<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Header e rodapé Bireme em todo o site: nas páginas montadas pelo tema, o primeiro
 * <header> e o último <footer> do HTML são trocados pelos do plugin. O conteúdo do tema
 * entre eles não muda.
 */
class Bireme_Chrome {

    /** @var string */
    private static $header = '';

    /** @var string */
    private static $footer = '';

    public function __construct() {
        add_action( 'template_redirect', array( $this, 'start' ), 999 );
        add_filter( 'body_class', array( $this, 'body_class' ) );
    }

    public static function enabled() {
        $settings = Bireme_Settings::get();
        return ! empty( $settings['global_chrome'] );
    }

    /**
     * Páginas do tema que recebem o header e o rodapé Bireme.
     */
    public static function applies() {
        static $result = null;
        if ( null !== $result ) {
            return $result;
        }
        $result = self::enabled()
            && ! is_admin()
            && ! wp_doing_ajax()
            && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
            && ! is_feed()
            && ! is_embed()
            && ! is_robots()
            && ! is_trackback()
            && ! ( function_exists( 'is_favicon' ) && is_favicon() )
            && ! Bireme_TV::is_context()
            && ! Bireme_Layout::is_builder_page();
        $result = (bool) apply_filters( 'bireme_builder_global_chrome', $result );
        return $result;
    }

    public function body_class( $classes ) {
        if ( self::applies() ) {
            $classes[] = 'bireme-chrome';
        }
        return $classes;
    }

    public function start() {
        if ( ! self::applies() ) {
            return;
        }
        ob_start();
        Bireme_Layout::render_header();
        self::$header = (string) ob_get_clean();

        ob_start();
        Bireme_Layout::render_footer();
        self::$footer = (string) ob_get_clean();

        ob_start( array( __CLASS__, 'replace' ) );
    }

    /**
     * Troca o header e o rodapé do tema no HTML final.
     */
    public static function replace( $html ) {
        if ( ! is_string( $html ) || false === stripos( $html, '<body' ) || ! self::is_html_response() ) {
            return $html;
        }

        $masked = self::mask( $html );
        if ( ! preg_match( '/<body\b[^>]*>/i', $masked, $body, PREG_OFFSET_CAPTURE ) ) {
            return $html;
        }
        $body_end = $body[0][1] + strlen( $body[0][0] );
        $close    = strripos( $masked, '</body' );
        $close    = false === $close ? strlen( $html ) : $close;

        // O header do site vem antes do conteúdo principal e o rodapé depois dele,
        // para não trocar o cabeçalho ou o rodapé de um artigo.
        $content_start = preg_match( '/<(main|article)\b/i', $masked, $m, PREG_OFFSET_CAPTURE, $body_end ) ? $m[0][1] : $close;
        $content_end   = $body_end;
        if ( preg_match_all( '/<\/(main|article)\s*>/i', $masked, $ends, PREG_OFFSET_CAPTURE, $body_end ) ) {
            $last        = end( $ends[0] );
            $content_end = $last[1] + strlen( $last[0] );
        }

        $footer = self::last_element( $masked, 'footer', $body_end, $close );
        if ( $footer && $footer[0] < $content_end ) {
            $footer = null;
        }
        if ( $footer ) {
            $html   = substr_replace( $html, self::$footer, $footer[0], $footer[1] - $footer[0] );
            $masked = substr_replace( $masked, str_repeat( ' ', strlen( self::$footer ) ), $footer[0], $footer[1] - $footer[0] );
        } else {
            $html   = substr_replace( $html, self::$footer, $close, 0 );
            $masked = substr_replace( $masked, str_repeat( ' ', strlen( self::$footer ) ), $close, 0 );
        }

        $header = self::first_element( $masked, 'header', $body_end );
        if ( $header && $header[0] > $content_start ) {
            $header = null;
        }
        if ( $header ) {
            $html = substr_replace( $html, self::$header, $header[0], $header[1] - $header[0] );
        } else {
            $html = substr_replace( $html, self::$header, $body_end, 0 );
        }

        return $html;
    }

    private static function is_html_response() {
        foreach ( headers_list() as $header ) {
            if ( 0 === stripos( $header, 'content-type:' ) && false === stripos( $header, 'text/html' ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Cópia do HTML com comentários, scripts e estilos apagados (mesmo tamanho),
     * para que tags dentro deles não sejam consideradas.
     */
    private static function mask( $html ) {
        return preg_replace_callback(
            '/<!--.*?-->|<script\b.*?<\/script\s*>|<style\b.*?<\/style\s*>|<template\b.*?<\/template\s*>|<textarea\b.*?<\/textarea\s*>/is',
            static function ( $m ) {
                return str_repeat( ' ', strlen( $m[0] ) );
            },
            $html
        );
    }

    /**
     * Elementos de primeiro nível (não aninhados em outro igual) entre $from e $to: lista de [ início, fim ].
     */
    private static function elements( $masked, $tag, $from, $to ) {
        preg_match_all( '/<(\/?)' . $tag . '\b[^>]*>/i', $masked, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER, $from );
        $spans = array();
        $depth = 0;
        $start = 0;
        foreach ( $matches as $match ) {
            $pos = $match[0][1];
            if ( $pos >= $to ) {
                break;
            }
            if ( '' === $match[1][0] ) {
                if ( 0 === $depth ) {
                    $start = $pos;
                }
                $depth++;
            } elseif ( $depth > 0 ) {
                $depth--;
                if ( 0 === $depth ) {
                    $spans[] = array( $start, $pos + strlen( $match[0][0] ) );
                }
            }
        }
        return $spans;
    }

    private static function first_element( $masked, $tag, $from ) {
        $spans = self::elements( $masked, $tag, $from, strlen( $masked ) );
        return $spans ? $spans[0] : null;
    }

    private static function last_element( $masked, $tag, $from, $to ) {
        $spans = self::elements( $masked, $tag, $from, $to );
        return $spans ? end( $spans ) : null;
    }
}
