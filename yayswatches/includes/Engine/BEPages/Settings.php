<?php

namespace Yay_Swatches\Engine\BEPages;

use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Helpers\SupportHelper;
use Yay_Swatches\Engine\Register\ScriptName;
defined( 'ABSPATH' ) || exit;

class Settings {


	use SingletonTrait;

	public $setting_hookfix = null;

	protected function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ), YAY_SWATCHES_MENU_PRIORITY );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

		$priority = SupportHelper::get_enqueue_scripts_priority();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), $priority );

		add_filter( 'plugin_action_links_' . YAY_SWATCHES_BASE_NAME, array( $this, 'add_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_document_support_links' ), 10, 2 );
	}

	public function add_action_links( $links ) {
		$links = array_merge(
			array(
				'<a href="' . esc_url( admin_url( '/admin.php?page=yay-swatches' ) ) . '">' . __( 'Settings', 'yay-swatches' ) . '</a>',
			),
			$links
		);

		return $links;
	}

	public function add_document_support_links( $links, $file ) {
		if ( strpos( $file, YAY_SWATCHES_BASE_NAME ) !== false ) {
			$new_links = array(
				'doc'     => '<a href="https://yaycommerce.gitbook.io/yayswatches/" target="_blank">' . __( 'Docs', 'yay-swatches' ) . '</a>',
				'support' => '<a href="https://yaycommerce.com/support/" target="_blank" aria-label="' . esc_attr__( 'Visit community forums', 'yay-swatches' ) . '">' . esc_html__( 'Support', 'yay-swatches' ) . '</a>',
			);
			$links     = array_merge( $links, $new_links );
		}
		return $links;
	}

	public function admin_menu() {
		$page_title            = __( 'YaySwatches', 'yay-swatches' );
		$menu_title            = __( 'YaySwatches', 'yay-swatches' );
		$this->setting_hookfix = add_submenu_page( 'yaycommerce', $page_title, $menu_title, 'manage_woocommerce', 'yay-swatches', array( $this, 'submenu_page_callback' ), 0 );
		add_action( 'load-' . $this->setting_hookfix, array( $this, 'on_load_settings_page' ) );
	}

	public function admin_body_class( $classes ) {
		if ( strpos( $classes, 'yay-ui' ) === false ) {
			$classes .= ' yay-ui';
		}
		return $classes;
	}

	public function admin_enqueue_scripts( $hook_suffix ) {

		if ( $hook_suffix !== $this->setting_hookfix ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(ScriptName::STYLE_SETTINGS);

		$data_localize = SupportHelper::get_data_localize_backend();

		wp_localize_script(
			ScriptName::PAGE_SETTINGS,
			'yaySwatches',
			$data_localize
		);

		wp_enqueue_script( ScriptName::PAGE_SETTINGS );
		wp_enqueue_style( 'yay-swatches-style', YAY_SWATCHES_PLUGIN_URL . 'src/style.css', array(), YAY_SWATCHES_VERSION );
		$is_prod          = ! defined( 'YAY_SWATCHES_IS_DEVELOPMENT' ) || YAY_SWATCHES_IS_DEVELOPMENT !== true;
		if ( $is_prod ) {
			wp_enqueue_style( ScriptName::STYLE_SETTINGS );
		}
		
		wp_enqueue_script( 'yay-swatches-tooltip-1', YAY_SWATCHES_PLUGIN_URL . 'src/tooltip/popper.js', [], YAY_SWATCHES_VERSION, true );
		wp_enqueue_script( 'yay-swatches-tooltip', YAY_SWATCHES_PLUGIN_URL . 'src/tooltip/tippy.js', [], YAY_SWATCHES_VERSION, true );
	}

	public function submenu_page_callback() {
		echo '<div id="yay-swatches" class="yay-swatches-ui"></div>';
	}

	public function on_load_settings_page() {
		remove_all_actions( 'admin_notices' );
	}

}
