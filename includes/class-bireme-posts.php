<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Consulta de posts e opções de selects usadas pelas seções automáticas.
 */
class Bireme_Posts {

    /** IDs já exibidos por seções automáticas nesta página (para evitar repetição). */
    private static $shown = array();

    public static function mark_shown( array $ids ) {
        foreach ( $ids as $id ) {
            self::$shown[ (int) $id ] = true;
        }
    }

    public static function shown() {
        return array_keys( self::$shown );
    }

    /**
     * Post types públicos (exceto páginas e anexos), com "post" primeiro.
     */
    public static function post_type_options() {
        $options = array();
        foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
            if ( in_array( $type->name, array( 'page', 'attachment' ), true ) ) {
                continue;
            }
            $options[ $type->name ] = $type->labels->name;
        }
        if ( isset( $options['post'] ) ) {
            $options = array( 'post' => $options['post'] ) + $options;
        }
        return $options;
    }

    /**
     * Categorias de todos os idiomas: a escolhida é traduzida no front (ver query()).
     */
    public static function category_options() {
        $options = array( '' => __( 'Todas as categorias', 'bireme-builder' ) );
        foreach ( get_categories( array( 'hide_empty' => false, 'lang' => '' ) ) as $term ) {
            $label = $term->name;
            if ( function_exists( 'pll_get_term_language' ) ) {
                $lang = pll_get_term_language( $term->term_id );
                if ( $lang ) {
                    $label .= ' (' . $lang . ')';
                }
            }
            $options[ (string) $term->term_id ] = $label;
        }
        return $options;
    }

    public static function count_options( $min, $max ) {
        $options = array();
        for ( $i = $min; $i <= $max; $i++ ) {
            /* translators: %d: quantidade de posts */
            $options[ (string) $i ] = sprintf( _n( '%d post', '%d posts', $i, 'bireme-builder' ), $i );
        }
        return $options;
    }

    /**
     * @param array $args post_type, category, count, offset, exclude, orderby ('date'|'views'), days,
     *                    lang (slug do Polylang; vazio usa o idioma atual).
     * @return WP_Post[]
     */
    public static function query( array $args ) {
        $args = wp_parse_args( $args, array(
            'post_type' => 'post',
            'category'  => 0,
            'count'     => 6,
            'offset'    => 0,
            'exclude'   => array(),
            'orderby'   => 'date',
            'days'      => 0,
            'lang'      => '',
        ) );

        $query_args = array(
            'post_type'           => $args['post_type'] ? $args['post_type'] : 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => max( 1, absint( $args['count'] ) ),
            'offset'              => absint( $args['offset'] ),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        );

        if ( ! empty( $args['exclude'] ) ) {
            $query_args['post__not_in'] = array_map( 'absint', (array) $args['exclude'] );
        }

        $lang = sanitize_key( (string) $args['lang'] );
        if ( '' !== $lang && function_exists( 'pll_languages_list' ) ) {
            $query_args['lang'] = $lang;
        }

        $category_id = absint( $args['category'] );
        if ( $category_id && is_object_in_taxonomy( $query_args['post_type'], 'category' ) ) {
            if ( function_exists( 'pll_get_term' ) ) {
                $translated  = '' !== $lang ? pll_get_term( $category_id, $lang ) : pll_get_term( $category_id );
                $category_id = $translated ? $translated : $category_id;
            }
            $query_args['cat'] = $category_id;
        }

        if ( 'views' === $args['orderby'] ) {
            $query_args['meta_key'] = Bireme_Views::META_KEY; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';
        }

        if ( absint( $args['days'] ) > 0 ) {
            $query_args['date_query'] = array(
                array( 'after' => absint( $args['days'] ) . ' days ago', 'inclusive' => true ),
            );
        }

        $query_args = apply_filters( 'bireme_builder_posts_query_args', $query_args, $args );
        $query      = new WP_Query( $query_args );

        return $query->posts;
    }

    /**
     * Dados de card no mesmo formato dos itens manuais das seções.
     *
     * @param int $words Limite de palavras do resumo; 0 mostra o resumo do post por completo
     *                   (sem resumo, o trecho automático de 55 palavras do texto).
     */
    public static function card_data( WP_Post $post, $button_text = '', $words = 0 ) {
        $charset  = get_bloginfo( 'charset' );
        $thumb_id = (int) get_post_thumbnail_id( $post );
        $taxonomy = self::card_taxonomy( $post->post_type );
        $terms    = $taxonomy ? get_the_terms( $post, $taxonomy ) : array();
        $excerpt  = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
        if ( (int) $words <= 0 && ! has_excerpt( $post ) ) {
            $words = 55;
        }

        return array(
            'id'       => $post->ID,
            'image'    => array(
                'id'  => $thumb_id,
                'url' => $thumb_id ? (string) wp_get_attachment_url( $thumb_id ) : '',
                'alt' => $thumb_id ? (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '',
            ),
            'category' => ( $terms && ! is_wp_error( $terms ) ) ? html_entity_decode( $terms[0]->name, ENT_QUOTES, $charset ) : '',
            'title'    => html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, $charset ),
            'text'     => html_entity_decode(
                (int) $words > 0 ? wp_trim_words( $excerpt, (int) $words ) : trim( wp_strip_all_tags( $excerpt ) ),
                ENT_QUOTES,
                $charset
            ),
            'date'     => get_the_date( '', $post ),
            'link'     => array(
                'url'    => get_permalink( $post ),
                'text'   => $button_text,
                'target' => '',
            ),
        );
    }

    /**
     * Resumo do banner do slide: caracteres suficientes para preencher até 3 linhas.
     * O limite visual fica no CSS (line-clamp).
     */
    public static function banner_excerpt( WP_Post $post ) {
        $charset  = get_bloginfo( 'charset' );
        $body     = trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
        $lead     = has_excerpt( $post ) ? trim( wp_strip_all_tags( $post->post_excerpt ) ) : '';
        $complete = ( '' !== $lead && preg_match( '/[.!?…]\s*$/u', $lead ) );
        $source   = $lead;

        if ( mb_strlen( $lead ) < 280 && '' !== $body ) {
            if ( '' === $lead || ! $complete || false !== mb_stripos( $body, $lead ) ) {
                $source = $body;
            } else {
                $source = $lead . ' ' . $body;
            }
        }

        return html_entity_decode( wp_trim_words( $source, 60 ), ENT_QUOTES, $charset );
    }

    /**
     * URL do CSS compartilhado dos cards horizontais (para 'external_styles' das seções).
     */
    public static function cards_style_url() {
        $file = BIREME_BUILDER_PATH . 'assets/front/cards.css';
        return add_query_arg( 'ver', BIREME_BUILDER_VERSION . '-' . ( file_exists( $file ) ? filemtime( $file ) : '0' ), BIREME_BUILDER_URL . 'assets/front/cards.css' );
    }

    /**
     * Card horizontal (imagem à esquerda) usado na Lista de publicações e em Posts relacionados.
     *
     * @param array  $card     Formato de card_data().
     * @param string $modifier '' ou 'featured'.
     */
    public static function horizontal_card( array $card, $modifier = '', $heading = 'h2' ) {
        $url     = ! empty( $card['link']['url'] ) ? $card['link']['url'] : '';
        $heading = in_array( $heading, array( 'h2', 'h3', 'h4' ), true ) ? $heading : 'h2';
        $classes = 'bb-ap__card' . ( $modifier ? ' bb-ap__card--' . $modifier : '' ) . ( $url ? ' has-link' : '' );

        ob_start();
        ?>
        <article class="<?php echo esc_attr( $classes ); ?>">
            <div class="bb-ap__media">
                <?php echo Bireme_Render::image( $card['image'], 'medium_large', array( 'loading' => 'lazy', 'alt' => '', 'sizes' => '(max-width: 700px) 40vw, 260px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="bb-ap__body">
                <?php if ( '' !== $card['category'] ) : ?>
                    <span class="bb-ap__category"><?php echo esc_html( $card['category'] ); ?></span>
                <?php endif; ?>
                <<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lista fixa acima. ?> class="bb-ap__title">
                    <?php if ( $url ) : ?>
                        <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $card['title'] ); ?></a>
                    <?php else : ?>
                        <?php echo esc_html( $card['title'] ); ?>
                    <?php endif; ?>
                </<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <?php if ( '' !== $card['text'] ) : ?>
                    <p class="bb-ap__excerpt"><?php echo esc_html( $card['text'] ); ?></p>
                <?php endif; ?>
                <?php if ( '' !== $card['date'] ) : ?>
                    <span class="bb-ap__date"><?php echo esc_html( $card['date'] ); ?></span>
                <?php endif; ?>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }

    /**
     * Taxonomia exibida como "categoria" do card: category, ou a primeira pública hierárquica.
     */
    public static function card_taxonomy( $post_type ) {
        static $cache = array();
        if ( isset( $cache[ $post_type ] ) ) {
            return $cache[ $post_type ];
        }

        $found = '';
        foreach ( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) {
            if ( 'category' === $taxonomy->name ) {
                $found = 'category';
                break;
            }
            if ( ! $found && $taxonomy->public && $taxonomy->hierarchical ) {
                $found = $taxonomy->name;
            }
        }

        $cache[ $post_type ] = $found;
        return $found;
    }
}
