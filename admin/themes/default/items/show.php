<?php    
    $itemTitle = strip_formatting(item(array('Dublin Core', 'Title')));
    if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
        $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
    } else {
        $itemTitle = '';
    }
    $itemTitle = __('Item #%s', item('id')) . $itemTitle;
?>
<?php head(array('title' => $itemTitle, 'bodyclass'=>'items show primary-secondary')); ?>

<?php echo js('items'); ?>

<h1 id="item-title"><?php echo $itemTitle; ?> <span class="view-public-page">[ <a href="<?php echo html_escape(public_uri('items/show/'.item('id'))); ?>"><?php echo __('View Public Page'); ?></a> ]</span></h1>

<?php if (has_permission($item, 'edit')): ?>
<p id="edit-item" class="edit-button"><?php 
echo link_to_item(__('Edit this Item'), array('class'=>'edit'), 'edit'); ?></p>   
<?php endif; ?>

<ul class="item-pagination navigation group">
<li id="previous-item" class="previous">
    <?php echo link_to_previous_item(); ?>
</li>
<li id="next-item" class="next">
    <?php echo link_to_next_item(); ?>
</li>
</ul>
<div id="primary">
<?php echo flash(); ?>

<div id="item-images">
<?php echo display_files_for_item(array('imageSize' => 'fullsize')); ?> 
</div>

<div id="core-metadata" class="showitem">
<?php echo show_item_metadata(array('show_empty_elements' => true)); ?>
</div>

<?php fire_plugin_hook('admin_append_to_items_show_primary', $item); ?>

</div>
<div id="secondary">
    
    <div class="info-panel">
        <h2><?php echo __('Bibliographic Citation'); ?></h2>
        <div>
            <p><?php echo item_citation();?></p>
        </div>
    </div>
    
        <div id="collection" class="info-panel">
        <h2><?php echo __('Collection'); ?></h2>
            <div>
               <p><?php if (item_belongs_to_collection()) echo item('Collection Name'); else echo __('No Collection'); ?></p>
            </div>
        </div>
    
    <div id="tags" class="info-panel">
        <h2><?php echo __('Tags'); ?></h2>
        <div id="tag-cloud">
            <?php common('tag-list', compact('item'), 'items'); ?>
        </div>
    </div>
    
    <div class="info-panel">
        <h2><?php echo __('View File Metadata'); ?></h2>
            <div id="file-list">
                <?php if(!item_has_files()):?>
                    <p><?php echo __('There are no files for this item yet.');?> <?php echo link_to_item(__('Add a File'), array(), 'edit'); ?>.</p>
                <?php else: ?>
                    <ul>
                        <?php while(loop_files_for_item()): ?>
                            <li><?php echo link_to_file_metadata(array('class'=>'show', 'title'=>__('View File Metadata'))); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif;?>
            </div>
    </div>

    <div class="info-panel">
        <h2><?php echo __('Output Formats'); ?></h2>
        <div><?php echo output_format_list(); ?></div>
    </div>
    
    <?php fire_plugin_hook('admin_append_to_items_show_secondary', $item); ?>
</div>
<?php foot();?>
