<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$in_archive = Bireme_Archive::is_context();
$paged      = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

if ( $in_archive ) {
    global $wp_query;
    $list_query = $wp_query;
} else {
    $list_query = new WP_Query( array(
        'post_type'           => $data['post_type'] ? $data['post_type'] : 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => max( 1, absint( $data['per_page'] ) ),
        'paged'               => $paged,
        'ignore_sticky_posts' => true,
    ) );
}

$cards = array();
foreach ( $list_query->posts as $post_item ) {
    $cards[] = Bireme_Posts::card_data( $post_item );
}

if ( empty( $cards ) ) {
    if ( ! $in_archive ) {
        return;
    }
    ?>
    <div class="bb-ap">
        <p class="bb-ap__empty"><?php echo esc_html( $data['empty_text'] ); ?></p>
    </div>
    <?php
    return;
}

Bireme_Posts::mark_shown( wp_list_pluck( $cards, 'id' ) );

$uniform  = $in_archive && Bireme_Archive::is_edition_term();
$featured = ( ! $uniform && $data['featured'] ) ? array_shift( $cards ) : null;
$total      = (int) $list_query->max_num_pages;
$mode       = $data['pagination'];
$show_nums  = $total > 1 && in_array( $mode, array( 'both', 'numbers' ), true );
$next_url   = ( $paged < $total && in_array( $mode, array( 'both', 'loadmore' ), true ) ) ? get_pagenum_link( $paged + 1 ) : '';
$chevron    = static function ( $dir ) {
    $path = 'left' === $dir ? 'm15 6-6 6 6 6' : 'm9 6 6 6-6 6';
    return '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="' . $path . '" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
};

?>
<div class="bb-ap<?php echo $uniform ? ' bb-ap--uniform' : ''; ?>" data-bb-archive>
    <?php
    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML de Bireme_Posts::horizontal_card(), já escapado.
    if ( $featured ) {
        echo Bireme_Posts::horizontal_card( $featured, 'featured' );
    }
    ?>
    <div class="bb-ap__grid" data-bb-archive-grid><?php foreach ( $cards as $card ) { echo Bireme_Posts::horizontal_card( $card ); } ?></div>
    <?php // phpcs:enable ?>

    <?php if ( $show_nums || $next_url ) : ?>
        <div class="bb-ap__footer">
            <?php if ( $show_nums ) : ?>
                <nav class="bb-ap__pagination" aria-label="<?php esc_attr_e( 'Paginação', 'bireme-builder' ); ?>" data-bb-archive-pagination>
                    <?php
                    $links = paginate_links( array(
                        'total'     => $total,
                        'current'   => $paged,
                        'end_size'  => 1,
                        'mid_size'  => 3,
                        'prev_text' => $chevron( 'left' ) . '<span class="bireme-sr-only">' . esc_html__( 'Anterior', 'bireme-builder' ) . '</span>',
                        'next_text' => $chevron( 'right' ) . '<span class="bireme-sr-only">' . esc_html__( 'Próxima', 'bireme-builder' ) . '</span>',
                        'type'      => 'array',
                    ) );
                    ?>
                    <?php if ( $links ) : ?>
                        <ul class="bb-ap__pages">
                            <?php foreach ( $links as $page_link ) : ?>
                                <li><?php echo $page_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- gerado por paginate_links(). ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

            <?php if ( $next_url ) : ?>
                <a class="bb-ap__more" href="<?php echo esc_url( $next_url ); ?>" data-bb-archive-more data-loading="<?php esc_attr_e( 'Carregando...', 'bireme-builder' ); ?>"><?php echo esc_html( $data['load_more_text'] ); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
