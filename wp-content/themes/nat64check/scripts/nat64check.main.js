var nat_main = {
    args: {
        action: 'generating_results',
        url_test: '',
        hostname: '',
        init: false
    },
    init: function () {
        var self = this;

        jQuery('.multiselect .selectBox').click(function (ev) {
            self.toggle_select(ev, jQuery(this));
        });

        jQuery('.block-allsearches #main-search button').click(function (ev) {
            self.form_submit(ev, jQuery(this))
        });
        jQuery('.toggle-nav').click(function (ev) {
            self.toggle_nav(ev, jQuery(this));
        });

        jQuery('.user-setting i').click(function (ev) {
            self.user_setting(ev, jQuery(this));
        });

        self.click_swap('#add-schedule', '#acf-form .acf-actions a');

        jQuery('#server-select label').click(function (ev) {
            self.checkbox_swap(ev, jQuery(this));
        });

        jQuery('.user-register #acf-form .acf-form-submit input').val('Register!');

        jQuery('#test-filters .buttons-row a').click(function (ev) {
            self.test_filters(ev, jQuery(this));
        });

        jQuery('#paginator li a').click(function (ev) {
            self.paging_filters(ev, jQuery(this));
        });

        jQuery('#server-select a').click(function (ev) {
            self.choose_server(ev, jQuery(this));
        });

        jQuery('#server-form .summary-button a').click(function (ev) {
            self.choose_server(ev, jQuery(this));
        });

        jQuery('#change-pass-form a').click(function (ev) {

            self.change_pass(ev, jQuery(this));
        });

        jQuery('.home #server-select .checkboxes input').click(function (ev) {
            self.select_name_change(ev, jQuery(this));
        });

        self.page_loader();

        jQuery('#all-res-button').click(function (ev) {
            self.toggle_all_resources(ev, jQuery(this));
        });

        if (jQuery('body').hasClass('page-template-generating-results')) {
            jQuery('#header-bottom .checked-website-button a').css('pointer-events', 'none');
            self.args.url_test = jQuery('#get_url_test').val();
            self.args.hostname = jQuery('#get_hostname').val();
            self.ajax(self.args, self.form_result);
            jQuery('#header-bottom .checked-website-button a').click(function (ev) {
                ev.preventDefault();
                location.reload();
            });
        }

        jQuery('.user-checks .acf-field-5b9ba6f8a8eea .acf-input input').each(function (index) {
            var name = jQuery(this).val();
            jQuery(this).closest('.acf-fields').prepend('<div class="row-name"><h2>Name: </h2><span>' + name + '</span></div>');
        });

        jQuery('.user-checks .acf-actions a').html('<div id="add-schedule">Add schedule <i class="fa fa-plus-circle" aria-hidden="true"></i>');

        jQuery('.acf-row-handle .acf-icon').click(function (ev) {
            self.remove_rowname(ev, jQuery(this));
        });

        jQuery('.user-checks .acf-repeater .acf-row').each(function (index) {
            if (!jQuery(this).hasClass('acf-clone')) {
                if (!jQuery(this).hasClass('-collapsed')) {
                    jQuery(this).addClass('-collapsed');
                }
            }
        });
    },
    remove_rowname: function (ev, el) {
        var row_name = el.parent().next().find('.row-name');
        var open = el.parent().closest('.acf-row').hasClass('-collapsed');
        if (open) {
            row_name.css('display', 'none');
        }
        //		el.closest( 'row-name' ).remove();
    },
    checkbox_swap: function (ev, el) {
        ev.preventDefault();
        el.prev().click();
    },

    toggle_all_resources: function (ev, el) {
        ev.preventDefault();
        jQuery('.all-res-dropdown').toggleClass('active');
        jQuery('#all-res-button').toggleClass('active');
    },
    select_name_change: function (ev, el) {
        var current = jQuery('.selectBox .overSelect');
        var clicked = el.next();
        var server_count = jQuery('.home #server-select').data('server_count');
        clicked.toggleClass('checked');
        if (server_count == jQuery('.checked').length) {
            current.text('All locations');
        } else if (jQuery('.checked').length > 1) {
            current.text(jQuery('.checked').length + ' Servers');
        } else if (jQuery('.home #server-select .checkboxes input').next().hasClass('checked')) {
            current.text(jQuery('.home #server-select .checkboxes .checked').text());
        } else {
            current.text('All locations');
        }
    },
    change_pass: function (ev, el) {
        ev.preventDefault();

        var current_pass = jQuery('#change-pass-form .current-pass').val();
        var new_pass = jQuery('#change-pass-form .new-pass').val();
        if (current_pass && new_pass) {
            jQuery('#change-pass-form').submit();
        } else {
            jQuery('#change-pass-form .input').css('display', 'block');
        }
    },

    page_loader: function () {
        jQuery('#page-loader').fadeOut(200);
    },

    choose_server: function (ev, el) {
        ev.preventDefault();

        jQuery('#server-select input').val(el.attr('href').replace('#', ''));

        jQuery('#server-form form').submit();
    },

    test_filters: function (ev, el) {
        ev.preventDefault();
        if (el.hasClass('test')) {
            jQuery('#test-filters .test-value').val(el.html().toLowerCase());
        } else if (el.hasClass('score')) {
            jQuery('#test-filters .score-value').val(el.html().toLowerCase());
        } else {
            jQuery('#paginator .paging-value').val(el.html().toLowerCase());
        }
        jQuery('#test-filters').submit();
    },
    paging_filters: function (ev, el) {
        //ev.preventDefault();

        jQuery('#paginator .paging-value').val(el.html().toLowerCase());

        jQuery('#test-filters').submit();
    },

    click_swap: function (src, target) {
        jQuery(src).unbind('click');
        jQuery(src).click(function (ev) {
            ev.preventDefault();

            jQuery(target).click();

            jQuery('body').css('cursor', 'progress');
            jQuery(src).css('pointer-events', 'none');
            setTimeout(function () {
                jQuery(src).css('pointer-events', 'auto');
                jQuery('body').css('cursor', 'default');
            }, 2000);

        });
    },
    toggle_select: function (ev, el) {
        el.parent().toggleClass('active');
    },
    toggle_nav: function (ev) {
        ev.preventDefault();
        jQuery('body').toggleClass('show-nav');
    },

    user_setting: function (ev) {
        ev.preventDefault();
        jQuery('.user-setting .user-options').toggleClass('show-setting');
    },
    form_result: function (data) {
        var self = nat_main;

        jQuery('#response').html(data);
        jQuery('#header-bottom .checked-website-button a').css('pointer-events', 'auto');
        jQuery('#header-bottom .checked-website-button a').css('background-color', '#3DA637');
        jQuery('#header-bottom .checked-website-button a').html('<i class="fa fa-check inline-block font-25 color-white"></i> Check again');
        self.args.init = false;
        setTimeout(function () {
            window.location.href = jQuery('#result_url').attr('href')
        }, 5000);

    },
    ajax: function (args, succes) {
        var self = this;

        if (self.working) {
            self.working.abort();
        }

        self.working = jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            dataType: 'html',
            type: 'GET',
            data: args,
            success: function (data) {
                succes(data);
            }
        });
    },
};

jQuery(function () {
    nat_main.init();
});
