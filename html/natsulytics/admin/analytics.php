<?php
ini_set('memory_limit', '256M');
?>
<?php require('php/dom_elements/head.php'); ?>
<?php
if (isset($_POST['del_analytics'])) {
    $db->query("TRUNCATE `" . TABLE_NAME . "`", array());
    exit;
}

// Set timezone
date_default_timezone_set('US/Eastern');

// Get traffic
$traffic_daily = [];
$traffic_hourly = [];
$traffic_weekly = $db->query("SELECT * FROM `" . TABLE_NAME . "`", array());

if (is_array($traffic_weekly) && count($traffic_weekly) > 0) {
    // Fill daily and hourly arrays
    $curr_time = time();
    for ($i=0; $i < count($traffic_weekly); $i++) { 
        if ($traffic_weekly[$i]['time'] >= $curr_time - 604800) {
            $traffic_hourly[$i] = $traffic_weekly[$i];
        }
        if ($traffic_weekly[$i]['time'] >= $curr_time - 2592000) {
            $traffic_daily[$i] = $traffic_weekly[$i];
        }
    }

    
    $traffic_hourly = array_values($traffic_hourly);
    $traffic_weekly = array_values($traffic_weekly);

    // Subtitle for daily chart
    $subtitle_daily = "'Data from " . date("M j, Y", $traffic_daily[0]['time']) . " to " . date("M j, Y", $traffic_daily[(count($traffic_daily) - 1)]['time']) . "'";

    // Subtitle for hourly chart
    $subtitle_hourly = "'Data from " . date("M j, Y", $traffic_hourly[0]['time']) . " to " . date("M j, Y", $traffic_hourly[(count($traffic_hourly) - 1)]['time']) . "'";

    // Subtitle for weekly chart
    $subtitle_weekly = "'Data from " . date("M j, Y", $traffic_weekly[0]['time']) . " to " . date("M j, Y", $traffic_weekly[(count($traffic_weekly) - 1)]['time']) . "'";


    // Prepare daily data for chart
    $page_views_daily = "[";
    $unique_visitors_daily = "[";
    $day_start = date("M j, Y", $traffic_daily[0]['time']);
    $views = 0;
    $visitors = 0;
    $ips = [];

    for ($i = 0; $i < count($traffic_daily); $i++) {
        if (date("M j, Y", $traffic_daily[$i]["time"]) == $day_start && $i != (count($traffic_daily) - 1)) {
            $views++;

            if (!in_array($traffic_daily[$i]["ip"], $ips)) {
                $visitors++;
                $ips[] = $traffic_daily[$i]["ip"];
            }
        } else if ($i == (count($traffic_daily) - 1)) {
            $page_views_daily .= "{x: new Date('" . ($day_start) . "'), y: " . ($views + 1) . "},";
            if (!in_array($traffic_daily[$i]["ip"], $ips)) {
                $visitors++;
            }
            $unique_visitors_daily .= "{x: new Date('" . ($day_start) . "'), y: " . $visitors . "},";
        } else {
            $page_views_daily .= "{x: new Date('" . ($day_start) . "'), y: " . $views . "},";
            $unique_visitors_daily .= "{x: new Date('" . ($day_start) . "'), y: " . $visitors . "},";
            $views = 1;
            $ips = [$traffic_daily[$i]["ip"]];
            $visitors = 1;
            $day_start = date("M j, Y", $traffic_daily[$i]["time"]);
        }
    }

    $page_views_daily .= "]";
    $unique_visitors_daily .= "]";

    // Prepare hourly data for chart
    $page_views_hourly = "[";
    $unique_visitors_hourly = "[";
    $day_start = date("M j, Y H", $traffic_hourly[0]['time']);
    $day_start_hourly = date("M j, Y H:i:s", $traffic_hourly[0]['time']);
    $views = 0;
    $visitors = 0;
    $ips = [];
    $viewport_min = 0;
    $viewport_max = 0;

    for ($i = 0; $i < count($traffic_hourly); $i++) {
        if (date("M j, Y H", $traffic_hourly[$i]["time"]) == $day_start && $i != (count($traffic_hourly) - 1)) {
            $views++;

            if (!in_array($traffic_hourly[$i]["ip"], $ips)) {
                $visitors++;
                $ips[] = $traffic_hourly[$i]["ip"];
            }
        } else if ($i == (count($traffic_hourly) - 1)) {
            $page_views_hourly .= "{x: new Date('" . ($day_start_hourly) . "'), y: " . ($views + 1) . "},";
            if (!in_array($traffic_hourly[$i]["ip"], $ips)) {
                $visitors++;
            }
            $unique_visitors_hourly .= "{x: new Date('" . ($day_start_hourly) . "'), y: " . $visitors . "},";
            $viewport_max = "new Date('" . ($day_start_hourly) . "')";
        } else {
            $page_views_hourly .= "{x: new Date('" . ($day_start_hourly) . "'), y: " . $views . "},";
            $unique_visitors_hourly .= "{x: new Date('" . ($day_start_hourly) . "'), y: " . $visitors . "},";
            $views = 1;
            $ips = [$traffic_hourly[$i]["ip"]];
            $visitors = 1;
            $day_start = date("M j, Y H", $traffic_hourly[$i]["time"]);
            $day_start_hourly = date("M j, Y H:i:s", $traffic_hourly[$i]["time"]);
        }

        if ($i == floor(count($traffic_hourly) / 2)) {
            $viewport_min = "new Date('" . ($day_start_hourly) . "')";
        }
    }

    $page_views_hourly .= "]";
    $unique_visitors_hourly .= "]";

    // Prepare daily weekly for chart
    $page_views_weekly = "[";
    $unique_visitors_weekly = "[";
    $day_start = date("M j, Y", $traffic_weekly[0]['time']);
    $week_start = date("W", $traffic_weekly[0]['time']);
    $views = 0;
    $visitors = 0;
    $ips = [];

    for ($i = 0; $i < count($traffic_weekly); $i++) {
        if (date("W", $traffic_weekly[$i]["time"]) == $week_start && $i != (count($traffic_weekly) - 1)) {
            $views++;

            if (!in_array($traffic_weekly[$i]["ip"], $ips)) {
                $visitors++;
                $ips[] = $traffic_weekly[$i]["ip"];
            }
        } else if ($i == (count($traffic_weekly) - 1)) {
            $page_views_weekly .= "{x: new Date('" . ($day_start) . "'), y: " . ($views + 1) . "},";
            if (!in_array($traffic_weekly[$i]["ip"], $ips)) {
                $visitors++;
            }
            $unique_visitors_weekly .= "{x: new Date('" . ($day_start) . "'), y: " . $visitors . "},";
        } else {
            $page_views_weekly .= "{x: new Date('" . ($day_start) . "'), y: " . $views . "},";
            $unique_visitors_weekly .= "{x: new Date('" . ($day_start) . "'), y: " . $visitors . "},";
            $views = 1;
            $ips = [$traffic_weekly[$i]["ip"]];
            $visitors = 1;
            $day_start = date("M j, Y", $traffic_weekly[$i]["time"]);
            $week_start = date("W", $traffic_weekly[$i]['time']);
        }
    }

    $page_views_weekly .= "]";
    $unique_visitors_weekly .= "]";
} else {
    echo '<div class="jumbotron jumbotron-fluid" style="background: #555d66;">
            <div class="container">
            <h1 class="display-4">Ooops! <b>404</b></h1>
            <p class="lead">There\'s no web traffic available yet to show.</p>
            <a class="btn btn-primary btn-lg" href="/admin" role="button">Go back to Home</a>
            <a class="btn btn-primary btn-lg" href="' . $_SERVER['SCRIPT_NAME'] . '" role="button">Try again</a>
            </div>
        </div>';
    require('php/dom_elements/footer.php');
    die();
}

