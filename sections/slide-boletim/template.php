<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( 'auto' === $data['source'] ) {
    $slides = array();
    foreach ( Bireme_Posts::query( array(
        'post_type' => $data['post_type'],
        'category'  => $data['category'],
        'count'     => $data['count'],
    ) ) as $post_item ) {
        $card            = Bireme_Posts::card_data( $post_item, $data['button_text'] );
        $card['text']    = Bireme_Posts::banner_excerpt( $post_item );
        $edition_term    = Bireme_Single::term( $post_item, 'single_edition_tax' );
        $default_cat     = (int) get_option( 'default_category' );
        if ( $edition_term && 'category' === $edition_term->taxonomy && (int) $edition_term->term_id === $default_cat ) {
            $edition_term = null;
        }
        if ( $edition_term ) {
            $edition_link    = get_term_link( $edition_term );
            $card['edition'] = array(
                'name' => Bireme_Single::term_name( $edition_term ),
                'url'  => is_wp_error( $edition_link ) ? '' : $edition_link,
            );
        } else {
            $default_name = $default_cat ? html_entity_decode( get_cat_name( $default_cat ), ENT_QUOTES, get_bloginfo( 'charset' ) ) : '';
            $edition_name = ( '' !== $card['category'] && $card['category'] !== $default_name ) ? $card['category'] : '';
            $edition_url  = '';
            if ( '' !== $edition_name ) {
                $card_tax  = Bireme_Posts::card_taxonomy( $post_item->post_type );
                $card_terms = $card_tax ? get_the_terms( $post_item, $card_tax ) : array();
                if ( $card_terms && ! is_wp_error( $card_terms ) ) {
                    $card_link   = get_term_link( $card_terms[0] );
                    $edition_url = is_wp_error( $card_link ) ? '' : $card_link;
                }
            }
            $card['edition'] = array(
                'name' => $edition_name,
                'url'  => $edition_url,
            );
        }
        $slides[] = $card;
    }
    Bireme_Posts::mark_shown( wp_list_pluck( $slides, 'id' ) );
} else {
    $slides = array_values( array_filter( $data['items'], static function ( $row ) {
        return '' !== $row['title'] || ! empty( $row['image']['url'] );
    } ) );
}

if ( empty( $slides ) ) {
    return;
}

$total    = count( $slides );
$multiple = $total > 1;
$uid      = 'bb-sb-' . ( ! empty( $item['id'] ) ? $item['id'] : $index );

