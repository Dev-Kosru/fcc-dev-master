<? get_header() ?>

<main>
  <div class="fcc-title-bar">
    <? $term = get_the_terms($post->ID, 'category')[0] ?>
    <div class="fcc-title-bar-title">
      <span><a href="/news">News</a></span>
      <? $type = get_post_type();
      if ($type !== 'post')
        echo "<span>" . (get_post_type() == 'correspondent' ? '<a href="/news/the-correspondent/">The Correspondent</a>' : ucfirst(get_post_type())) . "</span>";
      if ($term) { ?><span><a href="/news?category=<?= $term->slug ?>"><?= $term->name ?></a></span><? } ?>
      <span><?= mb_strimwidth(trim(do_shortcode(get_the_title())), 0, 30, '...') ?></span>
    </div>
  </div>

  <div class="flex-grid">
    <div class="col-8">
      <div id="content" class="single-view">
        <img src="<?= get_the_post_thumbnail_url($featured, 'medium_large') ?>" class="single-featured" />
        <h1><?= get_the_title() ?></h1>

        <hr/>

        <div class="single-post-date">
          <?= get_the_date('d/m/Y H:i') ?>
        </div>
        
        <div class="single-post-desc">
          <?= preg_replace('/width: \d+px/', '', preg_replace('/ width="\d+"/', '', apply_filters('the_content', get_the_content()))) ?>
        </div>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <? do_shortcode('[news]') ?>
      <? do_shortcode('[speakers]') ?>
      <?= fcc_correspondent_preview_render(false, false, false, false, false) ?>
      <? dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<? get_footer(); ?>