// Prepare html for referral content
function prepareReferrals($traffic) {
    $referrals = [];

    if (is_array($traffic) && count($traffic) > 0) {
        $html = "<ul max-elements='15'>";
        for ($i = 0; $i < count($traffic); $i++) {
            if ($traffic[$i]["referral"] != null && !in_array($traffic[$i]["referral"], array_keys($referrals))) {
                $referrals[$traffic[$i]["referral"]] = 1;
            } else if ($traffic[$i]["referral"] != null) {
                $referrals[$traffic[$i]["referral"]]++;
            }
        }

        arsort($referrals);

        for ($i = 0; $i < count($referrals); $i++) {
            $html .= "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 20%;'>" . (array_values($referrals)[$i] >= 1000 ? (round(array_values($referrals)[$i] / 1000, 1) . "K") : array_values($referrals)[$i]) . "</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 80%;' data-placement='left' data-toggle='tooltip' title='" . array_keys($referrals)[$i] . "' ><a style='color: rgba(255, 255, 255, 0.5);' href='" . array_keys($referrals)[$i] . "' target='_blank'>" . (substr(str_replace(['http://', 'https://', 'www.'], '', array_keys($referrals)[$i]), -1) == '/' ? substr(str_replace(['http://', 'https://', 'www.'], '', array_keys($referrals)[$i]), 0, -1) : str_replace(['http://', 'https://', 'www.'], '', array_keys($referrals)[$i])) . "</a></span></li>";
        }
        $html .= "</ul>";

        if (count($referrals) == 0) {
            return "<ul><li style='color: rgba(255,255,255,0.5); font-weight: 300;'>No data available...</li></ul>";
        }

        return $html;
    }

    return "<ul><li>No data available...</li></ul>";
}

