<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$widths = array_map( 'absint', explode( '-', $data['layout'] ) );
$grid   = array();
foreach ( $widths as $width ) {
    $grid[] = 'minmax(0, ' . max( 1, $width ) . 'fr)';
}

$style = sprintf(
    '--bb-cols-template:%s;--bb-cols-gap:%dpx;',
    implode( ' ', $grid ),
    absint( $data['gap'] )
);
?>
<div class="bb-cols bb-cols--<?php echo esc_attr( count( $widths ) ); ?>" style="<?php echo esc_attr( $style ); ?>">
    <?php foreach ( $columns as $i => $column_html ) : ?>
        <div class="bb-cols__col bb-cols__col--<?php echo esc_attr( $i + 1 ); ?>">
            <?php echo $column_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML das seções filhas, já escapado nos templates. ?>
        </div>
    <?php endforeach; ?>
</div>
