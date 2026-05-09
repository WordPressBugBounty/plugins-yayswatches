<?php

namespace Yay_Swatches\Engine\FEPages;

use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Helpers\Helper;
use Yay_Swatches\Helpers\SupportHelper;

defined('ABSPATH') || exit;

class WooCommerceSwatches
{

	use SingletonTrait;

	private $default_swatch_customize_settings;
	private $default_button_customize_settings;
	private $default_sold_out_settings;
	private $sold_out_customize_settings;
	private $current_theme;


	protected function __construct() {

		$this->default_swatch_customize_settings = Helper::get_default_swatch_customize_settings();
		$this->default_button_customize_settings = Helper::get_default_button_customize_settings();
		$this->default_sold_out_settings         = Helper::get_default_sold_out_settings();
		$this->sold_out_customize_settings       = get_option( 'yay-swatches-sold-out-customize-settings', $this->default_sold_out_settings );
		$this->current_theme                     = Helper::get_current_theme_active();
		$priority = SupportHelper::get_enqueue_scripts_priority();

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), $priority);
		add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));

		// Product page
		add_filter('woocommerce_dropdown_variation_attribute_options_html', array($this, 'yay_swatches_custom_variation_attribute_options_html'), PHP_INT_MAX, 2);

		// Add 'yay-swatches-wrapper' class to body tag
		add_action('body_class', array($this, 'yay_swatches_add_body_class'));
	}

	public function enqueue_scripts()
	{

		$jquery_params = apply_filters('yay_swatches_jquery_params_args', array('jquery'));

		wp_enqueue_script('yay-swatches-callback', YAY_SWATCHES_PLUGIN_URL . 'src/callback.js', array_merge($jquery_params, array('wc-add-to-cart-variation')), YAY_SWATCHES_VERSION, true);


		wp_enqueue_script('yay-swatches-tooltip-1', YAY_SWATCHES_PLUGIN_URL . 'src/tooltip/popper.js', $jquery_params, YAY_SWATCHES_VERSION, true);
		wp_enqueue_script('yay-swatches-tooltip', YAY_SWATCHES_PLUGIN_URL . 'src/tooltip/tippy.js', $jquery_params, YAY_SWATCHES_VERSION, true);

		wp_enqueue_style('yay-swatches-style', YAY_SWATCHES_PLUGIN_URL . 'src/style.css', array(), YAY_SWATCHES_VERSION);

		wp_register_script('yay-swatches-frontend', YAY_SWATCHES_PLUGIN_URL . 'src/frontend-script.js', array_merge($jquery_params, array('wc-add-to-cart-variation')), YAY_SWATCHES_VERSION, true);

		$data_localize = SupportHelper::get_data_localize_frontend();

		wp_localize_script(
			'yay-swatches-frontend',
			'yaySwatches',
			apply_filters( 'yay_swatches_data_localize', $data_localize )
		);

		wp_enqueue_script( 'yay-swatches-frontend' );
	}

		public function enqueue_editor_assets()
	{

		$this->enqueue_scripts();
	}

	public function yay_swatches_custom_variation_attribute_options_html( $html, $args ) {
		$attribute      = $args['attribute'];
		$product        = $args['product'];
		$attribute_slug = sanitize_title( $attribute );
		$product_ID     = $product->get_ID();
		$terms_taxonomy = Helper::get_all_terms_by_sort( $product_ID, $attribute );
		$attribute_id   = $terms_taxonomy ? wc_attribute_taxonomy_id_by_name( $attribute ) : $attribute_slug;
		$attribute_type = get_option( 'yay-swatches-attribute-style-' . $attribute_id, 'dropdown' );
		$attribute_saved        = get_post_meta($product_ID, 'yay_swatches_product_attributes', true) ? get_post_meta($product_ID, 'yay_swatches_product_attributes', true) : array();
		if ( 'dropdown' === $attribute_type ) {
			return $html;
		}

		ob_start();
		require YAY_SWATCHES_PLUGIN_TEMPLATE . '/yay-swatches-term-template.php';
		$html = ob_get_clean();

		if (SupportHelper::detect_avoid_dom_unique_id_error()) {
			$html = str_replace('id', 'data-yay-swatches-id', $html);
		}

		return $html;
	}

	public function yay_swatches_add_body_class($classes)
	{
		$yay_swatches_wrapper_class  = '';
		$yay_swatches_wrapper_class .= 'yay-swatches-wrapper yay-swatches-wrapper-' . Helper::get_current_theme_active();
		if (Helper::is_product_page()) {
			$yay_swatches_wrapper_class .= ' yay-swatches-product-details-wrapper';
		}
		return array_merge($classes, array($yay_swatches_wrapper_class));
	}
}
