<?php

/*
 * Function to list wishlisted products of all users
 */
function woocommerce_admin_wishlist_data() {
    global $wpdb;
    $wishlist_table = $wpdb->prefix . 'wishlist_products';
    //$wishlist_query = $wpdb->get_results("SELECT * FROM $wishlist_table");
    $pagenum = isset($_GET['pagenum']) ? sanitize_text_field(absint($_GET['pagenum'])) : 1;
    $limit = 10; // number of rows in page
    $offset = ( $pagenum - 1 ) * $limit;
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $wishlist_table");
    $num_of_pages = ceil($total / $limit);
    $wishlist_query = $wpdb->get_results("SELECT * FROM $wishlist_table LIMIT $offset, $limit");
    ?>
    <h1>Wishlist</h1>
    <table class="woo-admin table table-striped table-bordered dataTable" width="100%">
        <thead>
        <th class="id">Sr No.</th>
        <th class="user_name">User</th>
        <th class="user_email">Email</th>
        <th class="product_image">Product Image</th>   
        <th class="product_name">Product Name</th>
        <th class="product_price">Price</th>
    </thead>
    <tbody>
        <?php
        $i = 1;
        foreach ($wishlist_query as $key => $val) {
            $product = wc_get_product($val->product_id);
            $user_id = $val->user_id;
            $user_info = get_userdata($user_id);
            $product_img = wp_get_attachment_image_url($product->image_id, 'thumbnail');
            ?>
            <tr>
                <td class="id"><?php echo $i; ?></td>
                <td class="user_name"><?php echo $user_info->user_login; ?></td>
                <td class="user_email"><?php echo $user_info->user_email; ?></td>
                <td class="product_image"><img src="<?php echo $product_img; ?>"></td>
                <td class="product_name"><?php echo $product->get_title(); ?></td>
                <td class="product_price"><?php echo $product->get_price_html(); ?></td>
            </tr>

            <?php
            $i++;
        }
        ?>
    </tbody>
    <?php
    $page_links = paginate_links(array(
        'base' => add_query_arg('pagenum', '%#%'),
        'format' => '?paged=%#%',
        'prev_text' => __('&laquo;', 'text-domain'),
        'next_text' => __('&raquo;', 'text-domain'),
        'total' => $num_of_pages,
        'current' => $pagenum
    ));

    if ($page_links) {
        echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
    }
    ?>
    </table>
    <?php
}
