<?php

echo '<footer>';
echo '<section class="bg-accent" style="padding-top:50px;padding-bottom:25px;">';
echo '<div class="container">';
// echo '<div class="row justify-content-center">';

// echo '<div class="col-lg-6 text-white" style="padding-bottom:50px;">';

// echo '</div>';
// echo '</div>';

echo '<div class="row">';

echo '<div class="col-lg-3 text-white">';
echo '<a href="' . home_url() . '">';
echo '<div style="width:125px;">';
echo logoSVG();
echo '</div>';
echo '</a>';

echo '<p class="">' . companyAbout() . '</p>';

echo '</div>';
echo '<div class="col-lg-3 text-white">';
echo '<strong>Navigation</strong>';
echo '<p>';
wp_nav_menu(array(
    'menu' => 'footer',
    'menu_class'=>'menu list-unstyled text-white text-uppercase mt-0'
    ));

echo '</div>';
echo '<div class="col-lg-3 text-white">';
echo '<strong>Shop</strong>';
echo '<p>';
wp_nav_menu(array(
    'menu' => 'Product Categories',
    'menu_class'=>'menu list-unstyled text-white text-uppercase mt-0'
    ));

echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';

echo '<section class="pt-5 bg-accent">';
echo '<div class="container">';
echo '<div class="row justify-content-center">';
echo '<div class="col-12">';



echo '</div>';
echo '<div class="col-12 text-center text-white">';

// echo get_template_part('partials/si');

echo '<div class="text-gray-1 pt-4">';

// the_field('website_message','options');

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
echo '<div class="text-center bg-light" style="padding:15px 0px;">';
    echo '<div class="d-flex justify-content-center align-items-center">';
        echo '<small class=""><a href="https://latinowebstudio.com/" target="_blank" rel="noopener noreferrer" title="Web Design, Web Development & SEO done by Latino Web Studio in Denver, CO" style="" class="">Web Design, Web Development & SEO in Denver, CO</a> done by Latino Web Studio</small>';
    echo '</div>';
echo '</div>';
echo '</footer>';

echo codeFooter();
// if(get_field('footer', 'options')) { the_field('footer', 'options'); }
// if(get_field('footer_code')) { the_field('footer_code'); }

wp_footer();

echo '</body>';
echo '</html>';
?>