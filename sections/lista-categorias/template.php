<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$taxonomy = taxonomy_exists( $data['taxonomy'] ) ? $data['taxonomy'] : 'category';
$args     = array(
    'taxonomy'   => $taxonomy,
    'hide_empty' => (bool) $data['hide_empty'],
    'orderby'    => 'count' === $data['orderby'] ? 'count' : 'name',
    'order'      => 'count' === $data['orderby'] ? 'DESC' : 'ASC',
    'number'     => absint( $data['number'] ),
);
if ( $data['parent_only'] ) {
    $args['parent'] = 0;
}
if ( 'category' === $taxonomy ) {
    $args['exclude'] = array( (int) get_option( 'default_category' ) );
}

$terms = get_terms( $args );
if ( is_wp_error( $terms ) || empty( $terms ) ) {
    return;
}

$style      = in_array( $data['style'], array( 'cards', 'list', 'chips' ), true ) ? $data['style'] : 'cards';
$current_id = ( is_category() || is_tag() || is_tax() ) ? (int) get_queried_object_id() : 0;
$charset    = get_bloginfo( 'charset' );
$arrow      = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$folder     = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M3 7.5A1.5 1.5 0 0 1 4.5 6h4.6l2 2h8.4A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg>';
$count_text = static function ( $count ) {
    /* translators: %d: quantidade de publicações */
    return sprintf( _n( '%d publicação', '%d publicações', $count, 'bireme-builder' ), $count );
};
?>
<div class="bb-lc bb-lc--<?php echo esc_attr( $style ); ?>" style="--bb-lc-cols: <?php echo esc_attr( absint( $data['columns'] ) ); ?>;">
    <?php if ( '' !== $data['title'] || ! empty( $data['more_link']['url'] ) ) : ?>
        <div class="bb-lc__head">
            <?php if ( '' !== $data['title'] ) : ?>
                <h2 class="bb-lc__title"><?php echo esc_html( $data['title'] ); ?></h2>
            <?php endif; ?>
            <?php if ( ! empty( $data['more_link']['url'] ) ) : ?>
                <a class="bb-lc__more" href="<?php echo esc_url( $data['more_link']['url'] ); ?>"<?php echo '_blank' === $data['more_link']['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html( '' !== $data['more_link']['text'] ? $data['more_link']['text'] : __( 'Ver todas', 'bireme-builder' ) ); ?>
                    <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ul class="bb-lc__items">
        <?php foreach ( $terms as $term ) : ?>
            <?php
            $link = get_term_link( $term );
            if ( is_wp_error( $link ) ) {
                continue;
            }
            $name    = html_entity_decode( $term->name, ENT_QUOTES, $charset );
            $current = $current_id === (int) $term->term_id;
            $aria    = $current ? ' aria-current="page"' : '';
            ?>
            <li class="bb-lc__item<?php echo $current ? ' is-current' : ''; ?>">
                <?php if ( 'cards' === $style ) : ?>
                    <a class="bb-lc__card" href="<?php echo esc_url( $link ); ?>"<?php echo $aria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                        <span class="bb-lc__icon"><?php echo $folder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span class="bb-lc__name"><?php echo esc_html( $name ); ?></span>
                        <?php if ( $data['show_description'] && '' !== trim( $term->description ) ) : ?>
                            <span class="bb-lc__desc"><?php echo esc_html( wp_trim_words( html_entity_decode( wp_strip_all_tags( $term->description ), ENT_QUOTES, $charset ), 18 ) ); ?></span>
                        <?php endif; ?>
                        <span class="bb-lc__foot">
                            <?php if ( $data['show_count'] ) : ?>
                                <span class="bb-lc__count"><?php echo esc_html( $count_text( (int) $term->count ) ); ?></span>
                            <?php endif; ?>
                            <span class="bb-lc__arrow"><?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        </span>
                    </a>
                <?php else : ?>
                    <a class="bb-lc__link" href="<?php echo esc_url( $link ); ?>"<?php echo $aria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                        <span class="bb-lc__name"><?php echo esc_html( $name ); ?></span>
                        <?php if ( $data['show_count'] ) : ?>
                            <span class="bb-lc__badge" aria-hidden="true"><?php echo esc_html( number_format_i18n( (int) $term->count ) ); ?></span>
                            <span class="bb-lc__sr"><?php echo esc_html( '(' . $count_text( (int) $term->count ) . ')' ); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
