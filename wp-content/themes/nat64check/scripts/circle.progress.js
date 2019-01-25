jQuery(function ($) {
    var nat = $('.big-circle-nat .ko-progress-circle__overlay p').text().replace('%', '');
    var ipv = $('.big-circle-ipv .ko-progress-circle__overlay p').text().replace('%', '');


    if (nat >= 85) {
        $('.big-circle-nat').css('background-color', '#3DA637');
        $('.big-circle-nat .ko-progress-circle').css('background-color', '#3DA637');
        $('.big-circle-nat .ko-progress-circle__overlay').css('background-color', '#3DA637');
    } else if (nat <= 60) {
        $('.big-circle-nat').css('background-color', '#E20000');
        $('.big-circle-nat .ko-progress-circle').css('background-color', '#E20000');
        $('.big-circle-nat .ko-progress-circle__overlay').css('background-color', '#E20000');
    } else {
        $('.big-circle-nat').css('background-color', '#FCB725');
        $('.big-circle-nat .ko-progress-circle').css('background-color', '#FCB725');
        $('.big-circle-nat .ko-progress-circle__overlay').css('background-color', '#FCB725');
    }
    if (ipv >= 85) {
        $('.big-circle-ipv').css('background-color', '#3DA637');
        $('.big-circle-ipv .ko-progress-circle').css('background-color', '#3DA637');
        $('.big-circle-ipv .ko-progress-circle__overlay').css('background-color', '#3DA637');
    } else if (ipv <= 60) {
        $('.big-circle-ipv').css('background-color', '#E20000');
        $('.big-circle-ipv .ko-progress-circle').css('background-color', '#E20000');
        $('.big-circle-ipv .ko-progress-circle__overlay').css('background-color', '#E20000');
    } else {
        $('.big-circle-ipv').css('background-color', '#FCB725');
        $('.big-circle-ipv .ko-progress-circle').css('background-color', '#FCB725');
        $('.big-circle-naipvt .ko-progress-circle__overlay').css('background-color', '#FCB725');
    }
    $('.count').each(function () {
        $(this).prop('Counter', 0).animate({
            Counter: $(this).text()
        }, {
            duration: 3000,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now) + '%');
            }
        });
    });
    $('.big-circle-nat .ko-progress-circle').attr('data-progress', nat);
    $('.big-circle-ipv .ko-progress-circle').attr('data-progress', ipv);

});

