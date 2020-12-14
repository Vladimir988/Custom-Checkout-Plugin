<?php namespace CustomCheckout\Admin;

use Premmerce\SDK\V2\FileManager\FileManager;
use Exception;

/**
 * Class Admin
 *
 * @package CustomCheckout\Admin
 */
class Admin
{
    /**
     * Ajax action handler
     */
    const AJAX_UPLOAD_IMAGE = 'upload_passport_image';

    /**
     * Product type
     */
    const PRODUCT_TYPE = 'passport';

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * Admin constructor.
     *
     * Register menu items and handlers
     *
     * @param FileManager $fileManager
     */
    public function __construct( FileManager $fileManager )
    {
        $this->fileManager = $fileManager;

        add_filter( 'product_type_selector', [$this, 'addProductType'] );
        add_filter( 'woocommerce_product_class', [$this, 'loadNewProductTypeClass'], 10, 2 );
        add_filter( 'admin_footer', [$this, 'enableRegPriceForPassportProduct'] );
        add_filter( 'woocommerce_product_options_general_product_data', [$this, 'productAddCustomSettings'] );

        add_action( 'wp_ajax_' . self::AJAX_UPLOAD_IMAGE, array($this, 'ajaxUploadPassportImage') );
        add_action( 'wp_ajax_nopriv_' . self::AJAX_UPLOAD_IMAGE, array($this, 'ajaxUploadPassportImage') );

        add_filter( 'woocommerce_admin_order_item_thumbnail', [$this, 'uniqProductThumbInOrderDetails'], 10, 3 );
    }

    public function uniqProductThumbInOrderDetails( $image, $itemId, $item )
    {
        $customImage = wc_get_order_item_meta( $itemId, 'Upload passport image', false );
        if( ! empty( $customImage ) ) {
            return '<img width="150" height="150" class="attachment-thumbnail size-thumbnail" loading="lazy" src="' . $customImage[0] . '">';
        }

        return $image;
    }

    /**
     * Add Product Type Passport
     *
     * @param array $types
     * @return void
     */
    public function addProductType( $types )
    {
        $types[self::PRODUCT_TYPE] = esc_html__( 'Passport', 'custom-checkout-plugin' );

        return $types;
    }

    /**
     * Load New Product Type Class
     *
     * @param string $className
     * @param string $productType
     * @return string
     */
    public function loadNewProductTypeClass( $className, $productType ) {
        if ( $productType == self::PRODUCT_TYPE ) {
            $className = 'CustomCheckout\Passport\ProductTypePassport';
        }
        return $className;
    }

    /**
     * Enable regular and sale price for product
     */
    public function enableRegPriceForPassportProduct()
    {
        if ( get_post_type() != 'product' ) {
            return;
        }

        wc_enqueue_js(
          "jQuery(document).ready(function () {
                //for Price tab
                jQuery('.options_group.pricing').addClass('show_if_product').show();
                //for Inventory tab
                jQuery('.inventory_options').addClass('show_if_product').show();
                jQuery('#inventory_product_data ._sku_field').addClass('hide_if_product').hide();
            });"
        );
    }

    /**
     * Add custom product settings fields
     */
    public function productAddCustomSettings()
    {
        echo '<div class="options_group"></div>';
    }

    public function ajaxUploadPassportImage()
    {
        $postedData = isset( $_POST )  ? $_POST  : array();
        $fileData   = isset( $_FILES ) ? $_FILES : array();
        $data       = array_merge( $postedData, $fileData );
        $error      = false;

        try {
            if ( preg_match( '/^data:image\/(\w+);base64,/', $data['image'], $type ) ) {
                $data = substr( $data['image'], strpos( $data['image'], ',' ) + 1 );
                $type = strtolower( $type[1] );

                if ( ! in_array( $type, ['jpg', 'jpeg', 'gif', 'png'] ) ) {
                    throw new Exception( esc_html__( 'Invalid image type', 'custom-checkout-plugin' ) );
                }
                $data = str_replace( ' ', '+', $data );
                $data = base64_decode( $data );

                if ( $data === false ) {
                    throw new Exception( esc_html__( 'base64_decode failed', 'custom-checkout-plugin' ) );
                }
            } else {
                throw new Exception( esc_html__( 'Did not match data URI with image data', 'custom-checkout-plugin' ) );
            }
        } catch ( Exception $e ) {
            $error = $e->getMessage();
        }

        $fileName     = "passport_".time().".{$type}";
        $uploadDir    = wp_get_upload_dir();
        $filePath     = $uploadDir['path'] . '/' . $fileName;
        $uploadedFile = file_put_contents( $filePath, $data );

        $response     = array();
        if( $uploadedFile !== false ) {
            $response['response'] = esc_html__( 'Success', 'custom-checkout-plugin' );
            $response['filename'] = $fileName;
            $response['url']      = $uploadDir['url'] . '/' . $fileName;
            $response['type']     = $type;
            echo json_encode( $response );
            die();
        }

        $response['response'] = esc_html__( 'Error', 'custom-checkout-plugin' );
        $response['error']    = $error;
        echo json_encode( $response );
        die();
    }
}