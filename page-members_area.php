<?php get_header() ?>

<main class="page-members-area">
  <? fcc_title_bar_render(); ?>

  <div class="app-container">
    <div id="react-app" style="position: relative;"></div>
  </div>
  
  <script src="https://www.gstatic.com/firebasejs/7.19.1/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/7.19.1/firebase-analytics.js"></script>
  <script type="text/javascript" src="<?= get_template_directory_uri() ?>/members.js"></script>
</main>

<?php get_footer() ?>