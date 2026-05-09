<?php

namespace Yay_Swatches\Engine;

use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Helpers\Helper;

use stdClass;

defined( 'ABSPATH' ) || exit;

class Ajax {

	use SingletonTrait;

	private $default_swatch_customize_settings;
	private $default_button_customize_settings;
	private $default_swatch_color_array;
	private $default_sold_out_customize_settings;

	protected function __construct() {

		$this->default_swatch_customize_settings   = Helper::get_default_swatch_customize_settings();
		$this->default_button_customize_settings   = Helper::get_default_button_customize_settings();
		$this->default_swatch_color_array          = Helper::get_colors_list();
		$this->default_sold_out_customize_settings = Helper::get_default_sold_out_settings();

		add_action( 'wp_ajax_get_available_variation', array( $this, 'get_available_variation' ) );
		add_action( 'wp_ajax_nopriv_get_available_variation', array( $this, 'get_available_variation' ) );
	}


	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_available_variation() {
		$nonce = isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ? sanitize_title( $_POST['_wpnonce'] ) : false;
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'yay-swatches-nonce' ) ) {
			wp_send_json_error();
		}

		if ( isset( $_POST['product_id'] ) ) {
			$product_ID         = intval( sanitize_title( $_POST['product_id'] ) );
			$available_variants = wc_get_product( $product_ID )->get_available_variations( 'objects' );
			$results            = array();
			foreach ( $available_variants as $product_variant ) {
				if ( $product_variant->is_in_stock() ) {
					$results[] = $product_variant->get_attributes();
				}
			}
			wp_send_json( $results );
		}
	}
}