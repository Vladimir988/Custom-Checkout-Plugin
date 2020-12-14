<?php namespace CustomCheckout\Passport;

use WC_Product_Simple;
use CustomCheckout\Admin\Admin;

/**
 * Class ProductTypePassport
 *
 * @package CustomCheckout\Passport
 */
class ProductTypePassport extends WC_Product_Simple
{
    /**
     * Return the product type
     * @return string
     */
    public function get_type()
    {
        return Admin::PRODUCT_TYPE;
    }
}