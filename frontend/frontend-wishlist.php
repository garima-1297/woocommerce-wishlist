<?php

/*
 * Shortcode to list wishlisted products
 */
add_shortcode('woocommerce-wishlist', 'woo_wishlist_frontend_module');

function woo_wishlist_frontend_module($atts, $content = null) {
    global $wpdb;
    extract(shortcode_atts(array(), $atts));
    $wishlist_table = $wpdb->prefix . 'wishlist_products';
    $wishlist_query = $wpdb->get_results("SELECT * FROM $wishlist_table");
    ?>
    <div class="wishlist-logo">
        <i class="far fa-heart" aria-hidden="true"></i>
        <h3>My Wishlist</h3>
    </div>
    <table class="wishlist-details">
        <thead>
        <th class="product_remove"></th>
        <th class="product_image"></th>   
        <th class="product_name">Product Name</th>
        <th class="product_price">Price</th>
        <th class="product_stock">Action</th>
    </thead>
    <tbody>
        <?php
        foreach ($wishlist_query as $key => $val) {
            $product = wc_get_product($val->product_id);
            $p_info = $product->get_data();
            $user_id = $val->user_id;
            $product_img = wp_get_attachment_image_url($product->image_id, 'thumbnail');
            if (get_current_user_id() == $user_id) {
                ?>
                <tr>
                    <td class="product_remove"><button type="submit" name="product-remove" value="<?php echo $val->product_id; ?>" title="Remove"><i class="fas fa-times" aria-hidden="true"></i></button></td>
                    <td class="product_image"><img src="<?php echo $product_img; ?>"></td>
                    <td class="product_name"><a href="<?php echo get_permalink($val->product_id); ?>"><?php echo $product->get_title(); ?></a></td>
                    <td class="product_price"><?php echo $product->get_price_html(); ?></td>
                    <td class="add_to_cart"><a href="<?php echo site_url(); ?>/cart/?add-to-cart=<?php echo $val->product_id; ?>">Add to Cart</a></td>
                </tr>
                <?php
            }
        }
        ?>
    </tbody>
    </table>
    <?php
}
