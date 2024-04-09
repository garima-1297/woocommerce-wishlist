jQuery(document).ready(function () {
    insert_product();
    delete_product();
    remove_from_wishlist();

    jQuery('[data-fancybox]').fancybox({
        'transitionIn': 'elastic',
        'transitionOut': 'elastic',
        'speedIn': 600,
        'speedOut': 200,
        'overlayShow': false
    });

});

function insert_product() {
    //To add product
    jQuery(".wishlist-toggle").click(function (e) {
        e.preventDefault();

        var product_id = jQuery(".wishlist-toggle input").val();
        if (jQuery(this).hasClass('user-not-logged-in')) {
            
        } else {
            if (product_id != "") {
                jQuery.ajax({
                    type: "POST",
                    dataType: 'text',
                    url: ajaxurl,
                    data: {
                        "action": "woocommerce_post_wishlist_product",
                        "prod_id": product_id,
                    },
                    success: function (response) {
                        //console.log(response);
                        if (response.success == undefined) {
                            setTimeout(function () {
                                location.reload();
                            }, 50);
                        }
                        jQuery('span.wishlist-message').show();
                        jQuery('.wishlist-rmv-toggle a').html('<i class="fa fa-heart-o" aria-hidden="true"></i>Remove from Wishlist');
                    }
                });
            }
        }
    });
}


function delete_product() {
    //To delete product
    jQuery("td.product_remove button").click(function (e) {
        //e.preventDefault();
        var product_id = jQuery(this).val();
        console.log(product_id);
        if (product_id != "") {
            jQuery.ajax({
                type: "POST",
                dataType: 'text',
                url: ajaxurl,
                data: {
                    "action": "woocommerce_remove_wishlist_product",
                    "product-remove": product_id,
                },
                success: function (response) {
                    console.log(JSON.stringify(response));
                    if (response.success == undefined) {
                        setTimeout(function () {
                            location.reload();
                        }, 50);
                    }
                }
            });
        }
    });
}

function remove_from_wishlist() {
    //To add product
    jQuery(".wishlist-rmv-toggle").click(function (e) {
        e.preventDefault();
        var product_id = jQuery(".wishlist-rmv-toggle input").val();
        console.log(product_id);
        jQuery.ajax({
            type: "POST",
            dataType: 'text',
            url: ajaxurl,
            data: {
                "action": "woocommerce_remove_wishlist_single_product",
                "prod_rmv_id": product_id,
            },
            success: function (response) {
                if (response.success == undefined) {
                    setTimeout(function () {
                        location.reload();
                    }, 50);
                }
                jQuery('span.wishlist-rmv').show();
                jQuery('.wishlist-toggle a').html('<i class="fa fa-heart-o" aria-hidden="true"></i>Add to Wishlist');
            }
        });
    });
}