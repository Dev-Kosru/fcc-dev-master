<?
global $post; 
$content = get_the_content();

get_header() ?>

<main class="page-gallery">
  <?= fcc_title_bar_render(false, false, false, false, false, "<a href='/about-us/fcc-photo-gallery'>FCC Photo Gallery</a>") ?>
  
  <div class="flex-grid">
    <div class="col-12">
      <div id="content">
        <div class="gallery-title title-dark"><?= $post->post_title ?></div>

        <? if ($content) { ?><div class="gallery-desc"><?= $content ?></div><? } ?>

        <div class='galleries'>
          <? $galleries = get_posts([
            'post_type' => 'gallery',
            'post_parent' => $post->ID,
            'numberposts' => -1,
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
        
        <?
        $gallery = get_field('images');
        if (!empty($gallery))
          fcc_gallery_preview_render(false, false, false, false, false, false, $gallery, true, false);
        ?>
      </div>
    </div>
  </div>
</main>

<? get_footer(); ?>