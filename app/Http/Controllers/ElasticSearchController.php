<?php

namespace App\Http\Controllers;

use DB;
//use App\Repositories\Cart\Cartaccess;
use Redirect;
use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\Discount;
class ElasticSearchController extends Controller
{

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
                //'user' => env('ELASTICSEARCH_USER'),
                //'pass' => env('ELASTICSEARCH_PASS')
            ],
        ];



        $this->esClient = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
        //  echo 'Data: <pre>' .print_r($this->esClient,true). '</pre>';
        //     die();
    }

    public function searchMain()
    {
        return view('esearch.index');
    }

    public function searchGet(Request $request)
    {
        echo $q = $request->input('q');
        
    }

    /*
     * index document
     */

    public function createIndex()
    {
        $params = [
            'index' => 'shopzit',
            'type' => 'products',
            'body' => [
                'settings' => [
                    'analysis' => [                    
                        'filter' => [
                            'autocomplete_filter' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 2,
                                'max_gram' => 10,
                            ],
                        ],
                        'analyzer' => [
                            'autocomplete' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'autocomplete_filter',
                                ],
                            ],
                        ],

                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                        ],
                        'tags' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete',
                        ],
                        // 'pin' => [
                        //     'properties' => [
                        //         'location' => [
                        //             "type" => "geo_point"
                        //         ]
                        //     ]
                        // ],
                        'description' => [
                            'type' => 'text',
                        ],
                        'short_description' => [
                            //  'type' => 'keyword',
                            // 'analyzer' => 'reuters',
                        ],
                        'is_feature' => [
                            'type' => 'integer'
                        ],
                        'feature_status' => [
                            'type' => 'integer'
                        ],
                        'category_child' => [
                            'type' => 'keyword'
                        ],
                        'condition' => [
                            'type' => 'keyword'
                        ],
                        'categories' => [
                            'type' => 'string',
                            'analyzer' => 'not_analyzed',
                        ],
                        'supported' => [
                            'type' => 'integer',
                        ],
                        'achieved' => [
                            'type' => 'integer',
                        ],
                        'category_parent' => [
                            'type' => 'keyword'
                        ],
                        'created_at' => [
                            'type' => 'date',
                            "format" => "yyyy-MM-dd HH:mm:ss",
                        ],
                        'feature_startdate' => [
                            'type' => 'date',
                            "format" => "yyyy-MM-dd HH:mm:ss",
                        ],
                        'feature_enddate' => [
                            'type' => 'date',
                            "format" => "yyyy-MM-dd HH:mm:ss",
                        ],
                        'updated_at' => [
                            'type' => 'date',
                            "format" => "yyyy-MM-dd HH:mm:ss",
                        ],
                        'price' => [
                            'type' => 'float'
                        ],
                        'flashsale_status' => [
                            'type' => 'keyword'
                        ],
                    ],
                ],
            ],
        ];
        $response = $this->esClient->index($params);
        // echo 'Data: <pre>' .print_r($response,true). '</pre>';
    }

    public function esMApping($product_id = '')
    {
        if ($product_id != '') {
            $products = DB::table('products')->select('*')->where('product_id', $product_id)->get()->toArray();
            // $products = DB::table('products')
            // ->select('products.*',  'product_variation.variation_id','product_variation.current_stock as variable_stock' ,'product_variation.attribute1','product_variation.attribute2','product_variation.attribute3',
            // 'product_variation.unit1','product_variation.unit2','product_variation.unit3', 'product_variation.option1','product_variation.option2','product_variation.option3','product_variation.product_variation_image', )
            // ->join('product_variation', 'products.product_id', '=', 'product_variation.product_id')
            // ->where('products.product_id', $product_id)
            // ->get()
            // ->toArray();
        } else {
            $products = DB::table('products')->select('*')->get()->toArray();
            // $products = DB::table('products')
            // ->select('products.*', 'product_variation.variation_id','product_variation.current_stock as variable_stock' ,'product_variation.attribute1','product_variation.attribute2','product_variation.attribute3',
            // 'product_variation.unit1','product_variation.unit2','product_variation.unit3', 'product_variation.option1','product_variation.option2','product_variation.option3','product_variation.product_variation_image', )
            // ->join('product_variation', 'products.product_id', '=', 'product_variation.product_id')
            // // ->where('products.product_id', $product_id)
            // ->get()
            // ->toArray();
        }

        // $arry['index']='shopzit';
        // $arry['type']='products';
        $commonWords = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'able', 'about', 'above', 'abroad', 'according', 'accordingly', 'across', 'actually', 'adj', 'after', 'afterwards', 'again', 'against', 'ago', 'ahead', 'ain\'t', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'alongside', 'already', 'also', 'although', 'always', 'am', 'amid', 'amidst', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'around', 'as', 'a\'s', 'aside', 'ask', 'asking', 'associated', 'at', 'available', 'away', 'awfully', 'b', 'back', 'backward', 'backwards', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'begin', 'behind', 'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'came', 'can', 'cannot', 'cant', 'can\'t', 'caption', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'c\'mon', 'co', 'co.', 'com', 'come', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could', 'couldn\'t', 'course', 'c\'s', 'currently', 'd', 'dare', 'daren\'t', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'different', 'directly', 'do', 'does', 'doesn\'t', 'doing', 'done', 'don\'t', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight', 'eighty', 'either', 'else', 'elsewhere', 'end', 'ending', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'evermore', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'fairly', 'far', 'farther', 'few', 'fewer', 'fifth', 'first', 'five', 'followed', 'following', 'follows', 'for', 'forever', 'former', 'formerly', 'forth', 'forward', 'found', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'half', 'happens', 'hardly', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'hello', 'help', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'here\'s', 'hereupon', 'hers', 'herself', 'he\'s', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully', 'how', 'howbeit', 'however', 'hundred', 'i', 'i\'d', 'ie', 'if', 'ignored', 'i\'ll', 'i\'m', 'immediate', 'in', 'inasmuch', 'inc', 'inc.', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'inside', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t', 'it', 'it\'d', 'it\'ll', 'its', 'it\'s', 'itself', 'i\'ve', 'j', 'just', 'k', 'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'like', 'liked', 'likely', 'likewise', 'little', 'look', 'looking', 'looks', 'low', 'lower', 'ltd', 'm', 'made', 'mainly', 'make', 'makes', 'many', 'may', 'maybe', 'mayn\'t', 'me', 'mean', 'meantime', 'meanwhile', 'merely', 'might', 'mightn\'t', 'mine', 'minus', 'miss', 'more', 'moreover', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'mustn\'t', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need', 'needn\'t', 'needs', 'neither', 'never', 'neverf', 'neverless', 'nevertheless', 'next', 'nine', 'ninety', 'no', 'nobody', 'non', 'none', 'nonetheless', 'noone', 'no-one', 'nor', 'normally', 'not', 'nothing', 'notwithstanding', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'one\'s', 'only', 'onto', 'opposite', 'or', 'other', 'others', 'otherwise', 'ought', 'oughtn\'t', 'our', 'ours', 'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'p', 'particular', 'particularly', 'past', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'presumably', 'probably', 'provided', 'provides', 'q', 'que', 'quite', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'recent', 'recently', 'regarding', 'regardless', 'regards', 'relatively', 'respectively', 'right', 'round', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously', 'seven', 'several', 'shall', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 'since', 'six', 'so', 'some', 'somebody', 'someday', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup', 'sure', 't', 'take', 'taken', 'taking', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'ll', 'thats', 'that\'s', 'that\'ve', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'there\'d', 'therefore', 'therein', 'there\'ll', 'there\'re', 'theres', 'there\'s', 'thereupon', 'there\'ve', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'thing', 'things', 'think', 'third', 'thirty', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'till', 'to', 'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 't\'s', 'twice', 'two', 'u', 'un', 'under', 'underneath', 'undoing', 'unfortunately', 'unless', 'unlike', 'unlikely', 'until', 'unto', 'up', 'upon', 'upwards', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v', 'value', 'various', 'versus', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we', 'we\'d', 'welcome', 'well', 'we\'ll', 'went', 'were', 'we\'re', 'weren\'t', 'we\'ve', 'what', 'whatever', 'what\'ll', 'what\'s', 'what\'ve', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'where\'s', 'whereupon', 'wherever', 'whether', 'which', 'whichever', 'while', 'whilst', 'whither', 'who', 'who\'d', 'whoever', 'whole', 'who\'ll', 'whom', 'whomever', 'who\'s', 'whose', 'why', 'will', 'willing', 'wish', 'with', 'within', 'without', 'wonder', 'won\'t', 'would', 'wouldn\'t', 'x', 'y', 'yes', 'yet', 'you', 'you\'d', 'you\'ll', 'your', 'you\'re', 'yours', 'yourself', 'yourselves', 'you\'ve', 'z', 'zero');
        foreach ($products as $key => $val) {
            $arry = array();
            foreach ($val as $key2 => $val2) {
                if ($key2 == 'title') {
                    $tags_title = explode(' ', $val2);
                }

                $arry[$key2] = $val2;
            }
            $product_tags = getTagsByProductId($val->product_id);
            $total_tags = array_merge($tags_title, $product_tags);
            $total_tags = array_unique($total_tags);
            //$total_tags = array_map('strtolower', $total_tags);
            foreach ($total_tags as $key3 => $value3) {
                if (in_array($value3, $commonWords)) {
                    unset($total_tags[$key3]);
                }
            }
            $total_tags = array_filter($total_tags);

            $total_tags = implode(",", $total_tags);
            $total_tags = strtolower($total_tags);
            $total_tags = preg_replace('/[^A-Za-z0-9 ,\-]/', '', $total_tags);
            $arry['tags'] = $total_tags;
            $geo = array();

            //$lat = $val->product_lat;
            //$lon = $val->product_long;
            $lat = '';
            $lon = '';
            if ($lat == '') {
                $lat = 31.470966;
            }
            if ($lon == '') {
                $lon = 74.314419;
            }
            $lat_long = array('lat' => $lat, 'lon' => $lon);
            $geo = array('location' => $lat_long);
            $categories = getCategoriesByProductID($val->product_id);

            $cats = array();
            if (count($categories) > 0) {
                foreach ($categories as $cat) {
                    $cats[] = getCategorySlugById($cat);
                }
            }
            $supported = array(1, 2, 3, 4, 5, 6);
            $arry['categories'] = $cats;
            $arry['pin'] = $geo;
            $arry['supported'] = $supported;

            $params = [
                'index' => 'shopzit',
                'type' => 'products',
                'id' => $val->product_id,
                'body' => $arry
            ];
            $response = $this->esClient->index($params);
            // echo 'Data: <pre>' .print_r($params,true). '</pre>';
        }
    }

    /*
     * Get document
     */

    public function getDocument($documentId = 13)
    {
        $params = [
            'index' => 'shopzit',
            'source' => [
                'query' => [
                    'match_all' => (object)[] // Retrieves all documents
                ]
            ]
        ];
        
        $response = $this->esClient->search($params);
        print_r($response);
    }

    /*
     * search document
     */

    public function searchDocument($all_filters_res,$slug)
    {
       
        $all_filters = $all_filters_res;
        $must_filter = array();
        $is_feature = array();
        $must_not = array();
        $filters = array();
        $filter_query = array();
        $location = array();
        $location_q = array();
        $constants = config('constants.shopzit_gamification');
        if(isset($slug) && $slug != '') {
            if($slug == 'welcome-deal'){
                if(isset($all_filters['category']) && $all_filters['category'] != ''){
                    $filter_query[]['term'] = array(
                        'categories.keyword' => $all_filters['category'],
                    );
                }
                $must_filter[] = [
                    'range' => [
                        'discount_percentage' => [
                            'lte' => '20' // less than or equal to 20%
                        ]
                    ]
                ];
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
            }elseif($slug == 'best-sellers'){
                if(isset($all_filters['category']) && $all_filters['category'] != ''){
                    $filter_query[]['term'] = array(
                        'categories.keyword' => $all_filters['category'],
                    );
                }
                $must_filter[] = [
                    'range' => [
                        'discount_percentage' => [
                            'lte' => '50' // less than or equal to 20%
                        ]
                    ]
                ];
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
            }elseif($slug == 'christmas-deal'){

                $cutoffDate = Carbon::create(2024, 12, 25);

                $discountIds = Discount::where('type', 'sale')
                    ->where('status', 'active')
                    ->where('state', 'live')
                    ->whereDate('end_time', '=', $cutoffDate)
                    ->pluck('id');
                // dd($discountIds);
                if(isset($all_filters['category']) && $all_filters['category'] != ''){
                    $filter_query[]['term'] = array(
                        'categories.keyword' => $all_filters['category'],
                    );
                }
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
                $must_filter[] = [
                    'terms' => [
                        'discount_id' => $discountIds // Match specific discount_id values
                    ]
                ];
            }else{
                $filter_query[]['term'] = array(
                    'categories.keyword' => $slug,
                );
            }
            
        }else{
            if (isset($all_filters['category']) && $all_filters['category'] != '') {
                $filter_query[]['term'] = array(
                    'categories.keyword' => $all_filters['category'],
                );
            }
        }
        if (isset($all_filters['keyword']) && $all_filters['keyword'] != '') {
            $must_filter[] = array('multi_match' => array('query' => $all_filters['keyword'],
            'fields' => [
                'title^4', // highest priority
                'tags^3', 
                'short_description^2', 
                'description' // lowest priority
            ]));
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
        if (isset($_GET['brands']) && $_GET['brands'] != '') {
            $brands = getUserHerdsNotNull();
            $must_filter[] = array('terms' => array('store_id' => $brands));
        }
        $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']: "";
        $storeinfo = getStorDataUserId($userId);
            $must_filter[] = array('term' => array('status' => 1));

        if (isset($all_filters['category_child']) && $all_filters['category_child'] != '') {
            $filter_query[]['term'] = array(
                'category_child' => $all_filters['category_child'],
            );
        }
       
        
      
        if (isset($all_filters['min_price']) && $all_filters['min_price'] != '') {
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['gte' => $all_filters['min_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['gte' => $all_filters['min_price']]]]]]]
                    ]
                ]
            ];
        }
        if (isset($all_filters['max_price']) && $all_filters['max_price'] != '') {
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['lte' => $all_filters['max_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['lte' => $all_filters['max_price']]]]]]]
                    ]
                ]
            ];
        }
        
        if (isset($all_filters['lat']) && isset($all_filters['lon']) && isset($all_filters['radius'])) {
            $location_q['geo_distance']["distance"] = $_GET["radius"] . "km";
            $location_q['geo_distance']["pin.location"] = ["lat" => $_GET['lat'], "lon" => $_GET['lon']];
            $filter_query[] = $location_q;
        }
        if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price' && isset($_GET['sort_order'])) {
            $sort = [
                '_script' => [
                    'type' => 'number',
                    'script' => [
                        'lang' => 'painless',
                        'source' => "
                            double salePrice = doc.containsKey('sale_price') && doc['sale_price'].size() > 0 ? doc['sale_price'].value : Double.MAX_VALUE;
                            double price = doc.containsKey('price') && doc['price'].size() > 0 ? doc['price'].value : Double.MAX_VALUE;
        
                            boolean onSale = doc.containsKey('on_sale') && doc['on_sale'].size() > 0 && doc['on_sale'].value == 1;
        
                            if (onSale) {
                                return salePrice;
                            } else {
                                return price;
                            }
                        "
                    ],
                    'order' => $_GET['sort_order']  // 'asc' or 'desc'
                ]
            ];
        }elseif(isset($_GET['sort_by']) && $_GET['sort_by'] == 'product_id' && isset($_GET['sort_order'])){
            if($_GET['sort_order'] == 'DESC'){
                $sort = array( 
                    'product_id' => 'DESC',
                );
            }else{
                $sort = array( 
                    'product_id' => 'ASC',
                );
            }
            
        } elseif (isset($_GET['recent'])) {
            $sort = array( 
                'product_id' => 'DESC',
            );
        } else {
                $sort = array(
                   
                    'product_id' => 'DESC',
                );
        }
        $from = isset($_GET['page']) ? $_GET['page'] - 1 : 0;
        $from = $from * 32;
        $params2 = [
            'index' => 'shopzit',
            'type' => 'products',
            'from' => $from,
            'size' => 32,
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
       
       
        

        // $response = $this->esClient->search($params);
       
        $response = $this->esClient->search($params2);
       
        $hits = isset($response['hits']['hits']) ? $response['hits']['hits'] : '';
        $results = array();
        if ($hits != '' && count($hits) > 0) {
            $results['total'] = $response['hits']['total'];
            $results['records'] = $hits;
        }
        
        return $results;
    }
    public function searchFilterDocument($all_filters_res,$slug)
    {
        
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
            $must_filter[] = array('multi_match' => array('query' => $all_filters['keyword'],
            'fields' => [
                'title^4', // highest priority
                'tags^3', 
                'short_description^2', 
                'description' // lowest priority
            ],
              "analyzer" => "standard"));
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
        if (isset($_GET['brands']) && $_GET['brands'] != '') {
            $brands = getUserHerdsNotNull();
            $must_filter[] = array('terms' => array('store_id' => $brands));
        }
        $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']: "";
        $storeinfo = getStorDataUserId($userId);
            $must_filter[] = array('term' => array('status' => 1));

        if (isset($all_filters['category_child']) && $all_filters['category_child'] != '') {
            $filter_query[]['term'] = array(
                'category_child' => $all_filters['category_child'],
            );
        }
       
        if(isset($slug) && $slug != '') {
            if($slug == 'welcome-deal'){
                if(isset($all_filters['category']) && $all_filters['category'] != ''){
                    $filter_query[]['term'] = array(
                        'categories.keyword' => $all_filters['category'],
                    );
                }
                $must_filter[] = [
                    'range' => [
                        'discount_percentage' => [
                            'lte' => '20' // less than or equal to 20%
                        ]
                    ]
                ];
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
            }elseif($slug == 'best-sellers'){
                if(isset($all_filters['category']) && $all_filters['category'] != ''){
                    $filter_query[]['term'] = array(
                        'categories.keyword' => $all_filters['category'],
                    );
                }
                $must_filter[] = [
                    'range' => [
                        'discount_percentage' => [
                            'lte' => '50' // less than or equal to 20%
                        ]
                    ]
                ];
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
            }elseif($slug == 'christmas-deal'){
                $cutoffDate = Carbon::create(2024, 12, 25);

                $discountIds = Discount::where('type', 'sale')
                    ->where('status', 'active')
                    ->where('state', 'live')
                    ->whereDate('end_time', '=', $cutoffDate)
                    ->pluck('id');
                if(isset($all_filters['category']) && $all_filters['category'] != ''){
                    $filter_query[]['term'] = array(
                        'categories.keyword' => $all_filters['category'],
                    );
                }
                $must_filter[] = [
                    'range' => [
                        'discount_percentage' => [
                            'lte' => '20' // less than or equal to 20%
                        ]
                    ]
                ];
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
                $must_filter[] = [
                    'terms' => [
                        'discount_id' => $discountIds // Match specific discount_id values
                    ]
                ];
            }else{
                $filter_query[]['term'] = array(
                    'categories.keyword' => $slug,
                );
            }
            
        }else{
             if (isset($all_filters['category']) && $all_filters['category'] != '') {
            $filter_query[]['term'] = array(
                'categories.keyword' => $all_filters['category'],
            );
        }
        }
        if (isset($all_filters['min_price']) && $all_filters['min_price'] != '') {
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['gte' => $all_filters['min_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['gte' => $all_filters['min_price']]]]]]]
                    ]
                ]
            ];
        }
        if (isset($all_filters['max_price']) && $all_filters['max_price'] != '') {
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['lte' => $all_filters['max_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['lte' => $all_filters['max_price']]]]]]]
                    ]
                ]
            ];
        }
        if (isset($all_filters['deal']) && $all_filters['deal'] != '') {
            $must_filter[] = [
                'term' => [
                    'discount_id' => $all_filters['deal']
                ]
            ];
            $must_filter[] = [
                'term' => [
                    'on_sale' => 1 // Ensures that the product is on sale
                ]
            ];
        }
        if (isset($all_filters['lat']) && isset($all_filters['lon']) && isset($all_filters['radius'])) {
            $location_q['geo_distance']["distance"] = $_GET["radius"] . "km";
            $location_q['geo_distance']["pin.location"] = ["lat" => $_GET['lat'], "lon" => $_GET['lon']];
            $filter_query[] = $location_q;
        }
        if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price' && isset($_GET['sort_order'])) {
            $sort = [
                '_script' => [
                    'type' => 'number',
                    'script' => [
                        'lang' => 'painless',
                        'source' => "
                            double salePrice = doc.containsKey('sale_price') && doc['sale_price'].size() > 0 ? doc['sale_price'].value : Double.MAX_VALUE;
                            double price = doc.containsKey('price') && doc['price'].size() > 0 ? doc['price'].value : Double.MAX_VALUE;
        
                            boolean onSale = doc.containsKey('on_sale') && doc['on_sale'].size() > 0 && doc['on_sale'].value == 1;
        
                            if (onSale) {
                                return salePrice;
                            } else {
                                return price;
                            }
                        "
                    ],
                    'order' => $_GET['sort_order']  // 'asc' or 'desc'
                ]
            ];
        }elseif(isset($_GET['sort_by']) && $_GET['sort_by'] == 'product_id' && isset($_GET['sort_order'])){
            if($_GET['sort_order'] == 'DESC'){
                $sort = array( 
                    'product_id' => 'DESC',
                );
            }else{
                $sort = array( 
                    'product_id' => 'ASC',
                );
            }
            
        } elseif (isset($_GET['recent'])) {
            $sort = array( 
                'product_id' => 'DESC',
            );
        } else {
            if (isset($all_filters['keyword']) && $all_filters['keyword'] != '') {
                $sort = array(
                    //'is_feature' => 'DESC',
                    // 'product_id' => 'asc',
                    //'_score' => 'DESC',
                );
            } else {
                $sort = array(
                   
                    'product_id' => 'DESC',
                );
            }
        }
        
        //$must_filter = array();
        //$must_not=array();
        //$filter_query=array();
      
        $params2 = [
            'index' => 'shopzit',
            'type' => 'products',
            'body' => [
                'query' => [
                    'function_score' => [
                        'query' => [
                            'bool' => [
                                'must' => $must_filter,
                                'must_not' => $must_not,
                                'filter' => $filter_query,
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
            ],
            'size' => 1000 // Set a high size value to fetch up to 10,000 results
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
    /*
     * search document Store
     */

     public function searchDocumentStore($all_filters_res, $store_id = '')
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
         $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']: "";
         $storeinfo = getStorDataUserId($userId);
 
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
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['gte' => $all_filters['min_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['gte' => $all_filters['min_price']]]]]]]
                    ]
                ]
            ];
        }
        if (isset($all_filters['max_price']) && $all_filters['max_price'] != '') {
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['lte' => $all_filters['max_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['lte' => $all_filters['max_price']]]]]]]
                    ]
                ]
            ];
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
       
         $params = [
             'index' => 'shopzit',
             'type' => 'products',
             'from' => $from,
             'size' => 21,
             'body' => [
                 'query' => [
                     'bool' => [
                         'must' => $must_filter,
                         'must_not' => $must_not,
                         'filter' => $filter_query,
                         // $category_query
                     ]      
                 ],
                  'sort' => $sort,
             ]
         ];
 
         //echo '$params: <pre>' .print_r($sort,true). '</pre>';
         // $response = $this->esClient->search($params);
        
         $response = $this->esClient->search($params2);
         $hits = isset($response['hits']['hits']) ? $response['hits']['hits'] : '';
         $results = array();
         if ($hits != '' && count($hits) > 0) {
             $results['total'] = $response['hits']['total'];
             $results['records'] = $hits;
         }
         return $results;
     }
     public function searchFilterDocumentStore($all_filters_res, $store_id = '')
     {
         
         $all_filters = $all_filters_res;
 
        
         $must_filter = array();
         $is_feature = array();
         $must_not = array();
         $filters = array();
         $filter_query = array();
         $location = array();
         $location_q = array();
         $constants = config('constants.shopzit_gamification');
 
         if (isset($all_filters['keywordstore']) && $all_filters['keywordstore'] != '') {
             $must_filter[] = array('multi_match' => array('query' => $all_filters['keywordstore'], 'fields' => array('tags'), "analyzer" => "standard"));
 
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
         $userId = !empty(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']: "";
         $storeinfo = getStorDataUserId($userId);
 
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
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['gte' => $all_filters['min_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['gte' => $all_filters['min_price']]]]]]]
                    ]
                ]
            ];
        }
        if (isset($all_filters['max_price']) && $all_filters['max_price'] != '') {
            $filter_query[] = [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [['term' => ['on_sale' => 1]], ['range' => ['sale_price' => ['lte' => $all_filters['max_price']]]]]]],
                        ['bool' => ['must' => [['term' => ['on_sale' => 0]], ['range' => ['price' => ['lte' => $all_filters['max_price']]]]]]]
                    ]
                ]
            ];
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
         $params2 = [
             'index' => 'shopzit',
             'type' => 'products',
             'size' => 1000,
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
       
         $params = [
             'index' => 'shopzit',
             'type' => 'products',
             'from' => $from,
             'size' => 21,
             'body' => [
                 'query' => [
                     'bool' => [
                         'must' => $must_filter,
                         'must_not' => $must_not,
                         'filter' => $filter_query,
                         // $category_query
                     ]      
                 ],
                  'sort' => $sort,
             ]
         ];
 
         //echo '$params: <pre>' .print_r($sort,true). '</pre>';
         // $response = $this->esClient->search($params);
        
         $response = $this->esClient->search($params2);
         $hits = isset($response['hits']['hits']) ? $response['hits']['hits'] : '';
         $results = array();
         if ($hits != '' && count($hits) > 0) {
             $results['total'] = $response['hits']['total'];
             $results['records'] = $hits;
         }
         return $results;
     }

    /*
     * update document
     */

    public function updateDocument($product_id)
    {
        if ($product_id != '') {
            $this->deleteDocument($product_id);
            
            $this->esMApping($product_id);
        }
    }
    /*
     * Update Product Document
     */

    public function updateProductDocument($product_id)
    {
        if ($product_id != '') {
            $this->deleteDocument($product_id);
            $this->esMApping($product_id);
        }
    }

    public function updateDocumentOld($product_id)
    {
        if ($product_id != '') {
            $products = DB::table('products')->select('*')->where('product_id', $product_id)->get()->toArray();
            //$products = DB::table('products')->select('*')->get()->toArray();
            // $arry['index']='shopzit';
            // $arry['type']='products';
            $commonWords = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'able', 'about', 'above', 'abroad', 'according', 'accordingly', 'across', 'actually', 'adj', 'after', 'afterwards', 'again', 'against', 'ago', 'ahead', 'ain\'t', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'alongside', 'already', 'also', 'although', 'always', 'am', 'amid', 'amidst', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'around', 'as', 'a\'s', 'aside', 'ask', 'asking', 'associated', 'at', 'available', 'away', 'awfully', 'b', 'back', 'backward', 'backwards', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'begin', 'behind', 'being', 'believe', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'came', 'can', 'cannot', 'cant', 'can\'t', 'caption', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'c\'mon', 'co', 'co.', 'com', 'come', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could', 'couldn\'t', 'course', 'c\'s', 'currently', 'd', 'dare', 'daren\'t', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'different', 'directly', 'do', 'does', 'doesn\'t', 'doing', 'done', 'don\'t', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight', 'eighty', 'either', 'else', 'elsewhere', 'end', 'ending', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'evermore', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'fairly', 'far', 'farther', 'few', 'fewer', 'fifth', 'first', 'five', 'followed', 'following', 'follows', 'for', 'forever', 'former', 'formerly', 'forth', 'forward', 'found', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'half', 'happens', 'hardly', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'hello', 'help', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'here\'s', 'hereupon', 'hers', 'herself', 'he\'s', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully', 'how', 'howbeit', 'however', 'hundred', 'i', 'i\'d', 'ie', 'if', 'ignored', 'i\'ll', 'i\'m', 'immediate', 'in', 'inasmuch', 'inc', 'inc.', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'inside', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t', 'it', 'it\'d', 'it\'ll', 'its', 'it\'s', 'itself', 'i\'ve', 'j', 'just', 'k', 'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'like', 'liked', 'likely', 'likewise', 'little', 'look', 'looking', 'looks', 'low', 'lower', 'ltd', 'm', 'made', 'mainly', 'make', 'makes', 'many', 'may', 'maybe', 'mayn\'t', 'me', 'mean', 'meantime', 'meanwhile', 'merely', 'might', 'mightn\'t', 'mine', 'minus', 'miss', 'more', 'moreover', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'mustn\'t', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need', 'needn\'t', 'needs', 'neither', 'never', 'neverf', 'neverless', 'nevertheless', 'next', 'nine', 'ninety', 'no', 'nobody', 'non', 'none', 'nonetheless', 'noone', 'no-one', 'nor', 'normally', 'not', 'nothing', 'notwithstanding', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'one\'s', 'only', 'onto', 'opposite', 'or', 'other', 'others', 'otherwise', 'ought', 'oughtn\'t', 'our', 'ours', 'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'p', 'particular', 'particularly', 'past', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'presumably', 'probably', 'provided', 'provides', 'q', 'que', 'quite', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'recent', 'recently', 'regarding', 'regardless', 'regards', 'relatively', 'respectively', 'right', 'round', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously', 'seven', 'several', 'shall', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 'since', 'six', 'so', 'some', 'somebody', 'someday', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup', 'sure', 't', 'take', 'taken', 'taking', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'ll', 'thats', 'that\'s', 'that\'ve', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'there\'d', 'therefore', 'therein', 'there\'ll', 'there\'re', 'theres', 'there\'s', 'thereupon', 'there\'ve', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'thing', 'things', 'think', 'third', 'thirty', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'till', 'to', 'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 't\'s', 'twice', 'two', 'u', 'un', 'under', 'underneath', 'undoing', 'unfortunately', 'unless', 'unlike', 'unlikely', 'until', 'unto', 'up', 'upon', 'upwards', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v', 'value', 'various', 'versus', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we', 'we\'d', 'welcome', 'well', 'we\'ll', 'went', 'were', 'we\'re', 'weren\'t', 'we\'ve', 'what', 'whatever', 'what\'ll', 'what\'s', 'what\'ve', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'where\'s', 'whereupon', 'wherever', 'whether', 'which', 'whichever', 'while', 'whilst', 'whither', 'who', 'who\'d', 'whoever', 'whole', 'who\'ll', 'whom', 'whomever', 'who\'s', 'whose', 'why', 'will', 'willing', 'wish', 'with', 'within', 'without', 'wonder', 'won\'t', 'would', 'wouldn\'t', 'x', 'y', 'yes', 'yet', 'you', 'you\'d', 'you\'ll', 'your', 'you\'re', 'yours', 'yourself', 'yourselves', 'you\'ve', 'z', 'zero');
            /*
              foreach ($products as $key => $val) {
              $arry = array();
              foreach ($val as $key2 => $val2) {
              if ($key2 == 'title') {
              $arry['tags'] = explode(' ', $val2);
              }
              if ($key2 == 'product_condition') {
              if ($val2 == '') {
              $val2 = 'new';
              }
              $key2 = 'condition';
              }
              $arry[$key2] = $val2;
              }

              $geo = array();

              //$lat = $val->product_lat;
              //$lon = $val->product_long;
              $lat = '';
              $lon = '';
              if ($lat == '') {
              $lat = 31.470966;
              }
              if ($lon == '') {
              $lon = 74.314419;
              }
              $lat_long = array('lat' => $lat, 'lon' => $lon);
              $geo = array('location' => $lat_long);
              $categories = getCategoriesByProductID($val->product_id);
              $cats = array();
              if (count($categories) > 0) {
              foreach ($categories as $cat) {
              $cats[] = getCategorySlugById($cat);
              }
              }

              $arry['categories'] = $cats;
              $arry['pin'] = $geo;
              $params = [
              'index' => 'shopzit',
              'type' => 'products',
              'id' => $val->product_id,
              'body' => $arry
              ];
              $this->deleteDocument($val->product_id);
              $response = $this->esClient->index($params);
              }
             */
            foreach ($products as $key => $val) {
                $arry = array();
                foreach ($val as $key2 => $val2) {
                    if ($key2 == 'title') {
                        $tags_title = explode(' ', $val2);
                    }
                    $arry[$key2] = $val2;
                }
                $product_tags = getTagsByProductId($val->product_id);
                $total_tags = array_merge($tags_title, $product_tags);
                $total_tags = array_unique($total_tags);
                foreach ($total_tags as $key3 => $value3) {
                    if (in_array($value3, $commonWords)) {
                        unset($total_tags[$key3]);
                    }
                }
                $total_tags = array_filter($total_tags);

                $total_tags = implode(",", $total_tags);
                $arry['tags'] = $total_tags;

                $geo = array();

                //$lat = $val->product_lat;
                //$lon = $val->product_long;
                $lat = '';
                $lon = '';
                if ($lat == '') {
                    $lat = 31.470966;
                }
                if ($lon == '') {
                    $lon = 74.314419;
                }
                $lat_long = array('lat' => $lat, 'lon' => $lon);
                $geo = array('location' => $lat_long);
                $categories = getCategoriesByProductID($val->product_id);

                $cats = array();
                if (count($categories) > 0) {
                    foreach ($categories as $cat) {
                        $cats[] = getCategorySlugById($cat);
                    }
                }
                $supported = array(1, 2, 3, 4, 5, 6);
                $arry['categories'] = $cats;
                $arry['pin'] = $geo;
                $arry['supported'] = $supported;

                $params = [
                    'index' => 'shopzit',
                    'type' => 'products',
                    'id' => $val->product_id,
                    'body' => $arry
                ];
                $this->deleteDocument($val->product_id);
                $response = $this->esClient->index($params);
            }
        } else {
            return 'no product found to update';
        }
    }

    public function updateDocumentStore($store_id)
    {
        if ($store_id != '') {
            $products = DB::table('products')->select('*')->where('store_id', $store_id)->get()->toArray();
            foreach ($products as $key => $val) {
                $productId = $val->product_id;
                $this->deleteDocument($productId);
                $this->esMApping($productId);
            }
        }
    }

    /*
      public function updateDocumentStoreOld($store_id) {
      if ($store_id != '') {
      $products = DB::table('products')->select('*')->where('store_id', $store_id)->get()->toArray();
      //$products = DB::table('products')->select('*')->get()->toArray();
      // $arry['index']='shopzit';
      // $arry['type']='products';

      foreach ($products as $key => $val) {
      $arry = array();
      foreach ($val as $key2 => $val2) {
      if ($key2 == 'title') {

      $tags_title = explode(' ', $val2);
      }

      $arry[$key2] = $val2;
      }
      $product_tags = getTagsByProductId($val->product_id);
      $total_tags = array_merge($tags_title, $product_tags);
      $total_tags = array_unique($total_tags);
      foreach ($total_tags as $key3 => $value3) {
      if (in_array($value3, $commonWords)) {
      unset($total_tags[$key3]);
      }
      }
      $total_tags = array_filter($total_tags);

      $total_tags = implode(",", $total_tags);

      $arry['tags'] = $total_tags;

      $geo = array();

      //$lat = $val->product_lat;
      //$lon = $val->product_long;
      $lat = '';
      $lon = '';
      if ($lat == '') {
      $lat = 31.470966;
      }
      if ($lon == '') {
      $lon = 74.314419;
      }
      $lat_long = array('lat' => $lat, 'lon' => $lon);
      $geo = array('location' => $lat_long);
      $categories = getCategoriesByProductID($val->product_id);

      $cats = array();
      if (count($categories) > 0) {
      foreach ($categories as $cat) {
      $cats[] = getCategorySlugById($cat);
      }
      }
      $supported = array(1, 2, 3, 4, 5, 6);
      $arry['categories'] = $cats;
      $arry['pin'] = $geo;
      $arry['supported'] = $supported;

      $params = [
      'index' => 'shopzit',
      'type' => 'products',
      'id' => $val->product_id,
      'body' => $arry
      ];
      $this->deleteDocument($val->product_id);
      $response = $this->esClient->index($params);
      }
      } else {
      return 'no product found to update';
      }
      }
     */
    /*
     * delete document
     */

    public function deleteDocument($product_id = '')
    {
        if ($product_id != '') {
            $params = [
                'index' => 'shopzit',
                'type' => 'products',
                'id' => $product_id,
            ];
            $response = $this->esClient->delete($params);
        } else {
            return 'no product found to delete';
        }
    }

    public function deleteIndex()
    {
        $deleteParams = array();
        $deleteParams['index'] = 'shopzit';
        $this->esClient->indices()->delete($deleteParams);
    }

    public function keywordsearch(Request $request)
    {
        $filter_query = [];
        $must_filter = array();
        if ($request->has('deal') && $request->filled('deal')) {
            if ($request->input('deal') == 'welcome-deal') {
                $must_filter[] = [
                    'range' => [
                        'discount_percentage' => [
                            'lte' => '20' // less than or equal to 20%
                        ]
                    ]
                ];
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
            } elseif ($request->input('deal') == 'best-sellers') {
                $must_filter[] = [
                    'range' => [
                        'discount_percentage' => [
                            'lte' => '50' // less than or equal to 50%
                        ]
                    ]
                ];
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
            } elseif ($request->input('deal') == 'christmas-deal') {
                $must_filter[] = [
                    'term' => [
                        'on_sale' => 1 // Ensures that the product is on sale
                    ]
                ];
            }
         }
        
        
        

        if($request->has('category') && $request->filled('category')){
            $filter_query[]['term'] = array(
                'categories.keyword' => $request->input('category'),
            );
        }
      
        $arr = array();
       
        $keyword = $request->input('keyword');
        $isBrandSearch = strpos($keyword, '@') !== false; 
    
        if ($keyword) {
            $keyword = str_replace('@', '', $keyword); 
            if ($isBrandSearch) {
                // Brand-specific search
                $arr[] = 'find "' . $keyword . '" in brands';
            } else {
                // Product search
                $must_filter[] = [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => [
                            'title^4',
                            'tags^3',
                            'short_description^2',
                            'description',
                        ],
                        'fuzziness' => '2',
                        'prefix_length' => 1
                    ]
                ];
            }
        }
    
        // Add status filter
        $must_filter[] = ['term' => ['status' => 1]];
    
        // Elasticsearch query for products
        if (!$isBrandSearch) {
            $params = [
                'index' => 'shopzit',
                'type' => 'products',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => $must_filter,
                            'filter' => $filter_query
                        ]
                    ]
                ]
            ];
    
            $response = $this->esClient->search($params);
            if (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
                foreach ($response['hits']['hits'] as $val) {
                    $title = $val['_source']['title'];
                    $category_name = isset($val['_source']['categories'][0]) ? $val['_source']['categories'][0] : '';
                    $category_name = isset($val['_source']['categories'][1]) ? $val['_source']['categories'][1] : $category_name;
    
                    $comp_name = $title . ' in category ' . $category_name;
                    if (!in_array($comp_name, $arr)) {
                        $arr[] = $comp_name;
                    }
                }
            }
        }
    
        // Parsing results
        $parsedData = array_map(function ($item) use ($keyword, $isBrandSearch) {
            if (strpos($item, "in category") !== false && !$isBrandSearch) {
                // Handle product results
                $parts = explode(" in category ", $item);
                $categoryParts = explode("-", $parts[1]);
                $capitalizedCategory = array_map(function ($word) {
                    return ucfirst($word);
                }, $categoryParts);
                return [
                    'label' => $parts[0],
                    'category' => implode(" ", $capitalizedCategory),
                    'originalCategory' => $parts[1]
                ];
            } elseif (strpos($item, "in brands") !== false) {
                // Handle brand results
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
    
        return $responseList;
    }

    // public function keywordsearchStore(Request $request)
    // {
    //     $keyword = $request->input('keyword');
    //     $brandanme = $request->input('brand_id');
    //     $arr = array();
    //     $must_filter = array();
    //     $must_filter[] = array('multi_match' => array('query' => $keyword, 'fields' => array('tags'), "analyzer" => "standard"));

    //     $must_filter[] = array('term' => array('status' => 1));
    //     $params = [
    //         'index' => 'shopzit',
    //         'type' => 'products',
    //         'body' => [
    //             'query' => [
    //                 'bool' => [
    //                     'must' =>
    //                     $must_filter,
    //                 ]
    //             ]
    //         ]
    //     ];

    //     $response = $this->esClient->search($params);
    //     if (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
    //         $tg_name = '';
    //         foreach ($response['hits']['hits'] as $key => $val) {

    //             $tags = $val['_source']['tags'];
    //             $tags = explode(",", $tags);

    //             if (count($tags) > 0) {
    //                 foreach ($tags as $tg) {
    //                     similar_text($tg, $keyword, $percent);
    //                     if ($percent > 95) {
    //                         $tg_name = $tg;
    //                         break;
    //                     }
    //                 }
    //                 if ($tg_name == '') {
    //                     foreach ($tags as $tg) {
    //                         similar_text($tg, $keyword, $percent);
    //                         if ($percent > 80) {
    //                             $tg_name = $tg;
    //                             break;
    //                         }
    //                     }
    //                 }
    //                 if ($tg_name == '') {
    //                     foreach ($tags as $tg) {
    //                         similar_text($tg, $keyword, $percent);
    //                         if ($percent > 65) {
    //                             $tg_name = $tg;
    //                             break;
    //                         }
    //                     }
    //                 }
    //                 if ($tg_name == '') {
    //                     foreach ($tags as $tg) {
    //                         similar_text($tg, $keyword, $percent);
    //                         if ($percent > 50) {
    //                             $tg_name = $tg;
    //                             break;
    //                         }
    //                     }
    //                 }
    //                 if ($tg_name == '') {
    //                     foreach ($tags as $tg) {
    //                         similar_text($tg, $keyword, $percent);
    //                         if ($percent > 10) {
    //                             $tg_name = $tg;
    //                             break;
    //                         }
    //                     }
    //                 }
    //                 $category_name = isset($val['_source']['categories'][0]) ? $val['_source']['categories'][0] : '';
    //                 $category_name = isset($val['_source']['categories'][1]) ? $val['_source']['categories'][1] : $category_name;
    //                 // $comp_name = $tg_name . ' in category ' . $category_name;
    //                 // if (!in_array($comp_name, $arr)) {
    //                 //     $arr[] = $comp_name;
    //                 // }
    //             }
    //         }
    //     }
    //     $arr[] = 'find "' . $keyword . '" in '.$brandanme;
    //     return $arr;
    // }

    public function keywordsearchStore(Request $request)
{
    $keyword = $request->input('keyword');
    $category = $request->input('category');
    $brandId = $request->input('brand_id');
    $arr = array();
    $must_filter = [];

    // Add keyword filter
    if ($keyword) {
        $must_filter[] = [
           'multi_match' => [
                        'query' => $keyword,
                        'fields' => [
                            'title^4',
                            'tags^3',
                            'short_description^2',
                            'description',
                        ],
                        'fuzziness' => '2',
                        'prefix_length' => 1
                    ]
        ];
    }

    // Add status filter
    $must_filter[] = ['term' => ['status' => 1]];

    // Add category filter if provided
    if ($category) {
        $must_filter[] = ['term' => ['categories.keyword' => $category]];
    }

    // Add brand filter if provided
    if ($brandId) {
        $must_filter[] = ['term' => ['store_id' => $brandId]];
    }

    $params = [
        'index' => 'shopzit',
        'type' => 'products',
        'body' => [
            'query' => [
                'bool' => [
                    'must' => $must_filter,
                ]
            ]
        ]
    ];

    $response = $this->esClient->search($params);
    if (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
        $tg_name = '';
        foreach ($response['hits']['hits'] as $key => $val) {
            // $tags = $val['_source']['tags'];
            $title = $val['_source']['title'];
            // $tags = explode(",", $tags);

          
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
    return $responseList;
}

    /*
     * update mapping
     */

    public function updateMapping()
    {
        $params = [
            'index' => 'shopzit',
            'type' => 'products',
            'body' => [
                'products' => [
                    'properties' => [
                        'created_at' => [
                            'type' => 'date',
                            "format" => "yyyy-MM-dd HH:mm:ss",
                        ],
                        'price' => [
                            'type' => 'float'
                        ]
                    ]
                ]
            ]
        ];

        // Update the index mapping
        $this->esClient->indices()->putMapping($params);
    }

    public function listCreateIndex()
    {
        $params = [
            'index' => 'shopzit_list_managment',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'autocomplete_filter' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 2,
                                'max_gram' => 10,
                            ]
                        ],
                        'analyzer' => [
                            'autocomplete' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'autocomplete_filter'
                                ]
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'ads' => [
                        '_source' => [
                            'enabled' => true
                        ],
                        'properties' => [
                            'title' => [
                                'type' => 'text',
                                'analyzer' => 'autocomplete',
                            ],
                            'description' => [
                                'type' => 'text',
                                'analyzer' => 'autocomplete',
                            ],
                            'is_feature' => [
                                'type' => 'integer'
                            ],
                            'created_at' => [
                                'type' => 'date',
                                "format" => "yyyy-MM-dd HH:mm:ss",
                            ],
                            'updated_at' => [
                                'type' => 'date',
                                "format" => "yyyy-MM-dd HH:mm:ss",
                            ],
                            'price' => [
                                'type' => 'float'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->esClient->indices()->create($params);
        print_r($response);
    }

    public function listMapping($add_id = '')
    {
        if ($add_id != '') {
            $products = DB::table('products')
                ->select('products.product_id', 'products.user_id', 'products.store_id', 'products.title', 'products.slug', 'products.price', 'products.status', 'categories.categoryTitle', 'products.created_at', 'users.name')
                ->join('product_category', 'product_category.product_id', '=', 'products.product_id')
                ->join('categories', 'categories.id', '=', 'product_category.category_id')
                ->join('users', 'users.user_id', '=', 'products.user_id')
                ->where('products.product_id', $add_id)
                ->where('products.listing_type', '=', 'post')->get()->toArray();
            //$products = DB::table('products')->select('*')->where('product_id', $add_id)->where('listing_type', 'post')->get()->toArray();
        } else {
            $products = DB::table('products')
                ->select('products.product_id', 'products.user_id', 'products.store_id', 'products.title', 'products.slug', 'products.price', 'products.status', 'categories.categoryTitle', 'products.created_at', 'users.name')
                ->join('product_category', 'product_category.product_id', '=', 'products.product_id')
                ->join('categories', 'categories.id', '=', 'product_category.category_id')
                ->join('users', 'users.user_id', '=', 'products.user_id')
                //->where('products.product_id', $add_id)
                ->where('products.listing_type', '=', 'post')->get()->toArray();
        }

        // $arry['index']='shopzit';
        // $arry['type']='products';

        foreach ($products as $key => $val) {
            $arry = array();
            foreach ($val as $key2 => $val2) {
                if ($key2 == 'title') {
                    $arry['title2'] = explode(' ', $val2);
                }
                if ($key2 == 'product_condition') {
                    if ($val2 == '') {
                        $val2 = 'new';
                    }
                    $key2 = 'condition';
                }
                $arry[$key2] = $val2;
            }

            $geo = array();
            $params = [
                'index' => 'shopzit_list_managment',
                'type' => 'ads',
                'id' => $val->product_id,
                'body' => $arry
            ];

            $response = $this->esClient->index($params);
            
        }
    }

    public function listSearch($all_filters)
    {



        $must_filter = array();
        $must_not = array();
        $filters = array();
        $filter_query = array();
        $location = array();
        $location_q = array();


        if (isset($all_filters['keyword']) && $all_filters['keyword'] != '') {
            $must_filter[] = array('multi_match' => array('query' => $all_filters['keyword'], 'fields' => array('title', 'description')));
            //$must_filter[] = array('match' => array('description' => $all_filters['keyword']));
        }
        if (isset($all_filters['condition']) && $all_filters['condition'] != '') {
            $must_filter[] = array('term' => array('condition' => $all_filters['condition']));
        }
        if (isset($all_filters['status']) && $all_filters['status'] == 'active') {
            $must_filter[] = array('term' => array('status' => 1));
        }
        if (isset($all_filters['status']) && $all_filters['status'] == 'inactive') {
            $must_filter[] = array('term' => array('status' => 0));
        }
        if (isset($all_filters['status']) && $all_filters['status'] == 'deleted') {
            $must_filter[] = array('term' => array('status' => 2));
        }
        //$must_not[] = array('term' => array('is_deleted' => 1));

        if (isset($all_filters['category_child']) && $all_filters['category_child'] != '') {
            $filter_query[]['term'] = array(
                'category_child' => $all_filters['category_child'],
            );
        }
        if (isset($all_filters['category']) && $all_filters['category'] != '') {


            $filter_query[]['term'] = array(
                'categories' => $all_filters['category'],
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
        } else {
            $sort = array(
                'is_feature' => 'DESC',
            );
        }


        $from = isset($_GET['page']) ? $_GET['page'] - 1 : 0;
        $from = $from * 10;
        $params = [
            'index' => 'shopzit_list_managment',
            'type' => 'ads',
            'from' => $from,
            'size' => 10,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' =>
                        $must_filter,
                        'must_not' =>
                        $must_not,
                        'filter' =>
                        $filter_query, // $category_query
                    ]
                ],
                'sort' => $sort,
            ]
        ];
        $response = $this->esClient->search($params);
        $hits = isset($response['hits']['hits']) ? $response['hits']['hits'] : '';
        $results = array();
        if ($hits != '' && count($hits) > 0) {
            $results['total'] = $response['hits']['total'];
            $results['records'] = $hits;
        }

        return $results;
    }

    public function listUpdate($add_id)
    {
        if ($add_id != '') {
            $products = DB::table('products')
                ->select('products.product_id', 'products.user_id', 'products.store_id', 'products.title', 'products.slug', 'products.price', 'products.status', 'categories.categoryTitle', 'products.created_at', 'users.name')
                ->join('product_category', 'product_category.product_id', '=', 'products.product_id')
                ->join('categories', 'categories.id', '=', 'product_category.category_id')
                ->join('users', 'users.user_id', '=', 'products.user_id')
                ->where('products.product_id', $add_id)
                ->where('products.listing_type', '=', 'post')->get()->toArray();
            //$products = DB::table('products')->select('*')->get()->toArray();
            // $arry['index']='shopzit';
            // $arry['type']='products';

            foreach ($products as $key => $val) {
                $arry = array();
                foreach ($val as $key2 => $val2) {
                    if ($key2 == 'title') {
                        $arry['title2'] = explode(' ', $val2);
                    }
                    $arry[$key2] = $val2;
                }




                $params = [
                    'index' => 'shopzit_list_managment',
                    'type' => 'ads',
                    'id' => $val->product_id,
                    'body' => $arry
                ];
                $this->deleteDocumentlist($val->product_id);
                $response = $this->esClient->index($params);
            }
        } else {
            return 'no product found to update';
        }
    }

    public function keywordsearchList(Request $request)
    {
        $keyword = $request->input('keyword');

        $params = [
            'index' => 'shopzit_list_managment',
            'type' => 'ads',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $keyword,
                        //'fields' => array('title', 'description'),
                        'fields' => array('title'),
                        'analyzer' => 'standard'
                    ]
                ]
            ]
        ];

        $response = $this->esClient->search($params);
        $arr = array();
        if (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {

            foreach ($response['hits']['hits'] as $key => $val) {
                $arr[] = $val['_source']['title'];
            }
        }
        return $arr;
    }

    public function deleteDocumentlist($product_id = '')
    {
        if ($product_id != '') {
            $params = [
                'index' => 'shopzit_list_managment',
                'type' => 'ads',
                'id' => $product_id,
            ];

            $response = $this->esClient->delete($params);
        } else {
            return 'no product found to delete';
        }
    }

    public function deleteIndexlist()
    {
        $deleteParams = array();
        $deleteParams['index'] = 'shopzit_list_managment';
        $this->esClient->indices()->delete($deleteParams);
    }

    public function userCreateIndex()
    {

        $params = [
            'index' => 'shopzit_user_managment',
            'type' => 'users',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'autocomplete_filter' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 2,
                                'max_gram' => 10,
                            ],
                        ],
                        'analyzer' => [
                            'autocomplete' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'autocomplete_filter',
                                ],
                            ],
                        ],

                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete',
                        ],
                        'email' => [
                            'type' => 'keyword',
                            'analyzer' => 'autocomplete',
                        ],
                        'role_id' => [
                            'type' => 'integer'
                        ],
                        'user_id_g' => [
                            'type' => 'keyword'
                        ],
                        'created_at' => [
                            'type' => 'date',
                            "format" => "yyyy-MM-dd HH:mm:ss",
                        ],
                        'updated_at' => [
                            'type' => 'date',
                            "format" => "yyyy-MM-dd HH:mm:ss",
                        ],
                    ],
                ],
            ],
        ];
        $response = $this->esClient->index($params);
       // echo 'Data: <pre>' .print_r($response,true). '</pre>';

    }

    public function userMapping($userId = '')
    {
        if ($userId != '') {
            $users = DB::table('users')
                ->select('users.user_id', 'users.name', 'users.email', 'users.status', 'users.created_at', 'users.updated_at', 'role_user.role_id')
                ->join('role_user', 'role_user.user_id', '=', 'users.user_id')
                ->where('users.user_id', $userId)
                //->groupBy('users.user_id')
                ->get()->toArray();
        } else {
            $users = DB::table('users')
                ->select('users.user_id', 'users.name', 'users.email', 'users.status', 'users.created_at', 'users.updated_at', 'role_user.role_id')
                ->join('role_user', 'role_user.user_id', '=', 'users.user_id')
                //->groupBy('users.user_id')
                ->get()->toArray();
        }

        // $arry['index']='shopzit';
        // $arry['type']='products';

        foreach ($users as $key => $val) {
            $arry = array();
            foreach ($val as $key2 => $val2) {


                $arry[$key2] = $val2;
                if ($key2 == 'user_id') {
                    $arry['user_id_g'] = $val2;
                }
            }


            $params = [
                'index' => 'shopzit_user_managment',
                'type' => 'users',
                'id' => $val->user_id,
                'body' => $arry
            ];

            $response = $this->esClient->index($params);
        }
    }

    public function userSearch($all_filters)
    {



        $must_filter = array();
        $must_not = array();
        $filters = array();
        $filter_query = array();
        $location = array();
        $location_q = array();


        if (isset($all_filters['keyword']) && $all_filters['keyword'] != '') {
            $must_filter[] = array('multi_match' => array('query' => $all_filters['keyword'], 'fields' => array('name', 'email')));
            //$must_filter[] = array('match' => array('description' => $all_filters['keyword']));
        }
        if (isset($all_filters['condition']) && $all_filters['condition'] != '') {
            $must_filter[] = array('term' => array('condition' => $all_filters['condition']));
        }
        if (isset($all_filters['status']) && $all_filters['status'] == 'active') {
            $must_filter[] = array('term' => array('status' => 1));
        }
        if (isset($all_filters['userrole']) && $all_filters['userrole'] != '') {
            $must_filter[] = array('term' => array('role_id' => $all_filters['userrole']));
        }
        if (isset($all_filters['status']) && $all_filters['status'] == 'inactive') {
            $must_filter[] = array('term' => array('status' => 0));
        }
        //$must_not[] = array('term' => array('is_deleted' => 1));

        if (isset($all_filters['category_child']) && $all_filters['category_child'] != '') {
            $filter_query[]['term'] = array(
                'category_child' => $all_filters['category_child'],
            );
        }
        if (isset($all_filters['category']) && $all_filters['category'] != '') {


            $filter_query[]['term'] = array(
                'categories' => $all_filters['category'],
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
        } else {
            $sort = array(
                'created_at' => 'ASC',
            );
        }


        $from = isset($_GET['page']) ? $_GET['page'] - 1 : 0;
        $from = $from * 10;
        $params = [
            'index' => 'shopzit_user_managment',
            'type' => 'users',
            'from' => $from,
            'size' => 10,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' =>
                        $must_filter,
                        'must_not' =>
                        $must_not,
                        'filter' =>
                        $filter_query, // $category_query
                    ]
                ],
                'aggs' => [
                    'uniq_user_id_g' => [
                        'terms' => ['field' => 'user_id_g']
                    ]
                ],
                // 'sort' => $sort,
            ]
        ];
        // dd($params);
        $response = $this->esClient->search($params);

        $hits = isset($response['hits']['hits']) ? $response['hits']['hits'] : '';
        $results = array();
        if ($hits != '' && count($hits) > 0) {
            $results['total'] = $response['hits']['total'];
            $results['records'] = $hits;
        }
