<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>

<fieldset id="group-modality">
    <label>
        <input type="radio" name="group-modality" value="first" checked="checked"><span class="item-label"><?php esc_html_e( 'First', 'custom-checkout-plugin' ); ?></span>
    </label>
    <label>
        <input type="radio" name="group-modality" value="second"><span class="item-label"><?php esc_html_e( 'Second', 'custom-checkout-plugin' ); ?></span>
    </label>
</fieldset>