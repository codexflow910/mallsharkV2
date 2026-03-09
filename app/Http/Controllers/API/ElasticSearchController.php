<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;

class ElasticSearchController extends Controller
{
    //
    var $esClient;
    public function __construct()
    {
        $hosts = [
            [
                // 'host' => 'localhost', 
               'host' => env('ELASTICSEARCH_HOST','10.116.0.4'),
                'port' => env('ELASTICSEARCH_PORT','9200'),
                // 'port' => '9200',
                'scheme' => env('ELASTICSEARCH_SCHEME'),
                // 'scheme' => 'http',
                'user' => env('ELASTICSEARCH_USER'),
                'pass' => env('ELASTICSEARCH_PASS')
            ],
        ];



        $this->esClient = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
        //  echo 'Data: <pre>' .print_r($this->esClient,true). '</pre>';
        //     die();
    }
    public function keywordsearch(Request $request)
    {
        $filter_query = [];
        if($request->has('category') && $request->filled('category')){
            $filter_query[]['term'] = array(
                'categories.keyword' => $request->input('category'),
            );
        }
        $keyword = $request->input('keyword');
        $arr = array();
        $must_filter = array();
        $must_filter[] = array('multi_match' => array('query' => $keyword,
        'fields' => [
            'title^4', // highest priority
            'tags^3', 
            'short_description^2', 
            'description' // lowest priority
        ],
          "analyzer" => "standard"));

        $must_filter[] = array('term' => array('status' => 1));
        $params = [
            'index' => 'shopzit',
            'type' => 'products',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' =>$must_filter,
                        'filter' => $filter_query
                    ]
                ]
            ]
        ];
        
        $response = $this->esClient->search($params);

        if (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
            // dd($response['hits']['hits']);
            // print_rr($response['hits']['hits']);
            $tg_name = '';
            foreach ($response['hits']['hits'] as $key => $val) {
                // dd($val);
                // $tags = $val['_source']['tags'];
                $title = $val['_source']['title'];
                    $category_name = isset($val['_source']['categories'][0]) ? $val['_source']['categories'][0] : '';
                    $category_name = isset($val['_source']['categories'][1]) ? $val['_source']['categories'][1] : $category_name;
                    // $comp_name = $tg_name . ' in category ' . $category_name;
                    $comp_name = $title . ' in category ' . $category_name;
                    if (!in_array($comp_name, $arr)) {
                        $arr[] = $comp_name;
                    }
                // }
            }
        }
        //print_rr($arr);
        // die();
        $arr[] = 'find "' . $keyword . '" in brands';

        $parsedData = array_map(function($item) use ($keyword) {
            if (strpos($item, "in category") !== false) {
                $parts = explode(" in category ", $item);
                $categoryParts = explode("-", $parts[1]);
                $capitalizedCategory = array_map(function($word) {
                    return ucfirst($word);
                }, $categoryParts);
                return [
                    'label' => $parts[0],
                    'category' => implode(" ", $capitalizedCategory),
                    'originalCategory' => $parts[1]
                ];
            } elseif (strpos($item, "in brands") !== false) {
                return [
                    'label' => $item,
                    'category' => "General",
                    'originalLabel' => $keyword
                ];
            }
        }, $arr);
            $groupedData = [];
            foreach ($parsedData as $item) {
                $category = $item['category'] ?? 'General';
                if (!isset($groupedData[$category])) {
                    $groupedData[$category] = [];
                }
                $groupedData[$category][] = $item;
            }

            $responseList = [];
            foreach ($groupedData as $category => $items) {
                $responseList = array_merge($responseList, $items);
            }
        return response()->json([
            'status' => true,
            'data' => $groupedData
        ]);
    }

    public function searchDocumentStoreApi($all_filters_res, $store_id = '')
    {
       // dd($all_filters_res,$store_id);
        $all_filters = $all_filters_res;


        $must_filter = array();
        $is_feature = array();
        $must_not = array();
        $filters = array();
        $filter_query = array();
        $location = array();
        $location_q = array();
        $constants = config('constants.shopzit_gamification');

        if (isset($all_filters['keyword']) && $all_filters['keyword'] != '') {
            $must_filter[] = array('multi_match' => array('query' => $all_filters['keyword'], 'fields' => array('tags')));

            //$must_filter[] = array('match' => array('description' => $all_filters['keyword']));
        }

        if (isset($all_filters['condition']) && $all_filters['condition'] != '') {
            $must_filter[] = array('term' => array('condition' => $all_filters['condition']));
        }
        if (isset($all_filters['cashback']) && $all_filters['cashback'] != '') {
            $cashback = $all_filters['cashback'];
            if ($cashback == 'all' || $cashback == '') {
                $cashback = 'defaultt';
            }
            if ($cashback == $constants['CHOOSEN_CASHBACK']) {
                $cashback = 'choosen';
            }
            if ($cashback == $constants['STANDARD_CASHBACK']) {
                $cashback = 'standard';
            }
            $must_filter[] = array('term' => array('flashsale_status' => $cashback));
        }
        if ($store_id != '') {
            $must_filter[] = array('term' => array('store_id' => $store_id));
        }
        if (isset($_GET['brands']) && $_GET['brands'] != '') {
            $brands = getUserHerdsNotNull();
            $must_filter[] = array('terms' => array('store_id' => $brands));
        }

        // if( $storeinfo && $storeinfo->store_id != $store_id){
        //     $must_filter[] = array('term' => array('status' => 1));
        // }else{
            
            $must_filter[] = array('term' => array('status' => 1));
        // }

        // $must_filter[] = array('term' => array('supported' =>'6'));
        /* $filter_query[]['term'] = array(
          'supported' => array(),
          );
         *
         */
        //$must_not[] = array('term' => array('current_stock' => 0));

        if (isset($all_filters['category_child']) && $all_filters['category_child'] != '') {
            $filter_query[]['term'] = array(
                'category_child' => $all_filters['category_child'],
            );
        }
        if (isset($all_filters['category']) && $all_filters['category'] != '') {
            // $childCategories=getChildsCategoriesIds($categoryId);
            // $all_filters['category'] = strtolower($all_filters['category']);
            $filter_query[]['term'] = array(
                'categories.keyword' => $all_filters['category'],
            );
        }

        if (isset($all_filters['min_price']) && $all_filters['min_price'] != '') {
            $filter_query[]['range']['price'] = array('gte' => $all_filters['min_price']);
        }

        if (isset($all_filters['max_price']) && $all_filters['max_price'] != '') {
            $filter_query[]['range']['price'] = array('lte' => $all_filters['max_price']);
        }
        if (isset($all_filters['lat']) && isset($all_filters['lon']) && isset($all_filters['radius'])) {
            $location_q['geo_distance']["distance"] = $_GET["radius"] . "km";
            $location_q['geo_distance']["pin.location"] = ["lat" => $_GET['lat'], "lon" => $_GET['lon']];
            $filter_query[] = $location_q;
        }
        if (isset($_GET['sort_by']) && isset($_GET['sort_order'])) {
            $sort = array(
                $_GET['sort_by'] => $_GET['sort_order'],
            );
        } elseif (isset($_GET['recent'])) {
            $sort = array( 
                'product_id' => 'DESC',
            );
        } else {
            if (isset($all_filters['keywordstore']) && $all_filters['keywordstore'] != '') {
                $sort = array(
                    //'is_feature' => 'DESC',
                    'product_id' => 'asc',
                    //'_score' => 'DESC',
                );
            } else {
                $sort = array(
                   
                    'product_id' => 'asc',
                );
            }
        }
        //$must_filter = array();
        //$must_not=array();
        //$filter_query=array();
       
        $from = isset($_GET['page']) ? $_GET['page'] - 1 : 0;
        $from = $from * 24;
        $params2 = [
            'index' => 'shopzit',
            'type' => 'products',
            'from' => $from,
            'size' => 24,
            'body' => [
                'query' => [
                    'function_score' => [
                        'query' => [
                            'bool' => [
                                'must' => $must_filter,
                                'must_not' => $must_not,
                                'filter' => $filter_query,
                                // $category_query
                            ]
                        ],
                        'functions' => [
                            [
                                'filter' => [
                                    'term' => [
                                        'feature_status' => 1
                                    ]
                                ],
                                'weight' => 2.0
                            ]
                        ],
                        'score_mode' => 'sum'
                    ]
                    ],
                    'sort' => $sort,
            ]
        ];
       
        $response = $this->esClient->search($params2);
        $hits = isset($response['hits']['hits']) ? $response['hits']['hits'] : '';
        $results = array();
        if ($hits != '' && count($hits) > 0) {
            $results['total'] = $response['hits']['total'];
            $results['records'] = $hits;
        }
        return $results;
    }
}
