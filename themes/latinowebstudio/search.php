<?php get_header(); ?> 
<section class="pt-5 pb-5 body">
    <div class="container">
        <div class="row">
        <div class="col-lg-9 pr-lg-5">
<?php
echo '<div class="row pb-5 align-items-center">';
$s=get_search_query();
$args = array(
                's' =>$s
            );
    // The Query
$the_query = new WP_Query( $args );
if ( $the_query->have_posts() ) {
    echo '<div class="col-12">';
    _e("<h2 style='font-weight:bold;'>Search Results for: ".get_query_var('s')."</h2>");
    echo '</div>';
        while ( $the_query->have_posts() ) {
           $the_query->the_post();

               echo '<div class="col-md-6">';
               echo '<div class="row pb-5 align-items-center">';
               echo '<div class="col-md-6 col-5">';
               the_post_thumbnail('large',array('class'=>'w-100 h-auto img-hover','style'=>'mix-blend-mode:darken;'));
               echo '</div>';
               echo '<div class="col-md-6 col-7">';
               echo '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>';
               echo '</div>';
               echo '</div>';
               echo '</div>';

               // <li>
               //     <a href="the_permalink(); ">the_title();</a>
               // </li>
               
            }
        }else{
            ?>
        <h2 style='font-weight:bold;color:#000'>Nothing Found</h2>
        <div class="alert alert-info">
            <p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>
        </div>
        <?php 
        echo '</div>';
        } 
        
    echo '</div>';
    echo '</div>';
    echo get_template_part('partials/sidebar');

echo '</div>';
    echo '</div>';
    echo '</section>';

get_footer(); ?>