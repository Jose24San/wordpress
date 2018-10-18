<?php
function theme_enqueue_styles() {
    $parent_style = 'parent-style';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' ); 
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( $parent_style ) ); 
}

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );


function storefront_site_branding() {
        ?>
		<div class="site-branding">
		    <?php storefront_site_title_or_logo(); ?>
		    <p class="tagline">Koopon Owl</p>
		</div>
		<?php	
}



