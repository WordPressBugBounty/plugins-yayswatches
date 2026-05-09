<?php

namespace Yay_Swatches\Helpers;

use Yay_Swatches\I18n;
use Yay_Swatches\Services\SettingsService;
defined( 'ABSPATH' ) || exit;

class SupportHelper {

	public static function get_enqueue_scripts_priority( $priority = 10 ) {

		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			$priority = 9999;
		}

		return apply_filters( 'yay_swatches_enqueue_scripts_priority', $priority );
	}

	public static function get_data_localize_frontend() {

		$data_localize = array(
			'ajaxurl'         => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce'           => wp_create_nonce( 'yay-swatches-nonce' ),
			'is_product_page' => Helper::is_product_page() ? 'yes' : 'no',
			'is_theme_active' => Helper::get_current_theme_active(),
			'sold_out'        => get_option( 'yay-swatches-sold-out-customize-settings', Helper::get_default_sold_out_settings() ),
		);

		if ( class_exists( 'WC_Composite_Products' ) ) {
			$data_localize['wc_composite_products_active'] = 'yes';
		}

		return apply_filters( 'yay_swatches_data_localize', $data_localize );
	}

	public static function get_data_localize_backend() {
		$settings_service = SettingsService::get_instance();
		$data_settings = $settings_service->get_settings();
		$reviewed = get_option( 'yayswatches_reviewed', false );

		$data_localize = array(
			'admin_url'                    => admin_url( 'admin.php?page=wc-settings' ),
			'admin_post_url'               => admin_url( 'post.php?post=' ),
			'admin_product_attributes_url' => admin_url( 'edit.php?post_type=product&page=product_attributes' ),
			'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
			'single_product_url'           => site_url( '/?product=' ),
			'nonce'                        => wp_create_nonce( 'yay-swatches-nonce' ),
			'rest_url'                     => esc_url_raw( rest_url() ),
			'rest_base'                    => 'yayswatches/v1',
			'rest_nonce'                   => wp_create_nonce( 'wp_rest' ),
			'i18n'                         => I18n::getTranslation(),
			'favicon_url'                  => YAY_SWATCHES_PLUGIN_URL . 'assets/images/favicon.svg',
			'preview_image_url'            => YAY_SWATCHES_PLUGIN_URL . 'assets/images/preview_image.jpg',
			'preview_image_url_small'      => YAY_SWATCHES_PLUGIN_URL . 'assets/images/preview_image-150x150.jpg',
			'preview_image_url_medium'     => YAY_SWATCHES_PLUGIN_URL . 'assets/images/preview_image-200x300.jpg',
			'product_icon_url'             => YAY_SWATCHES_PLUGIN_URL . 'assets/images/product.svg',
			'cart_icon_url'          	   => YAY_SWATCHES_PLUGIN_URL . 'assets/images/shopping-cart-check.svg',
			'bg_pro_url'                   => YAY_SWATCHES_PLUGIN_URL . 'assets/images/bg-pro.png',
			'customer_avatar_url'            => YAY_SWATCHES_PLUGIN_URL . 'assets/images/avatar.png',
			'money_back_url'                 => YAY_SWATCHES_PLUGIN_URL . 'assets/images/money-back.svg',
			'logos_payment_url'              => YAY_SWATCHES_PLUGIN_URL . 'assets/images/logos-payment.svg',
			'employee_avatars_url'           => YAY_SWATCHES_PLUGIN_URL . 'assets/images/employee-avatars.png',
			'reviewed'                       => $reviewed,
			'data_settings'                => $data_settings,
		);

		return apply_filters( 'yay_swatches_data_localize_backend', $data_localize );
	}
		public static function detect_avoid_dom_unique_id_error() {
		if ( apply_filters( 'yay_swatches_use_data_attr_instead_id', false ) ) {
			return true;
		}
		return false;
	}
}