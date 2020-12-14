<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var string $link
 */

?>

<div class="img-wrapper" style="width: 100%; max-width: 400px; height: 100%; max-height: 700px;">
    <a id="upload_passport_image" href="<?php echo $link; ?>">
        <img src="<?php echo $link; ?>" alt="<?php esc_html_e( 'Default photo', 'custom-checkout-plugin' ); ?>">
    </a>
    <input type="hidden" id="upload_passport_image_input" name="upload-passport-image">
</div>

<br>