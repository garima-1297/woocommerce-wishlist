<?php

/*
  Plugin Name: Woocommerce Wishlist Plugin
  Version: 1.0
  Plugin URI: http://www.evincedev.com/
  Author: Evincedev
  Author URI: http://www.evincedev.com/
  Description: Adds wishlist button to single product page and automatically creates wishlist page. Also, show listing in admin.
 */

global $table_prefix, $wpdb;
define('WISHLIST_TBL', $wpdb->prefix . 'wishlist_products');

/*
 * Activation Hook
 */
class wishlist_operations_on_activation {

    function operation_methods() {
        self::create_wishlist_database_table();
        self::wishlist_shortcode();
    }

    function create_wishlist_database_table() {
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        $sql = "CREATE TABLE IF NOT EXISTS`" . WISHLIST_TBL . "` ( 
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `product_id` VARCHAR(200) NOT NULL,
            `user_id` VARCHAR(200) NOT NULL ,PRIMARY KEY (`id`)
            ) ";

        dbDelta($sql);
    }

    function wishlist_shortcode() {
        global $wpdb;
        if (null === $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'my-wishlist'", 'ARRAY_A')) {
            $my_page = array(
                'post_title' => wp_strip_all_tags('My Wishlist'),
                'post_content' => '[woocommerce-wishlist]',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'page',
            );

            // Insert the post into the database
            wp_insert_post($my_page);
        }
    }

}

register_activation_hook(__FILE__, array('wishlist_operations_on_activation', 'operation_methods'));

