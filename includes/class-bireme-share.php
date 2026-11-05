<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Preview ao compartilhar a home (WhatsApp, Facebook e similares):
 * imagem do logo, títulos dos 6 boletins mais recentes e o título do boletim
 * dos posts do slide inicial.
 */
class Bireme_Share {

    public function __construct() {
        add_action( 'wp_head', array( $this, 'tags' ), 1 );
    }

    public function tags() {
        if ( is_admin() || ! self::is_share_page() ) {
            return;
        }

        $share = self::data();
        $tags  = array(
            'og:type'        => 'website',
            'og:locale'      => get_locale(),
            'og:site_name'   => $share['site_name'],
            'og:url'         => $share['url'],
            'og:title'       => $share['title'],
            'og:description' => $share['description'],
            'og:image'       => $share['image'],
        );

        if ( $share['image_width'] && $share['image_height'] ) {
            $tags['og:image:width']  = (string) $share['image_width'];
            $tags['og:image:height'] = (string) $share['image_height'];
        }
        if ( $share['image_type'] ) {
            $tags['og:image:type'] = $share['image_type'];
        }
        if ( $share['site_name'] ) {
            $tags['og:image:alt'] = $share['site_name'];
        }

        $tags = apply_filters( 'bireme_builder_share_tags', $tags, $share );

        if ( $share['description'] ) {
            printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $share['description'] ) );
        }

        foreach ( $tags as $property => $content ) {
            if ( '' === (string) $content ) {
                continue;
            }
            printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), esc_attr( $content ) );
        }

        $twitter = array(
            'twitter:card'        => 'summary_large_image',
            'twitter:title'       => $share['title'],
            'twitter:description' => $share['description'],
            'twitter:image'       => $share['image'],
        );
        foreach ( $twitter as $name => $content ) {
            if ( '' === (string) $content ) {
                continue;
            }
            printf( '<meta name="%s" content="%s" />' . "\n", esc_attr( $name ), esc_attr( $content ) );
        }
    }

    /**
     * Dados do preview da home.
     *
     * @return array
     */
    public static function data() {
        $page_id = self::share_page_id();
        $names   = self::slide_titles( $page_id, 6 );
        if ( ! $names ) {
            foreach ( Bireme_Editions::latest( 6 ) as $edition ) {
                if ( '' !== $edition['name'] ) {
                    $names[] = $edition['name'];
                }
            }
        }

        $list      = implode( ' · ', $names );
        $bulletin  = self::slide_bulletin_title( $page_id );
        $site_name = Bireme_Settings::text( 'title' );
        $title     = $list;
        if ( '' !== $bulletin && '' !== $list ) {
            $title = $bulletin . ' — ' . $list;
        } elseif ( '' !== $bulletin ) {
            $title = $bulletin;
        } elseif ( '' === $title ) {
            $title = $site_name;
        }

        $image = self::image();
        $url   = $page_id ? get_permalink( $page_id ) : Bireme_Layout::home_url();

        return array(
            'site_name'    => $site_name,
            'url'          => $url ? $url : Bireme_Layout::home_url(),
            'title'        => $title,
            'description'  => '' !== $list ? $list : Bireme_Settings::text( 'subtitle' ),
            'image'        => $image['url'],
            'image_width'  => $image['width'],
            'image_height' => $image['height'],
            'image_type'   => $image['type'],
        );
    }

    /**
     * Home do site ou página com o slide inicial do boletim (ex.: /exemplo/).
     */
    private static function is_share_page() {
        if ( is_front_page() ) {
            return true;
        }
        if ( ! is_singular( Bireme_Plugin::post_types() ) ) {
            return false;
        }
        return (bool) self::find_item( Bireme_Plugin::get_items( self::share_page_id() ), 'slide-boletim' );
    }

    /**
     * Página cujo preview está sendo montado.
     */
    private static function share_page_id() {
        if ( is_singular() ) {
            return (int) get_queried_object_id();
        }
        return self::front_page_id();
    }

    /**
     * Página inicial no idioma atual.
     */
    private static function front_page_id() {
        if ( is_front_page() && is_page() ) {
            return (int) get_queried_object_id();
        }

        $page_id = (int) get_option( 'page_on_front' );
        if ( $page_id && function_exists( 'pll_get_post' ) ) {
            $translated = pll_get_post( $page_id );
            $page_id    = $translated ? (int) $translated : $page_id;
        }
        return $page_id;
    }

    /**
     * Título do boletim (edição) mais recente entre os posts do primeiro Slide Boletim.
     */
    private static function slide_bulletin_title( $page_id ) {
        $posts = $page_id ? self::slide_posts( $page_id ) : array();
        if ( ! $posts ) {
            $posts = Bireme_Posts::query( array( 'count' => 6 ) );
        }

        $best_time = 0;
        $best_name = '';
        foreach ( $posts as $post ) {
            if ( ! $post instanceof WP_Post ) {
                continue;
            }
            $term = self::bulletin_term( $post );
            if ( ! $term ) {
                continue;
            }
            $time = self::edition_time( $term, $post );
            if ( $time >= $best_time ) {
                $best_time = $time;
                $best_name = Bireme_Single::term_name( $term );
            }
        }
        return $best_name;
    }

    /**
     * Posts do primeiro Slide Boletim da página, na mesma regra da seção.
     *
     * @return WP_Post[]
     */
    private static function slide_posts( $page_id ) {
        $item = self::find_item( Bireme_Plugin::get_items( $page_id ), 'slide-boletim' );
        if ( ! $item ) {
            return array();
        }

        $section = Bireme_Sections::get( 'slide-boletim' );
        if ( ! $section ) {
            return array();
        }

        $data = Bireme_Fields::merge_defaults( $section['fields'], isset( $item['data'] ) ? $item['data'] : array() );
        if ( 'auto' === $data['source'] ) {
            return Bireme_Posts::query( array(
                'post_type' => $data['post_type'],
                'category'  => $data['category'],
                'count'     => $data['count'] ? $data['count'] : 6,
            ) );
        }

        $posts = array();
        foreach ( (array) $data['items'] as $row ) {
            $url = ( is_array( $row ) && ! empty( $row['link']['url'] ) ) ? $row['link']['url'] : '';
            $id  = $url ? url_to_postid( $url ) : 0;
            if ( ! $id ) {
                continue;
            }
            $post = get_post( $id );
            if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
                $posts[] = $post;
            }
        }
        return $posts;
    }

    /**
     * Títulos do slide inicial (até $limit). Manual usa o título de cada slide; automático, os posts.
     *
     * @return string[]
     */
    private static function slide_titles( $page_id, $limit ) {
        $limit   = max( 1, (int) $limit );
        $charset = get_bloginfo( 'charset' );
        $item    = $page_id ? self::find_item( Bireme_Plugin::get_items( $page_id ), 'slide-boletim' ) : null;
        $section = $item ? Bireme_Sections::get( 'slide-boletim' ) : null;
        $data    = $section ? Bireme_Fields::merge_defaults( $section['fields'], isset( $item['data'] ) ? $item['data'] : array() ) : array();

        if ( $data && 'manual' === $data['source'] ) {
            $titles = array();
            foreach ( (array) $data['items'] as $row ) {
                $title = ( is_array( $row ) && isset( $row['title'] ) ) ? trim( wp_strip_all_tags( $row['title'] ) ) : '';
                if ( '' === $title ) {
                    continue;
                }
                $titles[] = html_entity_decode( $title, ENT_QUOTES, $charset );
                if ( count( $titles ) >= $limit ) {
                    break;
                }
            }
            if ( $titles ) {
                return $titles;
            }
        }

        $posts = $page_id ? self::slide_posts( $page_id ) : array();
        if ( ! $posts ) {
            $posts = Bireme_Posts::query( array( 'count' => $limit ) );
        }

        $titles = array();
        foreach ( array_slice( $posts, 0, $limit ) as $post ) {
            if ( ! $post instanceof WP_Post ) {
                continue;
            }
            $titles[] = html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, $charset );
        }
        return $titles;
    }

    /**
     * Edição do post na taxonomia do boletim. A categoria padrão não conta como boletim.
     *
     * @return WP_Term|null
     */
    private static function bulletin_term( WP_Post $post ) {
        $term = Bireme_Single::term( $post, 'single_edition_tax' );
        if ( ! $term ) {
            return null;
        }
        if ( 'category' === $term->taxonomy && (int) $term->term_id === (int) get_option( 'default_category' ) ) {
            return null;
        }
        return $term;
    }

    private static function find_item( array $items, $type ) {
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) || empty( $item['type'] ) ) {
                continue;
            }
            if ( isset( $item['enabled'] ) && ! $item['enabled'] ) {
                continue;
            }
            if ( $type === $item['type'] ) {
                return $item;
            }
            if ( empty( $item['columns'] ) || ! is_array( $item['columns'] ) ) {
                continue;
            }
            foreach ( $item['columns'] as $column ) {
                $found = self::find_item( (array) $column, $type );
                if ( $found ) {
                    return $found;
                }
            }
        }
        return null;
    }

    private static function edition_time( $term, WP_Post $post ) {
        $manual = (string) get_term_meta( $term->term_id, Bireme_Editions::META_KEY, true );
        if ( preg_match( '/^\d{4}-\d{2}$/', $manual ) ) {
            $time = strtotime( $manual . '-01 12:00:00' );
            if ( $time ) {
                return $time;
            }
        }
        return (int) strtotime( $post->post_date );
    }

    /**
     * Logo do header em um card 1200×630. O arquivo original é baixo demais para o WhatsApp exibir.
     *
     * @return array{url:string,width:int,height:int,type:string}
     */
    private static function image() {
        $logo = Bireme_Settings::share_logo();
        if ( is_ssl() ) {
            $logo = set_url_scheme( $logo, 'https' );
        }

        $path   = self::local_path( $logo );
        $canvas = ( $path && file_exists( $path ) ) ? self::logo_card( $path ) : '';
        if ( $canvas ) {
            $upload = wp_upload_dir();
            $url    = trailingslashit( $upload['baseurl'] ) . 'bireme-share/home-logo.png';
            if ( is_ssl() ) {
                $url = set_url_scheme( $url, 'https' );
            }
            $url = add_query_arg( 'v', (string) filemtime( $canvas ), $url );
            return array(
                'url'    => $url,
                'width'  => 1200,
                'height' => 630,
                'type'   => 'image/png',
            );
        }

        $width  = 0;
        $height = 0;
        $type   = '';
        if ( $path && file_exists( $path ) ) {
            $size = wp_getimagesize( $path );
            if ( $size ) {
                $width  = (int) $size[0];
                $height = (int) $size[1];
                $type   = isset( $size['mime'] ) ? $size['mime'] : '';
            }
        }

        return array(
            'url'    => $logo,
            'width'  => $width,
            'height' => $height,
            'type'   => $type,
        );
    }

    /**
     * PNG 1200×630 com o logo centralizado em fundo branco. Reaproveita o arquivo se o logo não mudou.
     */
    private static function logo_card( $logo_path ) {
        if ( ! function_exists( 'imagecreatetruecolor' ) || ! function_exists( 'imagecreatefromstring' ) || ! function_exists( 'imagepng' ) ) {
            return '';
        }

        $upload = wp_upload_dir();
        if ( ! empty( $upload['error'] ) ) {
            return '';
        }

        $dir = trailingslashit( $upload['basedir'] ) . 'bireme-share';
        if ( ! wp_mkdir_p( $dir ) ) {
            return '';
        }

        $dest       = $dir . '/home-logo.png';
        $logo_mtime = (int) filemtime( $logo_path );
        if ( file_exists( $dest ) && (int) filemtime( $dest ) >= $logo_mtime ) {
            return $dest;
        }

        $raw = file_get_contents( $logo_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $src = $raw ? imagecreatefromstring( $raw ) : false;
        if ( ! $src ) {
            return '';
        }

        $canvas = imagecreatetruecolor( 1200, 630 );
        if ( ! $canvas ) {
            imagedestroy( $src );
            return '';
        }

        $white = imagecolorallocate( $canvas, 255, 255, 255 );
        imagefilledrectangle( $canvas, 0, 0, 1199, 629, $white );
        imagealphablending( $canvas, true );

        $sw = imagesx( $src );
        $sh = imagesy( $src );
        if ( $sw < 1 || $sh < 1 ) {
            imagedestroy( $src );
            imagedestroy( $canvas );
            return '';
        }

        $scale = min( 880 / $sw, 220 / $sh );
        $dw    = max( 1, (int) round( $sw * $scale ) );
        $dh    = max( 1, (int) round( $sh * $scale ) );
        $dx    = (int) floor( ( 1200 - $dw ) / 2 );
        $dy    = (int) floor( ( 630 - $dh ) / 2 );
        imagecopyresampled( $canvas, $src, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh );
        imagedestroy( $src );

        $saved = imagepng( $canvas, $dest );
        imagedestroy( $canvas );
        return $saved ? $dest : '';
    }

    private static function local_path( $url ) {
        $id = attachment_url_to_postid( $url );
        if ( $id ) {
            $file = get_attached_file( $id );
            if ( $file ) {
                return $file;
            }
        }

        $bases = array(
            BIREME_BUILDER_URL => BIREME_BUILDER_PATH,
        );
        $upload = wp_upload_dir();
        if ( ! empty( $upload['baseurl'] ) && empty( $upload['error'] ) ) {
            $bases[ $upload['baseurl'] ] = $upload['basedir'];
        }

        foreach ( $bases as $base_url => $base_path ) {
            $base_url = set_url_scheme( $base_url, is_ssl() ? 'https' : 'http' );
            if ( 0 !== strpos( $url, $base_url ) ) {
                $base_url = set_url_scheme( $base_url, 'http' );
                if ( 0 !== strpos( $url, $base_url ) ) {
                    $base_url = set_url_scheme( $base_url, 'https' );
                }
            }
            if ( 0 === strpos( $url, $base_url ) ) {
                $relative = rawurldecode( substr( $url, strlen( $base_url ) ) );
                return $base_path . ltrim( str_replace( '/', DIRECTORY_SEPARATOR, $relative ), DIRECTORY_SEPARATOR );
            }
        }
        return '';
    }
}
