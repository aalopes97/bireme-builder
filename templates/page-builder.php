<?php
/**
 * Template das páginas com Bireme Builder: header próprio, sem header/footer do tema.
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
<body <?php body_class( 'bireme-builder-page' ); ?>>
<?php wp_body_open(); ?>

<a class="bireme-skip-link" href="#bireme-main"><?php esc_html_e( 'Pular para o conteúdo', 'bireme-builder' ); ?></a>

<?php Bireme_Layout::render_header(); ?>

<main id="bireme-main" class="bireme-main">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
</main>

<?php Bireme_Layout::render_footer(); ?>

<?php wp_footer(); ?>
</body>
</html>
