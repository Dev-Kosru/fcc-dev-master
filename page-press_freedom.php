<?php 
$paged = $_GET['paged'] ?: 1;
$before = isset($_GET['before']) ? $_GET['before'] : null;
$after = isset($_GET['after']) ? $_GET['after'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

get_header() ?>

<main class="page-press_freedom">
  <?= fcc_title_bar_render() ?>

  <div class="flex-grid">
    <div class="col-8">
      <?= the_content() ?>
      <div class="title-dark">FCC Mission</div>
<br>
The FCC promotes and facilitates journalism of the highest standard, and defends press freedom in Hong Kong and across the region.<br>
<br>
We will speak out on behalf of our fellow journalists and correspondents, work to ensure they can operate freely and without interference and strive to provide the best resources and information we can to the community.<br>
<br>
The FCC is a private members club that is open to all views.<br>
<br>
<em>(Resolved at the Board of Governors Meeting on 16 July 2022)</em><br>
<br>
<br>
  <div class="title-dark">FCC Policy on Statements</div>
<br>
The FCC recognises that public statements in support of press freedom are a core component of its mission, to which it remains strongly committed.<br>
<br>
The FCC will continue to issue public statements regarding the defence of press freedom in Hong Kong and across the region.<br>
<br>
The responsibility for drafting such statements lies with the Press Freedom Committee; the final say on whether a statement should be published lies with the club’s board of governors.<br>
<br>
The board of governors will seek advice from practising lawyers experienced in giving legal advice to international media to help with issuing statements. When such legal advice is sought, it will be shared with the board in writing. Members are welcome to review the full “Press Freedom Statement Publication Process” document at the front desk.<br>
<br>
<br>

<div class="title-dark">Get in touch</div>
<br>
The Press Freedom Committee monitors press freedom issues in Hong Kong and around the region and welcomes input from members. If any member wishes to bring a press freedom issue to the committee’s attention, please email: <a href="mailto:pressfreedom@fcchk.org">pressfreedom@fcchk.org</a>.<br/>
<br/>
<em>(Resolved at the Board of Governors Meeting on 24 June 2023)</em>


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
              <input class="filter-search-input" type="text" size="20">
            </div>
          </div>
        </div>
      </div>

      <div id="content" class="press_freedom has-content">
        <?php
        $posts = get_posts([
          'category_name' => 'press-freedom',
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
        foreach ($posts as $item) { 
          $thumb = get_the_post_thumbnail_url($item, 'medium_large'); ?>
          <div class="fcc-preview">
            <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
              <img src='<?= get_the_post_thumbnail_url($item, 'medium_large') ?: (get_stylesheet_directory_uri() . '/images/press-freedom.jpg') ?>' />
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
        <?php } ?>
      </div>

      <div id="load-more" class="<?= !count($posts) ? 'empty' : '' ?>">
        <div class="btn btn-white">Load More</div>
        <div class="empty-text">No results</div>
        <?= $spinner ?>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <?= do_shortcode(get_field('page-settings-sidebar')); ?>
      <? dynamic_sidebar('sidebar-extras'); ?> 
    </div>
  </div>
</main>

<?php get_footer() ?>