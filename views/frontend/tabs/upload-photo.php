<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>

<input type="file" id="async-upload" name="async-upload">
<br>
<br>

<div class="canvas-wrapp">
    <div class="canvas-inner">
        <h2 class="title"><?php esc_html_e( 'Edit photo:', 'custom-checkout-plugin' ); ?></h2>

        <canvas id="test-area" class="test-area" width="600" height="500" style="border: 1px solid black;" >
            <?php esc_html_e( 'Your browser does not support canvas', 'custom-checkout-plugin' ); ?>
        </canvas>

        <i class="close">X</i>
        <div class="btn-wrapp">
            <input type="button" id="get-cropped-image-src" value="<?php esc_html_e( 'Crop', 'custom-checkout-plugin' ); ?>">
        </div>
    </div>
</div>
