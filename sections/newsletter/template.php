<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$uid         = 'bb-nl-' . ( ! empty( $item['id'] ) ? $item['id'] : $index );
$is_external = 'external' === $data['destination'];

if ( $is_external && '' === trim( $data['external_url'] ) ) {
    return;
}

$messages = array(
    'ok'      => $data['success'],
    'invalid' => __( 'Informe um e-mail válido.', 'bireme-builder' ),
    'limited' => __( 'Muitas tentativas. Tente novamente mais tarde.', 'bireme-builder' ),
    'error'   => __( 'Não foi possível concluir a inscrição. Tente novamente.', 'bireme-builder' ),
);

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- só exibe a mensagem do redirecionamento sem JS.
$status  = ( ! $is_external && isset( $_GET['bireme_nl'] ) ) ? sanitize_key( wp_unslash( $_GET['bireme_nl'] ) ) : '';
$message = isset( $messages[ $status ] ) ? $messages[ $status ] : '';

$current_url = home_url( add_query_arg( array() ) );
$current_url = remove_query_arg( 'bireme_nl', $current_url );
$lang        = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
$field_name  = $is_external ? ( '' !== $data['external_field'] ? $data['external_field'] : 'EMAIL' ) : 'email';
?>
<div class="bb-nl-wrap" id="<?php echo esc_attr( $uid ); ?>">
<div class="bb-nl">
    <div class="bb-nl__top">
        <div class="bb-nl__text">
            <?php if ( '' !== $data['title'] ) : ?>
                <h2 class="bb-nl__title"><?php echo esc_html( $data['title'] ); ?></h2>
            <?php endif; ?>
            <?php if ( '' !== $data['text'] ) : ?>
                <p class="bb-nl__desc"><?php echo esc_html( $data['text'] ); ?></p>
            <?php endif; ?>
        </div>
        <div class="bb-nl__media" aria-hidden="true">
            <?php $media = Bireme_Render::image( $data['image'], 'medium', array( 'alt' => '' ) ); ?>
            <?php if ( $media ) : ?>
                <?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php else : ?>
                <svg class="bb-nl__icon" viewBox="0 0 96 96" width="96" height="96" focusable="false">
                    <path d="M10 52 84 18 66 80 46 64z" fill="#1A5FD0"/>
                    <path d="M46 64 84 18 40 58z" fill="#0D47A8"/>
                    <rect x="30" y="26" width="40" height="28" rx="4" fill="#FE5604"/>
                    <path d="m30 30 20 14 20-14" fill="none" stroke="#fff" stroke-width="3" stroke-linejoin="round"/>
                    <circle cx="70" cy="24" r="9" fill="#FE5604" stroke="#fff" stroke-width="3"/>
                    <path d="M70 20v8" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M8 70h14M14 78h12M4 62h10" stroke="#FE8A4C" stroke-width="4" stroke-linecap="round"/>
                </svg>
            <?php endif; ?>
        </div>
    </div>

    <form
        class="bb-nl__form"
        method="post"
        <?php if ( $is_external ) : ?>
            action="<?php echo esc_url( $data['external_url'] ); ?>"
            <?php echo $data['external_blank'] ? 'target="_blank"' : ''; ?>
        <?php else : ?>
            action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
            data-bb-newsletter
            data-endpoint="<?php echo esc_url( rest_url( 'bireme/v1/newsletter' ) ); ?>"
            data-messages="<?php echo esc_attr( wp_json_encode( $messages ) ); ?>"
        <?php endif; ?>
    >
        <label class="bb-nl__sr-only" for="<?php echo esc_attr( $uid . '-email' ); ?>"><?php echo esc_html( $data['placeholder'] ); ?></label>
        <div class="bb-nl__field">
            <input
                type="email"
                id="<?php echo esc_attr( $uid . '-email' ); ?>"
                name="<?php echo esc_attr( $field_name ); ?>"
                placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>"
                autocomplete="email"
                required
            >
            <button type="submit"><?php echo esc_html( $data['button_text'] ); ?></button>
        </div>

        <?php if ( ! $is_external ) : ?>
            <input type="hidden" name="action" value="<?php echo esc_attr( Bireme_Newsletter::ACTION ); ?>">
            <input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>">
            <input type="hidden" name="redirect" value="<?php echo esc_url( $current_url ); ?>">
            <input type="hidden" name="anchor" value="<?php echo esc_attr( $uid ); ?>">
            <div class="bb-nl__hp" aria-hidden="true">
                <label><?php esc_html_e( 'Não preencha este campo', 'bireme-builder' ); ?> <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <p class="bb-nl__message<?php echo $status ? ' is-' . esc_attr( 'ok' === $status ? 'success' : 'error' ) : ''; ?>" role="status" aria-live="polite"><?php echo esc_html( $message ); ?></p>
        <?php endif; ?>
    </form>
</div>
</div>
