<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */
?>
<?php if(is_shop() || is_product_category() || is_product_tag() || is_product()  || is_cart()):?>
	<ul class="products  columns-<?php echo get_option('tmoption_shop_items_per_row') ?>" >
<?php else : ?>
	<ul class="products" >
<?php endif;?>

