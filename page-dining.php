<? 
$paged = $_GET['paged'] ?: 1;
$starts_before = $_GET['starts_before'] ?: "9999-99-99 99:99:99";
$starts_before = str_replace('+', ' ', $starts_before);
$ends_after = $_GET['ends_after'] ?: date("Y-m-d 00:00:00");
$ends_after = str_replace('+', ' ', $ends_after);
$parent = get_term_by('slug', 'fb-promotions', 'event-categories');
$cat = empty($_GET['category']) ? [$parent->slug] : explode(',', $_GET['category']);
$search = isset($_GET['search']) ? $_GET['search'] : null;
$icon = get_field('icon');
$slider = get_field('page-settings-slider');
$breadcrumb = get_field('page-settings-breadcrumb');
$sidebar = get_field('page-settings-sidebar');

get_header() ?>

<main class="page-dining">
  <? slider_render($slider);
  
  if ($breadcrumb)
    fcc_title_bar_render(); ?>

  <div class="flex-grid">
    <div class="col-8">
      <?= the_content() ?>

      <div class="page-actions">
        <div class="page-actions-section page-actions-filters">
          <div class="filter">
            <div class="label"><i class="fa fa-filter"></i><span class="label">Filter</span></div>
            <div class="filter-content">
              <? fcc_terms_checklist(['post_type' => 'event', 'taxonomy' => 'event-categories', 'child_of' => $parent->term_id, 'selected_cats' => $cat]) ?>
            </div>
          </div>
          <div class="filter">
            <div class="label"><i class="fa fa-search"></i><span class="label">Search</span></div>
            <div class="filter-content">
              <input class="filter-search-input" type="text" size="20">
            </div>
          </div>
        </div>
        <div class="page-actions-section page-actions-title">
          <div class="title-dark">Promotions</div>
        </div>
      </div>

      <div id="content" class="list-view">
        <?
        if (!empty($cat))
          $tax_query = [
            [
              'taxonomy' => 'event-categories',
              'terms' => $cat,
              'field' => 'slug',
            ]
          ];
        $posts = get_posts([
          'post_type' => 'event',
          'posts_per_page' => 10,
          'paged' => $paged,
          's' => $search,
          'meta_query' => [
            'relation' => 'AND',
            'start' => [
              'key' => '_event_start_local',
              'value' => $starts_before,
              'compare' => '<=', 
            ],
            'end' => [
              'key' => '_event_end_local',
              'value' => $ends_after,
              'compare' => '>=', 
            ],
          ],
          'orderby' => [
            'start' => 'ASC'
          ],
          'tax_query' => $tax_query,
        ]);

        foreach ($posts as $item) { 
          $thumb = get_the_post_thumbnail_url($item, 'medium_large'); ?>
          <div class="fcc-preview">
            <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
              <?= $thumb ? "<img src='$thumb' />" : "" ?>
            </a>
            <div class="fcc-preview-content">
              <div class="fcc-preview-title"><a href="<?= get_permalink($item) ?>"><?= $item->post_title ?></a></div>
              <div class="fcc-preview-date"><?= fcc_event_date($item) ?></div>
              <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($item->post_content))), 0, 300, '...') ?></div>
              <div class="fcc-preview-actions">
                <a class="btn btn-yellow" href="<?= get_permalink($item) ?>">Read More</a>
                
                <? if (get_field('event_quota', $item->ID) > 0) {?>
                  <a class="btn btn-blue" href="/members-area/#/app/promotions_promotion/<?= $item->ID ?>">Sign Up</a>
                <? } ?>
                
                <a class="fcc-share" href="<?= get_permalink($item) ?>" title="<?= $item->post_title ?>"><i class="fa fa-share-alt"></i></a>
              </div>
            </div>
          </div>
        <? } ?>
      </div>

      <div id="load-more" class="<?= !count($posts) ? 'empty' : '' ?>">
        <div class="btn btn-white">Load More</div>
        <div class="empty-text">No results</div>
        <?= $spinner ?>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <?= do_shortcode($sidebar); ?>
      <? dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<? get_footer() ?>