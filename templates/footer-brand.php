<?php
/**
 * Rodapé Bireme, modelo "Fundo azul com triângulos": colunas de links e projetos institucionais;
 * abaixo, logo, copyright e redes sociais.
 * Pode ser sobrescrito em <tema>/bireme-builder/footer-brand.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$bireme_copyright = str_replace( '{ano}', wp_date( 'Y' ), Bireme_Settings::text( 'footer_copyright' ) );
$bireme_logo      = Bireme_Settings::footer_brand_logo();
$bireme_logo_link = Bireme_Settings::localize_url( Bireme_Settings::localized( 'footer_logo_link' ) );
$bireme_socials   = Bireme_Layout::social_links();
$bireme_columns   = array();
$bireme_col_menus = array(
    1 => Bireme_Layout::MENU_FOOTER_COL1,
    2 => Bireme_Layout::MENU_FOOTER_COL2,
    3 => Bireme_Layout::MENU_FOOTER_COL3,
);
foreach ( $bireme_col_menus as $bireme_col => $bireme_location ) {
    $bireme_col_title = Bireme_Settings::text( 'footer_col' . $bireme_col . '_title' );
    $bireme_has_col   = has_nav_menu( $bireme_location );
    $bireme_col_links = $bireme_has_col ? array() : Bireme_Settings::footer_column_links( $bireme_col );
    if ( $bireme_has_col || $bireme_col_links ) {
        $bireme_columns[] = array(
            'title'    => $bireme_col_title,
            'links'    => $bireme_col_links,
            'location' => $bireme_location,
            'menu'     => $bireme_has_col,
            'external' => 3 === $bireme_col,
        );
    }
}
$bireme_chevron = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false"><path d="m9 6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<footer class="bireme-footer bireme-footer--brand">
    <div class="bireme-footer__top">
        <div class="bireme-footer__top-inner">
            <?php if ( $bireme_columns ) : ?>
                <div class="bireme-footer__columns">
                    <?php foreach ( $bireme_columns as $bireme_column ) : ?>
                        <nav class="bireme-footer__column"<?php echo '' !== $bireme_column['title'] ? ' aria-label="' . esc_attr( $bireme_column['title'] ) . '"' : ''; ?>>
                            <?php if ( '' !== $bireme_column['title'] ) : ?>
                                <p class="bireme-footer__column-title"><?php echo esc_html( $bireme_column['title'] ); ?></p>
                            <?php endif; ?>
                            <?php if ( ! empty( $bireme_column['menu'] ) ) : ?>
                                <?php
                                wp_nav_menu( array(
                                    'theme_location' => $bireme_column['location'],
                                    'container'      => false,
                                    'menu_class'     => 'bireme-footer__column-list',
                                    'depth'          => 1,
                                    'fallback_cb'    => false,
                                    'link_after'     => $bireme_chevron,
                                ) );
                                ?>
                            <?php elseif ( $bireme_column['links'] ) : ?>
                                <ul class="bireme-footer__column-list">
                                    <?php foreach ( $bireme_column['links'] as $bireme_link ) : ?>
                                        <li>
                                            <?php if ( $bireme_link['url'] ) : ?>
                                                <?php $bireme_external = ! empty( $bireme_column['external'] ) || ( 0 === strpos( $bireme_link['url'], 'http' ) && false === strpos( $bireme_link['url'], (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ); ?>
                                                <a href="<?php echo esc_url( $bireme_link['url'] ); ?>"<?php echo $bireme_external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $bireme_link['text'] ); ?><?php echo $bireme_chevron; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
                                            <?php else : ?>
                                                <span><?php echo esc_html( $bireme_link['text'] ); ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </nav>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bireme-footer__bottom">
        <div class="bireme-footer__bottom-inner">
            <?php if ( $bireme_logo_link ) : ?>
                <a class="bireme-footer__logo<?php echo $bireme_logo[1] ? ' is-default' : ''; ?>" href="<?php echo esc_url( $bireme_logo_link ); ?>" target="_blank" rel="noopener noreferrer">
            <?php else : ?>
                <span class="bireme-footer__logo<?php echo $bireme_logo[1] ? ' is-default' : ''; ?>">
            <?php endif; ?>
                <img src="<?php echo esc_url( $bireme_logo[0] ); ?>" alt="<?php esc_attr_e( 'OPAS | BIREME', 'bireme-builder' ); ?>" loading="lazy">
            <?php echo $bireme_logo_link ? '</a>' : '</span>'; ?>

            <?php if ( $bireme_copyright ) : ?>
                <p class="bireme-footer__copyright"><?php echo esc_html( $bireme_copyright ); ?></p>
            <?php endif; ?>

            <?php if ( $bireme_socials ) : ?>
                <div class="bireme-footer__aside">
                    <ul class="bireme-footer__social">
                            <?php foreach ( $bireme_socials as $bireme_social ) : ?>
                                <li>
                                    <a href="<?php echo esc_url( $bireme_social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $bireme_social['label'] ); ?>">
                                        <?php echo Bireme_Layout::social_icon( $bireme_social['network'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>
<!-- Bireme Builder | Desenvolvido por @toni | https://2wp.com.br -->
