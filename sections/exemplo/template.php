<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$has_image = ! empty( $data['image']['url'] );
$items     = $data['show_items'] ? array_filter( $data['items'], static function ( $row ) {
    return '' !== $row['title'] || '' !== $row['text'];
} ) : array();
?>
<div class="bb-exemplo bb-exemplo--image-<?php echo esc_attr( $data['image_position'] ); ?><?php echo $has_image ? '' : ' bb-exemplo--no-image'; ?>"<?php echo $data['bg_color'] ? ' style="background-color:' . esc_attr( $data['bg_color'] ) . ';"' : ''; ?>>
    <div class="bb-exemplo__inner">
        <div class="bb-exemplo__content">
            <?php if ( '' !== $data['title'] ) : ?>
                <h2 class="bb-exemplo__title"><?php echo esc_html( $data['title'] ); ?></h2>
            <?php endif; ?>

            <?php if ( '' !== $data['subtitle'] ) : ?>
                <p class="bb-exemplo__subtitle"><?php echo nl2br( esc_html( $data['subtitle'] ) ); ?></p>
            <?php endif; ?>

            <?php if ( '' !== $data['text'] ) : ?>
                <div class="bb-exemplo__text"><?php echo Bireme_Render::wysiwyg( $data['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php endif; ?>

            <?php echo Bireme_Render::link( $data['button'], 'bb-exemplo__button' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>

        <?php if ( $has_image ) : ?>
            <div class="bb-exemplo__media">
                <?php echo Bireme_Render::image( $data['image'], 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( $items ) : ?>
        <ul class="bb-exemplo__items">
            <?php foreach ( $items as $row ) : ?>
                <li class="bb-exemplo__item">
                    <?php if ( '' !== $row['title'] ) : ?>
                        <strong class="bb-exemplo__item-title"><?php echo esc_html( $row['title'] ); ?></strong>
                    <?php endif; ?>
                    <?php if ( '' !== $row['text'] ) : ?>
                        <p class="bb-exemplo__item-text"><?php echo nl2br( esc_html( $row['text'] ) ); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
