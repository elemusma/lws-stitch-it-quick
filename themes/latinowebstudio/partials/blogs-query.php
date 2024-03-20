<div class="col-lg-6 pr-lg-5 col-blog text-white mb-5">
<a href="<?php the_permalink(); ?>" class="text-white">
<div class="position-relative overflow-h">
<div class="overlay position-absolute z-1"></div>
<?php 
if(has_post_thumbnail()){
the_post_thumbnail('medium',array('class'=>'w-100 h-100 position-absolute bg-img'));
} else {
$globalImg = get_field('global_placeholder_image','options');
echo wp_get_attachment_image($globalImg['id'],'full','',['class'=>'w-100 h-100 position-absolute bg-img']);
} ?>

<div class="position-relative w-100 blog-content pt-5 p-4">

<h3 class="h4 text-white"><?php the_title(); ?></h3>

<hr class="border-white">
<p class=""><?php the_time('F jS, Y'); ?> by <?php echo get_the_author_posts_link(); ?></p>
<!-- <p><?php the_tags('Tags: '); ?> <span>Categories: <?php the_category( ', ', 'multiple', $post->ID); ?></span></p> -->
<a href="<?php the_permalink(); ?>" class="text-white" style="text-decoration:underline;">Read More</a>
</div>
</div> <!-- end of position relative -->
</a>
</div> <!-- end of col -->