<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Alternância de idiomas. Com tradução desta página em outros idiomas (Polylang), a TV abre
 * a tradução seguinte; sem tradução, recarrega esta mesma página com ?bb_tv_lang= e mostra
 * as notícias e os textos fixos do idioma pedido.
 */
$page_id       = (int) get_queried_object_id();
$rotate        = isset( $data['rotate_langs'] ) ? (string) $data['rotate_langs'] : 'none';
$languages     = function_exists( 'pll_languages_list' ) ? array_values( (array) pll_languages_list( array( 'fields' => 'slug' ) ) ) : array();
$page_lang     = ( $page_id && function_exists( 'pll_get_post_language' ) ) ? (string) pll_get_post_language( $page_id, 'slug' ) : '';
if ( '' === $page_lang && $languages ) {
    $page_lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
    if ( '' === $page_lang || ! in_array( $page_lang, $languages, true ) ) {
        $page_lang = function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : $languages[0];
    }
}
$view_lang     = $page_lang;
$next_lang_url = '';

$requested = isset( $_GET['bb_tv_lang'] ) ? sanitize_key( wp_unslash( $_GET['bb_tv_lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( '' !== $requested && in_array( $requested, $languages, true ) ) {
    $view_lang = $requested;
}

if ( 'none' !== $rotate && count( $languages ) > 1 && $page_id ) {
    $translations = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( $page_id ) : array();
    $translated   = array();
    foreach ( $languages as $slug ) {
        if ( ! empty( $translations[ $slug ] ) && 'publish' === get_post_status( $translations[ $slug ] ) ) {
            $translated[ $slug ] = (int) $translations[ $slug ];
        }
    }

    $position = array_search( $view_lang, $languages, true );
    $position = false === $position ? 0 : $position;
    $next     = $languages[ ( $position + 1 ) % count( $languages ) ];

    if ( count( $translated ) > 1 && isset( $translated[ $view_lang ] ) && $translated[ $view_lang ] === $page_id ) {
        // Páginas traduzidas: segue para a tradução do próximo idioma que tiver uma.
        $slugs         = array_keys( $translated );
        $here          = array_search( $view_lang, $slugs, true );
        $next_lang_url = get_permalink( $translated[ $slugs[ ( $here + 1 ) % count( $slugs ) ] ] );
    } else {
        $next_lang_url = $next === $page_lang
            ? get_permalink( $page_id )
            : add_query_arg( 'bb_tv_lang', $next, get_permalink( $page_id ) );
    }
}

$other_lang = '' !== $view_lang && $view_lang !== $page_lang;
$view_slug  = Bireme_I18n::lang_slug( $view_lang );
$page_slug  = Bireme_I18n::lang_slug( $page_lang );

/** Texto da tela no idioma exibido: os textos padrão são trocados pela versão do idioma. */
$tv_text = static function ( $text, $source = '' ) use ( $other_lang, $view_slug, $page_slug ) {
    if ( ! $other_lang ) {
        return '' !== $source ? __( $source, 'bireme-builder' ) : $text; // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
    }
    $text = '' !== $source ? $source : (string) $text;
    foreach ( Bireme_I18n::catalog() as $pt => $versions ) {
        $same = $pt === $text || ( '' !== $page_slug && isset( $versions[ $page_slug ] ) && $versions[ $page_slug ] === $text );
        if ( $same ) {
            return '' === $view_slug ? $pt : ( isset( $versions[ $view_slug ] ) ? $versions[ $view_slug ] : $pt );
        }
    }
    return $text;
};

$switched = false;
if ( $other_lang && function_exists( 'PLL' ) && PLL()->model ) {
    $language = PLL()->model->get_language( $view_lang );
    if ( $language && ! empty( $language->locale ) ) {
        $switched = switch_to_locale( $language->locale );
    }
}

if ( 'auto' === $data['source'] ) {
    $news = array();
    foreach ( Bireme_Posts::query( array(
        'post_type' => $data['post_type'],
        'category'  => $data['category'],
        'count'     => $data['count'],
        'lang'      => $other_lang ? $view_lang : '',
    ) ) as $post_item ) {
        $news[] = Bireme_Posts::card_data( $post_item );
    }
} else {
    $news = array_values( array_filter( $data['items'], static function ( $row ) {
        return '' !== $row['title'];
    } ) );
}

if ( $switched ) {
    restore_previous_locale();
}

if ( empty( $news ) ) {
    if ( $next_lang_url && $other_lang ) {
        // Idioma sem notícias: segue direto para o próximo.
        printf( '<script>window.location.replace(%s);</script>', wp_json_encode( esc_url_raw( $next_lang_url ) ) );
    }
    return;
}

$qr_api = static function ( $url ) {
    return $url ? 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=0&color=1E1E1E&data=' . rawurlencode( $url ) : '';
};

$first_url = ! empty( $news[0]['link']['url'] ) ? $news[0]['link']['url'] : '';
$qr_src    = '';
if ( 'current' === $data['qr'] ) {
    $qr_src = $qr_api( $first_url );
} elseif ( 'image' === $data['qr'] ) {
    $qr_src = Bireme_Render::image_url( $data['qr_image'], 'medium' );
}

$total     = count( $news );
$header_bg = Bireme_Render::image_url( $data['logo'], 'full' );
if ( ! $header_bg ) {
    $header_bg = BIREME_BUILDER_URL . 'assets/img/' . rawurlencode( 'header-tv-es (1).jpg' );
}
$lang = str_replace( '_', '-', get_locale() );
if ( $other_lang && function_exists( 'PLL' ) && PLL()->model ) {
    $language = PLL()->model->get_language( $view_lang );
    if ( $language && ! empty( $language->locale ) ) {
        $lang = str_replace( '_', '-', $language->locale );
    }
}
?>
<div class="bb-tv" data-bb-tv data-interval="<?php echo esc_attr( max( 3, absint( $data['interval'] ) ) ); ?>" data-refresh="<?php echo esc_attr( absint( $data['refresh'] ) ); ?>" data-lang="<?php echo esc_attr( $lang ); ?>"<?php if ( $next_lang_url ) : ?> data-next-lang="<?php echo esc_url( $next_lang_url ); ?>" data-rotate="<?php echo esc_attr( $rotate ); ?>"<?php endif; ?>>
    <header class="bb-tv__header" style="background-image: url('<?php echo esc_url( $header_bg ); ?>');">
        <h1 class="screen-reader-text">OPS | BIREME</h1>
    </header>

    <div class="bb-tv__body">
        <div class="bb-tv__stage" aria-live="polite">
            <?php foreach ( $news as $i => $item ) : ?>
                <?php
                $url   = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '';
                $image = Bireme_Render::image_url( $item['image'], 'full' );
                ?>
                <figure class="bb-tv__slide<?php echo 0 === $i ? ' is-active' : ''; ?><?php echo $image ? '' : ' is-noimage'; ?>" data-bb-tv-slide data-qr="<?php echo esc_url( 'current' === $data['qr'] ? $qr_api( $url ) : '' ); ?>"<?php echo 0 === $i ? '' : ' aria-hidden="true"'; ?>>
                    <?php if ( $image ) : ?>
                        <img class="bb-tv__image" src="<?php echo esc_url( $image ); ?>" alt="" <?php echo $i > 1 ? 'loading="lazy"' : ''; ?>>
                    <?php endif; ?>
                    <figcaption class="bb-tv__caption">
                        <?php if ( ! empty( $item['category'] ) || ! empty( $item['date'] ) ) : ?>
                            <div class="bb-tv__meta">
                                <?php if ( ! empty( $item['category'] ) ) : ?>
                                    <span class="bb-tv__tag"><?php echo esc_html( $item['category'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $item['date'] ) ) : ?>
                                    <span class="bb-tv__date"><?php echo esc_html( $item['date'] ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="bb-tv__title"><?php echo esc_html( $item['title'] ); ?></h2>
                    </figcaption>
                </figure>
            <?php endforeach; ?>

            <div class="bb-tv__status" aria-hidden="true">
                <span class="bb-tv__counter"><b data-bb-tv-index>01</b> / <?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
                <?php if ( $total > 1 ) : ?>
                    <span class="bb-tv__segments">
                        <?php for ( $i = 0; $i < $total; $i++ ) : ?>
                            <span class="bb-tv__seg" data-bb-tv-seg><i></i></span>
                        <?php endfor; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <aside class="bb-tv__side">
            <?php if ( '' !== $data['sidebar_title'] ) : ?>
                <h2 class="bb-tv__side-title"><?php echo esc_html( $tv_text( $data['sidebar_title'] ) ); ?></h2>
            <?php endif; ?>

            <div class="bb-tv__list-wrap">
                <ol class="bb-tv__list" data-bb-tv-list>
                    <?php foreach ( $news as $i => $item ) : ?>
                        <?php $thumb = Bireme_Render::image_url( $item['image'], 'thumbnail' ); ?>
                        <li class="bb-tv__item<?php echo 0 === $i ? ' is-active' : ''; ?>" data-bb-tv-item>
                            <span class="bb-tv__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
                            <?php if ( $thumb ) : ?>
                                <img class="bb-tv__thumb" src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
                            <?php endif; ?>
                            <span class="bb-tv__item-title"><?php echo esc_html( $item['title'] ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <?php if ( $qr_src || $data['show_clock'] ) : ?>
                <div class="bb-tv__bottom">
                    <?php if ( $qr_src ) : ?>
                        <div class="bb-tv__qr">
                            <span class="bb-tv__qr-code">
                                <img src="<?php echo esc_url( $qr_src ); ?>" alt="<?php echo esc_attr( $tv_text( '', 'QR Code da notícia' ) ); ?>" data-bb-tv-qr<?php echo 'current' === $data['qr'] ? ' data-dynamic' : ''; ?>>
                            </span>
                            <span class="bb-tv__qr-text">
                                <strong><?php echo esc_html( $tv_text( '', 'Leia no celular' ) ); ?></strong>
                                <?php echo esc_html( $tv_text( '', 'Aponte a câmera para o código e acesse a notícia completa.' ) ); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $data['show_clock'] ) : ?>
                        <div class="bb-tv__clock-card">
                            <time class="bb-tv__clock" data-bb-tv-clock>
                                <span class="bb-tv__clock-hm"><?php echo esc_html( wp_date( 'H:i' ) ); ?></span><span class="bb-tv__clock-s"><?php echo esc_html( wp_date( ':s' ) ); ?></span>
                            </time>
                            <p class="bb-tv__today" data-bb-tv-date><?php echo esc_html( wp_date( get_option( 'date_format' ) ) ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
