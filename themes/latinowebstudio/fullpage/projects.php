<?php 
/**
 * 
 * Template Name: Projects
 * 
 * 
 */
get_header(); 
?>
<link rel="stylesheet" type="text/css" href="<?php echo home_url(); ?>/wp-content/themes/insideoutcreative/fullpage/fullpage.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo home_url(); ?>/wp-content/themes/insideoutcreative/fullpage/examples.css">
<link rel="stylesheet" type="text/css" href="<?php echo home_url(); ?>/wp-content/themes/insideoutcreative/fullpage/custom.css">
<style>
header.position-relative {
    position: absolute !important;
    width: 100%;
    top: 0;
    left: 0;
    z-index:1;
    background: white;
}
section.hero.position-relative {
    display: none;
}
.section{
    background-size:cover!important;
    background-position:center!important;
}
/* #section0{
    background:url('/wp-content/uploads/2020/07/Sittin-Pretty-Autowerks-Contact-Us.jpg');
} */

</style>

<div id="fullpage">
    <!-- start of repeater slide -->
<?php  if( have_rows('section') ): 
while ( have_rows('section') ) : the_row(); 
$backgroundImage = get_sub_field('background_image');
// echo $backgroundImage['url'];
$SectionID = get_sub_field('title'); 
 ?>
<div class="section" id="<?php echo sanitize_title_with_dashes($SectionID); ?>" style="background:url('<?php echo $backgroundImage['url']; ?>');">
<div class="content text-white">
<div class="bar top"></div>
<div class="container">
	<div class="row">
		<div class="col-12">
	<h3><?php the_sub_field('title'); ?></h3>
	<?php the_sub_field('content'); ?>			
		</div>

	</div>

	</div>
<div class="bar bottom"></div>
</div>
</div>
<?php endwhile; else : endif; ?>
<!-- end of repeater slide -->

    <!-- <div class="section" id="section0">
    <div class="content text-white">
<div class="bar top"></div>
<h3>Materials</h3>
<p>
All of our products are created from raw wood. Some products include elements of premium stainless steel and/or 100% genuine leather.
</p>
<div class="bar bottom"></div>
</div>
    </div> -->
</div>

<script type="text/javascript" src="<?php echo home_url(); ?>/wp-content/themes/insideoutcreative/fullpage/fullpage.min.js"></script>
<script type="text/javascript" src="<?php echo home_url(); ?>/wp-content/themes/insideoutcreative/fullpage/examples.js"></script>

<script type="text/javascript">
    var myFullpage = new fullpage('#fullpage', {
        // sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
        anchors: ['value', 'expand', 'benchmarks', 'branding', 'marketing', 'pr-buzz', 'partnerships', 'efficiency'],
        navigation:true,
        autoScrolling:false,
        fitToSection: false,
        navigationTooltips: [
<?php if( have_rows('section') ): 
while ( have_rows('section') ) : the_row(); 
$SectionID = get_sub_field('title'); ?>
'<?php echo $SectionID; ?>', 
<?php endwhile; else : endif; ?>
    'Fueling', 'Drivetrain', 'Brakes', 'Exterior', 'Software', '2019 Audi S4', 'Efficiency'],
        showActiveTooltip: true,
        menu: '#menu'
    });
</script>
<!-- <script type="text/javascript">
var myFullpage = new fullpage('#fullpage', {
// sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
anchors: ['value', 'expand', 'benchmarks', 'branding', 'marketing', 'pr-buzz', 'partnerships', 'efficiency'],
navigation:true,
autoScrolling:false,
fitToSection: false,
navigationTooltips: ['Engine', 'Fueling', 'Drivetrain', 'Brakes', 'Exterior', 'Software', '2019 Audi S4', 'Efficiency'],
showActiveTooltip: true,
menu: '#menu'
});
</script> -->
<?php get_footer(); ?>