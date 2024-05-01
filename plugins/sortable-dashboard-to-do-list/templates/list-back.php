<?php if (!defined('ABSPATH')) {
    exit;
}
/* @var $args */
$toDoData = $args['args']['option']['data'] ?? [];
$toDoOptions = $args['args']['option']['extra'] ?? [];
$networkAdmin = $args['args']['network_admin'];
$dateFormat = $args['args']['date_time_format'];
?>
<ul id="sdtdl-list">
    <?php
    $count=0;
    foreach ($toDoData as $key => $toDoItem) {
        $count++;
        $dateAdded = wp_date($dateFormat, $toDoItem['added']);
        ?>
        <li class="sdtdl-item" data-key="<?php echo (int)$key; ?>" data-added="<?php echo (int)$toDoItem['added']; ?>" data-edited="<?php echo (int)$toDoItem['last_edited']; ?>" data-front="<?php echo sanitize_text_field($toDoItem['front']); ?>" data-id="<?php echo sanitize_text_field($toDoItem['id']); ?>">
            <span class="dashicons dashicons-sort"></span>
            <div class="sdtdl-item-title" title="<?php printf(esc_html__("Added %s", 'sortable-dashboard-to-do-list'), $dateAdded); ?>">
                <?php echo sanitize_text_field($toDoItem['title']); ?>
            </div>
            <div class="sdtdl-content-container">
                <div class="sdtdl-content-text">
                    <?php echo self::sanitize_item_content(stripslashes($toDoItem['content'])); ?>
                </div>
                <div class="sdtdl-dates">
                    <div class="sdtdl-date-added">
                        <?php echo '<span class="dashicons dashicons-plus"></span>' . sprintf(esc_html__("Added %s", 'sortable-dashboard-to-do-list'), $dateAdded);; ?>
                    </div>
                    <div class="sdtdl-date-edited">
                        <?php if ($toDoItem['last_edited']) {
                            $dateEdited = wp_date($dateFormat, $toDoItem['last_edited']);
                            echo '<span class="dashicons dashicons-edit"></span>' . sprintf(esc_html__("Last edit %s", 'sortable-dashboard-to-do-list'), $dateEdited);
                        } ?>
                    </div>
                </div>
            </div>
        </li>
    <?php } ?>
</ul>
<div class="sdtdl-dialog-content sdtdl-view-item">
    <div class="sdtdl-content-text"></div>
    <div class="sdtdl-dates"></div>
</div>
<div class="sdtdl-no-content-container">
    <div class="sdtdl-no-content">
        <?php esc_html_e('No additional content was provided.', 'sortable-dashboard-to-do-list'); ?>
    </div>
</div>
<?php if (!$networkAdmin) { ?>
    <div class="sdtdl-settings-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-labelledby="title"
             role="button" xmlns:xlink="http://www.w3.org/1999/xlink">
            <title><?php esc_html_e('Settings'); ?></title>
            <path data-name="layer1" d="M58.906 27a3.127 3.127 0 0 1-2.977-2.258 24.834 24.834 0 0 0-1.875-4.519 3.131 3.131 0 0 1 .505-3.71 3.1 3.1 0 0 0 0-4.376l-2.693-2.698a3.1 3.1 0 0 0-4.376 0 3.131 3.131 0 0 1-3.71.505 24.834 24.834 0 0 0-4.519-1.875A3.127 3.127 0 0 1 37 5.094 3.1 3.1 0 0 0 33.906 2h-3.812A3.1 3.1 0 0 0 27 5.094a3.127 3.127 0 0 1-2.258 2.977 24.834 24.834 0 0 0-4.519 1.875 3.131 3.131 0 0 1-3.71-.505 3.1 3.1 0 0 0-4.376 0l-2.695 2.7a3.1 3.1 0 0 0 0 4.376 3.131 3.131 0 0 1 .505 3.71 24.834 24.834 0 0 0-1.875 4.519A3.127 3.127 0 0 1 5.094 27 3.1 3.1 0 0 0 2 30.094v3.811A3.1 3.1 0 0 0 5.094 37a3.127 3.127 0 0 1 2.977 2.258 24.833 24.833 0 0 0 1.875 4.519 3.131 3.131 0 0 1-.505 3.71 3.1 3.1 0 0 0 0 4.376l2.7 2.7a3.1 3.1 0 0 0 4.376 0 3.131 3.131 0 0 1 3.71-.505 24.834 24.834 0 0 0 4.519 1.875A3.127 3.127 0 0 1 27 58.906 3.1 3.1 0 0 0 30.094 62h3.811A3.1 3.1 0 0 0 37 58.906a3.127 3.127 0 0 1 2.258-2.977 24.834 24.834 0 0 0 4.519-1.875 3.131 3.131 0 0 1 3.71.505 3.1 3.1 0 0 0 4.376 0l2.7-2.695a3.1 3.1 0 0 0 0-4.376 3.131 3.131 0 0 1-.505-3.71 24.833 24.833 0 0 0 1.875-4.519A3.127 3.127 0 0 1 58.906 37 3.1 3.1 0 0 0 62 33.906v-3.812A3.1 3.1 0 0 0 58.906 27z"
                  fill="#000" stroke="#202020" stroke-linecap="round" stroke-miterlimit="10"
                  stroke-width="2" stroke-linejoin="round"></path>
            <circle data-name="layer2"
                    cx="32" cy="32" r="14" fill="#fff" stroke="#202020" stroke-linecap="round"
                    stroke-miterlimit="10" stroke-width="2" stroke-linejoin="round"></circle>
        </svg>
    </div>
<?php } ?>
<div class="sdtdl-add-button" data-action="add">
    <?php esc_html_e('Add'); ?>
