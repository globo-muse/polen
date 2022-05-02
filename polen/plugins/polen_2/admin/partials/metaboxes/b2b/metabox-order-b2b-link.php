<?php
global $post;
$link = 'Link será gerado na criação do pedido';

if ($post->post_password) {
    $link = "https://pagar.polen.me?order={$post->ID}&code={$post->post_password}";
}
?>

<p>
    <b>Link para compra:</b> <?php echo $link; ?>
</p>