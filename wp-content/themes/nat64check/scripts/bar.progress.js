jQuery(function ($) {

    function bar_progress(percent, $element) {

        var progressBarWidth = percent * $element.width() / 100;
        //green
        if (percent >= 85) {
            $element.find('.progress').css('background-color', '#3DA637');
        }
        //yellow
        else if (percent < 60) {
            $element.find('.progress').css('background-color', '#FF0000');
        }
        //red
        else {
            $element.find('.progress').css('background-color', '#FCB725');
        }

        $element.find('.progress').animate({width: progressBarWidth}, 3000).html(percent + '% ');

//    $element.find('.progress').animate({
//        Counter: $(this).html( percent + '% ' ),
//        width: progressBarWidth
//    }, {
//        duration: 3000,
//        easing: 'swing',
//        step: function ( now ) {
//            $( this ).html( percent + '% ' );
//        }
//    });
    }

    var bar_nat = $('#progressbar-nat .progress').attr('value');
    var bar_ipv = $('#progressbar-ipv .progress').attr('value');


    bar_progress(bar_nat, $('#progressbar-nat'));
    bar_progress(bar_ipv, $('#progressbar-ipv'));

});
