	<? if (!isset($_GET['bare'])): ?>
	<footer>
		<div id="footer-columns">
			<div id="footer-logo">
				<a href="/"><img src="<?= get_stylesheet_directory_uri() ?>/images/logo.png" /></a>
			</div>

			<div id="footer-widget-1"><?php dynamic_sidebar( 'footer-1' ); ?></div>
			<div id="footer-widget-2"><?php dynamic_sidebar( 'footer-2' ); ?></div>
			<div id="footer-widget-3"><?php dynamic_sidebar( 'footer-3' ); ?></div>
			<div id="footer-widget-4"><?php dynamic_sidebar( 'footer-4' ); ?></div>
		</div>

		<div id="footer-copyright">Copyright © <?= date("Y") ?> The Foreign Correspondents' Club, Hong Kong. All rights reserved.</div>

	</footer><!-- #colophon -->
	<? endif; ?>
</div><!-- #page -->

<? if (!isset($_GET['bare'])): ?>
	<div id="fcc-social-sidebar">
		<? $link = urlencode(get_the_permalink());
			$title = urlencode(get_the_title()); ?>
		<a href="http://twitter.com/share?text=<?= $title ?>&url=<?= $link ?>&via=fcchk"><i class="fab fa-twitter"></i></a>
		<a href="http://www.facebook.com/sharer.php?u=<?= $link ?>&t=<?= $title ?>" rel="nofollow"><i class="fab fa-facebook-f"></i></a>
		<a href="http://www.linkedin.com/shareArticle?mini=true&url=<?= get_the_permalink() ?>&title=<?= $title ?>"><i class="fab fa-linkedin-in"></i></a>
		<a href="https://mail.google.com/mail/u/0/?view=cm&fs=1&su=<?= $title ?>&body=<?= $link ?>&ui=2&tf=1"><i class="far fa-envelope"></i></a>
	</div>

	<!-- GDPR Consent Bar -->
	<div id="footerGDPR">
			We measure site performance with cookies to improve performance.
			<button id="yesGDPR">OK</button>
	</div>
	<!-- End GDPR Consent Bar -->
<? endif; ?>

<?php wp_footer(); ?>

</body>
</html>