</div>
<div class="sdtdl-dialog-content sdtdl-options" title="<?php esc_html_e('Settings'); ?>">
    <div class="sdtdl-options-container">
        <label for="option-show-front"><input type="checkbox" id="option-show-front" <?php if ($toDoOptions['front']==='true'){echo ' checked';} ?>/><?php esc_html_e('Show list on website (current user only)','sortable-dashboard-to-do-list'); ?></label>
    </div>
</div>
<div class="sdtdl-dialog-content sdtdl-new-item" title="<?php esc_html_e('Add New To-Do Item', 'sortable-dashboard-to-do-list'); ?>">
    <div class="sdtdl-new-container">
        <label for="new-sdtdl-title" class="screen-reader-text"><?php esc_html_e('Title (required)', 'sortable-dashboard-to-do-list'); ?></label><input type="text" id="new-sdtdl-title"
                                                                                                                                placeholder="<?php esc_html_e('Title (required)', 'sortable-dashboard-to-do-list'); ?>"/>
        <label for="new-sdtdl-text" class="screen-reader-text"><?php esc_html_e('Description (optional)', 'sortable-dashboard-to-do-list'); ?></label><textarea id="new-sdtdl-text" rows="4"
                                                                                                                                        placeholder="<?php esc_html_e('Description (optional)', 'sortable-dashboard-to-do-list'); ?>"></textarea>
        <label for="new-show-front" class="show-front-option<?php if ($toDoOptions['front']==='false'){echo ' hidden-setting';} ?>"><input type="checkbox" id="new-show-front"/ checked><?php esc_html_e('Show on website (current user only)','sortable-dashboard-to-do-list'); ?></label>
    </div>
</div>
<div class="sdtdl-dialog-content sdtdl-edit-item" title="<?php esc_html_e('Edit To-Do Item', 'sortable-dashboard-to-do-list'); ?>">
    <div class="sdtdl-edit-container">
        <label for="edit-sdtdl-title" class="screen-reader-text"></label><input type="text" id="edit-sdtdl-title" placeholder="<?php esc_html_e('Title (required)', 'sortable-dashboard-to-do-list'); ?>"/>
        <label for="edit-sdtdl-text" class="screen-reader-text"></label><textarea id="edit-sdtdl-text" rows="4"
                                                                                  placeholder="<?php esc_html_e('Description (optional)', 'sortable-dashboard-to-do-list'); ?>"></textarea>
        <label for="edit-show-front" class="show-front-option<?php if ($toDoOptions['front']==='false'){echo ' hidden-setting';} ?>"><input type="checkbox" id="edit-show-front"/><?php esc_html_e('Show on website (current user only)','sortable-dashboard-to-do-list'); ?></label>
    </div>
</div>
<div class="sdtdl-dialog-content sdtdl-delete-item" title="<?php esc_html_e('Delete To-Do Item', 'sortable-dashboard-to-do-list'); ?>">
    <p><span class="dashicons dashicons-warning"></span><?php esc_html_e('This item will be permanently deleted and cannot be recovered. Are you sure?', 'sortable-dashboard-to-do-list'); ?></p>
</div>