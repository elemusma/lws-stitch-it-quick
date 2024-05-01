<?php if (!defined('ABSPATH')) {
    exit;
}
$toDoData = $this->_user_option['data'];
$dateFormat = $this->_date_time_format;
$cookie = $_COOKIE['sdtdl_front_state_' . $this->_user_id . '_' . get_current_blog_id()] ?? [];
if ($cookie) {
    $cookie = json_decode(stripslashes($cookie), true);
}
$previousState = $cookie['list_state'] ?? 'collapsed';
$class = '';
if ($previousState == 'open') {
    $class = " sdtdl-open";
}
?>
<div class="sdtdl-front<?php echo $class; ?>" data-close="<?php esc_html_e('Close'); ?>">
    <h3><?php esc_html_e('To-Do List', 'sortable-dashboard-to-do-list'); ?>
        <span class="dashicons dashicons-arrow-down-alt2" title="<?php esc_html_e('Collapse list', 'sortable-dashboard-to-do-list'); ?>"></span>
    </h3>
    <ul id="sdtdl-list">
        <?php
        $count = 0;
        foreach ($toDoData as $key => $toDoItem) {
            if ($toDoItem['front'] === 'false') {
                continue;
            }
            $count++;
            $dateAdded = wp_date($dateFormat, $toDoItem['added']);
            ?>
            <li class="sdtdl-item" data-key="<?php echo (int)$key; ?>" data-added="<?php echo (int)$toDoItem['added']; ?>" data-edited="<?php echo (int)$toDoItem['last_edited']; ?>"
                data-front="<?php echo sanitize_text_field($toDoItem['front']); ?>" data-id="<?php echo sanitize_text_field($toDoItem['id']); ?>">
                <span class="dashicons dashicons-sort"></span>
                <div class="sdtdl-item-title" title="<?php echo printf(esc_html__("Added %s", 'sortable-dashboard-to-do-list'), $dateAdded);; ?>">
                    <?php echo sanitize_text_field($toDoItem['title']); ?>
                </div>
                <div class="sdtdl-dialog-content sdtdl-view-item sdtdl-<?php echo sanitize_text_field($toDoItem['id']); ?>" data-id="<?php echo sanitize_text_field($toDoItem['id']); ?>"
                     title="<?php echo sanitize_text_field($toDoItem['title']); ?>">
                    <div class="sdtdl-content-text"><?php if (!stripslashes($toDoItem['content'])) {
                            esc_html_e('No additional content was provided.', 'sortable-dashboard-to-do-list');
                        } else {
                            $content = preg_replace('/(?<=<ul>|<\/li>)\s*?(?=<\/ul>|<li>)/is', '', $toDoItem['content']);
                            $content = preg_replace('/(?<=<ol>|<\/li>)\s*?(?=<\/ol>|<li>)/is', '', $content);
                            echo self::sanitize_item_content(stripslashes($content));;
                        } ?>
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
</div>
<div class="sdtdl-collapsed<?php echo $class; ?>" title="<?php esc_html_e('Show list', 'sortable-dashboard-to-do-list'); ?>">
    <span class="dashicons dashicons-editor-ul"></span>
    <span class="sdtdl-counter"><?php echo $count; ?></span>
</div>