<?php
/**
 * Rodapé das páginas com Bireme Builder.
 * Pode ser sobrescrito em <tema>/bireme-builder/footer.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$logo_link = Bireme_Settings::localize_url( Bireme_Settings::localized( 'footer_logo_link' ) );
$title     = Bireme_Settings::text( 'footer_title' );
$text      = Bireme_Settings::text( 'footer_text' );
$copyright = str_replace( '{ano}', wp_date( 'Y' ), Bireme_Settings::text( 'footer_copyright' ) );
$logo      = Bireme_Settings::footer_logo();
$socials   = Bireme_Layout::social_links();
?>
<footer class="bireme-footer">
    <div class="bireme-footer__inner">
        <div class="bireme-footer__brand">
            <?php if ( $logo ) : ?>
                <?php if ( $logo_link ) : ?>
                    <a class="bireme-footer__logo" href="<?php echo esc_url( $logo_link ); ?>" target="_blank" rel="noopener noreferrer">
                        <img src="<?php echo esc_url( $logo ); ?>" alt="<?php esc_attr_e( 'OPAS | BIREME', 'bireme-builder' ); ?>" loading="lazy">
                    </a>
                <?php else : ?>
                    <span class="bireme-footer__logo">
                        <img src="<?php echo esc_url( $logo ); ?>" alt="<?php esc_attr_e( 'OPAS | BIREME', 'bireme-builder' ); ?>" loading="lazy">
                    </span>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ( $title || $text ) : ?>
                <div class="bireme-footer__info">
                    <?php if ( $title ) : ?>
                        <p class="bireme-footer__title"><?php echo esc_html( $title ); ?></p>
                    <?php endif; ?>
                    <?php if ( $text ) : ?>
                        <p class="bireme-footer__text"><?php echo esc_html( $text ); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $copyright || $socials ) : ?>
            <div class="bireme-footer__aside">
                <?php if ( $copyright ) : ?>
                    <p class="bireme-footer__copyright"><?php echo esc_html( $copyright ); ?></p>
                <?php endif; ?>

                <?php if ( $socials ) : ?>
                    <ul class="bireme-footer__social">
                        <?php foreach ( $socials as $social ) : ?>
                            <li>
                                <a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
                                    <?php echo Bireme_Layout::social_icon( $social['network'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</footer>
<!-- Bireme Builder | Desenvolvido por @toni | https://2wp.com.br -->
