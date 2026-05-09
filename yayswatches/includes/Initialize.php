<?php
namespace Yay_Swatches;

use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Helpers\Helper;

/**
 * Yay_Swatches Plugin Initializer
 */
class Initialize {

	use SingletonTrait;

	/**
	 * The Constructor that load the engine classes
	 */
	protected function __construct() {
		// Engine
		Helper::get_instance_classes( array( '\Yay_Swatches', 'Engine' ), Helper::engine_classes() );
		// Register
		\Yay_Swatches\Engine\Register\RegisterFacade::get_instance();
		// BEPages
		Helper::get_instance_classes( array( '\Yay_Swatches', 'Engine', 'BEPages' ), Helper::backend_classes() );
		// FEPages
		Helper::get_instance_classes( array( '\Yay_Swatches', 'Engine', 'FEPages' ), Helper::frontend_classes() );
		// COMPATIBLES : THEMES, CACHES, PLUGINS
		Helper::get_instance_classes( array( '\Yay_Swatches', 'Engine', 'Compatibles' ), Helper::compatible_classes() );
	}
}