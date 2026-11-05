<?php
/**
 * Breadcrumb dos arquivos e posts. Pode ser sobrescrito em <tema>/bireme-builder/breadcrumb.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( empty( $bireme_crumbs ) ) {
    $bireme_crumbs = Bireme_Single::is_context() ? Bireme_Single::breadcrumb() : Bireme_Archive::breadcrumb();
}
$bireme_last   = count( $bireme_crumbs ) - 1;
?>
<nav class="bireme-breadcrumb" aria-label="<?php esc_attr_e( 'Você está em', 'bireme-builder' ); ?>">
    <ol class="bireme-breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
        <?php foreach ( $bireme_crumbs as $bireme_i => $bireme_crumb ) : ?>
            <li class="bireme-breadcrumb__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <?php if ( $bireme_crumb['url'] ) : ?>
                    <a href="<?php echo esc_url( $bireme_crumb['url'] ); ?>" itemprop="item"><span itemprop="name"><?php echo esc_html( $bireme_crumb['text'] ); ?></span></a>
                <?php else : ?>
                    <span itemprop="name"<?php echo $bireme_i === $bireme_last ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $bireme_crumb['text'] ); ?></span>
                <?php endif; ?>
                <meta itemprop="position" content="<?php echo esc_attr( $bireme_i + 1 ); ?>">
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
