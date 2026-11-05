<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$icons = array(
    'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6"/><circle cx="12" cy="7.5" r=".6" fill="currentColor"/>',
    'document' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
    'book'     => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/>',
    'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
    'health'   => '<path d="M20.8 5.6a5 5 0 0 0-7.1 0L12 7.3l-1.7-1.7a5 5 0 1 0-7.1 7.1L12 21.5l8.8-8.8a5 5 0 0 0 0-7.1z"/><path d="M12 10v5M9.5 12.5h5"/>',
    'chart'    => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    'database' => '<ellipse cx="12" cy="5.5" rx="8" ry="2.5"/><path d="M4 5.5v13c0 1.4 3.6 2.5 8 2.5s8-1.1 8-2.5v-13M4 12c0 1.4 3.6 2.5 8 2.5s8-1.1 8-2.5"/>',
    'people'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2a5 5 0 0 1 5.5 5"/>',
    'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>',
    'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    'video'    => '<rect x="2.5" y="5.5" width="14" height="13" rx="2"/><path d="m16.5 10.5 5-3v9l-5-3"/>',
    'idea'     => '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.6 10.8c.7.6 1.1 1.4 1.1 2.2h5c0-.8.4-1.6 1.1-2.2A6 6 0 0 0 12 3z"/>',
    'star'     => '<path d="m12 3 2.8 5.8 6.2.9-4.5 4.4 1.1 6.2L12 17.4l-5.6 2.9 1.1-6.2L3 9.7l6.2-.9z"/>',
    'link'     => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
);

$title     = (string) $data['title'];
$text      = (string) $data['text'];
$url       = ! empty( $data['link']['url'] ) ? (string) $data['link']['url'] : '';
$target    = ( $url && '_blank' === $data['link']['target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
$link_text = '' !== (string) $data['link']['text'] ? (string) $data['link']['text'] : __( 'Saiba mais', 'bireme-builder' );
$whole     = $url && 'card' === $data['link_style'];

$media_html = '';
if ( 'image' === $data['media'] && ! empty( $data['image']['url'] ) ) {
    $media_html = '<div class="bb-mb__media bb-mb__media--' . esc_attr( $data['image_ratio'] ) . '">'
        . Bireme_Render::image( $data['image'], 'large', array( 'loading' => 'lazy' ) )
        . '</div>';
} elseif ( 'icon' === $data['media'] ) {
    $style = $data['icon_color'] ? ' style="--bb-mb-icon:' . esc_attr( $data['icon_color'] ) . ';"' : '';
    if ( 'custom' === $data['icon'] && ! empty( $data['icon_image']['url'] ) ) {
        $media_html = '<div class="bb-mb__icon bb-mb__icon--image"' . $style . '>' . Bireme_Render::image( $data['icon_image'], 'thumbnail', array( 'alt' => '' ) ) . '</div>';
    } elseif ( isset( $icons[ $data['icon'] ] ) ) {
        $media_html = '<div class="bb-mb__icon"' . $style . '><svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $icons[ $data['icon'] ] . '</svg></div>';
    }
}

if ( '' === $title && '' === $text && '' === $media_html ) {
    return;
}

$classes = array(
    'bb-mb',
    'bb-mb--' . ( 'left' === $data['layout'] ? 'left' : 'top' ),
    'bb-mb--align-' . ( 'center' === $data['align'] ? 'center' : 'left' ),
    'bb-mb--' . ( 'plain' === $data['style'] ? 'plain' : 'card' ),
);
if ( $whole ) {
    $classes[] = 'has-link';
}
$box_style = ( 'card' === $data['style'] && $data['bg_color'] ) ? ' style="--bb-mb-bg:' . esc_attr( $data['bg_color'] ) . ';"' : '';
$arrow     = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<div class="bb-mb-wrap">
    <article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $box_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <?php if ( $whole ) : ?>
            <a class="bb-mb__cover" href="<?php echo esc_url( $url ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php echo esc_attr( '' !== $title ? $title : $link_text ); ?>"></a>
        <?php endif; ?>
        <?php echo $media_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- montado acima com dados escapados. ?>
        <div class="bb-mb__body">
            <?php if ( '' !== $title ) : ?>
                <h3 class="bb-mb__title"><?php echo esc_html( $title ); ?></h3>
            <?php endif; ?>
            <?php if ( '' !== $text ) : ?>
                <p class="bb-mb__text"><?php echo nl2br( esc_html( $text ) ); ?></p>
            <?php endif; ?>
            <?php if ( $url && ! $whole ) : ?>
                <a class="bb-mb__link bb-mb__link--<?php echo 'text' === $data['link_style'] ? 'text' : 'button'; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <span><?php echo esc_html( $link_text ); ?></span>
                    <?php echo 'text' === $data['link_style'] ? $arrow : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            <?php endif; ?>
        </div>
    </article>
</div>
