<?php

get_header();

echo '<section class="body" style="padding:50px 0px;">';
echo '<div class="container">';
echo '<div class="row">';

echo '<div class="col-12" style="padding-top:25px;padding-bottom:25px;">';
$posts_page_id = get_option( 'page_for_posts' );
$posts_page_title = get_the_title( $posts_page_id );

echo '<h1>' . $posts_page_title . '</h1>';

echo '</div>';

if ( have_posts() ) : 
    
    $counterBlog = 0;
    while ( have_posts() ) : the_post();
    
    if($counterBlog > 2) {
      $counterBlog = 0;
    }
    $counterBlog++;
    
echo '<div class="col-lg-4 col-md-6 col-12 pr-lg-5 col-blog bg-light" style="margin-bottom: 50px;" data-aos="fade-up" data-aos-delay="' . $counterBlog . '00">';

    
    echo '<div class="w-100 h-100 box-shadow blog-content position-relative overflow-h" style="border-radius:8px;">';
    echo '<div class="img-hover overflow-h">';
    echo '<a href="' . get_the_permalink() . '" style="text-decoration:none;" title="Read more for article ' . get_the_title() . '">';
    the_post_thumbnail('full',array(
      'class'=>'w-100 d-block',
      'style'=>'height:210px;object-fit:cover;object-position:top;'
    ));
    echo '</a>';
    echo '</div>';
// echo '<div>';

  // echo '<div class="overlay position-absolute"></div>';
  echo '<div class="position-relative z-1 bg-light" style="padding:25px;padding-bottom:100px;">';
  echo '<a href="' . get_the_permalink() . '" style="text-decoration:none;" title="Read more for article ' . get_the_title() . '">';
  echo '<h3 class="h5" style="line-height:1.5;">' . get_the_title() . '</h3>';
echo '</a>';

  echo '<hr class="border-white">';


  echo '<p class="">' . get_the_tags('Tags: ') . '</p>';
  // echo do_shortcode('[button]Read More[/button]');
  echo '</div>';

  echo '<a href="' . get_the_permalink() . '" title="Read more for article ' . get_the_title() . '" class="position-absolute z-3" style="color:var(--accent-secondary);bottom:25px;left:26px;">Read More</a>';



  // echo '</div>';


  echo '</div>';


  echo '</div>';

// echo '<h3>' . get_the_title() . '</h3>';

// // the_content();
// the_excerpt();

// wp_link_pages();

// edit_post_link();

endwhile;

// default pagination from wordpress
// if ( get_next_posts_link() ) {
// next_posts_link();
// }


// if ( get_previous_posts_link() ) {
// previous_posts_link();
// }

echo '<div class="col-12 d-flex justify-content-center pt-4">';
// pagination with page numbers
    the_posts_pagination( array(
        'mid_size' => 100,
        'prev_text' => __( 'Previous Page', 'textdomain' ),
        'next_text' => __( 'Next Page', 'textdomain' ),
        ) );
echo '</div>';

// the next two lines break the code, not sure why
// else :
// echo '<p>No posts found. :(</p>';

endif;

echo '</div>';
echo '</div>';
echo '</section>';

get_footer(); 
?>