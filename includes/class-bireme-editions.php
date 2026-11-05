<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Edições: termos da taxonomia configurada em "Taxonomia da edição", agrupados por ano e mês.
 * A data da edição é a definida no termo (campo "Data da edição") ou a do post mais recente.
 */
class Bireme_Editions {

    const META_KEY  = '_bireme_edition_date';
    const CACHE_KEY = 'bireme_edition_dates_';

    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_term_fields' ) );
        add_action( 'save_post', array( __CLASS__, 'flush' ) );
        add_action( 'deleted_post', array( __CLASS__, 'flush' ) );
        add_action( 'set_object_terms', array( __CLASS__, 'flush' ) );
        add_action( 'edited_term', array( __CLASS__, 'flush' ) );
        add_action( 'delete_term', array( __CLASS__, 'flush' ) );
    }

    /**
     * Taxonomia das edições: a "Taxonomia da edição" ou, se ela não tiver termos em uso,
     * a "Taxonomia do tema" (mesma regra do topo e do breadcrumb dos posts).
     */
    public static function taxonomy() {
        static $taxonomy = null;
        if ( null !== $taxonomy ) {
            return $taxonomy;
        }
        $settings = Bireme_Settings::get();
        $taxonomy = '';
        foreach ( array( 'single_edition_tax', 'single_topic_tax' ) as $key ) {
            $candidate = isset( $settings[ $key ] ) ? $settings[ $key ] : '';
            if ( ! $candidate || ! taxonomy_exists( $candidate ) ) {
                continue;
            }
            if ( '' === $taxonomy ) {
                $taxonomy = $candidate;
            }
            $count = wp_count_terms( array( 'taxonomy' => $candidate, 'hide_empty' => true ) );
            if ( ! is_wp_error( $count ) && (int) $count > 0 ) {
                $taxonomy = $candidate;
                break;
            }
        }
        return $taxonomy;
    }

    public static function flush() {
        $taxonomy = self::taxonomy();
        if ( $taxonomy ) {
            delete_transient( self::CACHE_KEY . $taxonomy );
        }
    }

    /**
     * Data do post publicado mais recente de cada edição: [ term_id => 'Y-m-d H:i:s' ].
     */
    private static function latest_post_dates( $taxonomy ) {
        $dates = get_transient( self::CACHE_KEY . $taxonomy );
        if ( is_array( $dates ) ) {
            return $dates;
        }

        global $wpdb;
        $types        = array_map( 'sanitize_key', Bireme_Single::post_types() );
        $placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql  = $wpdb->prepare( "SELECT tt.term_id, MAX(p.post_date) AS latest FROM {$wpdb->term_taxonomy} tt INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id WHERE tt.taxonomy = %s AND p.post_status = 'publish' AND p.post_type IN ($placeholders) GROUP BY tt.term_id", array_merge( array( $taxonomy ), $types ) );
        $rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery

        $dates = array();
        foreach ( (array) $rows as $row ) {
            $dates[ (int) $row->term_id ] = $row->latest;
        }
        set_transient( self::CACHE_KEY . $taxonomy, $dates, 12 * HOUR_IN_SECONDS );
        return $dates;
    }

    /**
     * Edições do idioma atual, mais recentes primeiro.
     *
     * @return array[] Cada item: url, name, count, image, time.
     */
    public static function latest( $limit = 6 ) {
        return array_slice( self::sorted(), 0, max( 0, (int) $limit ) );
    }

    /**
     * Edições do idioma atual agrupadas: [ ano => [ mês => [ [ term, url, name, count, time ] ] ] ], mais recentes primeiro.
     */
    public static function grouped( $limit_years = 0 ) {
        $groups = array();
        foreach ( self::sorted() as $edition ) {
            $year  = (int) gmdate( 'Y', $edition['time'] );
            $month = (int) gmdate( 'n', $edition['time'] );
            if ( $limit_years && ! isset( $groups[ $year ] ) && count( $groups ) >= $limit_years ) {
                break;
            }
            $groups[ $year ][ $month ][] = $edition;
        }
        return $groups;
    }

    /**
     * Edições ordenadas da mais recente para a mais antiga.
     */
    private static function sorted() {
        $editions = self::post_type() ? self::from_posts() : self::from_terms();
        usort( $editions, static function ( $a, $b ) {
            return $b['time'] <=> $a['time'];
        } );
        return $editions;
    }

    /**
     * Tipo de post das edições (ex.: "edition"), quando registrado no site.
     */
    public static function post_type() {
        $post_type = (string) apply_filters( 'bireme_builder_edition_post_type', 'edition' );
        return ( '' !== $post_type && post_type_exists( $post_type ) ) ? $post_type : '';
    }

    /**
     * Edições do tipo de post próprio, pela data de publicação.
     */
    private static function from_posts() {
        $posts = get_posts( array(
            'post_type'        => self::post_type(),
            'post_status'      => 'publish',
            'posts_per_page'   => -1,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'no_found_rows'    => true,
            'suppress_filters' => false,
        ) );

        $charset  = get_bloginfo( 'charset' );
        $editions = array();
        foreach ( $posts as $post ) {
            $thumb_id   = (int) get_post_thumbnail_id( $post );
            $editions[] = array(
                'url'   => get_permalink( $post ),
                'name'  => html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, $charset ),
                'count' => null,
                'image' => $thumb_id ? (string) wp_get_attachment_image_url( $thumb_id, 'medium_large' ) : '',
                'time'  => strtotime( $post->post_date ),
            );
        }
        return $editions;
    }

    /**
     * Edições como termos de taxonomia, pela "Data da edição" ou pelo post mais recente.
     */
    private static function from_terms() {
        $taxonomy = self::taxonomy();
        if ( ! $taxonomy ) {
            return array();
        }

        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
            'exclude'    => 'category' === $taxonomy ? array( (int) get_option( 'default_category' ) ) : array(),
        ) );
        if ( is_wp_error( $terms ) || ! $terms ) {
            return array();
        }

        $latest   = self::latest_post_dates( $taxonomy );
        $editions = array();
        foreach ( $terms as $term ) {
            $manual = (string) get_term_meta( $term->term_id, self::META_KEY, true );
            if ( preg_match( '/^\d{4}-\d{2}$/', $manual ) ) {
                $time = strtotime( $manual . '-01 12:00:00' );
            } elseif ( isset( $latest[ $term->term_id ] ) ) {
                $time = strtotime( $latest[ $term->term_id ] );
            } else {
                continue;
            }
            $link = get_term_link( $term );
            if ( ! $time || is_wp_error( $link ) ) {
                continue;
            }
            $editions[] = array(
                'url'   => $link,
                'name'  => Bireme_Single::term_name( $term ),
                'count' => (int) $term->count,
                'image' => '',
                'time'  => $time,
            );
        }
        return $editions;
    }

    /**
     * Nome do mês no idioma da página. Sem o pacote de tradução do WordPress no idioma,
     * usa os nomes em espanhol ou português definidos aqui.
     */
    public static function month_name( $month ) {
        global $wp_locale;
        $month   = max( 1, min( 12, (int) $month ) );
        $english = gmdate( 'F', mktime( 0, 0, 0, $month, 1 ) );
        $name    = $wp_locale ? $wp_locale->get_month( $month ) : $english;

        $names = array(
            'es' => array( 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' ),
            'pt' => array( 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro' ),
        );
        $lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
        $lang = '' !== $lang ? $lang : (string) get_locale();
        $lang = strtolower( substr( $lang, 0, 2 ) );

        if ( isset( $names[ $lang ] ) && 0 === strcasecmp( $name, $english ) ) {
            return $names[ $lang ][ $month - 1 ];
        }
        return $name;
    }

    /* Campo "Data da edição" na taxonomia ------------------------------------- */

    public function register_term_fields() {
        $taxonomy = self::post_type() ? '' : self::taxonomy();
        if ( ! $taxonomy ) {
            return;
        }
        add_action( $taxonomy . '_add_form_fields', array( $this, 'add_field' ) );
        add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_field' ) );
        add_action( 'created_' . $taxonomy, array( $this, 'save_field' ) );
        add_action( 'edited_' . $taxonomy, array( $this, 'save_field' ) );
    }

    public function add_field() {
        wp_nonce_field( 'bireme_edition_date', 'bireme_edition_date_nonce' );
        ?>
        <div class="form-field">
            <label for="bireme-edition-date"><?php esc_html_e( 'Data da edição', 'bireme-builder' ); ?></label>
            <input type="month" id="bireme-edition-date" name="bireme_edition_date" value="">
            <p><?php esc_html_e( 'Mês e ano usados na página de edições. Vazio: data do post mais recente da edição.', 'bireme-builder' ); ?></p>
        </div>
        <?php
    }

    public function edit_field( $term ) {
        $value = (string) get_term_meta( $term->term_id, self::META_KEY, true );
        ?>
        <tr class="form-field">
            <th scope="row"><label for="bireme-edition-date"><?php esc_html_e( 'Data da edição', 'bireme-builder' ); ?></label></th>
            <td>
                <?php wp_nonce_field( 'bireme_edition_date', 'bireme_edition_date_nonce' ); ?>
                <input type="month" id="bireme-edition-date" name="bireme_edition_date" value="<?php echo esc_attr( $value ); ?>">
                <p class="description"><?php esc_html_e( 'Mês e ano usados na página de edições. Vazio: data do post mais recente da edição.', 'bireme-builder' ); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save_field( $term_id ) {
        if ( ! isset( $_POST['bireme_edition_date_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bireme_edition_date_nonce'] ) ), 'bireme_edition_date' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_term', $term_id ) ) {
            return;
        }
        $value = isset( $_POST['bireme_edition_date'] ) ? sanitize_text_field( wp_unslash( $_POST['bireme_edition_date'] ) ) : '';
        if ( preg_match( '/^\d{4}-\d{2}$/', $value ) ) {
            update_term_meta( $term_id, self::META_KEY, $value );
        } else {
            delete_term_meta( $term_id, self::META_KEY );
        }
        self::flush();
    }
}
