<?php 
$paged = $_GET['paged'] ?: 1;
$category = !empty($_GET['category']) ? $_GET['category'] : null;
$before = !empty($_GET['before']) ? $_GET['before'] : null;
$after = !empty($_GET['after']) ? $_GET['after'] : null;
$search = !empty($_GET['search']) ? $_GET['search'] : null;

get_header() ?>

<main class="page-news">
  <?= fcc_title_bar_render(false, false, false, false, false, false, $category == 'club-notices' ? 'Club Notices' : ($category == 'news' ? 'General News' : false)) ?>

  <div class="flex-grid">
    <div class="col-8">
      <?= the_content() ?>

      <div class="page-news-nav">
        <? $pages = unserialize(get_option('fcc_pages')); ?>
        <a href="?category=club-notices" class="btn btn-yellow"><div class="title-dark">Club Notices</div></a>
        <a href="?category=press-freedom" class="btn btn-grey"><div class="title-dark">Press Freedom</div></a>
        <a href="?category=news" class="btn btn-blue"><div class="title-light">General News</div></a>
        <a href="?category=correspondent" class="btn btn-white"><div class="title-dark">The Correspondent</div></a>
      </div>

      <div class="page-actions">
        <div class="page-actions-section page-actions-filters">
          <div class="filter">
            <div class="label"><i class="fa fa-filter"></i><span class="label">Filter</span></div>
            <div class="filter-content">
              <div class="filter-date-picker">
                <input type="text" size="0" data-input readonly>
              </div>
            </div>
          </div>
          <div class="filter">
            <div class="label"><i class="fa fa-search"></i><span class="label">Search</span></div>
            <div class="filter-content">
              <input class="filter-search-input" type="text" size="20" value="<?= $search ?>">
            </div>
          </div>
        </div>
      </div>

      <div id="content" class="has-content">
        <? if ($category || $before || $search) {

          if ($category && $category == 'correspondent')
            $posts = get_posts([
              'post_type' => 'correspondent',
              'posts_per_page' => 10,
              'paged' => $paged,
              'date_query' => [
                [
                  'before' => $before,
                  'inclusive' => true,
                ],
                [
                  'after' => $after,
                  'inclusive' => true,
                ],
              ],
              's' => $search,
            ]);
          else
            $posts = get_posts([
              'posts_per_page' => 10,
              'paged' => $paged,
              'date_query' => [
                [
                  'before' => $before,
                  'inclusive' => true,
                ],
                [
                  'after' => $after,
                  'inclusive' => true,
                ]
              ],
              's' => $search,
              'category_name' => $category,
            ]);

          foreach ($posts as $item) { 
            $thumb = get_the_post_thumbnail_url($item, 'medium_large'); ?>
            <div class="fcc-preview">
              <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
                <img src='<?= $thumb ?: get_stylesheet_directory_uri() . '/images/press-freedom.jpg' ?>' />
              </a>
              <div class="fcc-preview-content">
                <div class="fcc-preview-title"><a href="<?= get_permalink($item) ?>"><?= $item->post_title ?></a></div>
                <div class="fcc-preview-date"><?= get_the_date('', $item) ?></div>
                <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($item->post_content))), 0, 300, '...') ?></div>
                <div class="fcc-preview-actions">
                  <a class="btn btn-yellow" href="<?= get_permalink($item) ?>">Read More</a>
                  <a class="fcc-share" href="<?= get_permalink($item) ?>" title="<?= $item->post_title ?>"><i class="fa fa-share-alt"></i></a>
                </div>
              </div>
            </div>
          <? }
        } else { ?>
          <div class="flex-grid">
            <div class="col-6">
              <?= fcc_notices_render(false, false, false) ?>
            </div>
            <div class="col-6">
              <?= fcc_press_freedom_render(false, false, false) ?>
            </div>
            <div class="col-6">
              <?= fcc_general_news_render(false, false, false) ?>
            </div>
            <div class="col-6">
              <div class="page-news-correspondent fadeIn">
                <?= fcc_correspondent_preview_render(false, false, false, false, false, 'latest_story') ?>
                <?= fcc_correspondent_preview_render(false, false, false, false, false, 'latest_stories') ?>
              </div>
            </div>
          </div>
        <? } ?>
      </div>

      <div id="load-more" class="<?= $category || $before || $search ? '' : 'hide' ?> <?= empty($posts) ? 'empty' : '' ?>">
        <div class="btn btn-white">Load More</div>
        <div class="empty-text">No results</div>
        <?= $spinner ?>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <?= do_shortcode(get_field('page-settings-sidebar')); ?>
      <?php dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<?php get_footer() ?>