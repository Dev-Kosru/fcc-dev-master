(function($) {
  
$(document).ready(function() {
  $('#loader').fadeOut();

  if ($('.home .fcc-carousel .carousel-cell').length) {
    if (!sessionStorage.getItem('returning'))
      sessionStorage.setItem('returning', true);
    else
      $('.fcc-carousel').data('flickity').remove($('.fcc-carousel .carousel-cell')[0]);
  }

  if (localStorage.getItem('lrc'))
    $('body').addClass('member-logged-in');

  var $header = $("header:not(.sticky)");
  var $stickyHeader = $header.clone().addClass('sticky').hide();
  $header.after($stickyHeader);

  $(window).on("scroll", function() {
    var fromTop = $(window).scrollTop();
    if (fromTop > $header.find('nav').offset().top) {
      if ($stickyHeader.is(':hidden')) {
        $stickyHeader.fadeIn();
      }
    } else {
      if ($stickyHeader.is(':visible')) {
        $stickyHeader.fadeOut();
      }
    }
  });

  $('body').on('click', '.menu-toggle', function() {
    $(this).closest('nav').toggleClass('toggled');
  });

  $('header ul#menu-primary li.current-menu-item').addClass('open');
  $('body').on('click', 'header ul#menu-primary a', function(e) {
    if (!$('header nav').hasClass('toggled'))
      return;

    if (!$(this).hasClass('btn') && e.target.offsetWidth - e.offsetX < 60) {
      $(this).closest('li').toggleClass('open');
      e.preventDefault();
    } else
      $('header nav').removeClass('toggled');
  });

  const intersectionObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fadedIn');
        observer.unobserve(entry.target);
      }
    });
  });
  [...document.querySelectorAll('.fadeIn')].forEach((element) => intersectionObserver.observe(element));

  function updateUrl(obj, nextPage = false) {
    let url = location.search;
    var query = url.substr(1);
    var params = {};
    query.split("&").forEach(function(part) {
      if (!part) return;
      var item = part.split("=");
      params[item[0]] = decodeURIComponent(item[1]);
    });
    if (nextPage) obj.paged = params.paged ? parseInt(params.paged) + 1 : 2;
    else delete params.paged;
    params = {...params, ...obj};
    return '?' + new URLSearchParams(params).toString();
  }

  $('body').on('click', '#load-more .btn', function(e) {
    e.preventDefault();
    var url = updateUrl({}, true);
    $("#load-more").removeClass().addClass('spinner');
    $.get(url, function (response) {
      $("#load-more").removeClass('spinner');
      var html = $(response);
      html = $("#content", html).html().trim();
      if (!html) $("#load-more").addClass('hide');
      $("#content").append(html);
      window.history.pushState({}, "", url);
    }).fail(function() {
      $("#load-more").removeClass('spinner');
    });
  });

  $('.page-actions-filters .label').click(function(e) {
    $(this).parent().toggleClass('open');
  }).siblings().find('input[type=checkbox]').change(function() {
    $('#content').empty();

    let url = updateUrl({
      category: $(this).closest('.filter-content').find('input:checked').map(function(index, item) {
        return $(item).val();
      }).toArray().join(',')
    });
    
    $("#load-more").removeClass().addClass('spinner');
    $.get(url, function (response) {
      $("#load-more").removeClass('spinner');
      var html = $(response);
      html = $("#content", html).html().trim();
      if (!html) $("#load-more").addClass('empty');
      $("#content").html(html);
      window.history.pushState({}, "", url);
    });
  });
  $('.page-actions-filters .filter, .page-actions-filters .search').click(function(e) {
    e.stopPropagation();
  });

  $(window).click(function() {
    $('.page-actions-filters .filter, .page-actions-filters .search').removeClass('open');
  });

  var view = ($('.page-actions-view .active').attr('class') || '').trim().split(' ')[0].slice(0, -5);
  $('body').on('click', '.page-actions-view > div', function(e) {
    e.preventDefault();
    $(this).siblings().removeClass('active');
    $('#content').removeClass().addClass($(this).attr('class'));
    $(this).addClass('active');
    var newView = $(this).attr('class').trim().split(' ')[0].slice(0, -5);

    if ((newView == 'calendar') != (view == 'calendar')) {
      view = newView;
      flatpickr.set('altFormat', view == 'calendar' ? 'F Y' : 'D j F Y');
      flatpickr.set('dateFormat', view == 'calendar' ? 'F Y' : 'D j F Y');
      flatpickr.setDate(flatpickr.selectedDates[0], true);
    }
    view = newView;
  });

  function loadContent(params) {
    $('#content').empty();
    let url = updateUrl(params);
    $("#load-more").removeClass().addClass('spinner');
    $.get(url, function (response) {
      $("#load-more").removeClass('spinner');
      var html = $(response);
      var content = $("#content", html)[0].outerHTML.replaceAll('fadeIn', '');
      $("#content").replaceWith(content);
      $("#load-more").replaceWith($("#load-more", html));
      
      window.history.pushState({}, "", url);
      
      window.document.dispatchEvent(new Event("DOMContentLoaded", {
        bubbles: true,
        cancelable: true
      }));
    });
  }

  var flatpickr;
  if ($('.page-actions-date').length) {
    var curDate = new Date();
    $('.page-actions-date input').val()
    flatpickr = $('.page-actions-date').flatpickr({
      defaultDate: curDate,
      altFormat: view == 'calendar' ? 'F Y' : 'D j F Y',
      dateFormat: view == 'calendar' ? 'F Y' : 'D j F Y',
      wrap: true,
      disableMobile: true,
      onReady: function(selectedDates, dateStr, instance) {
        $('.date-current').text(dateStr);
      },
      onChange: function(selectedDates, dateStr, instance) {
        $('.date-current').text(dateStr);
        curDate = selectedDates[0];
        loadContent({
          view,
          date: flatpickr.formatDate(curDate, 'Y-m-d'),
        });
      }
    });
    $('.page-actions-date .date-prev').click(function() {
      if (view == 'calendar')
        curDate.setMonth(curDate.getMonth() - 1);
      else
        curDate.setDate(curDate.getDate() - 1);
      flatpickr.setDate(curDate, true);
    });
    $('.page-actions-date .date-next').click(function() {
      if (view == 'calendar')
        curDate.setMonth(curDate.getMonth() + 1);
      else
        curDate.setDate(curDate.getDate() + 1);
      flatpickr.setDate(curDate, true);
    });
  }

  flatpickr = $('.filter-date-picker').flatpickr({
    inline: true,
    mode: 'range',
    onChange: function(selectedDates, dateStr, instance) {
      if (selectedDates.length == 2) {
        $(instance.calendarContainer).closest('.filter').removeClass('open');
        loadContent({
          after: flatpickr.formatDate(selectedDates[0], 'Y-m-d'),
          before: flatpickr.formatDate(selectedDates[1], 'Y-m-d'),
        });
      }
    }
  });
  var searchTimeout;
  $('.filter-search-input').keyup(function(e) {
    clearTimeout(searchTimeout);
    var $that = $(this);
    if(e.keyCode == 13)
      loadContent({
        search: $that.val(),
      });
    else
      searchTimeout = setTimeout(function() {
        loadContent({
          search: $that.val(),
        });
      }, 3000);
  });

  $('body').on('click', 'a.fcc-stream-image', function(e) {
    e.preventDefault();
    var link = $(this).attr('href');
    $(this).replaceWith(`<iframe src='${link}?autoplay=1' id="youtube-frame" width="100%" height="250" frameborder="0" allowfullscreen allow='autoplay'></iframe>`);
  });

  $('body').on('click', '.fcc-enquiry-box-btn', function() {
    $(this).siblings('.fcc-enquiry-box-form').slideToggle();
  });

  $('body').on('click', '#fcc-social-sidebar a, #fcc-share-popup a', function(e) {
    e.preventDefault();
    var left = ( $( window ).width()/2 ) - ( 550/2 ),
      top = ( $( window ).height()/2 ) - ( 450/2 ),
      share_link = $( this ).attr( 'href' ),
      new_window = window.open( share_link, '', 'scrollbars=1, height=450, width=550, left=' + left + ', top=' + top );

    if ( window.focus ) {
      new_window.focus();
    }
  });

  $('body').on('click', '.page-news-nav a', function(e) {
    e.preventDefault();
    $('#content').empty();
    $("#load-more").removeClass().addClass('spinner');
    var url = $(this).attr('href');
    $.get(url, function (response) {
      $("#load-more").removeClass('spinner');
      var html = $(response);
      var content = $("#content", html)[0].outerHTML.trim();
      $("#content").replaceWith(content);
      $("#load-more").replaceWith($("#load-more", html));
      window.history.pushState({}, "", url);
    });
  });

  $('body').on('click', '.accordion .accordion-title', function() {
    $(this).parent().toggleClass('open');
  });

  $('body').on('click', 'a.fcc-share', function(e) {
    e.preventDefault();
    e.stopPropagation();

    var href = $(this).attr('href');
    var link = encodeURIComponent(href);
    var title = encodeURIComponent($(this).attr('title'));
    
    $('body').append(`
      <div class='fcc-overlay'>
        <div id='fcc-share-popup'>
          <a href="http://twitter.com/share?text=${title}&url=${link}&via=fcchk"><i class="fab fa-twitter"></i></a>
          <a href="http://www.facebook.com/sharer.php?u=${link}&t=${title}" rel="nofollow"><i class="fab fa-facebook-f"></i></a>
          <a href="http://www.linkedin.com/shareArticle?mini=true&url=${href}&title=${title}"><i class="fab fa-linkedin-in"></i></a>
          <a href="https://mail.google.com/mail/u/0/?view=cm&fs=1&su=${title}&body=${link}&ui=2&tf=1"><i class="far fa-envelope"></i></a>
        </div>
      </div>`);
  });
  $('body').on('click', '.fcc-overlay', function() {
    $(this).remove();
  });

  $('body').on('click', 'a.fcc-jobs-apply', function(e) {
    e.preventDefault();
    $('input[name=position]').val($(this).data('position'));
    $('html, body').animate({
      scrollTop: $(".wpcf7").offset().top
    }, 1000);
  });

  $(".fcc-partner-clubs input").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $(this).siblings('table').find('tr').filter(function() {
      $(this).toggle($(this).find('th').length > 0 || $(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

  $('.echo-cwm-menu a').click(function(e) {
    e.preventDefault();

    if (!$(this).hasClass('selected')) {
      $(this).siblings().removeClass('selected');
      $(this).addClass('selected');
      var $contents = $(this).closest('.echo-cwm-inner').find('.echo-cwm-content');
      $contents.children().removeClass('selected');
      var $content = $contents.find('div[data-index=' + $(this).data('index') + ']');
      $content.addClass('selected');

      $('html, body').animate({
        scrollTop: $content.offset().top - 60
      }, 1000);
    }
  });
});

$(window).load(function() {
  $("iframe#twitter-widget-0").contents().find('head').append(`<style>
    .timeline-Viewport::-webkit-scrollbar {
      width: 10px;
    }
    .timeline-Viewport::-webkit-scrollbar-track {
      background-color: #eeeeee;
      border-radius: 10px;
    }
    .timeline-Viewport::-webkit-scrollbar-thumb {
      border-radius: 10px;
      background-color: white;
      -webkit-box-shadow: 0 0 6px rgba(0,0,0,0.5); 
    }
  </style>`);
});

})(jQuery);