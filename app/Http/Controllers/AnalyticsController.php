<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Analytics\Period;
use Analytics;

class AnalyticsController extends Controller {

    public function getAnalyticsData() {
        $analyticsData = Analytics::fetchVisitorsAndPageViews(Period::months(6));
        
        $most_visited_pages=Analytics::fetchMostVisitedPages(Period::months(6),20);
        //print_rr($most_visited_pages);
       // die();
        //$analyticsData=  json_encode($analyticsData);
        $country = Analytics::performQuery(Period::days(30), 'ga:sessions', ['dimensions' => 'ga:country', 'sort' => '-ga:sessions']);
        $top_countries = collect($country['rows'])->map(function (array $dateRow) {
            return [
                'country' => $dateRow[0],
                'sessions' => (int) $dateRow[1],
            ];
        });
        //print_rr($top_countries);
        //die();
        return view('admin.analytics', compact('most_visited_pages', 'most_visited_pages','top_countries','top_countries'));
    }

}
