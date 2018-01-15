<?php
if( !defined( 'ABSPATH' ) )
    exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link rel="author" type="text/plain" href="<?php echo home_url('humans.txt') ?>">

    <?php wp_head(); ?>

    <?php $extras_options = get_option ( 'wyvern_theme_extras_options' ) ?>
    <?php if ( $extras_options ) : ?>
        <?php echo $extras_options['custom_header_html'] ?>
    <?php endif; ?>
</head>
<body>

<div id="app"></div>

<!-- Prerendered content -->
<div id="prerendered">
    <?php
    /**
     * If there is no Javascript support,
     * show prerendered content.
     *
     * To generate prerendered content,
     * @use node prerender.js
     * @use npm prerender
     */
    $path = $_SERVER['REQUEST_URI'];
    $prerender_path = __DIR__ . '/prerender' . $path . 'index.html';
    if (file_exists($prerender_path)) {
      echo file_get_contents($prerender_path);
    }
    ?>
</div>

<!-- Disable prerendered if Javascript support found -->
<script type="text/javascript">
  var css = '#prerendered { display: none; }',
      head = document.head || document.getElementsByTagName('head')[0],
      style = document.createElement('style');

  style.type = 'text/css';
  if (style.styleSheet){
    style.styleSheet.cssText = css;
  } else {
    style.appendChild(document.createTextNode(css));
  }

  head.appendChild(style);
</script>

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