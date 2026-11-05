<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$count = max( 1, absint( $data['count'] ) );
$args  = array(
    'post_type' => $data['post_type'],
    'count'     => $count,
    'days'      => $data['days'],
);

$ranked = Bireme_Posts::query( array_merge( $args, array( 'orderby' => 'views' ) ) );

if ( $data['fill'] && count( $ranked ) < $count ) {
    $ranked = array_merge( $ranked, Bireme_Posts::query( array_merge( $args, array(
        'count'   => $count - count( $ranked ),
        'exclude' => wp_list_pluck( $ranked, 'ID' ),
    ) ) ) );
}

if ( empty( $ranked ) ) {
    return;
}
?>
<div class="bb-ml-wrap">
<div class="bb-ml">
    <?php if ( '' !== $data['title'] ) : ?>
        <h2 class="bb-ml__title"><?php echo esc_html( $data['title'] ); ?></h2>
    <?php endif; ?>

    <ol class="bb-ml__list">
        <?php foreach ( $ranked as $position => $post_item ) : ?>
            <?php $card = Bireme_Posts::card_data( $post_item ); ?>
            <li class="bb-ml__item">
                <a class="bb-ml__link" href="<?php echo esc_url( $card['link']['url'] ); ?>">
                    <span class="bb-ml__number" aria-hidden="true"><?php echo esc_html( $position + 1 ); ?></span>
                    <span class="bb-ml__text">
                        <span class="bb-ml__item-title"><?php echo esc_html( $card['title'] ); ?></span>
                        <span class="bb-ml__date"><?php echo esc_html( $card['date'] ); ?></span>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ol>
</div>
</div>
