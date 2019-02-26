<?php
$grey   = '#999999';
$yellow = '#FCB725';
$red    = '#E20000';
?>
<div class="block-loadingtimes bg-mid">
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <div class="title">
                    <h2>LOADING TIMES</h2>
                </div>
                <div class="content">
                    <p>a comment about loading times</p>
                </div>
            </div>
            <div class="col col-sm-3">
                <div class="loading-ipv4 inline-block">
                    <div class="title">IPv4</div>
                    <!-- TODO: fix invalid HTML -->
                    <!--suppress HtmlUnknownAttribute -->
                    <svg style="font-size: 7em" class="progress-circle" viewBox="0 0 44 44">
                        <circle class="bg rating-nat-bg" r="12" cx="22" cy="22" stroke-width="1"
                                fill="<?php echo $grey; ?>"></circle>
                        <circle id="rating-nat-progress" class="progress" r="10" cx="22" cy="22"
                                transform="rotate(-90, 22, 22)" stroke-width="1" fill="none" stroke-dasharray="101"
                                stroke-dashoffset="0"></circle>
                        <text id="rating-nat-timer" x="22" y="22" font-size="12" text-anchor="middle"
                              alignment-baseline="central" fill="green">
                            10S
                        </text>
                    </svg>
                </div>
            </div>
            <div class="col col-sm-3">
                <div class="loading-nat inline-block">
                    <div class="title">NAT64</div>
                    <!-- TODO: fix invalid HTML -->
                    <!--suppress HtmlUnknownAttribute -->
                    <svg style="font-size: 7em" class="progress-circle" viewBox="0 0 44 44">
                        <circle class="bg rating-nat-bg" r="12" cx="22" cy="22" stroke-width="1"
                                fill="<?php echo $yellow; ?>"></circle>
                        <circle id="rating-nat-progress" class="progress" r="10" cx="22" cy="22"
                                transform="rotate(-90, 22, 22)" stroke-width="1" fill="none" stroke-dasharray="101"
                                stroke-dashoffset="0"></circle>
                        <text id="rating-nat-timer" x="22" y="22" font-size="12" text-anchor="middle"
                              alignment-baseline="central" fill="green">
                            12S
                        </text>
                    </svg>
                </div>
            </div>
            <div class="col col-sm-3">
                <div class="loading-ipv6 inline-block">
                    <div class="title">IPv6</div>
                    <!-- TODO: fix invalid HTML -->
                    <!--suppress HtmlUnknownAttribute -->
                    <svg style="font-size: 7em" class="progress-circle" viewBox="0 0 44 44">
                        <circle class="bg rating-nat-bg" r="12" cx="22" cy="22" stroke-width="1"
                                fill="<?php echo $red; ?>"></circle>
                        <circle id="rating-nat-progress" class="progress" r="10" cx="22" cy="22"
                                transform="rotate(-90, 22, 22)" stroke-width="1" fill="none" stroke-dasharray="101"
                                stroke-dashoffset="0"></circle>
                        <text id="rating-nat-timer" x="22" y="22" font-size="12" text-anchor="middle"
                              alignment-baseline="central" fill="green">
                            20S
                        </text>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
