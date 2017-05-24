<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <script src="https://cdn.ravenjs.com/3.14.0/vue/raven.min.js"
          crossorigin="anonymous"></script>
    <script>Raven.config('https://131f75a00fe1410aa7714142e0855dd6@sentry.io/154236').install();</script>

    <?php wp_head(); ?>

    <?php $extras_options = get_option ( 'wyvern_theme_extras_options' ) ?>
    <?php if ( $extras_options ) : ?>
        <?php echo $extras_options['custom_header_html'] ?>
    <?php endif; ?>

    <link type="text/plain" rel="author" href="<?php echo home_url('humans.txt') ?>" />

</head>
<body>
<div id="content">
    <?php
    if ( have_posts() ) :

        if ( is_home() && ! is_front_page() ) {
            echo '<h1>' . single_post_title( '', false ) . '</h1>';
        }

        while ( have_posts() ) : the_post();

            if ( is_singular() ) {
                the_title( '<h1>', '</h1>' );
            } else {
                the_title( '<h2><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
            }

            the_content();

        endwhile;

    endif;
    ?>
</div>

<div id="app"></div>

<?php $tracking_options = get_option ( 'wyvern_theme_options_tracking' ) ?>
<!-- Tracking default -->
<?php if ( isset($tracking_options['google_analytics_id']) ) : ?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', '<?php echo $tracking_options['google_analytics_id'] ?>', 'auto');
    // Do not send pageview on page load, it will be sent by the router
    // ga('send', 'pageview');
</script>
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>