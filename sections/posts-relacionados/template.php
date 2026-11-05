<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_obj = Bireme_Single::current_post();
if ( ! $post_obj ) {
    return;
}

$count    = max( 1, absint( $data['count'] ) );
$settings = Bireme_Settings::get();
$keys     = array(
    'topic'   => array( 'single_topic_tax' ),
    'edition' => array( 'single_edition_tax' ),
    'both'    => array( 'single_topic_tax', 'single_edition_tax' ),
);

$tax_query = array( 'relation' => 'OR' );
foreach ( isset( $keys[ $data['relation'] ] ) ? $keys[ $data['relation'] ] : $keys['both'] as $setting ) {
    $taxonomy = $settings[ $setting ];
    if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
        continue;
    }
    $term_ids = wp_get_post_terms( $post_obj->ID, $taxonomy, array( 'fields' => 'ids' ) );
    if ( $term_ids && ! is_wp_error( $term_ids ) ) {
        $tax_query[] = array(
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $term_ids,
        );
    }
}

$base_args = array(
    'post_type'           => $post_obj->post_type,
    'post_status'         => 'publish',
    'posts_per_page'      => $count,
    'post__not_in'        => array( $post_obj->ID ),
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
    'suppress_filters'    => false,
);

$posts = array();
if ( count( $tax_query ) > 1 ) {
    $posts = get_posts( array_merge( $base_args, array( 'tax_query' => $tax_query ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
}

if ( $data['fill'] && count( $posts ) < $count ) {
    $exclude = array_merge( array( $post_obj->ID ), wp_list_pluck( $posts, 'ID' ) );
    $posts   = array_merge( $posts, get_posts( array_merge( $base_args, array(
        'posts_per_page' => $count - count( $posts ),
        'post__not_in'   => $exclude,
    ) ) ) );
}

if ( empty( $posts ) ) {
    return;
}

$more_url = ! empty( $data['more_link']['url'] ) ? $data['more_link']['url'] : '';
if ( ! $more_url ) {
    $topic = Bireme_Single::term( $post_obj, 'single_topic_tax' );
    $link  = $topic ? get_term_link( $topic ) : '';
    $more_url = ( $link && ! is_wp_error( $link ) ) ? $link : '';
}
$more_text = '' !== $data['more_link']['text'] ? $data['more_link']['text'] : __( 'Ver todos', 'bireme-builder' );
$arrow     = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<div class="bb-ap bb-rel">
    <?php if ( '' !== $data['title'] || $more_url ) : ?>
        <div class="bb-rel__head">
            <?php if ( '' !== $data['title'] ) : ?>
                <h2 class="bb-rel__title"><?php echo esc_html( $data['title'] ); ?></h2>
            <?php endif; ?>
            <?php if ( $more_url ) : ?>
                <a class="bb-rel__more" href="<?php echo esc_url( $more_url ); ?>"<?php echo '_blank' === $data['more_link']['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html( $more_text ); ?>
                    <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="bb-ap__grid">
        <?php foreach ( $posts as $related ) : ?>
            <?php echo Bireme_Posts::horizontal_card( Bireme_Posts::card_data( $related ), '', 'h3' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php endforeach; ?>
    </div>
</div>
