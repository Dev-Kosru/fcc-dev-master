<?php 
$paged = $_GET['paged'] ?: 1;
$search = !empty($_GET['search']) ? $_GET['search'] : null;

get_header() ?>

<main class="page-correspondent">
  <?= fcc_title_bar_render() ?>

  <div class="flex-grid">
    <div class="col-8">
      <?= the_content() ?>

      <div class="page-actions">
        <div class="title-dark">The Correspondent</div>
        <div class="page-actions-section page-actions-filters">
          <div class="filter">
            <div class="label"><i class="fa fa-search"></i><span class="label">Search</span></div>
            <div class="filter-content">
              <input class="filter-search-input" type="text" size="20" value="<?= $search ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="correspondent-featured">
        <? $featured = get_posts([
          'post_type' => 'correspondent',
          'posts_per_page' => 1,
          'tax_query' => [
            [
              'taxonomy' => 'magazine-post-type',
              'terms' => 'feature-story',
              'field' => 'slug',
            ]
          ],
        ])[0] ?>
        <div class="title">Featured Story</div>

        <div class="fcc-preview">
          <a href="<?= get_permalink($featured) ?>" class="fcc-preview-image">
            <img src="<?= get_the_post_thumbnail_url($featured, 'medium_large') ?>" />
          </a>
          <div class="fcc-preview-content">
            <div class="fcc-preview-title"><a href="<?= get_permalink($featured) ?>"><?= $featured->post_title ?></a></div>
            <div class="fcc-preview-desc"><?= $featured->post_excerpt ?></div>
            <div class="fcc-preview-actions">
              <a class="btn btn-yellow" href="<?= get_permalink($featured) ?>">Read More</a>
              <a class="fcc-share" href="<?= get_permalink($featured) ?>" title="<?= $featured->post_title ?>"><i class="fa fa-share-alt"></i></a>
            </div>
          </div>
        </div>
      </div>

      <div id="content" class="has-content">
        <? $posts = get_posts([
          'post_type' => 'correspondent',
          'posts_per_page' => 10,
          'paged' => $paged,
          's' => $search,
          'tax_query' => [
            [
              'taxonomy' => 'magazine-post-type',
              'terms' => 'story',
              'field' => 'slug',
            ]
          ],
        ]);
        foreach ($posts as $item) { 
          $thumb = get_the_post_thumbnail_url($item, 'medium_large'); ?>
          <div class="fcc-preview">
            <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
              <img src='<?= $thumb ?: get_stylesheet_directory_uri() . '/images/logo-blue.png' ?>' />
            </a>
            <div class="fcc-preview-content">
              <div class="fcc-preview-title"><a href="<?= get_permalink($item) ?>"><?= $item->post_title ?></a></div>
              <div class="fcc-preview-date"><?= get_the_date('', $item) ?></div>
              <div class="fcc-preview-desc"><?= $item->post_excerpt ?></div>
              <div class="fcc-preview-actions">
                <a class="btn btn-yellow" href="<?= get_permalink($item) ?>">Read More</a>
                <a class="fcc-share" href="<?= get_permalink($item) ?>" title="<?= $item->post_title ?>"><i class="fa fa-share-alt"></i></a>
              </div>
            </div>
          </div>
        <? } ?>
      </div>

      <div id="load-more" class="<?= empty($posts) ? 'empty' : '' ?>">
        <div class="btn btn-white">Load More</div>
        <div class="empty-text">No results</div>
        <?= $spinner ?>
      </div>

      <div class="correspondent-back-issues">
        <div class="title-dark">Back Issues</div>

        <? $issues = get_posts([
          'post_type' => 'correspondent',
          'posts_per_page' => -1,
          'tax_query' => [
            [
              'taxonomy' => 'magazine-post-type',
              'terms' => 'online-ebook',
              'field' => 'slug',
            ]
          ],
        ]); ?>

        <div class="correspondent-back-issues-gallery" data-flickity='{ "lazyLoad": 1, "cellAlign": "left", "contain": true, "adaptiveHeight": false, "autoPlay": false, "imagesLoaded": true, "pageDots": false }'>
          <?php foreach ($issues as $issue) { ?>
            <div class="carousel-cell">
              <img src="<?= get_the_post_thumbnail_url($issue, 'medium_large') ?>" />
              <a href="<?= get_field('wpcf-issuu-link', $issue->ID) ?>"><?= $issue->post_title ?></a>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <div class="sidebar-correspondent widget">
        <div class="title-dark">Flip Book Version</div>
        <? $issue = get_posts([
          'post_type' => 'correspondent',
          'posts_per_page' => 1,
          'tax_query' => [
            [
              'taxonomy' => 'magazine-post-type',
              'terms' => 'online-ebook',
              'field' => 'slug',
            ]
          ],
        ])[0] ?>
        <img src="<?= get_the_post_thumbnail_url($issue, 'medium_large') ?>" />
        <br/>
        <a class="btn btn-yellow" target="_blank" href="<?= get_field('wpcf-issuu-link', $issue->ID) ?>">Read More</a>
        <a class="fcc-share" href="<?= get_field('wpcf-issuu-link', $issue->ID) ?>" title="<?= $issue->post_title ?>"><i class="fa fa-share-alt"></i></a>
      </div>

      <?= do_shortcode(get_field('page-settings-sidebar')); ?>
      <? dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<?php get_footer() ?>