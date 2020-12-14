<?php namespace CustomCheckout;

use Premmerce\SDK\V2\FileManager\FileManager;
use CustomCheckout\Admin\Admin;
use CustomCheckout\Frontend\Frontend;

/**
 * Class CustomCheckoutPlugin
 *
 * @package CustomCheckout
 */
class CustomCheckoutPlugin
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * CustomCheckoutPlugin constructor.
     *
     * @param string $mainFile
     */
    public function __construct( $mainFile )
    {
        $this->fileManager = new FileManager( $mainFile );

        add_action( 'plugins_loaded', [$this, 'loadTextDomain'] );
        add_action( 'init', [$this, 'registerShortcode'] );
        add_action( 'woocommerce_loaded', array( $this, 'loadProductTypePassport' ) );
    }

    public function loadProductTypePassport()
    {
        require_once(__DIR__.'/Passport/ProductTypePassport.php');
    }

    /**
     * Run plugin part
     */
    public function run()
    {
        if ( is_admin() ) {
            new Admin( $this->fileManager );
        } else {
            new Frontend( $this->fileManager );
        }
    }

    /**
     * Load plugin translations
     */
    public function loadTextDomain()
    {
        $name = $this->fileManager->getPluginName();
        load_plugin_textdomain( 'custom-checkout-plugin', false, $name . '/languages/' );
    }

    /**
     * Register shortcode
     */
    public function registerShortcode()
    {
        add_shortcode('display_passport_product', function ($atts = [], $content = null) {
            $productId = Frontend::getFirstPassportProductId();
            return $this->fileManager->renderTemplate('frontend/shortcode.php', array(
                'link' => get_the_permalink( $productId ),
                'id'   => $productId,
            ));
        });
    }

    /**
     * Fired when the plugin is activated
     */
    public function activate()
    {
        // TODO: Implement activate() method.
    }

    /**
     * Fired when the plugin is deactivated
     */
    public function deactivate()
    {
        // TODO: Implement deactivate() method.
    }

    /**
     * Fired during plugin uninstall
     */
    public static function uninstall()
    {
        // TODO: Implement uninstall() method.
    }
}