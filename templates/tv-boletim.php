<?php
/**
 * Modelo de página "TV - Boletim": tela fixa para TV, sem header/rodapé.
 * Pode ser sobrescrito em <tema>/bireme-builder/tv-boletim.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=1900">
    <meta name="robots" content="noindex, nofollow">
    <?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
        <title><?php echo esc_html( wp_get_document_title() ); ?></title>
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bireme-tv-page' ); ?>>
<?php wp_body_open(); ?>

<main class="bireme-tv-screen">
    <?php echo Bireme_Render::render_sections( Bireme_TV::items() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML das seções, já escapado nos templates. ?>
</main>

<?php wp_footer(); ?>
</body>
</html>
