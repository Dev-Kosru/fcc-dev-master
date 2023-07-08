<?php
add_action('init', function() {
  wp_register_script(
    'fcc-blocks',
    get_template_directory_uri() . '/js/dist/backend.js',
    ['wp-blocks', 'wp-dom-ready','wp-element', 'wp-editor']
  );

  register_block_type('fcc/box', array(
    'editor_script' => 'fcc-blocks',
    // 'render_callback' => 'fcc_box_render',
  ));
});

add_action('acf/init', function () {
  acf_register_block([
    'name'				=> 'carousel',
    'title'				=> 'Carousel',
    'description'		=> 'A custom carousel block.',
    'render_callback'	=> 'fcc_carousel_render',
    'category'			=> 'common',
    'icon'				=> 'images-alt2',
    'keywords'			=> [ 'carousel', 'quote' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'carousel',
    'title' => 'Carousel fields',
    'fields' => [
      [
        'key' => 'carousel_posts',
        'label' => 'Carousel Posts',
        'name' => 'posts',
        'type' => 'post_object',
        'post_type' => array(
          0 => 'post', 1 => 'event', 2 => 'attachment', 3 => 'correspondent',
        ),
        'post_status' => ['publish'],
        'multiple' => 1,
        'return_format' => 'object',
        'ui' => 1,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/carousel',
        ],
      ],
    ],
  ]);

  //-------------------
  
  acf_register_block([
    'name'				=> 'notices',
    'title'				=> 'Notices',
    'description'		=> 'A custom notices block.',
    'render_callback'	=> 'fcc_notices_render',
    'category'			=> 'common',
    'icon'				=> 'megaphone',
    'keywords'			=> [ 'notices', 'quote' ],
    // 'mode' => 'edit'
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'stream',
    'title'				=> 'Video',
    'description'		=> 'A custom video block.',
    'render_callback'	=> 'fcc_stream_render',
    'category'			=> 'common',
    'icon'				=> 'format-video',
    'keywords'			=> [ 'stream', 'quote' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'stream',
    'title' => 'Stream block fields',
    'fields' => [
      [
        'key' => 'image',
        'label' => 'Image',
        'name' => 'image',
        'type' => 'image',
      ],
      [
        'key' => 'link',
        'label' => 'Embed Link (https://www.youtube.com/embed/***********)',
        'name' => 'link',
        'type' => 'text',
      ]
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/stream',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'twitter',
    'title'				=> 'Twitter',
    'description'		=> 'A custom twitter block.',
    'render_callback'	=> 'fcc_twitter_render',
    'category'			=> 'common',
    'icon'				=> 'twitter',
    'keywords'			=> [ 'twitter', 'quote' ],
    'mode' => 'edit'
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'press_freedom',
    'title'				=> 'Press Freedom',
    'description'		=> 'A custom Press Freedom block.',
    'render_callback'	=> 'fcc_press_freedom_render',
    'category'			=> 'common',
    'icon'				=> 'edit',
    'keywords'			=> [ 'press_freedom', 'quote' ],
    'mode' => 'edit'
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'events',
    'title'				=> 'Events',
    'description'		=> 'A custom Events block.',
    'render_callback'	=> 'fcc_events_render',
    'category'			=> 'common',
    'icon'				=> 'calendar-alt',
    'keywords'			=> [ 'events', 'quote' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'events',
    'title' => 'Upcoming Events block fields',
    'fields' => [
      [
        'key' => 'title',
        'label' => 'Title',
        'name' => 'title',
        'type' => 'text',
      ],
      [
        'key' => 'categories',
        'label' => 'Categories',
        'name' => 'categories',
        'type' => 'taxonomy',
        'taxonomy' => 'event-categories',
        'field_type' => 'multi_select',
        'add_term' => 0,
        'return_format' => 'id',
      ],
      [
        'key' => 'field_exclude_categories',
        'label' => 'Exclude Categories',
        'name' => 'exclude_categories',
        'type' => 'taxonomy',
        'taxonomy' => 'event-categories',
        'field_type' => 'multi_select',
        'add_term' => 0,
        'return_format' => 'id',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/events',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'promotions',
    'title'				=> 'Promotions',
    'description'		=> 'A custom promotions block.',
    'render_callback'	=> 'fcc_promotions_render',
    'category'			=> 'common',
    'icon'				=> 'tag',
    'keywords'			=> [ 'promotions', 'quote' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'promotions',
    'title' => 'Promotions fields',
    'fields' => [
      [
        'key' => 'promotions_posts',
        'label' => 'Promotions',
        'name' => 'posts',
        'type' => 'post_object',
        'post_type' => array(
          0 => 'event'
        ),
        'post_status' => ['publish'],
        'multiple' => 1,
        'return_format' => 'object',
        'ui' => 1,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/promotions',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'speakers',
    'title'				=> 'Latest Speakers',
    'description'		=> 'A custom speakers block.',
    'render_callback'	=> 'fcc_speakers_render',
    'category'			=> 'common',
    'icon'				=> 'groups',
    'keywords'			=> [ 'speakers', 'quote' ],
    'mode' => 'edit'
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'title',
    'title'				=> 'Decorated Title',
    'description'		=> 'A custom title block.',
    'render_callback'	=> 'fcc_title_render',
    'category'			=> 'common',
    'icon'				=> 'editor-textcolor',
    'keywords'			=> [ 'title', 'quote' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'title',
    'title' => 'Title block fields',
    'fields' => [
      [
        'key' => 'title_title',
        'label' => 'Title',
        'name' => 'title',
        'type' => 'text',
      ],
      [
        'key' => 'title_type',
        'label' => 'Type',
        'name' => 'type',
        'type' => 'select',
        'choices' => [
          'dark' => 'Dark',
          'light' => 'Light',
        ],
        'default_value' => 'dark',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/title',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'button',
    'title'				=> 'Button',
    'description'		=> 'A custom button block.',
    'render_callback'	=> 'fcc_button_render',
    'category'			=> 'common',
    'icon'				=> 'editor-removeformatting',
    'keywords'			=> [ 'button', 'quote' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'button',
    'title' => 'Button block fields',
    'fields' => [
      [
        'key' => 'title',
        'label' => 'Title',
        'name' => 'title',
        'type' => 'text',
      ],
      [
        'key' => 'link',
        'label' => 'Link',
        'name' => 'link',
        'type' => 'text',
      ],
      [
        'key' => 'type',
        'label' => 'Type',
        'name' => 'type',
        'type' => 'select',
        'choices' => [
          'blue' => 'Blue',
          'yellow' => 'Yellow',
          'white' => 'White',
        ],
        'default_value' => 'blue',
      ],
      [
        'key' => 'new_tab',
        'label' => 'Open in New Tab',
        'name' => 'new_tab',
        'type' => 'true_false',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/button',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'music',
    'title'				=> 'Music and Art',
    'description'		=> 'A custom music & art block.',
    'render_callback'	=> 'fcc_music_render',
    'category'			=> 'common',
    'icon'				=> 'art',
    'keywords'			=> [ 'music', 'quote' ],
    'mode' => 'edit'
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'icon_box',
    'title'				=> 'Icon Box',
    'description'		=> 'A custom icon blox.',
    'render_callback'	=> 'fcc_icon_box_render',
    'category'			=> 'common',
    'icon'				=> 'editor-contract',
    'keywords'			=> [ 'icon', 'box' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'icon-box',
    'title' => 'Icon box fields',
    'fields' => [
      [
        'key' => 'icon',
        'label' => 'Icon',
        'name' => 'icon',
        'type' => 'image',
      ],
      [
        'key' => 'content',
        'label' => 'Content',
        'name' => 'content',
        'type' => 'wysiwyg',
      ],
      [
        'key' => 'icon_box_link',
        'label' => 'Link',
        'name' => 'link',
        'type' => 'link',
        'return_format' => 'url',
      ],
      [
        'key' => 'shadow',
        'label' => 'Shadow',
        'name' => 'shadow',
        'type' => 'checkbox',
        'choices' => ['shadow' => 'Display Line Shadow']
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/icon-box',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'correspondent-preview',
    'title'				=> 'The Correspondent Preview',
    'description'		=> 'A custom correspondent preview block.',
    'render_callback'	=> 'fcc_correspondent_preview_render',
    'category'			=> 'common',
    'icon'				=> 'book',
    'keywords'			=> [ 'correspondent_preview', 'quote' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'correspondent-preview',
    'title' => 'Correspondent Preview',
    'fields' => [
      [
        'key' => 'correspondent-preview-mode',
        'label' => 'Mode',
        'name' => 'mode',
        'type' => 'select',
        'choices' => [
          'latest_story' => 'Latest Story',
          'latest_stories' => 'Latest Stories',
          'latest_issue' => 'Latest Issue'
        ]
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/correspondent-preview',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'gallery-preview',
    'title'				=> 'Gallery',
    'description'		=> 'A custom gallery block.',
    'render_callback'	=> 'fcc_gallery_preview_render',
    'category'			=> 'common',
    'icon'				=> 'images-alt2',
    'keywords'			=> [ 'gallery' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'gallery-preview',
    'title' => 'Gallery fields',
    'fields' => [
      [
        'key' => 'gallery-preview-gallery',
        'label' => 'Gallery',
        'name' => 'gallery',
        'type' => 'post_object',
        'post_type' => array(
          0 => 'gallery'
        ),
        'post_status' => ['publish'],
        'multiple' => 0,
        'return_format' => 'object',
        'ui' => 1,
      ],
      [
        'key' => 'gallery_thumbs',
        'label' => 'Thumbs',
        'name' => 'gallery_thumbs',
        'type' => 'checkbox',
        'choices' => ['thumbs' => 'Display Thumbs Below']
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/gallery-preview',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'history',
    'title'				=> 'History',
    'description'		=> 'A custom history slideshow block.',
    'render_callback'	=> 'fcc_history_render',
    'category'			=> 'common',
    'icon'				=> 'images-alt2',
    'keywords'			=> [ 'moments' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'history',
    'title' => 'History fields',
    'fields' => [
      [
        'key' => 'history_gallery',
        'label' => 'Gallery',
        'name' => 'gallery',
        'type' => 'gallery',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/history',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'title-bar',
    'title'				=> 'Breadcrumb',
    'description'		=> 'A custom Breadcrumb.',
    'render_callback'	=> 'fcc_title_bar_render',
    'category'			=> 'common',
    'icon'				=> 'editor-textcolor',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'preview'
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'enquiry-box',
    'title'				=> 'Enquiry Box',
    'description'		=> 'A custom enquiry box.',
    'render_callback'	=> 'fcc_enquiry_box_render',
    'category'			=> 'common',
    'icon'				=> 'email',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'enquiry-box',
    'title' => 'Enquiry Box fields',
    'fields' => [
      [
        'key' => 'enquiry-box-form',
        'label' => 'Form Shortcode',
        'name' => 'form',
        'type' => 'text',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/enquiry-box',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'staff',
    'title'				=> 'Staff',
    'description'		=> 'A custom Staff block.',
    'render_callback'	=> 'fcc_staff_render',
    'category'			=> 'common',
    'icon'				=> 'admin-users',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'group_5f2b33c75d604',
    'title' => 'Staff',
    'fields' => [
      [
        'key' => 'field_5fd2979286941',
        'label' => 'Title',
        'name' => 'staff_title',
        'type' => 'text',
      ],
      [
        'key' => 'field_5fd55da3a73d9',
        'label' => 'Link',
        'name' => 'staff_link',
        'type' => 'link',
        'return_format' => 'array',
      ],
      [
        'key' => 'field_5fd55dbea73da',
        'label' => 'People',
        'name' => 'staff_people',
        'type' => 'repeater',
        'collapsed' => 'field_5fd55dcea73db',
        'sub_fields' => [
          [
            'key' => 'field_5fd55dcea73db',
            'label' => 'Name',
            'name' => 'name',
            'type' => 'text',
          ],
          [
            'key' => 'field_5fd55ddba73dc',
            'label' => 'Image',
            'name' => 'image',
            'type' => 'image',
          ],
          [
            'key' => 'field_5fd55e00a73dd',
            'label' => 'Position',
            'name' => 'position',
            'type' => 'text',
          ],
          [
            'key' => 'field_5fd55e0da73dg',
            'label' => 'Profile Link',
            'name' => 'url',
            'type' => 'url',
          ],
        ],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/staff',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'product-categories',
    'title'				=> 'Product Categories',
    'description'		=> 'A custom product categories block',
    'render_callback'	=> 'fcc_product_categories_render',
    'category'			=> 'common',
    'icon'				=> 'table-row-before',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'product-categories',
    'title' => 'Product Categories fields',
    'fields' => [
      [
        'key' => 'product-categories-title',
        'label' => 'Title',
        'name' => 'product-categories-title',
        'type' => 'text',
      ],
      [
        'key' => 'product-categories-parent',
        'label' => 'Parent Category',
        'name' => 'product-categories-parent',
        'type' => 'taxonomy',
        'taxonomy' => 'product_cat',
        'field_type' => 'select',
        'add_term' => 0,
        'return_format' => 'id',
        'multiple' => 0,
      ],
      [
        'key' => 'product-categories-bg',
        'label' => 'Background',
        'name' => 'product-categories-bg',
        'type' => 'image',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/product-categories',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'product-promotions',
    'title'				=> 'Product Promotions',
    'description'		=> 'A custom product promotions block',
    'render_callback'	=> 'fcc_product_promotions_render',
    'category'			=> 'common',
    'icon'				=> 'table-row-before',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'product-promotions',
    'title' => 'Product promotions fields',
    'fields' => [
      [
        'key' => 'product-promotions-cat',
        'label' => 'Product Category',
        'name' => 'product-promotions-cat',
        'type' => 'taxonomy',
        'taxonomy' => 'product_cat',
        'field_type' => 'select',
        'add_term' => 0,
        'return_format' => 'id',
        'multiple' => 0,
      ],
      [
        'key' => 'product-promotions-promo',
        'label' => 'Product Promotion',
        'name' => 'product-promotions-promo',
        'type' => 'taxonomy',
        'taxonomy' => 'product_promo',
        'field_type' => 'select',
        'add_term' => 0,
        'return_format' => 'id',
        'multiple' => 0,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/product-promotions',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'product-slideshow',
    'title'				=> 'Product slideshow',
    'description'		=> 'A custom product slideshow block',
    'render_callback'	=> 'fcc_product_slideshow_render',
    'category'			=> 'common',
    'icon'				=> 'format-gallery',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'product-slideshow-products',
    'title' => 'Product/Post/Event Slideshow',
    'fields' => [
      [
        'key' => 'product-slideshow-products',
        'label' => 'Products/Posts/Events',
        'name' => 'product-slideshow-products',
        'type' => 'post_object',
        'post_type' => array(
          0 => 'product', 1 => 'post', 2 => 'event'
        ),
        'post_status' => ['publish'],
        'multiple' => 1,
        'return_format' => 'object',
        'ui' => 1,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/product-slideshow',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'timeline',
    'title'				=> 'Timeline',
    'description'		=> 'A custom timeline block',
    'render_callback'	=> 'fcc_timeline_render',
    'category'			=> 'common',
    'icon'				=> 'format-gallery',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'timeline',
    'title' => 'Timeline',
    'fields' => [
      [
        'key' => 'field_5fd194292a679',
        'label' => 'Items',
        'name' => 'items',
        'type' => 'repeater',
        'sub_fields' => [
          [
            'key' => 'field_5fd194652a67a',
            'label' => 'Date',
            'name' => 'date',
            'type' => 'text',
          ],
          [
            'key' => 'field_5fd1946d2a67b',
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'field_5fd194742a67c',
            'label' => 'Image',
            'name' => 'image',
            'type' => 'image',
          ],
          [
            'key' => 'field_5fd194c32a67d',
            'label' => 'Description',
            'name' => 'description',
            'type' => 'textarea',
          ],
        ],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/timeline',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'accordion',
    'title'				=> 'Accordion',
    'description'		=> 'A custom accordion block',
    'render_callback'	=> 'fcc_accordion_render',
    'category'			=> 'common',
    'icon'				=> 'editor-justify',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'accordion',
    'title' => 'accordion',
    'fields' => [
      [
        'key' => 'accordion-items',
        'label' => 'Items',
        'name' => 'items',
        'type' => 'repeater',
        'collapsed' => 'accordion-title',
        'sub_fields' => [
          [
            'key' => 'accordion-title',
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'accordion-content',
            'label' => 'Content',
            'name' => 'content',
            'type' => 'wysiwyg',
          ],
        ],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/accordion',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'jobs',
    'title'				=> 'Jobs',
    'description'		=> 'A custom jobs block',
    'render_callback'	=> 'fcc_jobs_render',
    'category'			=> 'common',
    'icon'				=> 'admin-tools',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'jobs',
    'title' => 'Jobs',
    'fields' => [
      [
        'key' => 'jobs-positions',
        'label' => 'Positions',
        'name' => 'positions',
        'type' => 'repeater',
        'collapsed' => 'jobs-title',
        'sub_fields' => [
          [
            'key' => 'jobs-title',
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'jobs-description',
            'label' => 'Description File',
            'name' => 'description',
            'type' => 'file',
          ],
        ],
      ],
      [
        'key' => 'jobs-form-description',
        'label' => 'Form Description',
        'name' => 'form-description',
        'type' => 'textarea',
      ],
      [
        'key' => 'jobs-form-shortcode',
        'label' => 'Form Shortcode',
        'name' => 'form-shortcode',
        'type' => 'text',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/jobs',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'partner-clubs',
    'title'				=> 'Reciprocal Clubs',
    'description'		=> 'A custom reciprocal clubs block',
    'render_callback'	=> 'fcc_partner_clubs_render',
    'category'			=> 'common',
    'icon'				=> 'groups',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'preview'
  ]);

  acf_add_local_field_group([
    'key' => 'partner-clubs',
    'title' => 'Reciprocal Clubs',
    'fields' => [
      [
        'key' => 'partner-clubs-all',
        'name' => 'all',
        'type' => 'checkbox',
        'choices' => ['all' => 'Display All Data']
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/partner-clubs',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'content-with-menu',
    'title'				=> 'Content with Menu',
    'description'		=> 'A custom Content with Menu block.',
    'render_callback'	=> 'echo_cwm_render',
    'category'			=> 'common',
    'icon'				=> 'table-col-after',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'cwm_fields',
    'title' => 'Content with Menu',
    'fields' => [
      [
        'key' => 'cwm_items',
        'label' => 'Items',
        'name' => 'items',
        'type' => 'repeater',
        // 'collapsed' => 'cwm_title',
        'sub_fields' => [
          [
            'key' => 'cwm_label',
            'label' => 'Label',
            'name' => 'label',
            'type' => 'text',
          ],
          [
            'key' => 'cwm_title',
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'cwm_content',
            'label' => 'Content',
            'name' => 'content',
            'type' => 'wysiwyg',
          ],
        ],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/content-with-menu',
        ],
      ],
    ],
  ]);

  //--------------------

  acf_register_block([
    'name'				=> 'banner',
    'title'				=> 'Banner',
    'description'		=> 'A custom Banner block.',
    'render_callback'	=> 'echo_banner_render',
    'category'			=> 'common',
    'icon'				=> 'archive',
    'keywords'			=> [ 'title', 'bar' ],
    'mode' => 'edit'
  ]);

  acf_add_local_field_group([
    'key' => 'banner_fields',
    'title' => 'Banner',
    'fields' => [
      [
        'key' => 'banner_bg',
        'label' => 'Background',
        'name' => 'bg',
        'type' => 'image',
      ],
      [
        'key' => 'banner_title',
        'label' => 'Title',
        'name' => 'title',
        'type' => 'textarea',
      ],
      [
        'key' => 'banner_description',
        'label' => 'Description',
        'name' => 'description',
        'type' => 'textarea',
      ],
      [
        'key' => 'banner_button',
        'label' => 'Button',
        'name' => 'button',
        'type' => 'link',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/banner',
        ],
      ],
    ],
  ]);
});