<? get_header(); 

$term_id = get_queried_object_id();
$term = get_term($term_id);
$members = get_posts([
  'post_type' => 'board-member',
  'posts_per_page' => -1,
  'orderby' => 'menu_order',
  'order' => 'ASC',
  'tax_query' => [
    [
      'taxonomy' => 'year-of-board',
      'terms'    => $term_id
    ],
  ],
]);

$board = [];
foreach ($members as $member) {
  $title = get_the_terms($member, 'board-title')[0];
  $board[$title->name][] = $member;
}

function render_staff($board, $title) {
  fcc_staff_render(false, false, false, false, false, $title, false,
    array_map(function($staff) {
      return [
        'image' => ['sizes' => ['medium_large' => get_the_post_thumbnail_url($staff, 'medium_large')]],
        'name' => $staff->post_title,
        'position' => get_field('wpcf-company-name', $staff->ID),
        'url' => get_field('wpcf-file-upload-0', $staff->ID),
      ];
    }, $board[$title])
  );
}
?>

<main>
  <div class="fcc-title-bar">
    <div class="fcc-title-bar-title"><span><a href="/about-us">About</a></span></div>
    <div class="fcc-title-bar-title">The Board</div>
  </div>

  <div class="title-dark">Board of Governors <?= $term->name ?></div>

  <? render_staff($board, 'President') ?>

  <div class="flex-grid">
    <div class="col-6"><? render_staff($board, 'First Vice President') ?></div>
    <div class="col-6"><? render_staff($board, 'Second Vice President') ?></div>
  </div>
  
  <div class="has-light-grey-background-color">
    <? render_staff($board, 'Correspondent Member Governors') ?>
  </div>
  
  <? render_staff($board, 'Journalist Member Governors') ?>
  
  <? render_staff($board, 'Associate Member Governors') ?>

  <div class="title-dark">Former Board of Governors</div>
  <div id="echo-board-members-former">
    <? $years = get_terms(['taxonomy' => 'year-of-board', 'order' => 'desc']); 
    foreach ($years as $year) { ?>
      <a href="<?= get_term_link($year) ?>">Board of Governors <?= $year->name ?></a>
    <? } ?>
  </div>
</main>

<?php get_footer();
