<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Front-end: substitui o conteúdo da página pelas seções do builder.
 *
 * Templates recebem:
 *  - $data    dados da seção (já com defaults mesclados)
 *  - $section configuração da seção (label, fields, url...)
 *  - $item    item salvo (id, type, enabled)
 *  - $index   posição da seção na página (0..n)
 *  - $columns (só containers) HTML já renderizado de cada coluna
 */
class Bireme_Render {

    private static $rendering = false;

    public function __construct() {
        // Prioridade alta: roda depois do wpautop/do_shortcode para não alterar o markup das seções.
        add_filter( 'the_content', array( $this, 'filter_content' ), 999 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function filter_content( $content ) {
        if ( self::$rendering || ! is_singular( Bireme_Plugin::post_types() ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $post_id = (int) get_the_ID();
        if ( $post_id !== (int) get_queried_object_id() ) {
            return $content;
        }

        $html = self::render_sections( $post_id );
        return '' !== $html ? $html : $content;
    }

    public function enqueue_assets() {
        if ( Bireme_TV::is_context() ) {
            $source = Bireme_TV::items();
        } elseif ( Bireme_404::is_context() ) {
            $source = Bireme_404::items();
        } elseif ( Bireme_Archive::is_context() ) {
            $source = Bireme_Archive::items();
        } elseif ( Bireme_Single::is_context() ) {
            $source = Bireme_Single::items();
        } elseif ( is_singular( Bireme_Plugin::post_types() ) ) {
            $source = get_queried_object_id();
        } else {
            return;
        }

        foreach ( self::get_active_types( $source ) as $type ) {
            $section = Bireme_Sections::get( $type );
            if ( ! $section ) {
                continue;
            }

            $deps = array();
            foreach ( $section['external_styles'] as $handle => $src ) {
                wp_enqueue_style( $handle, $src, array(), null );
                $deps[] = $handle;
            }

            if ( $section['style'] ) {
                wp_enqueue_style( 'bireme-section-' . $type, $section['style'], $deps, $section['style_ver'] );
            }

            if ( $section['script'] ) {
                wp_enqueue_script( 'bireme-section-' . $type, $section['script'], array(), $section['script_ver'], true );
            }
        }
    }

    /**
     * Itens visíveis cujo tipo de seção ainda existe.
     *
     * @param int|array $source ID do post ou lista de itens (ex.: uma coluna de container).
     */
    public static function get_active_items( $source ) {
        $items  = is_array( $source ) ? $source : Bireme_Plugin::get_items( $source );
        $active = array();
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) || empty( $item['type'] ) || ( isset( $item['enabled'] ) && ! $item['enabled'] ) ) {
                continue;
            }
            if ( Bireme_Sections::get( $item['type'] ) ) {
                $active[] = $item;
            }
        }
        return $active;
    }

    /**
     * Tipos de seção visíveis na página, incluindo os que estão dentro de containers.
     *
     * @param int|array $source ID do post ou lista de itens.
     */
    public static function get_active_types( $source ) {
        $types = array();
        foreach ( self::get_active_items( $source ) as $item ) {
            $types[ $item['type'] ] = true;
            if ( ! empty( $item['columns'] ) && is_array( $item['columns'] ) ) {
                foreach ( $item['columns'] as $column ) {
                    foreach ( self::get_active_items( (array) $column ) as $child ) {
                        $types[ $child['type'] ] = true;
                    }
                }
            }
        }
        return array_keys( $types );
    }

    /**
     * HTML de cada coluna de um container. Colunas além do layout atual vão para a última.
     *
     * @return string[]
     */
    public static function render_columns( array $item, array $data ) {
        $count   = Bireme_Sections::column_count( isset( $data['layout'] ) ? $data['layout'] : '' );
        $columns = array_fill( 0, $count, '' );
        $raw     = ( ! empty( $item['columns'] ) && is_array( $item['columns'] ) ) ? array_values( $item['columns'] ) : array();

        foreach ( $raw as $i => $column ) {
            $target = min( $i, $count - 1 );
            foreach ( self::get_active_items( (array) $column ) as $child_index => $child ) {
                $child_section = Bireme_Sections::get( $child['type'] );
                if ( $child_section && ! $child_section['container'] ) {
                    $columns[ $target ] .= self::render_section( $child, $child_index );
                }
            }
        }

        return $columns;
    }

    /**
     * @param int|array $source ID do post ou lista de itens.
     */
    public static function render_sections( $source ) {
        $items = self::get_active_items( $source );
        if ( empty( $items ) ) {
            return '';
        }

        self::$rendering = true;
        $html            = '';
        foreach ( $items as $index => $item ) {
            $html .= self::render_section( $item, $index );
        }
        self::$rendering = false;

        return '' !== trim( $html ) ? '<div class="bireme-sections">' . $html . '</div>' : '';
    }

    public static function render_section( array $item, $index = 0 ) {
        $section  = Bireme_Sections::get( $item['type'] );
        $template = Bireme_Sections::template_path( $item['type'] );
        if ( ! $section || ! $template ) {
            return '';
        }

        $data = Bireme_Fields::merge_defaults( $section['fields'], isset( $item['data'] ) ? $item['data'] : array() );
        $data = apply_filters( 'bireme_builder_section_data', $data, $item, $section );

        $id      = ! empty( $item['id'] ) ? $item['id'] : 'bb' . $index;
        $columns = $section['container'] ? self::render_columns( $item, $data ) : array();

        if ( $section['container'] && '' === trim( implode( '', $columns ) ) ) {
            return '';
        }

        $render = static function ( $template, $data, $section, $item, $index, $columns ) {
            ob_start();
            include $template;
            return ob_get_clean();
        };

        $inner = $render( $template, $data, $section, $item, $index, $columns );
        if ( '' === trim( $inner ) ) {
            return '';
        }

        return sprintf(
            '<section id="%1$s" class="bireme-section bireme-section--%2$s">%3$s</section>',
            esc_attr( 'bireme-' . $id ),
            esc_attr( $item['type'] ),
            $inner
        );
    }

    /* ------------------------------------------------------------------ *
     * Helpers para os templates
     * ------------------------------------------------------------------ */

    /**
     * HTML de um campo wysiwyg (já sanitizado no save).
     */
    public static function wysiwyg( $html ) {
        $html = (string) $html;
        return '' === trim( $html ) ? '' : do_shortcode( wpautop( $html ) );
    }

    /**
     * <img> de um campo image.
     */
    public static function image( $image, $size = 'large', array $attr = array() ) {
        if ( ! is_array( $image ) || ( empty( $image['id'] ) && empty( $image['url'] ) ) ) {
            return '';
        }

        if ( ! empty( $image['alt'] ) && ! isset( $attr['alt'] ) ) {
            $attr['alt'] = $image['alt'];
        }

        if ( ! empty( $image['id'] ) && wp_attachment_is_image( $image['id'] ) ) {
            return wp_get_attachment_image( (int) $image['id'], $size, false, $attr );
        }

        $attributes = '';
        foreach ( $attr as $name => $value ) {
            if ( 'alt' !== $name ) {
                $attributes .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
            }
        }

        return sprintf(
            '<img src="%s" alt="%s" loading="lazy"%s>',
            esc_url( $image['url'] ),
            esc_attr( isset( $attr['alt'] ) ? $attr['alt'] : '' ),
            $attributes
        );
    }

    /**
     * URL de um campo image no tamanho pedido (útil para background-image).
     */
    public static function image_url( $image, $size = 'full' ) {
        if ( ! is_array( $image ) ) {
            return '';
        }
        if ( ! empty( $image['id'] ) ) {
            $src = wp_get_attachment_image_url( (int) $image['id'], $size );
            if ( $src ) {
                return $src;
            }
        }
        return ! empty( $image['url'] ) ? $image['url'] : '';
    }

    /**
     * <a> de um campo link. Retorna vazio se não houver URL.
     */
    public static function link( $link, $class = '' ) {
        if ( ! is_array( $link ) || empty( $link['url'] ) ) {
            return '';
        }

        $text   = '' !== (string) $link['text'] ? $link['text'] : $link['url'];
        $target = ( isset( $link['target'] ) && '_blank' === $link['target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';

        return sprintf(
            '<a href="%s"%s%s>%s</a>',
            esc_url( $link['url'] ),
            $class ? ' class="' . esc_attr( $class ) . '"' : '',
            $target,
            esc_html( $text )
        );
    }
}