// Prepare html for top pages content
function prepareTopPages($traffic) {
    $top_pages = [];

    if (is_array($traffic) && count($traffic) > 0) {
        $html = "<ul max-elements='15'>";
        for ($i = 0; $i < count($traffic); $i++) {
            if ($traffic[$i]["page"] != null && !in_array($traffic[$i]["page"], array_keys($top_pages))) {
                $top_pages[$traffic[$i]["page"]] = 1;
            } else if ($traffic[$i]["page"] != null) {
                $top_pages[$traffic[$i]["page"]]++;
            }
        }

        arsort($top_pages);

        for ($i = 0; $i < count($top_pages); $i++) {
            $html .= "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 20%;'>" . (array_values($top_pages)[$i] >= 1000 ? (round(array_values($top_pages)[$i] / 1000, 1) . "K") : array_values($top_pages)[$i]) . "</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 80%;' data-placement='left' data-toggle='tooltip' title='" . array_keys($top_pages)[$i] . "'><a style='color: rgba(255, 255, 255, 0.5);' href='" . array_keys($top_pages)[$i] . "' target='_blank'>" . substr(array_keys($top_pages)[$i], 1) . "</a></span></li>";
        }
        $html .= "</ul>";

        if (count($top_pages) == 0) {
            return "<ul><li style='color: rgba(255,255,255,0.5); font-weight: 300;'>No data available...</li></ul>";
        }

        return $html;
    }

    return "<ul><li>No data available...</li></ul>";
}

// Prepare html for countries content
function prepareCountries($traffic) {
    $countries = [];

    if (is_array($traffic) && count($traffic) > 0) {
        $html = "<ul max-elements='15'>";
        for ($i = 0; $i < count($traffic); $i++) {
            if ($traffic[$i]["country"] != null && !in_array($traffic[$i]["country"], array_keys($countries))) {
                $countries[$traffic[$i]["country"]] = 1;
            } else if ($traffic[$i]["country"] != null) {
                $countries[$traffic[$i]["country"]]++;
            }
        }

        arsort($countries);

        for ($i = 0; $i < count($countries); $i++) {
            $html .= "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round((array_values($countries)[$i] * 100) / count($traffic), 1)) . "%</span><span data-toggle='tooltip' data-placement='left' title='" . array_keys($countries)[$i] . "' style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'>" . array_keys($countries)[$i] . "</span></li>";
        }
        $html .= "</ul>";

        if (count($countries) == 0) {
            return "<ul><li style='color: rgba(255,255,255,0.5); font-weight: 300;'>No data available...</li></ul>";
        }

        return $html;
    }

    return "<ul><li>No data available...</li></ul>";
}

