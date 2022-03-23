<?php

use Polen\Includes\Polen_Talent;

include_once dirname( __FILE__ ) . '/init.php';

$sql = "SELECT
p1.ID 'Prod_ID',
p1.post_title 'Talent_ID',
u1.ID 'User_ID'
FROM
wp_posts AS p1
INNER JOIN wp_users AS u1 ON u1.ID=p1.post_author
WHERE p1.post_type IN ('product')
AND p1.post_status IN ('publish','private')
AND p1.ID IN (SELECT
    p.ID
    FROM
    wp_posts AS p
    INNER JOIN wp_users AS u ON u.ID=p.post_author
    LEFT JOIN wp_video_info AS vi ON vi.talent_id=u.ID
    WHERE p.post_type IN ('product')
    AND vi.first_order=1
    AND vi.vimeo_process_complete=1);";



$alredy_first_order_complete = $wpdb->get_results($sql);
// var_dump($alredy_first_order_complete,$wpdb->last_error);die;
if(!empty($alredy_first_order_complete) && empty($wpdb->last_error)) {
    foreach($alredy_first_order_complete as $item) {
        if(empty(Polen_Talent::get_first_order_status_by_talent_id($item->User_ID))) {
            Polen_Talent::set_first_order_status_by_talent_id($item->User_ID, true);
        }
    }
} else {
    var_dump($wpdb->last_error, __LINE__);
}
unset($sql);
$sql = "SELECT
p1.ID 'Prod_ID',
p1.post_title 'Talent_ID',
u1.ID 'User_ID'
FROM
wp_posts AS p1
INNER JOIN wp_users AS u1 ON u1.ID=p1.post_author
WHERE p1.post_type IN ('product')
AND p1.post_status IN ('publish','private')
AND p1.ID NOT IN (SELECT
p.ID
FROM
wp_posts AS p
INNER JOIN wp_users AS u ON u.ID=p.post_author
LEFT JOIN wp_video_info AS vi ON vi.talent_id=u.ID
WHERE p.post_type NOT IN ('product')
AND vi.first_order=1
AND vi.vimeo_process_complete=1);";

$not_have_first_order = $wpdb->get_results($sql);
if(!empty($not_have_first_order) && empty($wpdb->last_error)) {
    foreach($not_have_first_order as $item) {
        if(empty(Polen_Talent::get_first_order_status_by_talent_id($item->User_ID))) {
            Polen_Talent::set_first_order_status_by_talent_id($item->User_ID, false);
        }
    }
} else {
    var_dump($wpdb->last_error, __LINE__);
}