//  print_rr($results);
//         die();
        return $results;
    }

    public function uerUpdate($userId)
    {
        if ($userId != '') {
            $users = DB::table('users')
                ->select('users.user_id', 'users.name', 'users.email', 'users.status', 'users.created_at', 'users.updated_at', 'role_user.role_id')
                ->join('role_user', 'role_user.user_id', '=', 'users.user_id')
                ->where('users.user_id', $userId)
                //->groupBy('users.user_id')
                ->get()->toArray();
            //$products = DB::table('products')->select('*')->get()->toArray();
            // $arry['index']='shopzit';
            // $arry['type']='products';

            foreach ($users as $key => $val) {
                $arry = array();
                foreach ($val as $key2 => $val2) {

                    $arry[$key2] = $val2;
                    if ($key2 == 'user_id') {
                        $arry['user_id_g'] = $val2;
                    }
                }
                $params = [
                    'index' => 'shopzit_user_managment',
                    'type' => 'users',
                    'id' => $val->user_id,
                    'body' => $arry
                ];
                $this->deleteDocumentuser($val->user_id);
                $response = $this->esClient->index($params);
            }
        } else {
            return 'no User found to update';
        }
    }

    public function update_index(Request $request)
    {
        $id = $request['id'];

        $params = [
            'index' => 'shopzit',
            'type' => 'products',
            'id' => $id,
            'body' => [
                'doc' => [
                    'supported' => array(8, 9, 10)
                ]
            ]
        ];

        // Update the index mapping
        $this->esClient->update($params);
        echo 'updated_successfully';
        die();
    }

    public function keywordsearchuser(Request $request)
    {
        $keyword = $request->input('keyword');

        $params = [
            'index' => 'shopzit_user_managment',
            'type' => 'users',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $keyword,
                        //'fields' => array('title', 'description'),
                        'fields' => array('name'),
                        'analyzer' => 'standard'
                    ]
                ]
            ]
        ];

        $response = $this->esClient->search($params);
        $arr = array();
        if (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {

            foreach ($response['hits']['hits'] as $key => $val) {
                $arr[] = $val['_source']['name'];
            }
        }
        return $arr;
    }

    public function deleteDocumentuser($product_id = '')
    {
        if ($product_id != '') {
            $params = [
                'index' => 'shopzit_user_managment',
                'type' => 'users',
                'id' => $product_id,
            ];

            $response = $this->esClient->delete($params);
        } else {
            return 'no User found to delete';
        }
    }

    public function deleteIndexuser()
    {
        $deleteParams = array();
        $deleteParams['index'] = 'shopzit_user_managment';
        $this->esClient->indices()->delete($deleteParams);
    }
}
