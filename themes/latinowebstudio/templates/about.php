<?php 
/**
 * Template Name: About
 */
get_header(); 

?>
<!-- start of intro -->
<section class="pt-5 pb-5 bg-accent text-white">
<div class="container">
<div class="row align-items-center">
<div class="col-md-6">
<?php the_field('content'); ?>
</div>
<div class="col-md-6 overflow-h">
    <div class="position-relative">
<?php if(get_field('image')){ 
$image = get_field('image');
echo wp_get_attachment_image($image['id'],'full',"",['class'=>'w-100 h-100 box-shadow img-hover']); ?>
<?php } else if(has_post_thumbnail()){
the_post_thumbnail('full',array('class'=>'w-100 h-100 box-shadow img-hover'));
} else { ?> 
<?php echo wp_get_attachment_image(26,'full',"",['class'=>'w-100 h-100 box-shadow img-hover']); ?>
<?php } ?>
</div>
</div>
</div>
</div>
</section>
<!-- end of intro -->

<?php if(have_rows('slides')) : while(have_rows('slides')): the_row(); 
$bgImage = get_sub_field('background_image');
?>
<!-- start of full height -->
<section class="pt-5 pb-5 position-relative bg-img d-flex justify-content-center align-items-center full-row" style="background:url('<?php echo wp_get_attachment_image_url($bgImage['id'],'full'); ?>');background-size:cover;background-attachment:fixed;">

<div class="overlay"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
            <div class="inner-content text-white pt-5 pb-5 position-relative">
                <div class="overlay position-absolute"></div>
                <div class="position-relative z-1 pl-5 pr-5">
<?php the_sub_field('content'); ?>
</div>
            </div>
            </div>
        </div>
    </div>
</section>
<!-- end of full height -->
<?php endwhile; else: endif; ?>

<?php if(have_rows('team_members')): ?>
<!-- start of team members -->
<section class="about-section our-team pt-5 pb-5 position-relative bg-accent texture-bg">
<div class="background-image"></div>
<div class="container pt-5 mb-5">
    <div class="row mb-5 pb-5 justify-content-center">
        <div class="col-md-9 text-white">
            <?php the_field('team_description'); ?>
        </div>
    </div>

<?php while(have_rows('team_members')): the_row(); ?>
<!-- start of team members -->
<div class="row pb-5 mb-5">
<div class="col-md-5 img--main">
<?php 
$headshot = get_sub_field('headshot');
echo wp_get_attachment_image($headshot['id'],'full',"",['class'=>'w-100 h-100']); ?>
</div>
<div class="col-lg-6 col-md-6 sm-text-center about"> 
<div class="about-first-half">
<div class="about-before"></div>
<div class="about-middle"></div>
</div>
<div class="about-after"></div>
<div class="about-details pt-5 pl-4 pr-4">
<div class="page details">
<h4 class="bodoni"><?php the_sub_field('name'); ?></h4>
<h5 class=""><?php the_sub_field('job_title'); ?></h5>
</div>
<p style="line-height:1.25"><small><?php the_sub_field('bio',false,false); ?></small></p>
</div>
</div>
</div>
<!-- end of team members -->
<?php endwhile; ?>

</div>
</section>
<!-- end of team members -->
<?php endif; ?>

<?php get_footer(); ?>