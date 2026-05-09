<?php
namespace Yay_Swatches\Engine;

use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Services\SettingsService;
use Yay_Swatches\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Yayswatches Rest API
 */
class RestAPI {

	use SingletonTrait;

	const REST_NAMESPACE = 'yayswatches/v1';

	private $default_swatch_customize_settings;
	private $default_button_customize_settings;
	private $default_sold_out_customize_settings;
	private $settings_service;
	protected function __construct() {
		$this->settings_service = SettingsService::get_instance();
		$this->default_swatch_customize_settings   = Helper::get_default_swatch_customize_settings();
		$this->default_button_customize_settings   = Helper::get_default_button_customize_settings();
		$this->default_sold_out_customize_settings = Helper::get_default_sold_out_settings();

		add_action( 'rest_api_init', array( $this, 'add_yayswatches_endpoint' ) );
	}

	/**
	 * Add Yayswatches Endpoints
	 */
	public function add_yayswatches_endpoint() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/settings',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'post_settings' ),
					'permission_callback' => array( $this, 'require_admin_permission' ),
				),
			)
		);

		$attribute_args = [
            'attribute_slug' => [
                'type'     => 'string',
                'required' => true,
            ],
			'page_no' => [
                'type'     => 'number',
                'required' => true,
            ]
        ];

		register_rest_route(
			self::REST_NAMESPACE,
			'/affected-products',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_affected_products' ],
					'permission_callback' => [ $this, 'require_admin_permission' ],
					'args'                => $attribute_args,
				),
			)
		);

		$product_id_args = [
            'product_id' => [
                'type'     => 'number',
                'required' => true,
            ],
        ];

		register_rest_route(
			self::REST_NAMESPACE,
			'/product-permalink/(?P<product_id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_product_permalink' ],
					'permission_callback' => [ $this, 'require_admin_permission' ],
					'args'                => $product_id_args,
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/available-variation/(?P<product_id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_available_variation' ),
					'permission_callback' => '__return_true',
					'args'                => $product_id_args,
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/affected-products/preload',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_preload_affected_products' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		$payload_args = [
            'attr_name' => [
                'type'     => 'string',
                'required' => true,
            ],
			'page_no' => [
                'type'     => 'number',
                'required' => true,
            ],
        ];

		register_rest_route(
			self::REST_NAMESPACE,
			'/affected-products/(?P<attr_name>[a-zA-Z0-9_-]+)/(?P<page_no>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_affected_products' ),
					'permission_callback' => [ $this, 'require_admin_permission' ],
					'args'                => $payload_args,
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/mark-reviewed',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'mark_reviewed' ),
					'permission_callback' => '__return_true',
				),
			)
		);

	}

	public function post_settings( $request ) {
		$params                        = $request->get_json_params();
		$attributes_data               = $params['attributes_custom_data'];
		$swatch_customize_settings     = $params['swatch_customize_settings'];
		$button_customize_settings     = $params['button_customize_settings'];
		$sold_out_customize_settings   = $params['sold_out_customize_settings'];

		$merged_swatch_customize_settings   = wp_parse_args( $swatch_customize_settings, $this->default_swatch_customize_settings );
		$merged_button_customize_settings   = wp_parse_args( $button_customize_settings, $this->default_button_customize_settings );
		$merged_sold_out_customize_settings = wp_parse_args( $sold_out_customize_settings, $this->default_sold_out_customize_settings );

		update_option( 'yay-swatches-swatch-customize-settings', $merged_swatch_customize_settings );
		update_option( 'yay-swatches-button-customize-settings', $merged_button_customize_settings );
		update_option( 'yay-swatches-sold-out-customize-settings', $merged_sold_out_customize_settings );

		foreach ( $attributes_data as $attribute ) {
			update_option( 'yay-swatches-attribute-style-' . $attribute['ID'], $attribute['style'] );
			if ( isset( $attribute['terms'] ) ) {
				foreach ( $attribute['terms'] as $term ) {
					if ( isset( $term['swatchColor'] ) ) {
						update_option( 'yay-swatches-swatch-color-' . $term['term_id'], $term['swatchColor'] );
					}
					if ( isset( $term['showHideDual'] ) ) {
						update_option( 'yay-swatches-show-hide-color-' . $term['term_id'], $term['showHideDual'] );
					}

					if ( isset( $term['swatchDualColor'] ) ) {
						update_option( 'yay-swatches-swatch-dual-color-' . $term['term_id'], $term['swatchDualColor'] );
					}

					if ( isset( $term['swatchImage'] ) ) {
						update_option( 'yay-swatches-swatch-image-' . $term['term_id'], $term['swatchImage'] );
					}
				}
			}
		}

		return rest_ensure_response( true );
	}

	public function get_preload_affected_products() {
		$attributes             = wc_get_attribute_taxonomies();
		$attributes_name_array  = array();
		$results = array();

		if ( $attributes ) {
			foreach ( $attributes as $attribute ) {
				$attribute_name  = $attribute->attribute_name;
				array_push( $attributes_name_array, $attribute_name );
			}
		}

		foreach ( $attributes_name_array as $attribute_name ) {
			$filtered_products = $this->settings_service->get_affected_products_info( $attribute_name );
			$results[$attribute_name] = $filtered_products;
		}

		return rest_ensure_response( $results );
	}

	public function get_affected_products( \WP_REST_Request $request ) {
		$params = $request->get_params();
		$attr_name = $params['attr_name'];
		$page_no = $params['page_no'];

		$filtered_products = $this->settings_service->get_affected_products_info( $attr_name, $page_no );
		return rest_ensure_response( $filtered_products );
	}

	public function require_admin_permission() {

		if ( ! is_user_logged_in() ) {
			return new \WP_Error(
				'yayswatches_unauthorized',
				__( 'Sorry, you need to Login to do this!!!.', 'yayswatches' ),
				[ 'status' => 401 ]
			);
		}

        if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) {
            return true;
        }

		return new \WP_Error(
			'yayswatches_forbidden',
			__( 'Sorry, you do not have permission to do this action!', 'yayswatches' ),
			[ 'status' => 403 ]
		);

    }

	public function mark_reviewed( \WP_REST_Request $request ) {
		update_option( 'yayswatches_reviewed', true );
	
		return rest_ensure_response( true );
	}
}