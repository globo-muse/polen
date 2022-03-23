<?php

$order_id; // = get_query_var('create-review');
$order; // = wc_get_order( $order_id );
$item_cart; //Polen\Includes\Cart\Polen_Cart_Item
$talent_id; //int

//action create_order_review
//order_id, rate, comment

?>
<main id="primary" class="site-main">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1>Avaliar Vídeo</h1>
        </div>
    </div>
    <?php polen_create_review($order_id); ?>
</main><!-- #main -->