// Prepare html for devices content
function prepareDevices($traffic) {
    $ios_phone = 0;
    $ios_tablet = 0;
    $android_phone = 0;
    $android_tablet = 0;
    $other_phone = 0;
    $other_tablet = 0;
    $desktop = 0;

    if (is_array($traffic) && count($traffic) > 0) {
        $html = "<ul max-elements='7'>";
        for ($i = 0; $i < count($traffic); $i++) {
            if ($traffic[$i]["phone"] == 1 && $traffic[$i]["ios"] == 1) {
                $ios_phone++;
            } else if ($traffic[$i]["phone"] == 1 && $traffic[$i]["android"] == 1) {
                $android_phone++;
            } else if ($traffic[$i]["phone"] == 1) {
                $other_phone++;
            } else if ($traffic[$i]["tablet"] == 1 && $traffic[$i]["ios"] == 1) {
                $ios_tablet++;
            } else if ($traffic[$i]["tablet"] == 1 && $traffic[$i]["android"] == 1) {
                $android_tablet++;
            } else if ($traffic[$i]["tablet"] == 1) {
                $other_tablet++;
            } else {
                $desktop++;
            }
        }

        $html .= $desktop > 0 ? "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round(($desktop * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'><i style='margin-right: 0.25em; color: #2FAB60' class='fas fa-desktop'></i>Desktop</span></li>" : null;
        $html .= $android_phone > 0 ? "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round(($android_phone * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'><i style='margin-right: 0.25em; color: #2FAB60' class='fab fa-android'></i>Phone</span></li>" : null;
        $html .= $android_tablet > 0 ? "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round(($android_tablet * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'><i style='margin-right: 0.25em; color: #2FAB60' class='fab fa-android'></i>Tablet</span></li>" : null;
        $html .= $ios_phone > 0 ? "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round(($ios_phone * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'><i style='margin-right: 0.25em; color: #2FAB60' class='fab fa-apple'></i>Phone</span></li>" : null;
        $html .= $ios_tablet > 0 ? "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round(($ios_tablet * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'><i style='margin-right: 0.25em; color: #2FAB60' class='fab fa-apple'></i>Tablet</span></li>" : null;
        $html .= $other_phone > 0 ? "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round(($other_phone * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'><i style='margin-right: 0.25em; color: #2FAB60' class='fas fa-mobile'></i>Phone</span></li>" : null;
        $html .= $other_tablet > 0 ? "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round(($other_tablet * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'><i style='margin-right: 0.25em; color: #2FAB60' class='fas fa-tablet'></i>Tablet</span></li>" : null;

        $html .= "</ul>";

        return $html;
    }

    return "<ul><li>No data available...</li></ul>";
}