$arrow = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M19 12H5M12 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<div class="bb-sb" data-bb-slide-boletim data-autoplay="<?php echo ( $data['autoplay'] && $multiple ) ? '1' : '0'; ?>" data-interval="<?php echo esc_attr( absint( $data['interval'] ) * 1000 ); ?>">
    <div class="bb-sb__stage">
        <div class="bb-sb__viewport" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Destaques', 'bireme-builder' ); ?>">
            <?php foreach ( $slides as $i => $slide ) : ?>
                <article
                    class="bb-sb__slide<?php echo 0 === $i ? ' is-active' : ''; ?>"
                    id="<?php echo esc_attr( $uid . '-slide-' . $i ); ?>"
                    data-index="<?php echo esc_attr( $i ); ?>"
                    role="group"
                    aria-roledescription="slide"
                    aria-label="<?php echo esc_attr( sprintf( '%d / %d', $i + 1, $total ) ); ?>"
                    <?php echo 0 === $i ? '' : 'aria-hidden="true"'; ?>
                >
                    <div class="bb-sb__media">
                        <?php
                        echo Bireme_Render::image( $slide['image'], 'full', array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            'class'   => 'bb-sb__img',
                            'loading' => 0 === $i ? 'eager' : 'lazy',
                            'alt'     => '' !== $slide['image']['alt'] ? $slide['image']['alt'] : '',
                        ) );
                        ?>
                    </div>
                    <?php
                    $slide_url    = ! empty( $slide['link']['url'] ) ? $slide['link']['url'] : '';
                    $slide_target = ( $slide_url && ! empty( $slide['link']['target'] ) && '_blank' === $slide['link']['target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
                    ?>
                    <?php if ( $slide_url ) : ?>
                        <a class="bb-sb__hit" href="<?php echo esc_url( $slide_url ); ?>"<?php echo $slide_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> tabindex="-1" aria-hidden="true"></a>
                    <?php endif; ?>
                    <?php
                    $edition_name = '';
                    $edition_url  = '';
                    if ( ! empty( $slide['edition']['name'] ) ) {
                        $edition_name = $slide['edition']['name'];
                        $edition_url  = ! empty( $slide['edition']['url'] ) ? $slide['edition']['url'] : '';
                    } elseif ( ! isset( $slide['edition'] ) && ! empty( $slide['category'] ) ) {
                        $edition_name = $slide['category'];
                    }
                    ?>
                    <?php if ( '' !== $edition_name ) : ?>
                        <p class="bb-sb__edition">
                            <?php if ( $edition_url ) : ?>
                                <a href="<?php echo esc_url( $edition_url ); ?>"><?php echo esc_html( $edition_name ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $edition_name ); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <div class="bb-sb__content">
                        <?php if ( '' !== $slide['title'] ) : ?>
                            <h2 class="bb-sb__title"><?php echo esc_html( $slide['title'] ); ?></h2>
                        <?php endif; ?>
                        <?php if ( '' !== $slide['text'] ) : ?>
                            <p class="bb-sb__text"><?php echo esc_html( $slide['text'] ); ?></p>
                        <?php endif; ?>
                        <?php
                        $button         = $slide['link'];
                        $button['text'] = '' !== $button['text'] ? $button['text'] : __( 'Ler agora', 'bireme-builder' );
                        echo Bireme_Render::link( $button, 'bb-sb__button' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if ( $multiple ) : ?>
                <span class="bb-sb__counter" aria-hidden="true"><span data-bb-current>1</span>/<?php echo esc_html( $total ); ?></span>

                <div class="bb-sb__dots">
                    <?php foreach ( $slides as $i => $slide ) : ?>
                        <button
                            type="button"
                            class="bb-sb__dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
                            data-bb-go="<?php echo esc_attr( $i ); ?>"
                            aria-controls="<?php echo esc_attr( $uid . '-slide-' . $i ); ?>"
                            aria-label="<?php echo esc_attr( sprintf( __( 'Ir para o slide %d', 'bireme-builder' ), $i + 1 ) ); ?>"
                            <?php echo 0 === $i ? 'aria-current="true"' : ''; ?>
                        ></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $multiple ) : ?>
            <button type="button" class="bb-sb__arrow bb-sb__arrow--prev" data-bb-prev aria-label="<?php esc_attr_e( 'Slide anterior', 'bireme-builder' ); ?>">
                <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </button>
            <button type="button" class="bb-sb__arrow bb-sb__arrow--next" data-bb-next aria-label="<?php esc_attr_e( 'Próximo slide', 'bireme-builder' ); ?>">
                <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if ( $multiple ) : ?>
        <div class="bb-sb__cards" style="--bb-sb-cols: <?php echo esc_attr( $total ); ?>;">
            <?php foreach ( $slides as $i => $slide ) : ?>
                <?php
                $card_url    = ! empty( $slide['link']['url'] ) ? $slide['link']['url'] : '';
                $card_target = ( $card_url && ! empty( $slide['link']['target'] ) && '_blank' === $slide['link']['target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
                $card_tag    = $card_url ? 'a' : 'button';
                ?>
                <<?php echo $card_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 'a' ou 'button'. ?>
                    <?php if ( $card_url ) : ?>
                        href="<?php echo esc_url( $card_url ); ?>"<?php echo $card_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php else : ?>
                        type="button"
                        data-bb-go="<?php echo esc_attr( $i ); ?>"
                        aria-controls="<?php echo esc_attr( $uid . '-slide-' . $i ); ?>"
                    <?php endif; ?>
                    class="bb-sb__card<?php echo 0 === $i ? ' is-active' : ''; ?>"
                    <?php echo ( 0 === $i && ! $card_url ) ? 'aria-current="true"' : ''; ?>
                >
                    <span class="bb-sb__card-media">
                        <?php
                        echo Bireme_Render::image( $slide['image'], 'medium_large', array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            'class'   => 'bb-sb__card-img',
                            'loading' => 'lazy',
                            'alt'     => '',
                        ) );
                        ?>
                    </span>
                    <?php if ( '' !== $slide['title'] ) : ?>
                        <span class="bb-sb__card-title"><?php echo esc_html( $slide['title'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( '' !== $slide['date'] ) : ?>
                        <span class="bb-sb__date"><?php echo esc_html( $slide['date'] ); ?></span>
                    <?php endif; ?>
                </<?php echo $card_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
