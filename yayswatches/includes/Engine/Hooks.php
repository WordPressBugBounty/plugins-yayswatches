<?php
namespace Yay_Swatches\Engine;

use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Helpers\Helper;

class Hooks 
{
	use SingletonTrait;

	protected function __construct() {

		// Hooks for Attribute Type
		add_action( 'yay_swatches_attribute_radio_type', array( $this, 'attribute_radio_type' ), 10, 1 );
		add_action( 'yay_swatches_attribute_button_type', array( $this, 'attribute_button_type' ), 10, 2 );
		add_action( 'yay_swatches_attribute_custom_type', array( $this, 'attribute_custom_type' ), 10, 4 );
		add_action( 'yay_swatches_attribute_variant_image_type', array( $this, 'attribute_variant_image_type' ), 10, 4 );

	}

	public function attribute_radio_type( $attribute_data ) {
			$default_active_class = ! empty( $attribute_data['term_active_class'] ) ? $attribute_data['term_active_class'] : false;
			$radio_auto_checked   = $default_active_class ? 'checked="checked"' : '';
			$product_ID           = $attribute_data['product_id'];
			$product_term_slug    = 'yayswatches-radio-attr-' . $attribute_data['term_slug'] . '-' . $product_ID;
			
			?>
			<label class="yay-swatches-radio-wrapper">
				<input id="<?php echo esc_attr( $product_term_slug ); ?>" data-type="radio" type="radio" <?php echo esc_attr( $radio_auto_checked ); ?> class='yay-swatches-attribute-term yay-swatches-swatch-radio <?php echo esc_attr( $default_active_class ); ?>' data-product_id='<?php echo esc_attr( $product_ID ); ?>' data-attribute='<?php echo esc_attr( $attribute_data['attribute_slug'] ); ?>' name="yay-swatches-radio-<?php echo esc_attr( $attribute_data['attribute_slug'] ); ?>" data-term="<?php echo esc_attr( $attribute_data['term_slug'] ); ?>" >
				<span class='yay-swatches-radio-label'><?php echo esc_html( $attribute_data['term_name'] ); ?></span>
			</label>
			<?php
	}

	public function attribute_button_type( $attribute_data, $data_styles ) {
		if ( 'button' === $attribute_data['attribute_type'] ) {
			$default_active_class = $attribute_data['term_active_class'];
			?>
				<span style="<?php echo esc_attr( $data_styles ); ?>" class="yay-swatches-attribute-term yay-swatches-button <?php echo esc_attr( $default_active_class ); ?>" data-product_id="<?php echo esc_attr( $attribute_data['product_id'] ); ?>" data-attribute="<?php echo esc_attr( $attribute_data['attribute_slug'] ); ?>" data-term="<?php echo esc_attr( $attribute_data['term_slug'] ); ?>" data-label-text="<?php echo esc_attr( $attribute_data['term_name'] ); ?>"><?php echo esc_html( $attribute_data['term_name'] ); ?></span>
			<?php
		}
	}

