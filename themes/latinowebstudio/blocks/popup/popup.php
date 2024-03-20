<?php
echo '<div class="modal-content ' . get_field('column_classes') . ' position-fixed w-100 h-100 z-3 d-flex align-items-center justify-content-center" style="' . get_field('column_style') . '">';
echo '<div class="bg-overlay"></div>';
echo '<div class="bg-content">';
echo '<div class="bg-content-inner" style="padding:3rem;">';
echo '<div class="close" id="">X</div>';
echo '<div>';
echo get_field('content');
echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';
?>