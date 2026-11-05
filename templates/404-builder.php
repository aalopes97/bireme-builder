<?php
/**
 * Página 404 no layout Bireme.
 * Pode ser sobrescrito em <tema>/bireme-builder/404-builder.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$bireme_title     = Bireme_Settings::text( 'notfound_title' );
$bireme_title     = '' !== $bireme_title ? $bireme_title : __( 'Página não encontrada', 'bireme-builder' );
$bireme_text      = Bireme_Settings::text( 'notfound_text' );
$bireme_search_ph = Bireme_Settings::text( 'search_placeholder' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
        <title><?php echo esc_html( wp_get_document_title() ); ?></title>
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bireme-builder-page bireme-builder-404' ); ?>>
<?php wp_body_open(); ?>

<a class="bireme-skip-link" href="#bireme-main"><?php esc_html_e( 'Pular para o conteúdo', 'bireme-builder' ); ?></a>

<?php Bireme_Layout::render_header(); ?>

<main id="bireme-main" class="bireme-main">
    <div class="bireme-archive-head">
        <?php Bireme_Layout::render_breadcrumb( Bireme_404::breadcrumb() ); ?>
    </div>

    <section class="bireme-404" aria-labelledby="bireme-404-title">
        <div class="bireme-404__box">
            <p class="bireme-404__code" aria-hidden="true">404</p>
            <div class="bireme-404__body">
                <p class="bireme-404__kicker"><?php esc_html_e( 'Erro 404', 'bireme-builder' ); ?></p>
                <h1 id="bireme-404-title" class="bireme-404__title"><?php echo esc_html( $bireme_title ); ?></h1>
                <?php if ( '' !== $bireme_text ) : ?>
                    <p class="bireme-404__text"><?php echo esc_html( $bireme_text ); ?></p>
                <?php endif; ?>

                <form role="search" method="get" class="bireme-404__search" action="<?php echo esc_url( Bireme_Layout::home_url() ); ?>">
                    <label class="bireme-sr-only" for="bireme-404-search"><?php echo esc_html( $bireme_search_ph ); ?></label>
                    <input type="search" id="bireme-404-search" name="s" placeholder="<?php echo esc_attr( $bireme_search_ph ); ?>">
                    <button type="submit"><?php echo Bireme_Layout::icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Buscar', 'bireme-builder' ); ?></span></button>
                </form>

                <ul class="bireme-404__links">
                    <?php foreach ( Bireme_404::links() as $bireme_link ) : ?>
                        <li>
                            <a class="bireme-404__link<?php echo $bireme_link['primary'] ? ' is-primary' : ''; ?>" href="<?php echo esc_url( $bireme_link['url'] ); ?>"><?php echo esc_html( $bireme_link['text'] ); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <?php echo Bireme_Render::render_sections( Bireme_404::items() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML das seções, já escapado nos templates. ?>
</main>

<?php Bireme_Layout::render_footer(); ?>

<?php wp_footer(); ?>
</body>
</html>