#Add settings menu in plugins listing
function wishlist_settings_link($links) {
    $settings_link = '<a href="admin.php?page=wishlist">' . __('Settings') . '</a>';
    array_push($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'wishlist_settings_link');

#Enqueue frontend scripts 
function wishlist_plugin_scripts_styles() {
    wp_enqueue_style('wishlist-style', plugins_url('assets/css/style.css', __FILE__), array(), '1.0.0');
    wp_enqueue_style('fancybox-css', plugins_url('assets/css/jquery.fancybox.min.css', __FILE__), array(), '1.0.0');
    wp_enqueue_style('fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_script('fancybox-js', plugins_url('assets/js/jquery.fancybox.min.js', __FILE__), array('jquery'), '', true);
    wp_enqueue_script('wishlist-main', plugins_url('assets/js/script.js', __FILE__), array('jquery'), '', true);
}

add_action('wp_enqueue_scripts', 'wishlist_plugin_scripts_styles');

#Delete wishlist table 
function delete_wishlist_table() {
    global $wpdb;
    $wishlist_table = $wpdb->prefix . 'wishlist_products';
    $sql = "DROP TABLE IF EXISTS $wishlist_table";
    $wpdb->query($sql);

    $page = get_page_by_path('my-wishlist');
    wp_delete_post($page->ID);

    delete_option("my_plugin_db_version");
}

register_uninstall_hook(__FILE__, 'delete_wishlist_table');

#Enqueue admin css
function wishlist_enqueue_admin_style() {
    wp_enqueue_style('custom_wp_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
}

add_action('admin_enqueue_scripts', 'wishlist_enqueue_admin_style');

#ajaxurl
function myplugin_ajaxurl() {

    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

add_action('wp_head', 'myplugin_ajaxurl');

#Create wishlist menu in admin
function woocommerce_wishlist_create_menu() {

    $capability = current_user_can('manage_woocommerce') ? 'manage_woocommerce' : 'manage_options';
    add_submenu_page('woocommerce', __('Wishlist', 'woo-wishlist'), __('Wishlist', 'woo-wishlist'), $capability, 'wishlist', 'woocommerce_admin_wishlist_data');
}

add_action('admin_menu', 'woocommerce_wishlist_create_menu');

#add wishlist button on product detail page
add_action('woocommerce_after_add_to_cart_button', 'wishlist_toggle', 25);

function wishlist_toggle() {
    global $product, $wpdb;
    $wishlist_table = $wpdb->prefix . 'wishlist_products';
    $p_id = $product->get_id();
    $wishlist_query = $wpdb->get_results("SELECT `product_id` FROM $wishlist_table WHERE `product_id` = $p_id");
    if (is_user_logged_in()) {
        if ($wishlist_query) {
            echo '<div class="wishlist-rmv-toggle"><a data-product="' . esc_attr($product->get_id()) . '" href="" title="' . esc_attr__("Remove from wishlist", "text-domain") . '"><input type="hidden" name="prod_rmv_id" value="' . esc_attr($product->get_id()) . '"><i class="far fa-heart" aria-hidden="true"></i>Remove from Wishlist</a><span class="wishlist-rmv" style="display:none;">Product removed from wishlist!</span></div>';
        } else {
            echo '<div class="wishlist-toggle"><a data-product="' . esc_attr($product->get_id()) . '" href="" title="' . esc_attr__("Add to wishlist", "text-domain") . '"><input type="hidden" name="prod_id" value="' . esc_attr($product->get_id()) . '"><i class="far fa-heart" aria-hidden="true"></i>Add to Wishlist</a><span class="wishlist-message" style="display:none;">Product added to wishlist!</span></div>';
        }
    } else {
        echo '<div id="product_' . esc_attr($product->get_id()) . '" class="product-quickview" style="display: none;">Login to add the products to wishlist!</div>';
        echo '<div class="wishlist-toggle user-not-logged-in"><a data-fancybox data-src="#product_' . esc_attr($product->get_id()) . '" data-product="' . esc_attr($product->get_id()) . '" href="" title="' . esc_attr__("Add to wishlist", "text-domain") . '"><input type="hidden" name="prod_id" value="' . esc_attr($product->get_id()) . '"><i class="far fa-heart" aria-hidden="true"></i>Add to Wishlist</a><span class="wishlist-message" style="display:none;">Product added to wishlist!</span></div>';
    }
}

#insert product into wishlist
add_action('wp_ajax_woocommerce_post_wishlist_product', 'woocommerce_post_wishlist_product');
add_action('wp_ajax_nopriv_woocommerce_post_product_data', 'woocommerce_post_wishlist_product');

function woocommerce_post_wishlist_product() {
    global $wpdb;

    $wishlist_table = $wpdb->prefix . 'wishlist_products';
    $user_id = get_current_user_id();
    $product_id = sanitize_text_field($_POST['prod_id']);

    $wishlist_query = $wpdb->get_results("SELECT `user_id` FROM `$wishlist_table` WHERE `product_id` = '$product_id'");
    if ($wishlist_query) {
        echo "Already Exists";
    } else {
        $insert_query = "INSERT INTO $wishlist_table (`product_id`,`user_id`) VALUES ($product_id,$user_id)";
        $wpdb->query($insert_query);
    }
}

#Remove product from wishlist
add_action('wp_ajax_woocommerce_remove_wishlist_product', 'woocommerce_remove_wishlist_product');
add_action('wp_ajax_nopriv_woocommerce_remove_wishlist_product', 'woocommerce_remove_wishlist_product');

function woocommerce_remove_wishlist_product() {
    global $wpdb;
    $wishlist_table = $wpdb->prefix . 'wishlist_products';
    $prod_remove = sanitize_text_field($_POST['product-remove']);

    $remove_query = "DELETE FROM $wishlist_table WHERE product_id = '$prod_remove'";
    $wpdb->query($remove_query);
}

add_action('wp_ajax_woocommerce_remove_wishlist_single_product', 'woocommerce_remove_wishlist_single_product');
add_action('wp_ajax_nopriv_woocommerce_remove_wishlist_single_product', 'woocommerce_remove_wishlist_single_product');

function woocommerce_remove_wishlist_single_product() {
    global $wpdb;
    $wishlist_table = $wpdb->prefix . 'wishlist_products';
    $prod_remove = sanitize_text_field($_POST['prod_rmv_id']);

    $remove_query = "DELETE FROM $wishlist_table WHERE product_id = '$prod_remove'";
    $wpdb->query($remove_query);
}

/*
 * This file contains frontend functions
 */
require "frontend/frontend-wishlist.php";

/*
 * This file contains admin functions
 */
require "admin/admin-wishlist.php";

