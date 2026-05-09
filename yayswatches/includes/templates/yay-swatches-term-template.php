<?php
defined( 'ABSPATH' ) || exit;
use Yay_Swatches\Helpers\Helper;
$data_styles               = '';
$slug_terms                = $terms_taxonomy ? $terms_taxonomy : Helper::get_terms_attribute_not_exists( $attribute, $args['options'], $product );
$swatch_customize_settings = get_option( 'yay-swatches-swatch-customize-settings', $this->default_swatch_customize_settings );
$button_customize_settings = get_option( 'yay-swatches-button-customize-settings', $this->default_button_customize_settings );
$default_selected_term     = $args['selected'];
$allow_html                = Helper::get_allow_html();
?>
<div class="yay-swatch-variant-default-wrapper"><?php echo wp_kses( $html, $allow_html ); ?></div>
<?php
$yay_swatches_class = '';
if ( 'button' === $attribute_type ) {
	$styles       = $button_customize_settings;
	$data_styles .= Helper::get_button_style( $styles );
}
if ( 'custom' === $attribute_type || 'variant_image' === $attribute_type ) {
	$styles             = $swatch_customize_settings;
	$data_styles       .= Helper::get_image_swatch_style( $styles );
	$yay_swatches_class = 'yay-swatch-wrapper-class';
}
$tootip_options = [
	'arrow'          => isset( $styles['swatchTooltipArrow'] ) && $styles['swatchTooltipArrow'] ? 'yes' : 'no',
	'shadow'         => isset( $styles['swatchTooltipBoxShadow'] ) && $styles['swatchTooltipBoxShadow'] ? 'yes' : 'no',
	'animation'   => isset( $styles['swatchTooltipAnimation'] ) && $styles['swatchTooltipAnimation'] ? 'yes' : 'no',
	'showImage'      => isset( $styles['swatchTooltipImage'] ) && $styles['swatchTooltipImage'] ? 'yes' : 'no',
];
$show_clear_button = 'enable';
if ( isset( $swatch_customize_settings['clearButtonShowHideOptions'] ) ) {
	$showHide = $swatch_customize_settings['clearButtonShowHideOptions'];
	$hideWhere = isset( $swatch_customize_settings['hideClearButtonOptions'] ) ? $swatch_customize_settings['hideClearButtonOptions'] : '';

	if ( $showHide === 'hide' && $hideWhere !== 'shop' ) {
		$show_clear_button = 'disable';
	}
}
$tick_selected = isset( $styles['tickSelected'] ) && 'enable' === $styles['tickSelected'] ? 'enable' : 'disable';
?>
<div data-type="<?php echo esc_attr($attribute_type); ?>" data-show-image="<?php echo $tootip_options['showImage']; ?>" data-arrow="<?php echo $tootip_options['arrow']; ?>" data-shadow="<?php echo $tootip_options['shadow']; ?>" data-animation="<?php echo $tootip_options['animation']; ?>" data-clear-button="<?php echo $show_clear_button; ?>" data-tick-selected="<?php echo $tick_selected; ?>" data-attribute="<?php echo esc_attr( $attribute_slug ); ?>" data-show-tooltip="<?php echo ( isset( $styles['swatchTooltip'] ) && 'enable' === $styles['swatchTooltip'] ? 'yes' : 'no' ); ?>" class="yay-variant-wrapper">
<?php
$variations = $product->get_children();
foreach ( $slug_terms as $key => $yay_term ) {
	$yay_term_slug = $terms_taxonomy ? $yay_term->slug : $yay_term;
	if ( in_array( $yay_term_slug, $args['options'], true ) ) {
		$term_id = $terms_taxonomy ? ( isset( $yay_term->term_id ) ? $yay_term->term_id : false ) : $yay_term;
		$special_terms_by_term_id = $attribute_saved ? ( isset( $attribute_saved[ $attribute_id ]['terms'][ $term_id ] ) ? $attribute_saved[ $attribute_id ]['terms'][ $term_id ] : false ) : false;

		$attribute_data = array(
			'product_id'        => $product_ID,
			'attribute_type'    => $attribute_type,
			'attribute_slug'    => $attribute_slug,
			'term_name'         => $terms_taxonomy ? $yay_term->name : $yay_term,
			'term_slug'         => $yay_term_slug,
			'term_active_class' => $default_selected_term === $yay_term_slug ? 'yay-swatches-active' : '',
		);

		if ( $term_id ) :  // only excute when term exists
			$flag_custom_type = false;
			$way_swatch_types = array( 'custom', 'button', 'variant_image', 'radio' );
			$flag_button_type        = 'button' === $attribute_type ? true : false;
			$flag_radio_type         = 'radio' === $attribute_type ? true : false;
			$flag_variant_image_type = 'variant_image' === $attribute_type ? true : false;
			if ( 'custom' === $attribute_type ) {
				$data_custom_type = Helper::get_data_custom_type( $special_terms_by_term_id, $term_id );
				$flag_custom_type = true;
			}

			//radio
			if ( $flag_radio_type  ) {
				do_action( 'yay_swatches_attribute_radio_type', $attribute_data );
			}

			// button
			if ($flag_button_type){
				do_action( 'yay_swatches_attribute_button_type', $attribute_data, $data_styles );
			}
			
			// custom
			if ( $flag_custom_type ) {
				do_action( 'yay_swatches_attribute_custom_type', $attribute_data, $data_styles, $data_custom_type, $styles);
			}
			// variant image
			if ( $flag_variant_image_type ) {
				$terms_img_id       = Helper::get_image_id_by_variation_id( $variations, $attribute_data );
				$variant_image_size = $swatch_customize_settings['imageSize'];
				$variant_image_url  = $terms_img_id ? wp_get_attachment_image_url( $terms_img_id, $variant_image_size ) : ( get_the_post_thumbnail_url( $product->get_id(), $variant_image_size ) ? get_the_post_thumbnail_url( $product->get_id(), $variant_image_size ) : wc_placeholder_img_src( $variant_image_size ) );
				do_action( 'yay_swatches_attribute_variant_image_type', $attribute_data, $data_styles, $styles, $variant_image_url );
			}
		endif;
	}
}
?>
</div>
