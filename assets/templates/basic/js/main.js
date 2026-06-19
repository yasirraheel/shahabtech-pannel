// const { FALSE } = require("sass");


(function ($) {
  "use strict";

  // ============== Header Hide Click On Body Js Start ========
  $('.header-button').on('click', function () {
    $('.body-overlay').toggleClass('show')
  });
  $('.body-overlay').on('click', function () {
    $('.header-button').trigger('click')
    $(this).removeClass('show');
  });
  // =============== Header Hide Click On Body Js End =========

  // ==========================================
  //      Start Document Ready function
  // ==========================================
  $(document).ready(function () {

    // ========================== Header Hide Scroll Bar Js Start =====================
    $('.navbar-toggler.header-button').on('click', function () {
      $('body').toggleClass('scroll-hide-sm')
    });
    $('.body-overlay').on('click', function () {
      $('body').removeClass('scroll-hide-sm')
    });
    // ========================== Header Hide Scroll Bar Js End =====================

    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js Start =====================
    $('.dropdown-item').on('click', function () {
      $(this).closest('.dropdown-menu').addClass('d-block')
    });
    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js End =====================


    // ========================== add active class to ul>li top Active current page Js Start =====================
    function dynamicActiveMenuClass(selector) {
      let FileName = window.location.pathname.split("/").reverse()[0];

      selector.find("li").each(function () {
        let anchor = $(this).find("a");
        if ($(anchor).attr("href") == FileName) {
          $(this).addClass("active");
        }
      });
      // if any li has active element add class
      selector.children("li").each(function () {
        if ($(this).find(".active").length) {
          $(this).addClass("active");
        }
      });
      // if no file name return
      if ("" == FileName) {
        selector.find("li").eq(0).addClass("active");
      }
    }
    if ($('ul').length) {
      dynamicActiveMenuClass($('ul'));
    }
    // ========================== add active class to ul>li top Active current page Js End =====================

    // ================== Password Show Hide Js Start ==========
    $(".toggle-password").on('click', function () {
      $(this).toggleClass("fa-eye");
      var input = $($(this).attr("id"));
      if (input.attr("type") == "password") {
        input.attr("type", "text");
      } else {
        input.attr("type", "password");
      }
    });
    // =============== Password Show Hide Js End =================


    //==========auction sidebar js start here===========
    $(".sidebar-filter__button").on("click", function () {
      $(".left-sidebar").addClass('show-auction-sidebar');
      $(".sidebar-overlay").addClass('show');
    });

    $(".close-sidebar, .sidebar-overlay").on("click", function () {
      $(".left-sidebar").removeClass('show-auction-sidebar');
      $(".sidebar-overlay").removeClass('show');
    });

    $(".toggle-profile-sidebar").on('click', function () {
      $(".profile-setting__sidebar").toggleClass('show-profile-sidebar');
    });
    $(".close-profile-sidebar").on('click', function () {
      $(".profile-setting__sidebar").removeClass('show-profile-sidebar');
    });

    $(".has-dropdown > a").on('click', function () {
      $(".sidebar-submenu").slideUp(200);
      if (
        $(this)
          .parent()
          .hasClass("active")
      ) {
        $(".has-dropdown").removeClass("active");
        $(this)
          .parent()
          .removeClass("active");
      } else {
        $(".has-dropdown").removeClass("active");
        $(this)
          .next(".sidebar-submenu")
          .slideDown(200);
        $(this)
          .parent()
          .addClass("active");
      }
    });
    // Sidebar Dropdown Menu End
    // Sidebar Icon & Overlay js 
    $(".dashboard-body__bar-icon").on("click", function () {
      $(".sidebar-menu").addClass('show-sidebar');
      $(".sidebar-overlay").addClass('show');
    });
    $(".sidebar-menu__close, .sidebar-overlay").on("click", function () {
      $(".sidebar-menu").removeClass('show-sidebar');
      $(".sidebar-overlay").removeClass('show');
    });
    // Sidebar Icon & Overlay js 
    // ===================== Sidebar Menu Js End =================

    // ==================== User product Dropdown Start ==================
    $('.user-info__button').on('click', function (e) {
      e.stopPropagation();
      $('.user-info-dropdown').toggleClass('show');
    });

    $('.user-info-dropdown').on('click', function (e) {
      e.stopPropagation();
      $('.user-info-dropdown').addClass('show');
    });

    $('body').on('click', function () {
      $('.user-info-dropdown').removeClass('show');
    });


    // $('.user-info__button').attr('tabindex', -1).focus();
    // ==================== User product Dropdown End ==================

    // ====================responsive User product Dropdown Start ==================
    $(".header-account-button").on("click", function () {
      $(".user-dropdown-wrapper").addClass('show');
      $(".sidebar-overlay").addClass('show');
    });
    $(".user-dropdown-wrapper__close, .sidebar-overlay").on("click", function () {
      $(".user-dropdown-wrapper").removeClass('show');
      $(".sidebar-overlay").removeClass('show');
    });
    // ====================responsive User product Dropdown End ==================


    // =========show more js start here=========
    jQuery(document).ready(function ($) {
      $(".show-all ").click(function (e) {
        $(".table tbody tr:hidden").slice(0, 3).fadeIn();
        if ($(".table tbody tr:hidden").length < 1) $(this).fadeOut();
      })
    })
    // =========show more js end here========= 

  });
  // ==========================================
  //      End Document Ready function
  // ==========================================

  // ========================= Preloader Js Start =====================
  $(window).on("load", function () {
    $('.preloader').fadeOut();
  })
  // ========================= Preloader Js End=====================



  $(window).on('scroll', function () {
    if ($(window).scrollTop() > 500) {
      $('.header').addClass('fixed-header');
    }
    else {
      $('.header').removeClass('fixed-header');
    }
  });

  // ========================= Header Sticky Js End===================

  //============================ Scroll To Top Icon Js Start =========
  var btn = $('.scroll-top');

  $(window).scroll(function () {
    if ($(window).scrollTop() > 3000) {
      btn.addClass('show');
    } else {
      btn.removeClass('show');
    }
  });

  btn.on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, '300');
  });
  //========================= Scroll To Top Icon Js End ======================


  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })





  // Image Uploader
  function proPicURL(input) {
    if (input.files && input.files[0]) {
      console.log(input);
      var reader = new FileReader();
      reader.onload = function (e) {
        var preview = $(input).closest('.image-upload-wrapper').find('.image-upload-preview');
        $(preview).css('background-image', 'url(' + e.target.result + ')');
        $(preview).addClass('has-image');
        $(preview).hide();
        $(preview).fadeIn(650);
      }
      reader.readAsDataURL(input.files[0]);
    }
  }
  $(".image-upload-input").on('change', function () {
    proPicURL(this);
  });
  $(".remove-image").on('click', function () {
    $(this).parents(".image-upload-preview").css('background-image', 'none');
    $(this).parents(".image-upload-preview").removeClass('has-image');
    $(this).parents(".image-upload-wrapper").find('input[type=file]').val('');
  });
  $("form").on("change", ".file-upload-field", function () {
    $(this).parent(".file-upload-wrapper").attr("data-text", $(this).val().replace(/.*(\/|\\)/, ''));
  });






  $.each($('.select2'), function () {
    $(this)
      .wrap(`<div class="position-relative"></div>`)
      .select2({
        dropdownParent: $(this).parent()
      });
  });

  $.each($('.select2-auto-tokenize'), function () {
    $(this)
      .wrap(`<div class="position-relative"></div>`)
      .select2({
        tags: true,
        tokenSeparators: [','],
        dropdownParent: $(this).parent()
      });
  });





})(jQuery);


