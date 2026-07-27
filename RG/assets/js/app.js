$(function () {

  /* Smooth scroll to How It Works */
  $('a[href="#how"]').on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: $('#how-it-works').offset().top - 80 }, 400);
  });

  /* Navbar link — scroll to How It Works */
  $('.navbar-nav .nav-link').filter(function () {
    return $(this).text().trim() === 'How it works';
  }).on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: $('#how-it-works').offset().top - 80 }, 400);
  });

  /* Sticky nav shadow on scroll */
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 10) {
      $('#main-nav').css('box-shadow', '0 2px 12px rgba(0,0,0,0.07)');
    } else {
      $('#main-nav').css('box-shadow', 'none');
    }
  });

  /* Upload resume button to redirect to login */
  $('#btn-upload').on('click', function () {
    window.location.href = 'jobs.php';
  });

  /* Nav auth buttons */
  $('#btn-login').on('click', function () {
    window.location.href = 'login.php';
  });

  $('#btn-signup').on('click', function () {
    window.location.href = 'register.php';
  });

  /* Step card hover — animate number color */
  $('.step-card').on('mouseenter', function () {
    $(this).find('.step-number').animate({ opacity: 0.4 }, 150, function () {
      $(this).css('color', '#534AB7').animate({ opacity: 1 }, 150);
    });
  }).on('mouseleave', function () {
    $(this).find('.step-number').animate({ opacity: 0.4 }, 150, function () {
      $(this).css('color', '#AFA9EC').animate({ opacity: 1 }, 150);
    });
  });

});
