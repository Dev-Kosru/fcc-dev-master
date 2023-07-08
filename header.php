<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>The Foreign Correspondents' Club Hong Kong</title>
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="shortcut icon" href="<?= get_stylesheet_directory_uri() ?>/images/favicon.png">
	
	<?
	global $post;
	if (is_singular()) {
		$title = get_the_title();
		$description = mb_strimwidth(trim(strip_tags(do_shortcode(get_the_content()))), 0, 200, '...');
		$image = '';
		if(!has_post_thumbnail( $post->ID )) //the post does not have featured image, use a default image
			$image = get_stylesheet_directory_uri() . '/images/logo-blue.png';
		else
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium_large' )[0];
		?>

		<meta property="og:locale" content="en_GB" />
		<meta property="og:type" content="website"/>
		<meta property="og:title" content="<?= $title ?>" />
		<meta property="og:description" content="<?= $description ?>"/>
		<meta property="og:type" content="article" />
		<meta property="og:url" content="<?= get_permalink() ?>" />
		<meta property="og:site_name" content="The Foreign Correspondents&#039; Club, Hong Kong | FCC" />
		<meta property="og:image" content="<?= $image ?>"/>
		<meta property="og:image:url" content="<?= $image ?>"/>
		<meta property="og:image:secure_url" content="<?= $image ?>"/>
		<meta property="article:publisher" content="https://www.facebook.com/fcchk.org/"/>
		<meta itemprop="name" content="<?= $title ?>"/>
		<meta itemprop="headline" content="<?= $title ?>"/>
		<meta itemprop="description" content="<?= $description ?>"/>
		<meta itemprop="image" content="<?= $image ?>"/>
		<meta name="twitter:title" content="<?= $title ?>"/>
		<meta name="twitter:url" content="https://www.fcchk.org"/><meta name="twitter:description" content="<?= $description ?>"/>
		<meta name="twitter:image" content="<?= $image ?>"/>
		<meta name="twitter:card" content="summary_large_image"/>
		<meta name="twitter:site" content="@fcchk"/>
		<meta name="description" content="<?= $description ?>"/>
	<? }

	wp_head(); ?>
</head>

<body <?php body_class(isset($_GET['bare']) ? 'page-template-template-bare' : ''); ?>>
<!-- <div id="loader" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: white; z-index: 100;"></div> -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-48761744-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-48761744-1');
  gtag('config', 'G-CEL2DJRM0V');
</script>


<?php wp_body_open(); ?>
<div id="page" class="site">

	<? if (!isset($_GET['bare'])): ?>
	<header class="container">
		<div id="logo">
			<a href="/"><img src="<?= get_stylesheet_directory_uri() ?>/images/logo.png" /></a>
		</div>

		<div id="header-buttons">
			<a id="header-tel" href="tel:0085225211511"><i class="fas fa-phone-volume"></i></a>
			<a id="header-email" href="mailto:concierge@fcchk.org"><i class="fas fa-envelope"></i></a>
			<?= get_search_form() ?>
			<a class="btn btn-yellow members-area" href="/members-area">Members Area</a>
			<a class="btn btn-yellow members-area members-area-logout" href="/members-area#/app/login">Logout</a>
		</div>

		<nav>
			<button class="menu-toggle" aria-controls="site-navigation" aria-expanded="true"><span>Menu</span></button>

			<?= wp_nav_menu([
				'theme_location' => 'primary',
				'items_wrap'           => '
					<ul id="%1$s" class="%2$s">
						%3$s
						<div class="members-buttons xs-visible">
							<a href="/members-area" class="btn btn-yellow">Members Area</a>
							<a class="btn btn-yellow members-area-logout" href="/members-area#/app/login">Logout</a>
						</div>
					</ul>',
			]) ?>
		</nav>

		<div class="clearfix"></div>
	</header>
	<? endif; ?>
