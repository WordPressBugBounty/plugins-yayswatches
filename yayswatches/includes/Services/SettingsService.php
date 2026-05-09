<?php

namespace Yay_Swatches\Services;
use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Helpers\Helper;

/**
 * @method static SettingsService get_instance()
 */
class SettingsService {
    use SingletonTrait;

    private $default_swatch_customize_settings;
	private $default_button_customize_settings;
	private $default_sold_out_customize_settings;
	private $default_collection_customize_settings;

    protected function __construct() {
        $this->default_swatch_customize_settings     = Helper::get_default_swatch_customize_settings();
		$this->default_button_customize_settings     = Helper::get_default_button_customize_settings();
		$this->default_sold_out_customize_settings   = Helper::get_default_sold_out_settings();
		$this->default_collection_customize_settings = Helper::get_default_collection_customize_settings();
    }

    public function get_settings() {
        $attributes = wc_get_attribute_taxonomies();
		$attributes_custom_data = new \stdClass();

		if ( $attributes ) {
			foreach ( $attributes as $attribute ) {
				$attribute_id = $attribute->attribute_id;
				$attribute_name = $attribute->attribute_name;
				$attribute_label = $attribute->attribute_label;

				$attribute_style = get_option( 'yay-swatches-attribute-style-' . $attribute_id, 'dropdown' );
				$is_archive_show = get_option( 'yay-swatches-attribute-show-archive-' . $attribute_id, 'no' );
				$terms = get_terms(
					array(
						'taxonomy'   => wc_attribute_taxonomy_name( $attribute_name ),
						'hide_empty' => false,
					)
				);

				foreach ( $terms as $term ) {
					$term_name = sanitize_title( $term->name );
					$default_swatch_color_by_term_name = in_array( $term_name, array_keys( Helper::get_colors_list() ), true ) ? Helper::get_colors_list()[$term_name] : '#2271b1';
					$default_swatch_dual_color_by_term_name = in_array( $term_name, array_keys( Helper::get_colors_list() ), true ) ? Helper::get_colors_list()[$term_name] : '#2271b1';

					$swatch_color = get_option( 'yay-swatches-swatch-color-' . $term->term_id, $default_swatch_color_by_term_name );
					$swatch_showHide = get_option( 'yay-swatches-show-hide-color-' . $term->term_id, false );
					$swatch_dual_color = get_option( 'yay-swatches-swatch-dual-color-' . $term->term_id, $default_swatch_dual_color_by_term_name );
					$swatch_image = get_option( 'yay-swatches-swatch-image-' . $term->term_id, '' );
					
					$term->swatchColor = $swatch_color;
					$term->showHideDual = ( '1' === strtolower( $swatch_showHide ) || 'true' === strtolower( $swatch_showHide ) ) ? true : false;
					$term->swatchDualColor = $swatch_dual_color;
					$term->swatchImage = $swatch_image;
				}

				$attributes_custom_data->$attribute_name = array(
					'ID'              => $attribute_id,
					'name'            => $attribute_name,
					'label'           => $attribute_label,
					'style'           => $attribute_style,
					'is_archive_show' => $is_archive_show,
					'terms'           => $terms,
				);
			}
		}

		$swatch_customize_settings = wp_parse_args( 
			get_option( 'yay-swatches-swatch-customize-settings', $this->default_swatch_customize_settings ),
			$this->default_swatch_customize_settings 
		);
		$button_customize_settings = wp_parse_args( 
			get_option( 'yay-swatches-button-customize-settings', $this->default_button_customize_settings ),
			$this->default_button_customize_settings 
		);
		$sold_out_customize_settings = wp_parse_args( 
			get_option( 'yay-swatches-sold-out-customize-settings', $this->default_sold_out_customize_settings ),
			$this->default_sold_out_customize_settings 
		);
		

		$all_data_settings = array(
			'attributes_custom_data'        =>  $attributes_custom_data ,
			'swatch_customize_settings'     =>  $swatch_customize_settings,
			'button_customize_settings'     =>  $button_customize_settings,
			'sold_out_customize_settings'   =>  $sold_out_customize_settings,
		);

		return $all_data_settings;
    }

    public function get_affected_products_info( $attribute_name, $page_no = 1) {
		$post_per_page = 10;
		$skip = ( $page_no - 1 ) * $post_per_page;
		$terms = get_terms(
			array(
				'taxonomy'   => wc_attribute_taxonomy_name( $attribute_name ),
				'hide_empty' => false,
			)
		);
		$terms_slug_array = array();
		foreach ( $terms as $term ) {
			array_push( $terms_slug_array, $term->slug );
		}
		$term_filter_query = array(
			'taxonomy' => 'pa_' . $attribute_name,
			'field'    => 'slug',
			'terms'    => $terms_slug_array,
			'operator' => 'IN',
		);
		$variable_product_filter_query = array(
			'taxonomy' => 'product_type',
			'field'    => 'slug',
			'terms'    => 'variable',
		);
		$args_query = array(
			'post_type'      => array( 'product' ),
			'posts_per_page' => -1,
			'tax_query'      => array(
				$term_filter_query,
				$variable_product_filter_query,
			),
		);

		$filtered_products_list_by_attribute_name = new \WP_Query( $args_query );
		$filtered_products = array(
			'affectedProductsQuantity' => $filtered_products_list_by_attribute_name->post_count,
			'listAffectedProducts'     => array_map(
				function($post) {
					return array(
						'ID' => $post->ID,
						'post_title' => $post->post_title,
						'view_url' => get_permalink($post->ID),
						'image_url' => get_the_post_thumbnail_url($post->ID, 'thumbnail') ?: wc_placeholder_img_src()
					);
				},
				array_slice($filtered_products_list_by_attribute_name->posts, $skip, $post_per_page)
			),
		);
		return $filtered_products;
	}
}