	public function attribute_custom_type( $attribute_data, $data_styles, $data_custom_type, $styles ) {
		$default_active_class = $attribute_data['term_active_class'];
		if ( Helper::show_term_name_variant_custom() ) {
			echo '<div class="yay-swatches-custom-variant-custom-wrapper">';
		}
		// Prepare background for the swatches
		$data_tooltip_img = '';
		$background_styles = '';
		$imagePosition = 'center';
		$imageSize     = 'cover';
		switch ( $styles['imagePosition'] ) {
			case 'top':
				$imagePosition = 'center top';
				break;
			case 'bottom':
				$imagePosition = 'center bottom';
				break;
			case 'center':
				$imagePosition = 'center center';
				$imageSize     = 'contain';
				break;
			default:
				break;
		}
		if ( isset( $data_custom_type['swatch_image'] ) && ! empty( $data_custom_type['swatch_image'] ) ){
			if ( is_string( $data_custom_type['swatch_image'] ) && ctype_digit( $data_custom_type['swatch_image'] ) ) {
        		$swatch_image_id = (int) $data_custom_type['swatch_image'];
				$data_custom_type['swatch_image']  = wp_get_attachment_image_url($swatch_image_id, $styles['imageSize'] );
    		}

			$background_styles = 'background:url(' . $data_custom_type['swatch_image'] . ')' .
			';background-position:' . $imagePosition .
			';background-repeat: no-repeat;background-color: transparent;background-size:' . $imageSize . ';';
			$data_tooltip_img =  'data-tooltip-img=' . $data_custom_type['swatch_image'];
		} else{
			$is_dual_color = 'true' === strtolower( $data_custom_type['swatch_show_hide'] ) || '1' === strtolower( $data_custom_type['swatch_show_hide'] );
			if ( isset( $data_custom_type['swatch_show_hide'] ) && $is_dual_color ) {
				$bg_linear_gradient = apply_filters( 'yay_swatches_background_linear_gradient', '135deg' );
				$background_styles        = 'background:linear-gradient(' . $bg_linear_gradient . ',' . $data_custom_type['swatch_color'] . ' 50%, ' . $data_custom_type['swatch_dual_color'] . ' 50%);';
			} 
			else {
				$background_styles = 'background:' . $data_custom_type['swatch_color'] . '';
			}
		}
		
		// Prepare design styles for the swatches
		$swatches_styles_class = Helper::get_swatches_design_class( $styles );
		?>
		<span data-type="swatch" style="<?php echo esc_attr( $data_styles ); ?>" class='yay-swatches-attribute-term <?php echo esc_attr( $swatches_styles_class ); ?> <?php echo esc_attr( $default_active_class ); ?>' data-product_id="<?php echo esc_attr( $attribute_data['product_id'] ); ?>" data-attribute="<?php echo esc_attr( $attribute_data['attribute_slug'] ); ?>" data-term="<?php echo esc_attr( $attribute_data['term_slug'] ); ?>" <?php echo esc_attr( $data_tooltip_img ); ?>  data-tippy-text="<?php echo esc_attr( $attribute_data['term_name'] ); ?>"  data-label-text="<?php echo esc_attr( $attribute_data['term_name'] ); ?>">
			<span class="yay-swatches-color" style="<?php echo esc_attr( $background_styles ); ?>"></span>
		</span>
		<?php
		if ( Helper::show_term_name_variant_custom() ) {
			echo '<div class="yay-swatches-label-name">' . esc_attr( $attribute_data['term_name'] ) . '</div>';
			echo '</div>';
		}
	}

	public function attribute_variant_image_type( $attribute_data, $data_styles, $styles, $variant_image_url ) {
		// Prepare background for the swatches
		$data_tooltip_img = '';
		$background_styles = '';
		$imagePosition = 'center';
		$imageSize     = 'cover';
		switch ( $styles['imagePosition'] ) {
			case 'top':
				$imagePosition = 'center top';
				break;
			case 'bottom':
				$imagePosition = 'center bottom';
				break;
			case 'center':
				$imagePosition = 'center center';
				$imageSize     = 'contain';
				break;
			default:
				break;
		}
			$background_styles = 'background:url(' . $variant_image_url . ')' .
			';background-position:' . $imagePosition .
			';background-repeat: no-repeat;background-color: transparent;background-size:' . $imageSize . ';';
			$data_tooltip_img =  'data-tooltip-img=' . $variant_image_url;
		//Prepare design styles for the swatches
		$swatches_styles_class = Helper::get_swatches_design_class( $styles, 'variant_image' );
		$default_active_class = $attribute_data['term_active_class'];
		if ( Helper::show_term_name_variant_image() ) {
			echo '<div class="yay-swatches-custom-variant-image-wrapper">';
		}
		?>
			<span data-type="swatch" style="<?php echo esc_attr( $data_styles ); ?>" class="yay-swatches-attribute-term <?php echo esc_attr( $swatches_styles_class ); ?> <?php echo esc_attr( $default_active_class ); ?>" data-product_id="<?php echo esc_attr( $attribute_data['product_id'] ); ?>" data-attribute="<?php echo esc_attr( $attribute_data['attribute_slug'] ); ?>" data-term="<?php echo esc_attr( $attribute_data['term_slug'] ); ?>" <?php echo esc_attr( $data_tooltip_img ); ?>  data-tippy-text="<?php echo esc_attr( $attribute_data['term_name'] ); ?>" data-label-text="<?php echo esc_attr( $attribute_data['term_name'] ); ?>"><span class="yay-swatches-color" style="<?php echo esc_attr( $background_styles ); ?>"></span></span>
		<?php
		if ( Helper::show_term_name_variant_image() ) {
			echo '<div class="yay-swatches-label-name">' . esc_attr( $attribute_data['term_name'] ) . '</div>';
			echo '</div>';
		}
	}

}
