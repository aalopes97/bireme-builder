<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_obj = Bireme_Single::current_post();
if ( ! $post_obj ) {
    return;
}

$is_single = Bireme_Single::is_context();
$edition   = $data['show_edition'] ? Bireme_Single::term( $post_obj, 'single_edition_tax' ) : null;
$topic     = $data['show_topic'] ? Bireme_Single::term( $post_obj, 'single_topic_tax' ) : null;
$title_tag = $is_single ? 'h1' : 'h2';
$content   = apply_filters( 'the_content', get_the_content( null, false, $post_obj ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- filtro do core.
$content   = str_replace( ']]>', ']]&gt;', $content );

$icons = array(
    'date'   => '<rect x="3.5" y="5" width="17" height="15" rx="2.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M3.5 10h17M8 3v4M16 3v4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    'time'   => '<circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 7.5V12l3 2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
    'topic'  => '<path d="M3.5 12.6V4.5a1 1 0 0 1 1-1h8.1a1 1 0 0 1 .7.3l7.2 7.2a1 1 0 0 1 0 1.4l-8.1 8.1a1 1 0 0 1-1.4 0l-7.2-7.2a1 1 0 0 1-.3-.7z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>',
    'listen' => '<path d="M4 9.5h3.5L12 5.5v13l-4.5-4H4z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M15.5 9a4 4 0 0 1 0 6M18 6.5a7.5 7.5 0 0 1 0 11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    'stop'   => '<rect x="6" y="6" width="12" height="12" rx="2" fill="currentColor"/>',
);
$icon = static function ( $name ) use ( $icons ) {
    return '<svg class="bb-pc__icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
};
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- ícones SVG fixos acima.
?>
<div class="bb-pc-wrap">
<article class="bb-pc" data-bb-post>
    <?php if ( $edition ) : ?>
        <?php $edition_link = get_term_link( $edition ); ?>
        <p class="bb-pc__edition">
            <?php if ( ! is_wp_error( $edition_link ) ) : ?>
                <a href="<?php echo esc_url( $edition_link ); ?>"><?php echo esc_html( Bireme_Single::term_name( $edition ) ); ?></a>
            <?php else : ?>
                <?php echo esc_html( Bireme_Single::term_name( $edition ) ); ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <<?php echo $title_tag; ?> class="bb-pc__title" data-bb-post-title><?php echo esc_html( html_entity_decode( wp_strip_all_tags( get_the_title( $post_obj ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ); ?></<?php echo $title_tag; ?>>

    <?php if ( $data['show_date'] || $data['show_reading'] || $topic || $data['show_listen'] ) : ?>
        <ul class="bb-pc__meta">
            <?php if ( $data['show_date'] ) : ?>
                <li><?php echo $icon( 'date' ); ?><time datetime="<?php echo esc_attr( get_the_date( 'c', $post_obj ) ); ?>"><?php echo esc_html( get_the_date( '', $post_obj ) ); ?></time></li>
            <?php endif; ?>
            <?php if ( $data['show_reading'] ) : ?>
                <?php $minutes = Bireme_Single::reading_time( $post_obj ); ?>
                <li><?php echo $icon( 'time' ); ?><span><?php echo esc_html( sprintf( /* translators: %d: minutos */ _n( '%d min de leitura', '%d min de leitura', $minutes, 'bireme-builder' ), $minutes ) ); ?></span></li>
            <?php endif; ?>
            <?php if ( $topic ) : ?>
                <?php $topic_link = get_term_link( $topic ); ?>
                <li class="bb-pc__topic"><?php echo $icon( 'topic' ); ?>
                    <?php if ( ! is_wp_error( $topic_link ) ) : ?>
                        <a href="<?php echo esc_url( $topic_link ); ?>"><?php echo esc_html( Bireme_Single::term_name( $topic ) ); ?></a>
                    <?php else : ?>
                        <span><?php echo esc_html( Bireme_Single::term_name( $topic ) ); ?></span>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
            <?php if ( $data['show_listen'] ) : ?>
                <li class="bb-pc__listen" data-bb-listen hidden>
                    <button type="button" class="bb-pc__listen-btn" data-bb-listen-toggle aria-pressed="false"
                        data-label-play="<?php echo esc_attr( $data['listen_text'] ); ?>"
                        data-label-pause="<?php esc_attr_e( 'Pausar leitura', 'bireme-builder' ); ?>"
                        data-label-resume="<?php esc_attr_e( 'Continuar leitura', 'bireme-builder' ); ?>">
                        <?php echo $icon( 'listen' ); ?><span data-bb-listen-label><?php echo esc_html( $data['listen_text'] ); ?></span>
                    </button>
                    <button type="button" class="bb-pc__listen-stop" data-bb-listen-stop hidden aria-label="<?php esc_attr_e( 'Parar leitura', 'bireme-builder' ); ?>">
                        <?php echo $icon( 'stop' ); ?>
                    </button>
                </li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

    <?php if ( $data['show_image'] && has_post_thumbnail( $post_obj ) ) : ?>
        <figure class="bb-pc__featured">
            <?php echo get_the_post_thumbnail( $post_obj, 'large' ); ?>
        </figure>
    <?php endif; ?>

    <div class="bb-pc__content" data-bb-post-content>
        <?php echo $content; // Conteúdo do post já filtrado pelo the_content. ?>
    </div>
</article>
</div>
<?php
// phpcs:enable
