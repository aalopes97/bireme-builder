<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$groups  = Bireme_Editions::grouped( absint( $data['years'] ) );
$uid     = 'bb-ed-' . substr( md5( wp_json_encode( $data ) ), 0, 6 );
$arrow   = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$chevron = '<svg class="bb-ed__chevron" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';

$articles_text = static function ( $count ) {
    /* translators: %d: quantidade de artigos */
    return sprintf( _n( '%d artigo', '%d artigos', $count, 'bireme-builder' ), $count );
};
?>
<?php if ( $data['page_head'] && '' !== $data['title'] ) : ?>
    <div class="bireme-archive-head">
        <h1 class="bireme-archive-head__title"><?php echo esc_html( $data['title'] ); ?></h1>
        <?php
        Bireme_Layout::render_breadcrumb( array(
            array( 'text' => __( 'Início', 'bireme-builder' ), 'url' => Bireme_Layout::home_url() ),
            array( 'text' => is_page() ? get_the_title( get_queried_object_id() ) : $data['title'], 'url' => '' ),
        ) );
        ?>
    </div>
    <?php $data['title'] = ''; ?>
<?php endif; ?>
<div class="bb-ed">
    <?php if ( '' !== $data['title'] || '' !== $data['text'] ) : ?>
        <div class="bb-ed__head">
            <?php if ( '' !== $data['title'] ) : ?>
                <h2 class="bb-ed__title"><?php echo esc_html( $data['title'] ); ?></h2>
            <?php endif; ?>
            <?php if ( '' !== $data['text'] ) : ?>
                <p class="bb-ed__intro"><?php echo nl2br( esc_html( $data['text'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ( ! $groups ) : ?>
        <?php if ( '' !== $data['empty_text'] ) : ?>
            <p class="bb-ed__empty"><?php echo esc_html( $data['empty_text'] ); ?></p>
        <?php endif; ?>
    <?php else : ?>

        <?php if ( $data['year_nav'] && count( $groups ) > 1 ) : ?>
            <nav class="bb-ed__years-nav" aria-label="<?php esc_attr_e( 'Anos', 'bireme-builder' ); ?>">
                <?php foreach ( array_keys( $groups ) as $year ) : ?>
                    <a href="#<?php echo esc_attr( $uid . '-' . $year ); ?>" data-bb-ed-year="<?php echo esc_attr( $year ); ?>"><?php echo esc_html( $year ); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php $first = true; ?>
        <?php foreach ( $groups as $year => $months ) : ?>
            <?php
            $collapsible = $data['collapse'] && ! $first;
            $tag         = $collapsible ? 'details' : 'section';
            ?>
            <<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="bb-ed__year<?php echo $collapsible ? ' is-collapsible' : ''; ?>" id="<?php echo esc_attr( $uid . '-' . $year ); ?>">
                <?php if ( $collapsible ) : ?><summary class="bb-ed__year-head"><?php else : ?><div class="bb-ed__year-head"><?php endif; ?>
                    <h3 class="bb-ed__year-title"><?php echo esc_html( $year ); ?></h3>
                    <?php if ( $collapsible ) : ?>
                        <?php echo $chevron; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endif; ?>
                <?php if ( $collapsible ) : ?></summary><?php else : ?></div><?php endif; ?>

                <ol class="bb-ed__grid">
                    <?php foreach ( $months as $month => $month_editions ) : ?>
                        <?php foreach ( $month_editions as $edition ) : ?>
                            <li class="bb-ed__item">
                                <a class="bb-ed__card<?php echo $edition['image'] ? ' has-image' : ''; ?>" href="<?php echo esc_url( $edition['url'] ); ?>">
                                    <?php if ( $edition['image'] ) : ?>
                                        <span class="bb-ed__cover"><img src="<?php echo esc_url( $edition['image'] ); ?>" alt="" loading="lazy"></span>
                                    <?php endif; ?>
                                    <span class="bb-ed__month">
                                        <time datetime="<?php echo esc_attr( gmdate( 'Y-m', $edition['time'] ) ); ?>"><?php echo esc_html( Bireme_Editions::month_name( $month ) ); ?></time>
                                    </span>
                                    <span class="bb-ed__name"><?php echo esc_html( $edition['name'] ); ?></span>
                                    <span class="bb-ed__foot">
                                        <?php if ( $data['show_count'] && null !== $edition['count'] ) : ?>
                                            <span class="bb-ed__count"><?php echo esc_html( $articles_text( $edition['count'] ) ); ?></span>
                                        <?php endif; ?>
                                        <span class="bb-ed__arrow"><?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ol>
            </<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <?php $first = false; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
