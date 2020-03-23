$("link#poster_share").length || $("head").append('<link type="text/css" id="poster_share" rel="stylesheet" href="' + _win.uri + '/css/poster-share.css">');
function share_img() {
    if ($(".poster-share").length) {
        $('.poster-share').addClass('poster-share_xs');
    }
    if (!$(".poster-share #img_jg").length) {
        setTimeout(function() {
            _html2canvas();
        }, 300);
    }
}

function _html2canvas() {
    if (!$(".qrcode canvas").length) {
    	tbquire(['qrcode'], function() {
        $(".qrcode").qrcode({
            width: 180,
            height: 180,
            correctLevel: 0,
            text: document.URL,
            background: "#fff",
            foreground: "#555"
        });
    	});
    }
    tbquire(['html2canvas'], function() {
    setTimeout(function() {
        html2canvas($('.share_b')[0], {
            scale: 2,
            logging: false,
            useCORS: true
        }).then(function(t) {
            $(".share_ok").html('<span id="img_jg"><img class="fit-cover" src="' + t.toDataURL() + '"></span>');
            $('.fx_loading').css('background','#0a9cf9').html('<i class="fa fa-check-circle-o"></i>分享图片已生成，请长按保存');
        });
    }, 100);
    });
}

function fx_img_gb() {
    $('.poster-share').removeClass('poster-share_xs');
}
