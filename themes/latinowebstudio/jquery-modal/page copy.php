<?php get_header();
global $post; 
if ( ! post_password_required( $post ) ) {
if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<section class="pt-5 pb-5">
<div class="container">
<div class="row">
<div class="col-md-12">
<?php the_content(); ?>
</div>
</div>
</div>
</section>
<?php endwhile; else: ?>
    <section class="pt-5 pb-5">
<div class="container">
<div class="row">
<div class="col-md-12">
<p>Sorry, no posts matched your criteria.</p>
</div>
</div>
</div>
</section>
<?php endif; 

} else {
// we will show password form here ?>

<section class="pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
            <?php echo get_the_password_form(); ?>             
            </div>
        </div>
    </div>
</section>
   
<?php }
get_footer(); ?>