<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}

$donors_tags_taxonomy = get_taxonomy(Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME);?>

<div class="donors-tags-wrapper">
    <div class="new-tag-form">
        <input type="text" name="new-tags" value="">
        <input type="submit" value="<?php _e('Add');?>">
    </div>
    <div class="tags-list">
    <?php foreach($donor->get_tags() as $donor_tag) { /** @var $donor_tag WP_Term */ ?>
        <span class="tag">#<?php echo $donor_tag->name;?></span>
    <?php }?>
    </div>
    <div class="frequently-used-tags"><a href="#"><?php echo $donors_tags_taxonomy->labels->choose_from_most_used;?></a></div>
</div>
