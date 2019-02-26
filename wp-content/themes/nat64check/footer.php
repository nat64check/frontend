</div>
<footer id="footer">
    <div id="footer-top" class="bg-highest">
        <div class="container">
            <div class="row">
                <div class="partner col flex-container align-items-center justify-content-center">
                    <a href="https://www.internetsociety.org/deploy360/" target="_blank"><img
                                src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo-internet-society.png"
                                alt="Internet Society"/></a>
                </div>
                <div class="partner col flex-container align-items-center justify-content-center">
                    <a href="https://go6.si/" target="_blank"><img
                                src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo-go6.png"
                                alt="Go6"/></a>
                </div>
                <div class="partner col flex-container align-items-center justify-content-center">
                    <a href="https://www.steffann.nl/site/" target="_blank"><img
                                src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo-steffann.png"
                                alt="S.J.M. Steffann"/></a>
                </div>
                <div class="partner col flex-container align-items-center justify-content-center">
                    <a href="https://www.simplyunderstand.com/" target="_blank"><img
                                src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo-simplyunderstand.png"
                                alt="Simply Understand"/></a>
                </div>
                <div class="partner col flex-container align-items-center justify-content-center max">
                    <a href="https://www.max.nl/" target="_blank"><img
                                src="<?php echo get_stylesheet_directory_uri(); ?>/graphics/logo-max.svg"
                                alt="Internetbureau Max"/></a>
                </div>
            </div>
        </div>
    </div>
    <div id="footer-bottom" class="bg-dark">
        <div class="container">
            <ul class="footer-content row">
				<?php dynamic_sidebar( 'footer-sidebar' ); ?>
            </ul>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</div>
</body>
</html>
