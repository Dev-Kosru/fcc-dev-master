<? 
global $post;
global $wpdb;

$term_id = get_queried_object_id();
$term = get_term($term_id);
$subcats_image = get_field('product-category-bg', $term->taxonomy . '_' . $term_id); 
$promo = $promo ?: get_field('product-promotions-promo');

$children = implode(',', array_merge([$term_id], get_term_children($term_id, 'product_cat')));
$sections_ids = $wpdb->get_col("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'product_selection' AND term_taxonomy_id IN (
  SELECT term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id IN (
    SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ({$children})
  )
)");
$selections = empty($sections_ids) ? [] : get_terms('product_selection', ['include' => $sections_ids]);

get_header();
?>

<main class="">
  <div class="fcc-title-bar">
    <div class="fcc-title-bar-title"><span><a href="/fcc-e-shop">FCC e-Shop</a></span></div>
    
    <div class="fcc-title-bar-title">
      <? 
      $bc_term = get_term($term_id);
      $cats[] = $bc_term;
      while ($bc_term->parent) {
        $bc_term = get_term($bc_term->parent); 
        $cats[] = $bc_term;
      }
      $cats = array_reverse($cats);
      foreach ($cats as $cat) { ?>
        <span><a href="/product-category/<?= $cat->slug ?>"><?= $cat->name ?></a></span>
      <? } ?>
    </div>
  </div>

  <? if ($term->parent) { ?>
    <div class="flex-grid">
      <div class="col-3"></div>
      <div class="col-9">
        <div id="content">
          <? while (have_posts()) { 
            the_post(); ?>
            <div class="fcc-product">
              <a href="<?= the_permalink() ?>">
                <img src="<?= get_the_post_thumbnail_url($post->ID, 'woocommerce_thumbnail') ?: get_stylesheet_directory_uri() . '/images/logo-blue.png' ?>" >
              </a>
              <div class="fcc-product-content">
                <div class="product-name"><?= the_title() ?></div>
                <!-- <div class="product-desc"><?= the_content() ?></div> -->
                <div class="product-btns">
                  <a href="/?p=<?= $product->ID ?>" class="btn btn-blue">View Product</a>
                  <a href="/members-area/#/shop" class="btn btn-yellow">Add to Cart</a>
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
    </div>
  <? } else { ?>
    <? if (!empty($subcats_image)) 
      echo fcc_product_categories_render(null, null, null, null, null, $term->name, $subcats_image, $term_id); ?>

    <? $promo_terms = get_terms(['taxonomy' => 'product_promo']);
    foreach ($promo_terms as $promo_term) { 
      fcc_product_promotions_render(null, null, null, null, null, $term_id, $promo_term->term_id);
    } ?>

    <? if (!empty($selections)) { ?>
    <div class="fcc-product-selections">
      <? for ($i = 0; $i < min(3, count($selections)); $i++) {
        $selection = $selections[$i]; ?>
        <div class="fcc-product-selection" style="background-image: url(<?= get_field('product-selection-image', $selection->taxonomy . '_' . $selection->term_id)['sizes']['woocommerce_thumbnail'] ?>)">
          <div class="title-light"><?= $selection->name ?></div>
          <a href="" class="btn btn-yellow">Shop Now</a>
        </div>  
      <? } ?>
    </div>
    <? } ?>

    <? if (count($selections) > 3) { ?>
    <div class="fcc-product-selections fcc-product-selections-extra">
      <? for ($i = 3; $i < min(5, count($selections)); $i++) {
        $selection = $selections[$i]; ?>
        <div class="fcc-product-selection" style="background-image: url(<?= get_field('product-selection-image', $selection->taxonomy . '_' . $selection->term_id)['sizes']['woocommerce_thumbnail'] ?>)">
          <div class="title-light"><?= $selection->name ?></div>
          <hr/>
          <a href="" class="btn btn-yellow">Shop Now</a>
        </div>  
      <? } ?>
    </div>
    <? } ?>

    <div id="content">
      <? while (have_posts()) { 
        the_post(); ?>
        <div class="fcc-product">
          <a href="<?= the_permalink() ?>">
            <img src="<?= get_the_post_thumbnail_url($post->ID, 'woocommerce_thumbnail') ?: get_stylesheet_directory_uri() . '/images/logo-blue.png' ?>" >
          </a>
          <div class="fcc-product-content">
            <div class="product-name"><?= the_title() ?></div>
            <!-- <div class="product-desc"><?= the_content() ?></div> -->
            <div class="product-btns">
              <a href="/?p=<?= $product->ID ?>" class="btn btn-blue">View Product</a>
              <a href="/members-area/#/shop" class="btn btn-yellow">Add to Cart</a>
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
  <? } ?>
</main>

<? get_footer() ?>