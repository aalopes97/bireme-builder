<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$limit = max( 1, absint( $data['count'] ) );
$cap   = 0;
if ( Bireme_Single::is_context() || Bireme_Archive::is_context() ) {
    $cap   = max( 1, (int) apply_filters( 'bireme_builder_sidebar_topics_max', 4 ) );
    $limit = min( $limit, $cap );
}

if ( 'post' === $data['source'] ) {
    $themes     = array();
    $charset    = get_bloginfo( 'charset' );
    $current_id = Bireme_Single::is_context() ? (int) get_queried_object_id() : 0;
    $chosen     = $current_id ? Bireme_Topics::get_ids( $current_id ) : array();

    if ( $chosen ) {
        $topic_posts = get_posts( array(
            'post_type'        => 'any',
            'post__in'         => $chosen,
            'orderby'          => 'post__in',
            'posts_per_page'   => count( $chosen ),
            'lang'             => '',
        ) );
    } else {
        $topic_posts = get_posts( array(
            'post_type'           => Bireme_Single::post_types(),
            'post_status'         => 'publish',
            'posts_per_page'      => $limit,
            'post__not_in'        => $current_id ? array( $current_id ) : array(),
            'ignore_sticky_posts' => true,
            'suppress_filters'    => false,
        ) );
    }

    foreach ( $topic_posts as $topic_post ) {
        $excerpt  = has_excerpt( $topic_post ) ? $topic_post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $topic_post->post_content ) );
        $themes[] = array(
            'icon'     => array(),
            'title'    => html_entity_decode( wp_strip_all_tags( get_the_title( $topic_post ) ), ENT_QUOTES, $charset ),
            'subtitle' => html_entity_decode( wp_trim_words( $excerpt, 7 ), ENT_QUOTES, $charset ),
            'link'     => array(
                'url'    => get_permalink( $topic_post ),
                'text'   => '',
                'target' => '',
            ),
        );
    }
} elseif ( 'auto' === $data['source'] ) {
    $themes  = array();
    $charset = get_bloginfo( 'charset' );
    $terms   = get_terms( array(
        'taxonomy'   => 'category',
        'hide_empty' => true,
        'number'     => $limit,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'exclude'    => array( (int) get_option( 'default_category' ) ),
    ) );
    foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
        $link     = get_term_link( $term );
        $themes[] = array(
            'icon'     => array(),
            'title'    => html_entity_decode( $term->name, ENT_QUOTES, $charset ),
            'subtitle' => wp_trim_words( html_entity_decode( wp_strip_all_tags( $term->description ), ENT_QUOTES, $charset ), 8 ),
            'link'     => array(
                'url'    => is_wp_error( $link ) ? '' : $link,
                'text'   => '',
                'target' => '',
            ),
        );
    }
} else {
    $themes = array_values( array_filter( $data['items'], static function ( $row ) {
        return '' !== $row['title'];
    } ) );
}

if ( $cap ) {
    $themes = array_slice( $themes, 0, $cap );
}

if ( empty( $themes ) ) {
    return;
}

$info_icon  = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7.5" r="1.3" fill="currentColor"/></svg>';
$arrow_icon = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<div class="bb-td-wrap">
<div class="bb-td">
    <?php if ( '' !== $data['title'] ) : ?>
        <h2 class="bb-td__title"><?php echo esc_html( $data['title'] ); ?></h2>
    <?php endif; ?>

    <ul class="bb-td__list">
        <?php foreach ( $themes as $theme ) : ?>
            <?php
            $url = ! empty( $theme['link']['url'] ) ? $theme['link']['url'] : '';
            $tag = $url ? 'a' : 'div';
            $attrs = $url ? ' href="' . esc_url( $url ) . '"' . ( '_blank' === $theme['link']['target'] ? ' target="_blank" rel="noopener noreferrer"' : '' ) : '';
            ?>
            <li class="bb-td__item">
                <<?php echo $tag; ?> class="bb-td__link"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <span class="bb-td__icon">
                        <?php
                        if ( ! empty( $theme['icon']['url'] ) ) {
                            echo Bireme_Render::image( $theme['icon'], 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } else {
                            echo $info_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                        ?>
                    </span>
                    <span class="bb-td__text">
                        <span class="bb-td__item-title"><?php echo esc_html( $theme['title'] ); ?></span>
                        <?php if ( '' !== $theme['subtitle'] ) : ?>
                            <span class="bb-td__item-subtitle"><?php echo esc_html( $theme['subtitle'] ); ?></span>
                        <?php endif; ?>
                    </span>
                    <?php if ( $url ) : ?>
                        <span class="bb-td__arrow"><?php echo $arrow_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                </<?php echo $tag; ?>>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
</div>
