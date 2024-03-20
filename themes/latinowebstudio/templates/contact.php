<?php
/**
 * Template Name: Contact
 */
get_header(); ?>
<style>
    .hero-content,.hero-img{
    display: none;
}
section.hero {
    display: none;
}
</style>
<?php 
echo '<section class="pb-5 position-relative" style="overflow:hidden;padding-top:150px;">';

if(has_post_thumbnail()){
    the_post_thumbnail('full',array('class'=>'bg-img position-absolute w-100 h-100'));
} else { 
    $globalPlaceholderImg = get_field('global_placeholder_image','options');
    echo wp_get_attachment_image($globalPlaceholderImg['id'],'full','',['class'=>'bg-img position-absolute w-100 h-100']);
} 
    echo '<div class="container pb-4">';
    echo '<div class="row justify-content-center">';
    echo '<div class="col-md-9">';
    echo '<div class="content position-relative pt-5 pb-5 p-4">';
    echo '<div class="position-absolute bg-white" style="opacity:.75;width:100%;height:100%;top:0;left:0;"></div>';
    echo '<div class="position-relative">';
if(have_posts()) : while(have_posts()): the_post(); the_content(); endwhile; else:
echo '<p>Sorry, no posts matched your criteria.</p>';
endif;
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
get_footer(); ?>