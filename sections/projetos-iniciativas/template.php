<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$projects = array_values( array_filter( $data['items'], static function ( $row ) {
    return '' !== $row['title'] || ! empty( $row['logo']['id'] ) || ! empty( $row['logo']['url'] );
} ) );

if ( empty( $projects ) ) {
    return;
}

$arrow = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<div class="bb-pi" style="--bb-pi-cols: <?php echo esc_attr( absint( $data['columns'] ) ); ?>;">
    <?php if ( '' !== $data['title'] || ! empty( $data['more_link']['url'] ) ) : ?>
        <div class="bb-pi__head">
            <?php if ( '' !== $data['title'] ) : ?>
                <h2 class="bb-pi__title"><?php echo esc_html( $data['title'] ); ?></h2>
            <?php endif; ?>
            <?php if ( ! empty( $data['more_link']['url'] ) ) : ?>
                <a class="bb-pi__more" href="<?php echo esc_url( $data['more_link']['url'] ); ?>"<?php echo '_blank' === $data['more_link']['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html( '' !== $data['more_link']['text'] ? $data['more_link']['text'] : __( 'Ver todos os Projetos', 'bireme-builder' ) ); ?>
                    <?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="bb-pi__box">
        <ul class="bb-pi__grid">
            <?php foreach ( $projects as $project ) : ?>
                <?php
                $url    = ! empty( $project['link']['url'] ) ? $project['link']['url'] : '';
                $target = ( $url && '_blank' === $project['link']['target'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
                $logo   = Bireme_Render::image( $project['logo'], 'medium', array( 'loading' => 'lazy', 'alt' => '' ) );
                ?>
                <li class="bb-pi__item<?php echo $url ? ' has-link' : ''; ?>">
                    <?php if ( $logo ) : ?>
                        <div class="bb-pi__logo"><?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    <?php endif; ?>
                    <?php if ( '' !== $project['title'] ) : ?>
                        <h3 class="bb-pi__name">
                            <?php if ( $url ) : ?>
                                <a href="<?php echo esc_url( $url ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $project['title'] ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $project['title'] ); ?>
                            <?php endif; ?>
                        </h3>
                    <?php endif; ?>
                    <?php if ( '' !== $project['text'] ) : ?>
                        <p class="bb-pi__text"><?php echo esc_html( $project['text'] ); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
