<!DOCTYPE html>
<html dir="ltr" style="color:#ffffff;" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>"/>
    <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0">
<div id="wrapper" dir="ltr" style="background-color:#EDF0F3;margin:0;padding:75px 0 75px 0;width:100%">
    <table border="0" cellpadding="0" cellspacing="0"
           style="background-color:#FFFFFF;width:600px;margin: auto;">
        <tr>
            <td align="center" valign="top">
                <div id="template_header_image" style="padding:30px 0; border-bottom:1px solid #EDF0F3;">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo.png" alt="Logo nat64check"
                         style="width:auto;height:28px;"/>
                </div>
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
                    <tr>
                        <td align="center" valign="top">
                            <!-- Body -->
                            <table border="0" cellpadding="0" cellspacing="0" width="800" id="template_body"
                                   style="background-color:#FFFFFF;border:0;">
                                <tr>
                                    <td valign="top" id="body_content" style="">
                                        <!-- Content -->
                                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                            <tr>
                                                <td valign="top" style="padding:45px 45px 30px;">
                                                    <div id="body_content_inner"
                                                         style="color:#616161;font-family:Arial,sans-serif;font-weight:normal;font-size:16px;line-height:150%;text-align:center;">
                                                        <p>&nbsp;</p>
                                                        <p><?php echo $post_args->message; ?></p>
                                                        <p>&nbsp;</p>
                                                        <p>The NAT64check team</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- End Content -->
                                    </td>
                                </tr>
                            </table>
                            <!-- End Body -->
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="0" cellspacing="0"
           style="background-color:#EDF0F3;width:600px;margin: auto;">
        <tr>
            <td align="center" valign="top">
                <!-- Footer -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_footer">
                    <tr>
                        <td valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <!-- End Footer -->
            </td>
        </tr>
    </table>
</div>
</body>
</html>
