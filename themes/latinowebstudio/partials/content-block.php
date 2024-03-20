<?php

echo '<div class="col-lg-9 text-center pb-4 ' . get_field('column_classes') . '" style="' . get_field('column_style') . '">';
    echo '<div data-aos="fade-up">';
        echo get_field('content');
    echo '</div>';
echo '</div>';

?>