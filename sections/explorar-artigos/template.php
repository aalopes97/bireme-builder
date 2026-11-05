<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( 'auto' === $data['source'] ) {
    $cards = array();
    foreach ( Bireme_Posts::query( array(
        'post_type' => $data['post_type'],
        'category'  => $data['category'],
        'count'     => $data['count'],
        'exclude'   => $data['skip_shown'] ? Bireme_Posts::shown() : array(),
    ) ) as $post_item ) {
        $cards[] = Bireme_Posts::card_data( $post_item, '', 0 );
    }
    Bireme_Posts::mark_shown( wp_list_pluck( $cards, 'id' ) );
} else {
    $cards = array_values( array_filter( $data['items'], static function ( $row ) {
        return '' !== $row['title'];
    } ) );
}

if ( empty( $cards ) ) {
    return;
}

$arrow = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<div class="bb-ea" style="--bb-ea-cols: <?php echo esc_attr( absint( $data['columns'] ) ); ?>;">
    <?php if ( '' !== $data['title'] || ! empty( $data['more_link']['url'] ) ) : ?>
        <div class="bb-ea__head">
            <?php if ( '' !== $data['title'] ) : ?>
                <h2 class="bb-ea__title"><?php echo esc_html( $data['title'] ); ?></h2>
            <?php endif; ?>
            <?php if ( ! empty( $data['more_link']['url'] ) ) : ?>
                <a class="bb-ea__more" href="<?php echo esc_url( $data['more_link']['url'] ); ?>"<?php echo '_blank' === $data['more_link']['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html( '' !== $data['more_link']['text'] ? $data['more_link']['text'] : __( 'Ver todas as notícias', 'bireme-builder' ) ); ?>
                    <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="bb-ea__grid">
        <?php foreach ( $cards as $card ) : ?>
            <?php
            $url    = ! empty( $card['link']['url'] ) ? $card['link']['url'] : '';
            $target = ( $url && '_blank' === $card['link']['target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
            ?>
            <article class="bb-ea__card<?php echo $url ? ' has-link' : ''; ?>">
                <div class="bb-ea__media">
                    <?php echo Bireme_Render::image( $card['image'], 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <div class="bb-ea__body">
                    <?php if ( '' !== $card['category'] ) : ?>
                        <span class="bb-ea__category"><?php echo esc_html( $card['category'] ); ?></span>
                    <?php endif; ?>
                    <h3 class="bb-ea__card-title">
                        <?php if ( $url ) : ?>
                            <a href="<?php echo esc_url( $url ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $card['title'] ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( $card['title'] ); ?>
                        <?php endif; ?>
                    </h3>
                    <?php if ( '' !== $card['text'] ) : ?>
                        <p class="bb-ea__excerpt"><?php echo esc_html( $card['text'] ); ?></p>
                    <?php endif; ?>
                    <?php if ( '' !== $card['date'] ) : ?>
                        <span class="bb-ea__date"><?php echo esc_html( $card['date'] ); ?></span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
