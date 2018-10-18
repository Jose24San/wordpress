<?php
class sp_cdm_fileview {
	
	
function view(){
	
		global $wpdb;
	
	
	
	echo '<h2>'.__("User Files","sp-cdm").'</h2>'.sp_client_upload_nav_menu().''.__("Choose a user below to view their files","sp-cdm").'<p>
	<div style="margin-top:20px;margin-bottom:20px">
	<script type="text/javascript">
	jQuery(document).ready(function() {
	
	jQuery("#user_uid").change(function() {
		jQuery.cookie("pid", 0, { expires: 7 , path:"/" });
	window.location = "admin.php?page=sp-client-document-manager-fileview&id=" + jQuery("#user_uid").val();
	
	
	})
	});
	</script>
	<form>';
	wp_dropdown_users(array('name' => 'user_uid', 'show_option_none' => 'Choose a user', 'selected'=>sanitize_text_field($_GET['id'])));
	 
	echo '</form></div>';


		if($_GET['id'] != ''){
			
			
			echo do_shortcode('[sp-client-document-manager uid="'.sanitize_text_field($_GET['id']).'"]');
			
		}



}
	
}

?>