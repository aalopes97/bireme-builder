<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Meta box com o builder de seções.
 */
class Bireme_Metabox {

    const NONCE_ACTION = 'bireme_builder_save';
    const NONCE_NAME   = 'bireme_builder_nonce';
    const FIELD_NAME   = 'bireme_builder_data';

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
    }

    public function add_meta_box() {
        foreach ( Bireme_Plugin::post_types() as $post_type ) {
            add_meta_box(
                'bireme-builder-metabox',
                __( 'Bireme Builder', 'bireme-builder' ),
                array( $this, 'render' ),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    public function render( $post ) {
        $sections = Bireme_Sections::all();
        $items    = Bireme_Plugin::get_items( $post->ID );

        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
        ?>
        <div id="bireme-builder" class="bireme-builder<?php echo empty( $items ) ? ' is-empty' : ''; ?>">
            <input type="hidden" id="bireme-builder-data" name="<?php echo esc_attr( self::FIELD_NAME ); ?>" value="<?php echo esc_attr( wp_json_encode( $items ) ); ?>">

            <aside class="bireme-builder__library">
                <h4 class="bireme-builder__heading"><?php esc_html_e( 'Seções disponíveis', 'bireme-builder' ); ?></h4>
                <?php if ( empty( $sections ) ) : ?>
                    <p class="description"><?php esc_html_e( 'Nenhuma seção registrada em /sections.', 'bireme-builder' ); ?></p>
                <?php else : ?>
                    <p class="description"><?php esc_html_e( 'Arraste para a área ao lado ou clique em +.', 'bireme-builder' ); ?></p>
                    <ul class="bireme-library">
                        <?php foreach ( $sections as $slug => $section ) : ?>
                            <li class="bireme-library__item" data-type="<?php echo esc_attr( $slug ); ?>">
                                <?php if ( $section['thumb'] ) : ?>
                                    <img class="bireme-library__thumb" src="<?php echo esc_url( $section['thumb'] ); ?>" alt="">
                                <?php else : ?>
                                    <span class="bireme-library__icon dashicons <?php echo esc_attr( $section['icon'] ); ?>" aria-hidden="true"></span>
                                <?php endif; ?>
                                <span class="bireme-library__text">
                                    <strong><?php echo esc_html( $section['label'] ); ?></strong>
                                    <?php if ( $section['description'] ) : ?>
                                        <small><?php echo esc_html( $section['description'] ); ?></small>
                                    <?php endif; ?>
                                </span>
                                <button type="button" class="bireme-library__add" aria-label="<?php echo esc_attr( sprintf( __( 'Adicionar %s', 'bireme-builder' ), $section['label'] ) ); ?>">
                                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>

            <div class="bireme-builder__main">
                <p class="bireme-builder__notice">
                    <span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
                    <?php esc_html_e( 'Quando houver seções visíveis, a página publicada usa o header do Bireme Builder (no lugar do header e footer do tema) e as seções substituem o conteúdo do editor.', 'bireme-builder' ); ?>
                </p>
                <div class="bireme-builder__canvas-wrap">
                    <div class="bireme-canvas"></div>
                    <div class="bireme-builder__empty">
                        <span class="dashicons dashicons-welcome-add-page" aria-hidden="true"></span>
                        <?php esc_html_e( 'Arraste seções para cá', 'bireme-builder' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function enqueue( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->post_type, Bireme_Plugin::post_types(), true ) ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_style( 'wp-color-picker' );

        $css = BIREME_BUILDER_PATH . 'assets/admin/builder.css';
        $js  = BIREME_BUILDER_PATH . 'assets/admin/builder.js';

        wp_enqueue_style(
            'bireme-builder-admin',
            BIREME_BUILDER_URL . 'assets/admin/builder.css',
            array( 'dashicons' ),
            BIREME_BUILDER_VERSION . '-' . filemtime( $css )
        );

        wp_enqueue_script(
            'bireme-builder-admin',
            BIREME_BUILDER_URL . 'assets/admin/builder.js',
            array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'wp-color-picker' ),
            BIREME_BUILDER_VERSION . '-' . filemtime( $js ),
            true
        );

        wp_localize_script( 'bireme-builder-admin', 'BiremeBuilder', array(
            'sections' => Bireme_Sections::admin_schema(),
            'i18n'     => array(
                'drag'             => __( 'Arrastar para reordenar', 'bireme-builder' ),
                'toggle'           => __( 'Abrir/fechar', 'bireme-builder' ),
                'toggleVisibility' => __( 'Mostrar/ocultar na página', 'bireme-builder' ),
                'duplicate'        => __( 'Duplicar', 'bireme-builder' ),
                'remove'           => __( 'Remover', 'bireme-builder' ),
                'hidden'           => __( 'Oculta', 'bireme-builder' ),
                'confirmRemove'    => __( 'Remover esta seção?', 'bireme-builder' ),
                'confirmRemoveRow' => __( 'Remover este item?', 'bireme-builder' ),
                'selectImage'      => __( 'Selecionar imagem', 'bireme-builder' ),
                'changeImage'      => __( 'Trocar imagem', 'bireme-builder' ),
                'removeImage'      => __( 'Remover imagem', 'bireme-builder' ),
                'useImage'         => __( 'Usar esta imagem', 'bireme-builder' ),
                'linkText'         => __( 'Texto do link', 'bireme-builder' ),
                'linkUrl'          => __( 'URL (https://... ou /pagina)', 'bireme-builder' ),
                'newTab'           => __( 'Abrir em nova aba', 'bireme-builder' ),
                'addItem'          => __( 'Adicionar item', 'bireme-builder' ),
                'item'             => __( 'Item', 'bireme-builder' ),
                'column'           => __( 'Coluna', 'bireme-builder' ),
                'dropHere'         => __( 'Arraste seções para esta coluna', 'bireme-builder' ),
            ),
        ) );
    }

    public function save( $post_id, $post ) {
        if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }
        if ( ! in_array( $post->post_type, Bireme_Plugin::post_types(), true ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( ! isset( $_POST[ self::FIELD_NAME ] ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitizado campo a campo em sanitize_items().
        $raw   = json_decode( wp_unslash( $_POST[ self::FIELD_NAME ] ), true );
        $items = self::sanitize_items( $raw );

        if ( empty( $items ) ) {
            delete_post_meta( $post_id, Bireme_Plugin::META_KEY );
            return;
        }

        update_post_meta( $post_id, Bireme_Plugin::META_KEY, wp_slash( $items ) );
    }

    /**
     * @param array $items           Itens vindos do JSON do builder.
     * @param bool  $allow_container Containers só são aceitos no nível raiz (sem aninhamento).
     */
    public static function sanitize_items( $items, $allow_container = true ) {
        $clean = array();
        $seen  = array();

        if ( ! is_array( $items ) ) {
            return $clean;
        }

        foreach ( $items as $item ) {
            if ( ! is_array( $item ) || empty( $item['type'] ) ) {
                continue;
            }

            $type    = sanitize_key( $item['type'] );
            $section = Bireme_Sections::get( $type );
            if ( ! $section || ( $section['container'] && ! $allow_container ) ) {
                continue;
            }

            $id = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
            if ( '' === $id || isset( $seen[ $id ] ) ) {
                $id = 'bb' . strtolower( wp_generate_password( 10, false ) );
            }
            $seen[ $id ] = true;

            $entry = array(
                'id'      => $id,
                'type'    => $type,
                'enabled' => ! isset( $item['enabled'] ) || (bool) $item['enabled'],
                'data'    => Bireme_Fields::sanitize_data( $section['fields'], isset( $item['data'] ) ? $item['data'] : array() ),
            );

            if ( $section['container'] ) {
                $entry['columns'] = array();
                $raw_columns      = ( isset( $item['columns'] ) && is_array( $item['columns'] ) ) ? array_values( $item['columns'] ) : array();
                foreach ( array_slice( $raw_columns, 0, 4 ) as $column ) {
                    $entry['columns'][] = self::sanitize_items( $column, false );
                }
            }

            $clean[] = $entry;
        }

        return $clean;
    }
}
