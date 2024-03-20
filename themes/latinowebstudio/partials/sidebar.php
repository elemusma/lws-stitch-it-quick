<?php 

echo '<div class="col-md-3 sidebar order-2">';
// echo '<div class="card p-3 mb-2">';
// get_search_form();
// echo '</div>';

echo '<div class="card p-3 mt-2 mb-2">';
echo '<h3 class="teko">Recent Posts</h3>';

$recentBlog = new WP_Query(array(
'posts_per_page' => 5,
'post_type' => 'post',
'post__not_in' => [get_the_ID()],
));
echo '<ul>';
while($recentBlog->have_posts()){
$recentBlog->the_post();
echo '<li><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></li>';
} wp_reset_postdata();
echo '</ul>';
echo '</div>';
echo '</div>';

?>