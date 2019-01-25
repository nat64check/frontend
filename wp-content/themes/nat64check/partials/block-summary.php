<?php
$faq_id  = get_category_by_slug( 'faq' )->term_id;
$blog_id = get_category_by_slug( 'blog' )->term_id
?>
<div class="block-summary bg-high">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="title">
                    <h2>SUMMARY</h2>
                </div>
                <div class="content">
                    <p>This website,<?php echo '<strong>www.example</strong>' ?>, has some problems
                        with <?php echo '<strong>NAT64</strong>' ?> and <?php echo '<strong>IPv6</strong>' ?>
                        connectivity. Here is a summary of the issues you'r experiencing and the potential
                        soloutions:</p>
                </div>
                <div class="summary">
                    <h3 class="bg-low">
                        <div class="problem bg-secondary inline-block">1</div>
                        <p class="inline-block">Broadcasting false IPv6 addresses </p><i
                                class="fa fa-caret-down inline-block" aria-hidden="true"></i></h3>
                </div>
                <div class="summary-print inline-block">
                    <a href="#" class="button bg-highest"><i class="fa fa-print" aria-hidden="true"></i>Print</a>
                </div>
                <div class="summary-download inline-block">
                    <a href="#" class="button bg-dark"><i class="fa fa-download" aria-hidden="true"></i>Download report
                        data</a>
                </div>
				<?php
				if ( ! is_user_logged_in() ) { ?>
                    <div class="summary-signup inline-block">
                        <a href="<?php echo get_site_url() . '/register/'; ?>" class="button bg-accent"><i
                                    class="fa fa-pencil-square-o" aria-hidden="true"></i>Signup for an account</a>
                    </div>
                    <div class="signup-benefits">
                        <p><i class="fa fa-check" aria-hidden="true"></i>aasfsaf</p>
                        <p><i class="fa fa-check" aria-hidden="true"></i>asfsaf</p>
                        <p><i class="fa fa-check" aria-hidden="true"></i>asffsa</p>
                    </div>
					<?php
				}
				?>
            </div>
            <div class="col-lg-4">
                <div class="widgets-right">
                    <div class="first-widget bg-mid">
                        <h2>Findout more</h2>
                        <ul>
                            <li>
                                <p>What does it take to be NAT64 and IPv6 compatible ? Read our FAQs to ﬁnd out</p>
                                <p class="text-right"><a href="<?php echo get_term_link( $faq_id ); ?>">Frequently Asked
                                        Questions <i class="fa fa-caret-right" aria-hidden="true"></i></a></p>

                            </li>
                            <li>
                                <p>How does your country fare? Read in-depth analysis of the latest trends in our
                                    blog.</p>
                                <p class="text-right"><a href="<?php echo get_term_link( $blog_id ); ?>">Read the blog
                                        <i class="fa fa-caret-right" aria-hidden="true"></i></a></p>
                            </li>
                        </ul>
                    </div>
                    <div class="second-widget text-center bg-highest">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo.png"
                             alt="<?php bloginfo( 'name' ); ?>"/>
                        <p>is an open source project. You can run your own version, test locally, or add to the global
                            pool.</p>
                        <div class="get-intouch text-center">
                            <h3 class="inline-block">Interested?</h3> <a href="#" class="inline-block">Get in
                                touch. </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
