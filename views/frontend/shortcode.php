<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 *
 * @var string $link
 * @var string $id
 *
 */

?>

<input type="number" name="quantity_of_passport_products" class="quantity_of_passport_products" value="1" min="1">
<button data-href="<?php echo $link; ?>" data-product-id="<?php echo $id; ?>" id="display_passport_product"><?php esc_html_e( 'Display', 'custom-checkout-plugin' ); ?></button>