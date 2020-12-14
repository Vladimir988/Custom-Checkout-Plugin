<?php namespace CustomCheckout\Frontend;

use Premmerce\SDK\V2\FileManager\FileManager;
use CustomCheckout\Admin\Admin;
use WP_Query;

/**
 * Class Frontend
 *
 * @package CustomCheckout\Frontend
 */
class Frontend
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * Plugin version
     */
    const VERSION = '1.0';

    /**
     * @var $fields
     */
    private $fields;

    public function __construct( FileManager $fileManager )
    {
        $this->fileManager = $fileManager;

        $this->fields = array(
          'color-picker',
          'upload-passport-image',
          'first-name',
          'second-name',
          'season',
          'nationality',
          'group-modality',
        );

        add_filter( 'init', [$this, 'writeToSession'], 10 );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueueScripts'] );
        add_filter( 'wc_get_template_part', [$this, 'customWcTemplatePart'], 10, 3 );
        add_action( 'woocommerce_single_product_summary', [$this, 'removeActionsWooSingleProductSummary'], 0 );
        add_action( 'woocommerce_before_add_to_cart_button', [$this, 'addCustomFieldsToProduct'], 20 );
        add_action( 'woocommerce_add_cart_item_data', [$this, 'addToCartCustomItemData'], 20, 4 );
        add_action( 'woocommerce_add_order_item_meta', [$this, 'addToOrderCustomItemData'], 20, 2 );
        add_action( 'woocommerce_cart_item_thumbnail', [$this, 'uniqProductThumbInCart'], 20, 3 );
        add_action( 'woocommerce_cart_item_name', [$this, 'addCustomDataToCart'], 20, 3 );
        add_filter( 'woocommerce_cart_item_data_to_validate', [$this, 'validateCartItemData'], 10, 2 );
        add_filter( 'woocommerce_add_to_cart_redirect', [$this, 'checkProductQuantity'], 10, 2 );
    }

    public function checkProductQuantity( $url, $addingToCart )
    {
        if( $addingToCart ) {
            if ( ! $this->isPassport( $addingToCart->get_id() ) ) {
                return $url;
            }

            if( session_status() === PHP_SESSION_ACTIVE ) {
                if( ! empty( $_SESSION[Admin::PRODUCT_TYPE . '-quantity'] ) ) {
                    if( $_SESSION[Admin::PRODUCT_TYPE . '-quantity'] > 1 ) {
                        $_SESSION[Admin::PRODUCT_TYPE . '-quantity'] = absint( $_SESSION[Admin::PRODUCT_TYPE . '-quantity'] ) - 1;
                        $redirect = add_query_arg( array( 'quantity' => $_SESSION[Admin::PRODUCT_TYPE . '-quantity'] ), home_url( $_SERVER['REQUEST_URI'] ) );
                        wp_safe_redirect( $redirect );
                        exit;
                    } else {
                        wp_safe_redirect( wc_get_cart_url() );
                        exit;
                    }
                }
            }
        }

        return $url;
    }

    public function writeToSession()
    {
        if ( session_status() === PHP_SESSION_NONE ) {
            session_start();
        }

        if( ! empty( $_GET['id'] ) && ! empty( $_GET['quantity'] ) && $_GET['id'] == self::getFirstPassportProductId() ) {
            $_SESSION[Admin::PRODUCT_TYPE . '-quantity'] = absint( $_GET['quantity'] );
        }
    }

    /**
     * Add scripts
     */
    public function enqueueScripts()
    {
        wp_enqueue_style( 'custom-checkout-frontend-style', $this->fileManager->locateAsset( 'frontend/css/style.css' ), array(), self::VERSION );

        wp_enqueue_script( 'custom-checkout-cropper-script', $this->fileManager->locateAsset( 'frontend/js/cropper.min.js' ), array('jquery'), self::VERSION );
        wp_enqueue_script( 'custom-checkout-frontend-script', $this->fileManager->locateAsset( 'frontend/js/common.js' ), array('jquery'), self::VERSION );

        $wpData = array(
          'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        );
        wp_localize_script( 'custom-checkout-frontend-script', 'wpData', $wpData );

        if( ! empty( $_REQUEST['passport-edit'] ) ) {
            if ( ! $this->isPassport( get_the_ID() ) ) {
                return;
            }

            $cart = WC()->session->get( 'cart', null );
            if( $cart ) {
                foreach ( $cart as $key => $values ) {
                    if ( $_REQUEST['passport-edit'] == $key ) {

                        $passportAttributes = array(
                          'data' => $values,
                        );
                        wp_localize_script( 'custom-checkout-frontend-script', 'passportAttributes', $passportAttributes );

                    }
                }
            }
        }
    }

    public function customWcTemplatePart( $template, $slug, $name )
    {
        if ( get_the_ID() == self::getFirstPassportProductId() && $slug == 'content' && $name == 'single-product' ) {
            return $this->fileManager->locateTemplate( 'frontend/content-passport-product.php' );
        }

        return $template;
    }

    public function removeActionsWooSingleProductSummary()
    {
        if ( ! $this->isPassport( get_the_ID() ) ) {
            return;
        }

        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
    }

    public function addCustomFieldsToProduct()
    {
        if ( ! $this->isPassport( get_the_ID() ) ) {
            return;
        }

        ob_start();

            echo $this->customCheckoutRenderProductEdit();
            echo $this->customCheckoutRenderImage();
            echo $this->customCheckoutRenderColorPicker();
            echo $this->customCheckoutRenderModality();
            echo $this->customCheckoutRenderUploadPhoto();
            echo $this->customCheckoutRenderInputFields();
            $content = ob_get_contents();

        ob_end_flush();
        return $content;
    }

    public function addToCartCustomItemData( $cartItemData, $productId, $variationId, $quantity )
    {
        foreach ( $this->fields as $field ) {
            if( isset( $_REQUEST[$field] ) && $_REQUEST[$field] != '' ) {
                $cartItemData[$field] = sanitize_text_field( $_REQUEST[$field] );
            }
        }

        return $cartItemData;
    }

    public function addToOrderCustomItemData( $itemId, $values )
    {
        foreach ( $this->fields as $field ) {
            if ( ! empty( $values[$field] ) ) {
                wc_add_order_item_meta( $itemId, ucfirst( str_replace( '-', ' ', $field ) ), $values[$field] );
            }
        }
    }

    public function uniqProductThumbInCart( $productImg, $cartItem, $cartItemKey )
    {
        if( ! empty( $cartItem['upload-passport-image'] ) ) {
            $productImg = '<img src="' . $cartItem['upload-passport-image'] . '">';
        } elseif( $cartItem['product_id'] == self::getFirstPassportProductId() ) {
            $defaultPhoto = $this->fileManager->locateAsset('frontend/images/default-photo.png');
            $productImg = '<img src="' . $defaultPhoto . '">';
        }

        return $productImg;
    }

    public function addCustomDataToCart( $productName, $cartItem, $cartItemKey )
    {
        $isPasport = false;
        $fields    = '';
        foreach ( $this->fields as $field ) {
            if( $field == 'upload-passport-image' ) {
                $isPasport = true;
                continue;
            }
            if ( ! empty( $cartItem[$field] ) ) {
                $isPasport = true;
                $fields .= '<div class="' . $field . '">' . ucfirst( str_replace( '-', ' ', $field ) ) . ': ' . $cartItem[$field] . '</div>';
            }
        }

        if( $isPasport ) {
            $link = add_query_arg( array( 'passport-edit' => $cartItemKey ), get_the_permalink( $cartItem['product_id'] ) );
            $fields .= '<a href="' . $link . '" class="edit-product" target="_blank">' . esc_attr__( 'Edit', 'custom-checkout-plugin' ) . '</a>';
        }

        return $productName . $fields;
    }

    public static function getFirstPassportProductId()
    {
        $args = [
          'post_type' => 'product',
          'tax_query' => [
            [
              'taxonomy' => 'product_type',
              'field'    => 'slug',
              'terms'    => [Admin::PRODUCT_TYPE],
            ],
          ],
        ];

        $query = new WP_Query( $args );

        if ( ! empty( $query->posts ) ) {
            return $query->posts[0]->ID;
        }

        return false;
    }

    public function validateCartItemData( $array, $product )
    {
        if( ! empty( $_REQUEST['passport-edit'] ) ) {
            $cart = WC()->session->get( 'cart', null );
            if( $cart ) {
                foreach ( $cart as $key => $values ) {
                    if( $_REQUEST['passport-edit'] == $key ) {
                        WC()->cart->remove_cart_item( $key );
                    }
                }
            }
        }

        if ( $product->get_id() == self::getFirstPassportProductId() ) {
            return array(
              'type'       => Admin::PRODUCT_TYPE,
              'attributes' => '',
            );
        }

        return $array;
    }
    
    protected function isPassport( $id )
    {
        if( self::getFirstPassportProductId() == $id ) {
            return true;
        }

        return false;
    }

    protected function customCheckoutRenderProductEdit()
    {
        return $this->fileManager->renderTemplate( 'frontend/tabs/passport-edit.php' );
    }

    protected function customCheckoutRenderImage()
    {
        $defaultPhoto = $this->fileManager->locateAsset('frontend/images/default-photo.png');
        return $this->fileManager->renderTemplate( 'frontend/tabs/image.php', array(
          'link' => $defaultPhoto,
        ) );
    }

    protected function customCheckoutRenderColorPicker()
    {
        return $this->fileManager->renderTemplate( 'frontend/tabs/color-picker.php' );
    }

    protected function customCheckoutRenderModality()
    {
        return $this->fileManager->renderTemplate( 'frontend/tabs/modality.php' );
    }

    protected function customCheckoutRenderUploadPhoto()
    {
        return $this->fileManager->renderTemplate( 'frontend/tabs/upload-photo.php');
    }

    protected function customCheckoutRenderInputFields()
    {
        return $this->fileManager->renderTemplate( 'frontend/tabs/input-fields.php' );
    }
}