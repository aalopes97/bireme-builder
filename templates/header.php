<?php
/**
 * Header Bireme. Pode ser sobrescrito em <tema>/bireme-builder/header.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$bireme_settings  = Bireme_Settings::get();
$bireme_title     = Bireme_Settings::text( 'title' );
$bireme_search_ph = Bireme_Settings::text( 'search_placeholder' );
$bireme_home      = Bireme_Layout::home_url();
$bireme_languages = $bireme_settings['show_languages'] ? Bireme_Layout::languages() : array();
$bireme_top_menu  = has_nav_menu( Bireme_Layout::MENU_TOP );
$bireme_top_links = $bireme_top_menu ? array() : Bireme_Settings::top_links();
$bireme_has_top   = $bireme_top_menu || ! empty( $bireme_top_links ) || ! empty( $bireme_languages );
?>
<header class="bireme-header">
    <?php if ( $bireme_has_top ) : ?>
        <div class="bireme-header__topbar">
            <div class="bireme-header__container bireme-header__topbar-inner">
                <?php if ( $bireme_top_menu ) : ?>
                    <nav class="bireme-header__toplinks" aria-label="<?php esc_attr_e( 'Links institucionais', 'bireme-builder' ); ?>">
                        <?php
                        wp_nav_menu( array(
                            'theme_location' => Bireme_Layout::MENU_TOP,
                            'container'      => false,
                            'menu_class'     => 'bireme-header__inline-list',
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ) );
                        ?>
                    </nav>
                <?php elseif ( $bireme_top_links ) : ?>
                    <nav class="bireme-header__toplinks" aria-label="<?php esc_attr_e( 'Links institucionais', 'bireme-builder' ); ?>">
                        <ul class="bireme-header__inline-list">
                            <?php foreach ( $bireme_top_links as $bireme_link ) : ?>
                                <li>
                                    <?php if ( $bireme_link['url'] ) : ?>
                                        <a href="<?php echo esc_url( $bireme_link['url'] ); ?>"><?php echo esc_html( $bireme_link['text'] ); ?></a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $bireme_link['text'] ); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php else : ?>
                    <span></span>
                <?php endif; ?>

                <?php if ( $bireme_languages ) : ?>
                    <nav class="bireme-header__langs" aria-label="<?php esc_attr_e( 'Idiomas', 'bireme-builder' ); ?>">
                        <ul class="bireme-header__inline-list">
                            <?php foreach ( $bireme_languages as $bireme_lang ) : ?>
                                <li<?php echo $bireme_lang['current'] ? ' class="is-current"' : ''; ?>>
                                    <a
                                        href="<?php echo esc_url( $bireme_lang['url'] ); ?>"
                                        <?php if ( $bireme_lang['locale'] ) : ?>
                                            lang="<?php echo esc_attr( $bireme_lang['locale'] ); ?>"
                                            hreflang="<?php echo esc_attr( $bireme_lang['locale'] ); ?>"
                                        <?php endif; ?>
                                        <?php echo $bireme_lang['current'] ? 'aria-current="true"' : ''; ?>
                                    ><?php echo esc_html( $bireme_lang['name'] ); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="bireme-header__main">
        <div class="bireme-header__container">
            <div class="bireme-header__brand-row">
                <?php if ( '' !== $bireme_title ) : ?>
                    <a class="bireme-header__brand" href="<?php echo esc_url( $bireme_home ); ?>">
                        <span class="bireme-header__title"><?php echo esc_html( $bireme_title ); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ( $bireme_settings['show_search'] ) : ?>
                    <form role="search" method="get" class="bireme-header__search" action="<?php echo esc_url( $bireme_home ); ?>">
                        <label class="bireme-sr-only" for="bireme-header-search"><?php echo esc_html( $bireme_search_ph ); ?></label>
                        <input type="search" id="bireme-header-search" name="s" placeholder="<?php echo esc_attr( $bireme_search_ph ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
                        <button type="submit" aria-label="<?php esc_attr_e( 'Buscar', 'bireme-builder' ); ?>">
                            <?php echo Bireme_Layout::icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </button>
                    </form>
                <?php endif; ?>

                <button type="button" class="bireme-header__toggle" aria-expanded="false" aria-controls="bireme-header-nav">
                    <span class="bireme-header__toggle-bars" aria-hidden="true"></span>
                    <span class="bireme-sr-only"><?php esc_html_e( 'Menu', 'bireme-builder' ); ?></span>
                </button>
            </div>

            <nav id="bireme-header-nav" class="bireme-header__nav" aria-label="<?php esc_attr_e( 'Menu principal', 'bireme-builder' ); ?>">
                <?php
                wp_nav_menu( array(
                    'theme_location' => Bireme_Layout::MENU_PRIMARY,
                    'container'      => false,
                    'menu_class'     => 'bireme-header__menu',
                    'depth'          => 2,
                    'fallback_cb'    => array( 'Bireme_Layout', 'menu_fallback' ),
                ) );
                ?>
            </nav>
        </div>
    </div>
</header>
