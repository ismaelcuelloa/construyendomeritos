(function($) {
    "use strict"
    $(document).on('ready', function() {
        if($('.popup-video').length > 0) {
            $(".popup-video").magnificPopup({
                type: "iframe",
            });
        }
    })
    if( 'no' === histudy_license.license ) {
        $('a.ocdi__gl-item-button.button.button-primary').text('Activate');
        $('a.ocdi__gl-item-button.button.button-primary').attr('href', histudy_license.license_redirect_url);
    }
    if(localStorage.getItem('rbt-license-success-alert') == 'true') {
        console.log($('.rbt-success-alert'));
        $('.rbt-success-alert').addClass('d-none');
    }
    if($('.rbt-success-alert').length > 0) {
        $('.rbt-success-alert img').on('click', function() {
            $(this).closest('.rbt-success-alert').addClass('d-none');
            localStorage.setItem('rbt-license-success-alert', true);
        })
    }
    $("[data-background]").each(function () {
        $(this).css("background-image", "url( " + $(this).attr("data-background") + "  )");
    });
    $('.rbt-tab-buttons button').on('click', function(e) {
        e.preventDefault();
        const parentClass = $(this).closest('.rainbow-dashboard-box-wrapper');
        parentClass.find('.rbt-tab-buttons button, .rbt-tab-buttons button').removeClass('active');
        const section = $(this).data('target');
        $(this).addClass('active');
        parentClass.find('.rbt-tab-content').addClass('d-none');
        parentClass.find('#'+section).removeClass('d-none');
    })
})(jQuery);