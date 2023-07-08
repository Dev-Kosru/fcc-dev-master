<? get_header() ?>

<main class="page-gallery">
  <?= the_content() ?>
  
  <div class="flex-grid">
    <div class="col-12">
      <div id="content" class='galleries'>
        <? $galleries = get_posts([
          'post_type' => 'gallery',
          'post_parent' => 0,
          'posts_per_page' => -1,
        ]);
        foreach ($galleries as $gallery) {
          if (get_field('hidden', $gallery->ID))
            continue;

          $thumb = get_the_post_thumbnail_url($gallery, 'medium_large'); ?>

          <a class="galleries-item" href="<?= get_permalink($gallery) ?>">
            <? if ($thumb) { ?><img src="<?= $thumb ?>"><? } ?>
            <div class="galleries-title"><?= $gallery->post_title ?></div>
          </a>
        <? } ?>
      </div>
    </div>
  </div>
</main>

<? get_footer(); ?>