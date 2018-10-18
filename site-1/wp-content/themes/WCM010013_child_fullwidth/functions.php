<?php
/**
 * TemplateMela
 * @copyright  Copyright (c) TemplateMela. (http://www.templatemela.com)
 * @license    http://www.templatemela.com/license/
 * @author         TemplateMela
 * @version        Release: 1.0
 */
/**  Set Default options : Theme Settings  */

function tm_set_default_options_1()
{ 
	/*  General Settings  */	
	
	add_option("tmoption_logo_image", get_stylesheet_directory_uri()."/images/megnor/logo.png"); // set logo image
}
add_action('init', 'tm_set_default_options_1'); 
?>