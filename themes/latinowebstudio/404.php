<?php get_header(); ?>
<style>
    .error404 section.hero {
    display: none;
}
</style>
<?php
$globalImg = get_field('global_placeholder_image','options');
echo '<section class="pt-5 pb-5 position-relative bg-attachment" style="background:url('. $globalImg['url'] . ');background-size:cover;background-attachment:fixed;">';

echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col-md-12 text-center pt-5 pb-5" style="background:rgba(255,255,255,.75);">';
echo '<h1>Whoops... 404 Page Not Found</h1>';
echo '<p>We couldn\'t find the page you\'re looking for. The page may have been moved or deleted. Please double-check the URL or go back to the homepage.</p>';
echo '<a href="' . home_url() . '" class="btn mt-5 mb-3 bg-accent text-white br-0 b-0">Go back home</a>';
echo '<h2 class="mb-3" style="-webkit-text-stroke: 2px rgba(0, 0, 0, .5);">Or</h2>';
get_search_form();
echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
get_footer();
?>