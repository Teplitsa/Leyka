<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="add-donor-comment">
    <a href="#" class="add-donor-comment-link"><?php _e('Add a comment', 'leyka');?></a>
    <form class="new-donor-comment-form" data-nonce="<?php echo wp_create_nonce('new-donor-comment');?>" method="post">

        <label for="donor-comment-field"><?php _e('Comment text', 'leyka');?></label>
        <input type="text" id="donor-comment-field" name="donor-comment" value="">

        <input type="submit" value="<?php _e('Add the comment', 'leyka');?>">

    </form>
<!--    --><?php //$donor->add_comment('Тест коммента к донору! '.rand(0, 100000));?>
</div>

<?php if( !$donor->comments_exist() ) {?>
<div class="no-comments"><?php _e('No comments about this donor yet', 'leyka');?></div>
    <?php return;
}

//echo '<pre>'.print_r($donor->get_comments(), 1).'</pre>';?>

<table class="donor-comments donor-info-table">
    <thead>
        <tr>
            <th><?php _e('Date', 'leyka');?></th>
            <th><?php _e('Comment', 'leyka');?></th>
            <th><?php _e('Author', 'leyka');?></th>
            <th><?php _e('Change', 'leyka');?></th>
            <th><?php _e('Delete', 'leyka');?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($donor->get_comments() as $comment_id => $comment) {?>
        <tr>
            <td class="donor-comment-date"><?php echo date(get_option('date_format'), (int)$comment['date']);?></td>
            <td class="donor-comment-text"><?php echo esc_html($comment['text']);?></td>
            <td class="donor-comment-author"><?php echo $comment['author_name'];?></td>
            <td class="donor-comment-edit">
                <a href="#" class="comment-icon-edit" data-comment-id="<?php echo $comment_id;?>"> </a>
            </td>
            <td class="donor-comment-delete">
                <a href="#" class="comment-icon-delete" data-comment-id="<?php echo $comment_id;?>"> </a>
            </td>
        </tr>
    <?php }?>
    </tbody>
</table>