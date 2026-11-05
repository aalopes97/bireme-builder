<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * "Temas em Destaque" por post: meta box para escolher os posts exibidos na barra lateral.
 * Sem seleção, a seção mostra os posts mais recentes.
 */
class Bireme_Topics {

    const META_KEY = '_bireme_featured_topics';
    const NONCE    = 'bireme_topics_nonce';
    const AJAX     = 'bireme_search_posts';
    const MAX      = 4;

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'wp_ajax_' . self::AJAX, array( $this, 'ajax_search' ) );
    }

    /**
     * IDs escolhidos no post (apenas publicados, na ordem definida).
     *
     * @return int[]
     */
    public static function get_ids( $post_id ) {
        $ids = get_post_meta( $post_id, self::META_KEY, true );
        if ( ! is_array( $ids ) ) {
            return array();
        }
        return array_values( array_filter( array_map( 'absint', $ids ), static function ( $id ) {
            return $id && 'publish' === get_post_status( $id );
        } ) );
    }

    public function add_meta_box() {
        foreach ( Bireme_Single::post_types() as $post_type ) {
            add_meta_box(
                'bireme-featured-topics',
                __( 'Temas em Destaque (barra lateral)', 'bireme-builder' ),
                array( $this, 'render' ),
                $post_type,
                'side',
                'default'
            );
        }
    }

    private function is_screen( $hook ) {
        global $post_type;
        return in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && in_array( $post_type, Bireme_Single::post_types(), true );
    }

    public function enqueue( $hook ) {
        if ( ! $this->is_screen( $hook ) ) {
            return;
        }

        $js  = BIREME_BUILDER_PATH . 'assets/admin/topics.js';
        $css = BIREME_BUILDER_PATH . 'assets/admin/topics.css';

        wp_enqueue_style( 'bireme-topics', BIREME_BUILDER_URL . 'assets/admin/topics.css', array(), BIREME_BUILDER_VERSION . '-' . filemtime( $css ) );
        wp_enqueue_script( 'bireme-topics', BIREME_BUILDER_URL . 'assets/admin/topics.js', array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ), BIREME_BUILDER_VERSION . '-' . filemtime( $js ), true );
        wp_localize_script( 'bireme-topics', 'BiremeTopics', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'action'  => self::AJAX,
            'nonce'   => wp_create_nonce( self::AJAX ),
            'max'     => self::MAX,
            'i18n'    => array(
                'remove'  => __( 'Remover', 'bireme-builder' ),
                'limit'   => sprintf( /* translators: %d: limite */ __( 'Limite de %d posts.', 'bireme-builder' ), self::MAX ),
                'noMatch' => __( 'Nenhum post encontrado.', 'bireme-builder' ),
            ),
        ) );
    }

    public function render( $post ) {
        wp_nonce_field( 'bireme_topics_save', self::NONCE );
        $ids = self::get_ids( $post->ID );
        ?>
        <div class="bireme-topics" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
            <label class="screen-reader-text" for="bireme-topics-search"><?php esc_html_e( 'Buscar posts', 'bireme-builder' ); ?></label>
            <input type="search" id="bireme-topics-search" class="bireme-topics__search widefat" placeholder="<?php esc_attr_e( 'Buscar posts pelo título...', 'bireme-builder' ); ?>" autocomplete="off">
            <ul class="bireme-topics__list">
                <?php foreach ( $ids as $id ) : ?>
                    <li class="bireme-topics__item" data-id="<?php echo esc_attr( $id ); ?>">
                        <span class="dashicons dashicons-menu bireme-topics__handle" aria-hidden="true"></span>
                        <span class="bireme-topics__title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
                        <button type="button" class="bireme-topics__remove" aria-label="<?php esc_attr_e( 'Remover', 'bireme-builder' ); ?>">&times;</button>
                        <input type="hidden" name="bireme_featured_topics[]" value="<?php echo esc_attr( $id ); ?>">
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="description"><?php esc_html_e( 'Arraste para ordenar. Sem seleção, a barra lateral mostra os posts mais recentes.', 'bireme-builder' ); ?></p>
        </div>
        <?php
    }

    public function save( $post_id, $post ) {
        if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), 'bireme_topics_save' ) ) {
            return;
        }
        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
            return;
        }
        if ( ! in_array( $post->post_type, Bireme_Single::post_types(), true ) || ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $raw = isset( $_POST['bireme_featured_topics'] ) ? (array) wp_unslash( $_POST['bireme_featured_topics'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- convertido para inteiros abaixo.
        $ids = array_slice( array_values( array_unique( array_filter( array_map( 'absint', $raw ), static function ( $id ) use ( $post_id ) {
            return $id && $id !== (int) $post_id && get_post( $id );
        } ) ) ), 0, self::MAX );

        if ( $ids ) {
            update_post_meta( $post_id, self::META_KEY, $ids );
        } else {
            delete_post_meta( $post_id, self::META_KEY );
        }
    }

    public function ajax_search() {
        check_ajax_referer( self::AJAX, 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( null, 403 );
        }

        $term    = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
        $exclude = isset( $_GET['exclude'] ) ? array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['exclude'] ) ) ) ) : array();

        $query = new WP_Query( array(
            'post_type'           => Bireme_Single::post_types(),
            'post_status'         => 'publish',
            's'                   => $term,
            'posts_per_page'      => 10,
            'post__not_in'        => array_filter( $exclude ),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ) );

        $results = array();
        foreach ( $query->posts as $found ) {
            $results[] = array(
                'id'    => $found->ID,
                'label' => html_entity_decode( get_the_title( $found ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
                'date'  => get_the_date( '', $found ),
            );
        }
        wp_send_json_success( $results );
    }
}