// Prepare html for browsers content
function prepareBrowsers($traffic) {
    $browsers = [];

    if (is_array($traffic) && count($traffic) > 0) {
        $html = "<ul max-elements='6'>";
        for ($i = 0; $i < count($traffic); $i++) {
            if ($traffic[$i]["browser"] != null && !in_array($traffic[$i]["browser"], array_keys($browsers))) {
                $browsers[$traffic[$i]["browser"]] = 1;
            } else if ($traffic[$i]["browser"] != null) {
                $browsers[$traffic[$i]["browser"]]++;
            }
        }

        arsort($browsers);

        for ($i = 0; $i < count($browsers); $i++) {
            $html .= "<li><span style='font-weight: 700; vertical-align: top; color: #00C5E5; display: inline-block; width: 25%;'>" . (round((array_values($browsers)[$i] * 100) / count($traffic), 1)) . "%</span><span style='font-weight: 300; vertical-align: top; color: rgba(255, 255, 255, 0.5); display: inline-block; width: 75%;'>";
            switch (array_keys($browsers)[$i]) {
                case "Chrome":
                    $html .= '<i style="margin-right: 0.25em; color: #2FAB60" class="fab fa-chrome"></i>';
                    break;
                case "Firefox":
                    $html .= '<i style="margin-right: 0.25em; color: #2FAB60" class="fab fa-firefox-browser"></i>';
                    break;
                case "Opera":
                    $html .= '<i style="margin-right: 0.25em; color: #2FAB60" class="fab fa-opera"></i>';
                    break;
                case "Edge":
                    $html .= '<i style="margin-right: 0.25em; color: #2FAB60" class="fab fa-edge"></i>';
                    break;
                case "Safari":
                    $html .= '<i style="margin-right: 0.25em; color: #2FAB60" class="fab fa-safari"></i>';
                    break;
                case "Internet Explorer":
                    $html .= '<i style="margin-right: 0.25em; color: #2FAB60" class="fab fa-internet-explorer"></i>';
                    break;
                default:
                    $html .= '<i style="margin-right: 0.25em; color: #2FAB60" class="fas fa-question-circle"></i>';
                    break;
            }
            $html .= array_keys($browsers)[$i] . "</span></li>";
        }
        $html .= "</ul>";

        if (count($browsers) == 0) {
            return "<ul><li style='color: rgba(255,255,255,0.5); font-weight: 300;'>No data available...</li></ul>";
        }

        return $html;
    }

    return "<ul><li>No data available...</li></ul>";
}

?>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div class="container-sm" style="margin-top: 1em;">
    <ul class="nav nav-pills mb-3 nav-fill" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" data-toggle="pill" href="#hourly_chart_pill" role="tab" aria-selected="false">Hourly Traffic</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="pill" href="#daily_chart_pill" role="tab" aria-selected="true">Daily Traffic</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="pill" href="#weekly_chart_pill" role="tab" aria-selected="false">Weekly Traffic</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-toggle="pill" href="#stats_pill" role="tab" aria-selected="false">Stats</a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="hourly_chart_pill" role="tabpanel">
            <div id="hourly_chart" style="height: 400px; width: 100%;"></div>
            <div class="row web_analytics_grid" style="margin-top: 2em">
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Referrals</div>
                    <?php echo prepareReferrals($traffic_hourly); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Top Pages</div>
                    <?php echo prepareTopPages($traffic_hourly); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Devices</div>
                    <?php echo prepareDevices($traffic_hourly); ?>
                    <div class="h4" style="margin-top: 1.3262em; margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Browsers</div>
                    <?php echo prepareBrowsers($traffic_hourly); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Countries</div>
                    <?php echo prepareCountries($traffic_hourly); ?>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="daily_chart_pill" role="tabpanel">
            <div id="daily_chart" style="height: 400px; width: 100%;"></div>
            <div class="row web_analytics_grid" style="margin-top: 2em">
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Referrals</div>
                    <?php echo prepareReferrals($traffic_daily); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Top Pages</div>
                    <?php echo prepareTopPages($traffic_daily); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Devices</div>
                    <?php echo prepareDevices($traffic_daily); ?>
                    <div class="h4" style="margin-top: 1.3262em; margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Browsers</div>
                    <?php echo prepareBrowsers($traffic_daily); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Countries</div>
                    <?php echo prepareCountries($traffic_daily); ?>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="weekly_chart_pill" role="tabpanel">
            <div id="weekly_chart" style="height: 400px; width: 100%;"></div>
            <div class="row web_analytics_grid" style="margin-top: 2em">
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Referrals</div>
                    <?php echo prepareReferrals($traffic_weekly); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Top Pages</div>
                    <?php echo prepareTopPages($traffic_weekly); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Devices</div>
                    <?php echo prepareDevices($traffic_weekly); ?>
                    <div class="h4" style="margin-top: 1.3262em; margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Browsers</div>
                    <?php echo prepareBrowsers($traffic_weekly); ?>
                </div>
                <div class="col">
                    <div class="h4" style="margin-bottom: 0.5em; font-weight: 500 ; color: rgba(255, 255, 255, 0.8)">Countries</div>
                    <?php echo prepareCountries($traffic_weekly); ?>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="stats_pill" role="tabpanel">
            <?php
            $unrecognized_user_agents = [];
            for ($i = 0; $i < count($traffic_weekly); $i++) {
                if ($traffic_weekly[$i]['user_agent'] != NULL) {
                    $unrecognized_user_agents[] = $traffic_weekly[$i]['user_agent'];
                }
            }
            echo '<pre style="color: white">';
            print_r($unrecognized_user_agents);
            echo '</pre>';
            ?>
        </div>
    </div>
    <button type="button" class="btn btn-secondary btn-lg btn-block del-analytics-btn" style='margin: 2em auto 0.5em 0'>Delete All Web Traffic Data</button>
