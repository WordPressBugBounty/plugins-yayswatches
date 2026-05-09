<?php
namespace Yay_Swatches\Engine\Register;

use Yay_Swatches\Utils\SingletonTrait;
use Yay_Swatches\Engine\Register\ScriptName;

/** Register in Production Mode */
class RegisterProd {
    use SingletonTrait;

    /** Hooks Initialization */
    protected function __construct() {
        add_action( 'init', array( $this, 'register_all_scripts' ) );
    }

    public function register_all_scripts() {
        $deps = array( 'react', 'react-dom', 'wp-hooks', 'wp-i18n', 'wp-components' );

        wp_register_script( ScriptName::PAGE_SETTINGS, YAY_SWATCHES_PLUGIN_URL . 'assets/dist/main.js', $deps, YAY_SWATCHES_VERSION, true );
    }
}