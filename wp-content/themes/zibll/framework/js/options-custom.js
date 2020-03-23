/**
 * Custom scripts needed for the colorpicker, image button selectors,
 * and navigation tabs.
 */

jQuery(document).ready(function ($) {

	// Loads the color pickers
	$('.of-color').wpColorPicker();

	// Image Options
	$('.of-radio-img-img').click(function () {
		$(this).parent().parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
		$(this).addClass('of-radio-img-selected');
	});

	$('.of-radio-img-label').hide();
	$('.of-radio-img-img').show();
	$('.of-radio-img-radio').hide();

	// Loads tabbed sections if they exist
	if ($('.nav-tab-wrapper').length > 0) {
		options_framework_tabs();
	}

	function options_framework_tabs() {

		var $group = $('.group'),
			$navtabs = $('.nav-tab-wrapper a'),
			active_tab = '';

		// Hides all the .group sections to start
		$group.hide();

		// Find if a selected tab is saved in localStorage
		if (typeof (localStorage) != 'undefined') {
			active_tab = localStorage.getItem('active_tab');
		}

		// If active tab is saved and exists, load it's .group
		if (active_tab != '' && $(active_tab).length) {
			$(active_tab).fadeIn();
			$(active_tab + '-tab').addClass('nav-tab-active');
		} else {
			$('.group:first').fadeIn();
			$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
		}

		// Bind tabs clicks
		$navtabs.click(function (e) {

			e.preventDefault();
			// Remove active class from all tabs
			$navtabs.removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active').blur();
			if (typeof (localStorage) != 'undefined') {
				localStorage.setItem('active_tab', $(this).attr('href'));
			}
			var selected = $(this).attr('href');
			$group.hide();
			$(selected).fadeIn();
		});
	}

	$("[data-html-src]").each(function () {
		$(this).load($(this).attr('data-html-src'));
	});

	setTimeout(function () {
		$('#optionsframework-wrap .updated.fade').css('right', '-300px');
	}, 5000);
	$(".number-slider").ionRangeSlider({
		type: "single",
		keyboard: true
	});

	$('a.button-nav').click(function () {
		$('#optionsframework-wrap nav,.button-nav-bj').toggleClass('xs');
	})
	$('a.button-nav-bj').click(function () {
		$('#optionsframework-wrap nav,#optionsframework-wrap .button-nav-bj').removeClass('xs');
	})

	var getUrl = window.location.hash;
	getUrl=getUrl.replace("#","");
	if(getUrl && $('a#'+getUrl).length){
	$('a#'+getUrl).click();
}
});