</div>
<div class="modal fade" id="del-analytics-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Web Analytics Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete all web analytics data?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary">Yes</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Daily web traffic chart
    var daily_chart = new CanvasJS.Chart("daily_chart", {
        backgroundColor: "#343a40",
        animationEnabled: true,
        zoomEnabled: true,
        title: {
            text: "Daily Web Traffic",
            padding: 15,
            margin: -20,
            fontColor: "white",
            fontWeight: "normal",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        },
        subtitles: [{
            text: <?php echo $subtitle_daily; ?>,
            margin: 15,
            fontColor: "rgba(255,255,255,.5)",
            fontWeight: "lighter",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        }],
        legend: {
            fontColor: "white",
            fontWeight: "bold",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        },
        axisX: {
            labelFormatter: function(e) {
                var val = CanvasJS.formatDate(e.value, "D");
                if (val == "1" || val == "21" || val == "31") {
                    return val + "st";
                } else if (val == "2" || val == "22") {
                    return val + "nd";
                } else if (val == "3" || val == "23") {
                    return val + "rd";
                }
                return val + "th";
            },
            interval: 1,
            intervalType: "day",
            margin: 10,
            labelAngle: 0,
            labelFontColor: "rgba(255,255,255,.5)",
            labelFontWeight: "lighter",
            labelFontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
            lineThickness: 0.5,
            gridThickness: 0.5,
            gridColor: "rgba(255,255,255,0.075)"
        },
        axisY: {
            labelFontColor: "rgba(255,255,255,.75)",
            labelFontWeight: "normal",
            labelFontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
            labelFormatter: function(e) {
                if (e.value >= 1000) {
                    return (e.value / 1000) + "K";
                }
                return e.value;
            },
            lineThickness: 0.5,
            gridThickness: 0.5,
            gridColor: "rgba(255,255,255,0.075)",
            interlacedColor: "#393e44",
            margin: 15,
            includeZero: false
        },
        toolTip: {
            shared: true
        },
        data: [{
                type: "splineArea",
                name: "Page Views",
                showInLegend: true,
                legendMarkerType: "circle",
                color: "rgba(10, 159, 183, 1)",
                markerSize: 7,
                fillOpacity: 0.3,
                dataPoints: <?php echo $page_views_daily; ?>
            },
            {
                type: "splineArea",
                name: "Visitors",
                showInLegend: true,
                legendMarkerType: "square",
                color: "rgba(46, 219, 109, 0.7)",
                markerSize: 7,
                markerType: "square",
                fillOpacity: 0.3,
                dataPoints: <?php echo $unique_visitors_daily; ?>
            }
        ]
    });

    // Hourly web traffic chart
    var hourly_chart = new CanvasJS.Chart("hourly_chart", {
        backgroundColor: "#343a40",
        animationEnabled: true,
        zoomEnabled: true,
        title: {
            text: "Hourly Web Traffic",
            padding: 15,
            margin: -20,
            fontColor: "white",
            fontWeight: "normal",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        },
        subtitles: [{
            text: <?php echo $subtitle_hourly; ?>,
            margin: 15,
            fontColor: "rgba(255,255,255,.5)",
            fontWeight: "lighter",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        }],
        legend: {
            fontColor: "white",
            fontWeight: "bold",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        },
        axisX: {
            interval: 1,
            margin: 10,
            labelAngle: 0,
            labelFontColor: "rgba(255,255,255,.5)",
            labelFontWeight: "lighter",
            labelFontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
            lineThickness: 0.5,
            gridThickness: 0.5,
            gridColor: "rgba(255,255,255,0.075)",
            viewportMinimum: <?php echo $viewport_min; ?>,
            viewportMaximum: <?php echo $viewport_max; ?>
        },
        axisY: {
            labelFontColor: "rgba(255,255,255,.75)",
            labelFontWeight: "normal",
            labelFontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
            labelFormatter: function(e) {
                if (e.value >= 1000) {
                    return (e.value / 1000) + "K";
                }
                return e.value;
            },
            lineThickness: 0.5,
            gridThickness: 0.5,
            gridColor: "rgba(255,255,255,0.075)",
            interlacedColor: "#393e44",
            margin: 15,
            includeZero: false
        },
        toolTip: {
            shared: true
        },
        data: [{
                type: "splineArea",
                name: "Page Views",
                showInLegend: true,
                legendMarkerType: "circle",
                color: "rgba(10, 159, 183, 1)",
                markerSize: 7,
                fillOpacity: 0.3,
                dataPoints: <?php echo $page_views_hourly; ?>,
                xValueFormatString: "DD MMM hh TT"
            },
            {
                type: "splineArea",
                name: "Visitors",
                showInLegend: true,
                legendMarkerType: "square",
                color: "rgba(46, 219, 109, 0.7)",
                markerSize: 7,
                markerType: "square",
                fillOpacity: 0.3,
                dataPoints: <?php echo $unique_visitors_hourly; ?>,
                xValueFormatString: "DD MMM hh TT"
            }
        ]
    });

    // Weekly web traffic chart
    var weekly_chart = new CanvasJS.Chart("weekly_chart", {
        backgroundColor: "#343a40",
        animationEnabled: true,
        zoomEnabled: true,
        title: {
            text: "Weekly Web Traffic",
            padding: 15,
            margin: -20,
            fontColor: "white",
            fontWeight: "normal",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        },
        subtitles: [{
            text: <?php echo $subtitle_weekly; ?>,
            margin: 15,
            fontColor: "rgba(255,255,255,.5)",
            fontWeight: "lighter",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        }],
        legend: {
            fontColor: "white",
            fontWeight: "bold",
            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
        },
        axisX: {
            interval: 1,
            margin: 10,
            labelAngle: 0,
            labelFontColor: "rgba(255,255,255,.5)",
            labelFontWeight: "lighter",
            labelFontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
            lineThickness: 0.5,
            gridThickness: 0.5,
            gridColor: "rgba(255,255,255,0.075)"
        },
        axisY: {
            labelFontColor: "rgba(255,255,255,.75)",
            labelFontWeight: "normal",
            labelFontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
            labelFormatter: function(e) {
                if (e.value >= 1000) {
                    return (e.value / 1000) + "K";
                }
                return e.value;
            },
            lineThickness: 0.5,
            gridThickness: 0.5,
            gridColor: "rgba(255,255,255,0.075)",
            interlacedColor: "#393e44",
            margin: 15,
            includeZero: false
        },
        toolTip: {
            shared: true
        },
        data: [{
                type: "splineArea",
                name: "Page Views",
                showInLegend: true,
                legendMarkerType: "circle",
                color: "rgba(10, 159, 183, 1)",
                markerSize: 7,
                fillOpacity: 0.3,
                dataPoints: <?php echo $page_views_weekly; ?>
            },
            {
                type: "splineArea",
                name: "Visitors",
                showInLegend: true,
                legendMarkerType: "square",
                color: "rgba(46, 219, 109, 0.7)",
                markerSize: 7,
                markerType: "square",
                fillOpacity: 0.3,
                dataPoints: <?php echo $unique_visitors_weekly; ?>
            }
        ]
    });
    hourly_chart.render();
    hourly_chart.render();
</script>
<?php require('php/dom_elements/footer.php'); ?>