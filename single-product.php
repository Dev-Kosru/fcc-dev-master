<? 
global $post;

get_header() ?>

<main>
  <div class="fcc-title-bar">
    <div class="fcc-title-bar-title">
      <span><a href="/fcc-e-shop">FCC e-Shop</a></span>

      <? $term = get_the_terms($post->ID, 'product_cat')[0];
      $cats[] = $term;
      while ($term->parent) {
        $term = get_term($term->parent); 
        $cats[] = $term;
      }
      $cats = array_reverse($cats);
      foreach ($cats as $cat) { ?>
        <span><a href="/product-category/<?= $cat->slug ?>"><?= $cat->name ?></a></span>
      <? } ?>

      <span><?= mb_strimwidth(trim(do_shortcode(get_the_title())), 0, 30, '...') ?></span>
    </div>
  </div>

  <div id="content" class="single-view">
    <div class="flex-grid">
      <div class="col-3">
        <img src="<?= get_the_post_thumbnail_url($featured, 'medium_large') ?>" class="single-featured" />
      </div>

      <div class="col-9">
        <h1><?= get_the_title() ?></h1>

        <div class="single-post-desc">
          <?= the_content() ?>
        </div>

        <a href="/members-area/#/app/shop_item/<?= get_the_ID() ?>" class="btn btn-white">Check Prices</a>

        <div class="shop-delivery-terms">
          <h3>Delivery Terms</h3>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur sit amet vestibulum enim, id mattis tortor. Proin laoreet, justo ut consectetur pulvinar, sem risus venenatis sem, id ultrices justo lacus quis ex. Sed eget purus bibendum, blandit nisi et
        </div>
      </div>
    </div>

    <? 
    $cats = get_the_terms($post, 'product_cat');
    $terms = array_map(fn($term) => $term->term_id, $cats);
    $products = get_posts([
      'post_type' => 'product',
      'tax_query' => [
        [
          'taxonomy' => 'product_cat',
          'terms' => $terms,
          'field' => 'id',
        ]
      ],
    ]) ?>

    <div class="fcc-product-promotions">
      <div class="title-dark">Related Products</div>
      
      <div class="carousel" data-flickity='{ "cellAlign": "left", "wrapAround": true, "adaptiveHeight": false, "autoPlay": false, "arrowShape": { "x0": 10, "x1": 60, "y1": 50, "x2": 60, "y2": 45, "x3": 15} }'>
        <?php foreach ($products as $product) { 
          $thumbnail_id = get_post_thumbnail_id( $product->ID );
          $image = wp_get_attachment_image_src( $thumbnail_id, 'shop_single' )[0]; ?>

          <div class="carousel-cell">
            <div class="fcc-product">
              <a href="/?p=<?= $product->ID ?>">
                <img src="<?= $image ?: get_stylesheet_directory_uri() . '/images/logo-blue.png' ?>" />
              </a>
              <div class="fcc-product-content">
                <div class="product-name"><?= $product->post_title ?></div>
                <!-- <div class="product-desc"><?= $product->post_content ?></div> -->
                <div class="product-btns">
                  <a href="/?p=<?= $product->ID ?>" class="btn btn-blue">View Product</a>
                  <a href="/members-area/#/shop" class="btn btn-yellow">Add to Cart</a>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div> 
  </div>
</main>

<? get_footer(); ?>