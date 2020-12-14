<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

$edit = ! empty( $_REQUEST['passport-edit'] ) ? $_REQUEST['passport-edit'] : '';

?>

<input type="hidden" name="passport-edit" class="passport-edit" value="<?php echo $edit; ?>">
<br>