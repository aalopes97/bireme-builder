<?php
/**
 * Plugin Name:  Bireme Builder
 * Description:  Mini builder de seções com drag and drop dentro das páginas.
 * Version:      1.0.1
 * Author:       Toni Lopes (@toni) - 2WP
 * Author URI:   https://2wp.com.br
 * Text Domain:  bireme-builder
 *
 * Desenvolvido por @toni - 2WP | https://2wp.com.br
 * Builder de seções, layout Bireme (header/rodapé), arquivo, post, TV Boletim e newsletter.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BIREME_BUILDER_VERSION', '1.0.0' );
define( 'BIREME_BUILDER_PATH',    plugin_dir_path( __FILE__ ) );
define( 'BIREME_BUILDER_URL',     plugin_dir_url( __FILE__ ) );

require_once BIREME_BUILDER_PATH . 'includes/class-bireme-plugin.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-fields.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-sections.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-metabox.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-render.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-settings.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-layout.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-chrome.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-posts.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-views.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-newsletter.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-archive.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-single.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-404.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-tv.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-topics.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-i18n.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-editions.php';
require_once BIREME_BUILDER_PATH . 'includes/class-bireme-share.php';

add_action( 'plugins_loaded', array( 'Bireme_Plugin', 'init' ) );
