<?php

function custom_modify_block_output($block_content, $block) {
    // Check if it's the core/paragraph, core/image, or core/columns block
    if (in_array($block['blockName'], array('core/image', 'core/columns', 'core/quote'))) {
        // Modify the block content as needed
        $block_content = '<section class=""><div class="container"><div class="row"><div class="col-12">' . $block_content . '</div></div></div></section>';
    }
    return $block_content;
    }
    
    // add_filter('render_block', 'custom_modify_block_output', 10, 2);

?>