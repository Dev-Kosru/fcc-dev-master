<? 
$paged = $_GET['paged'] ?: 1;

get_header() ?>

<main class="page-directory">
  <?= fcc_title_bar_render() ?>

  <div class="flex-grid">
    <div class="col-12">
      <?= the_content() ?>

      <div class="page-actions">
        <div class="page-actions-section page-actions-filters">
          <div class="filter">
            <div class="label"><i class="fa fa-search"></i><span class="label">Search</span></div>
            <div class="filter-content">
              <input class="filter-search-input" type="text" size="20">
            </div>
          </div>
        </div>
      </div>

      <div id="content" class="list-view">
        <?
        $posts = get_posts([
          'post_type' => 'directory',
          'posts_per_page' => 20,
          'paged' => $paged,
          's' => $search,
          'meta_query' => [
            'relation' => 'OR',
            [
              'key' => 'freelancer',
              'value' => $search,
              'compare' => 'LIKE', 
            ],
            [
              'key' => 'editor',
              'value' => $search,
              'compare' => 'LIKE', 
            ],
            [
              'key' => 'desc',
              'value' => $search,
              'compare' => 'LIKE', 
            ],
            [
              'key' => 'affiliation',
              'value' => $search,
              'compare' => 'LIKE', 
            ],
            [
              'key' => 'specialities',
              'value' => $search,
              'compare' => 'LIKE', 
            ],
            [
              'key' => 'languages-reporting',
              'value' => $search,
              'compare' => 'LIKE', 
            ],
            [
              'key' => 'languages-writing',
              'value' => $search,
              'compare' => 'LIKE', 
            ],
          ],
          'orderby' => [
            'post_title' => 'ASC'
          ],
        ]);

        $fields = [
          ['email', 'fa fa-envelope', 'E-mail Address', false],
          ['freelancer', 'fas fa-laptop-house', 'Freelancer open to commission approaches?', true],
          ['editor', 'fas fa-comment-alt', 'Editor Open to Pitches?', true],
          ['description', 'fas fa-briefcase', 'Brief Description of Experience', false],
          ['affiliation', 'fas fa-newspaper', 'Current Media Outlet Affiliation', false],
          ['portfolio', 'fas fa-folder-open', 'Freelance Portfolio', false],
          ['specialities', 'fas fa-pencil-alt', 'Coverage Specialties', false],
          ['languages-reporting', 'fas fa-language', 'Language Skills (for reporting)', false],
          ['languages-writing', '', 'Language Skills (for writing/filing)', false],
          ['website', 'fas fa-globe', 'Personal website/ Online Portfolio', false],
          ['linkedin', 'fab fa-linkedin', 'Linkedin Profile', false],
          ['twitter', 'fab fa-twitter-square', 'Twitter Handle', false],
        ];

        foreach ($posts as $item) { 
          $meta = get_post_meta($item->ID); ?>

          <div class="dir-person accordion">
            <div class="accordion-title"><?= $item->post_title ?></div>
            <div class="accordion-content">
              <? foreach ($fields as $field) {
                $value = $meta[$field[0]][0];
                if ($field[3])
                  $value = $meta[$field[0]][0] ? 'Yes' : 'No';

                $icon = empty($field[1]) ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : "<i class='{$field[1]}'></i>";

                echo "<div class='dir-row'>
                  <div class='dir-label'>{$icon} {$field[2]}</div>
                  <div class='dir-value'>{$value}</div>
                </div>";
              } ?>
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
  </div>
</main>

<? get_footer() ?>