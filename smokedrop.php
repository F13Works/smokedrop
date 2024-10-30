<?php
/*
Plugin Name: SmokeDrop
Description: Dropship Marketplace - Import & dropship products in your woocommerce store.
Version: 1.0.1
Author: SmokeDrop
Text Domain: smokedrop
Author URI: https://thesmokedrop.com
License: GPL2
Text Domain: SmokeDrop
*/
add_action('admin_menu', 'smokedrop_plugin_setup_menu');
 
function smokedrop_plugin_setup_menu(){
    add_menu_page( 'SmokeDrop', 'SmokeDrop', 'manage_options', 'smokedrop-plugin', 'smokedrop_init', plugins_url( 'smokedrop/images/sd.png' ) );
	
}
 
function smokedrop_init(){
    echo '<h1>SmokeDrop</h1> <a type="submit" href="https://wholesale.thesmokedrop.com" target="_blank" class="button button-primary"
    id="btn-submit">Go To SmokeDrop Dashboard</a>  
    <p style="font-style: italic">If your site is not yet connected to SmokeDrop, please follow the <a
            href="https://thesmokedrop.com/how-to-connect-a-woocommerce-store-to-smoke-drop/">instalation instructions.</a></p>		   
    ';

}
?>