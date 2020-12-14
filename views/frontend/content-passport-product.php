<?php

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form();
    return;
}
?>
    <div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

    <style>.quantity {display: none;}.edit-link {display: none;}</style>

    <?php
        /**
         * Hook: woocommerce_single_product_summary.
         *
         * @hooked woocommerce_template_single_add_to_cart - 30
         * @hooked WC_Structured_Data::generate_product_data() - 60
         *
         * @removed woocommerce_template_single_title - 5
         * @removed woocommerce_template_single_price - 10
         * @removed woocommerce_template_single_rating - 10
         * @removed woocommerce_template_single_excerpt - 20
         * @removed woocommerce_template_single_meta - 40
         * @removed woocommerce_template_single_sharing - 50
         */
        do_action( 'woocommerce_single_product_summary' );
    ?>

    </div>

<?php do_action( 'woocommerce_after_single_product' ); ?>