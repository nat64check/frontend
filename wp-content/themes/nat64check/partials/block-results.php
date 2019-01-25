<?php
//$test_id = base64_decode( $_GET['test_id'] ); 
//global $nat_api;
//$test_id = 131;
//$request = json_decode( $nat_api->request( 'testruns/'.$test_id.'/', 'user' )['body'] );
//$scores = (object)array(
//	'image'		=> $request->image_score,
//	'ressource'	=> $request->resource_score,
//	'overal'	=> $request->overall_score,
//);
?>
<div class="block-results bg-high">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="title">
                    <h2>RESULTS</h2>
                </div>
                <div class="content">
                    <p>This website has an overall moderate rating. It is experiencing some problems
                        with NAT64 and IPv6. The following report details some of the steps you can take
                        to improve your website’s rating and reach more customers.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- change bg class to change color of button for dynamic -->
                <a href="#" class="button bg-accent">
                    <div class="rating-icon inline-block">
                        <i class="fa fa-minus-circle" aria-hidden="true"></i>
                    </div>
                    <div class="rating-txt inline-block">
                        <p>overall rating</p>
                        <h3>MODERATE</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
