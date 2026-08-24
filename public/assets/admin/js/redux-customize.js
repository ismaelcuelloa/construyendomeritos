(function($) {
    "use strict"
    $('.rbt-course-card-disabled').each(function() {
        const parentClass = $(this).closest( '.redux-image-select' );
        parentClass.addClass( 'rbt-no-pointer-activity' );
    })
})(jQuery);