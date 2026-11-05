<?php
/**
 * Template dos posts no layout Bireme.
 * Pode ser sobrescrito em <tema>/bireme-builder/single-builder.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
<body <?php body_class( 'bireme-builder-page bireme-builder-single' ); ?>>
<?php wp_body_open(); ?>

<a class="bireme-skip-link" href="#bireme-main"><?php esc_html_e( 'Pular para o conteúdo', 'bireme-builder' ); ?></a>

<?php Bireme_Layout::render_header(); ?>

<main id="bireme-main" class="bireme-main">
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <div class="bireme-archive-head">
            <p class="bireme-archive-head__title"><?php echo esc_html( Bireme_Single::title() ); ?></p>
            <?php Bireme_Layout::render_breadcrumb(); ?>
        </div>

        <?php echo Bireme_Render::render_sections( Bireme_Single::items() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML das seções, já escapado nos templates. ?>
        <?php
    endwhile;
    ?>
</main>

<?php Bireme_Layout::render_footer(); ?>

<?php wp_footer(); ?>
</body>
</html>
