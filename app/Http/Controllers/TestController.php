<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

use LearnositySdk\Request\Init;
use LearnositySdk\Request\DataApi;


class TestController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    // show available test
    public function show(Request $request)
    {

        $UserName  = Auth::user()->username;
        $SQL1 = "GetContactTestByLogin '".$UserName."'";
        $results = DB::select("exec ".$SQL1);
      //  die(var_dump($results));

        $request->session()->put('username', $UserName);

        return view('testlist')->with([
            'results'   => $results
        ]);
    }

    // get a test
    public function test(Request $request)
    {
        //include_once  'LearnosityConfig.php';


        $consumer_key =  'bUOZ4qYtvk1qS7jT';
        $consumer_secret = 'MWEZJVpzxqshlE8MiWwnNVhIFj9hqsBCPPmjvbjs';

        /*
        $consumer_key = 'yis0TYCu7U9V4o7M';
        $consumer_secret = '74c5fd430cf1242a527f6223aebd42d30464be22';
        */
        //$domain = $_SERVER['SERVER_NAME'];
        $domain = 'learnosity.lti-inc.net';

        $security = [
            'consumer_key' => $consumer_key,
            'domain'       => $domain
        ];

        //die(var_dump(Uuid::generate()));
        $UserName = $request->session()->get('username');
        $TestDescription = $request->testdescription;

        $SQL1 = "GetItems ".$request->testid;
        $Items = DB::select("exec ".$SQL1);

        $items = array();
        foreach ($Items as $Item)
        {
            $items[] = $Item->taskid;
        }
        $request = [
            'user_id'        => $UserName,
            'session_id'     => $request->session_id,
            'items'          => $items,//['LTI-ESL-IL-1','LTI-ESL-IL-2','LTI-ESL-IL-3'],//
            'rendering_type' => 'assess',
            'state'          => 'initial',
            'type'           => 'submit_practice',
            'activity_id'    => $request->activity_id,
            'name'           => 'IL',
            'course_id'      => 'demo_course',
            "config"         => [
                'ui_style'                   => 'horizontal',
                "ignore_question_attributes" => [
                    "instant_feedback"
                ],
                'navigation' => [
                    'show_accessibility' => [
                        'show_colourscheme' => true,
                        'show_fontsize' => true,
                        'show_zoom' => true
                    ]
                ],
                'configuration' => [
                    'onsubmit_redirect_url'  => '/completetest?contacttestid='.$request->contacttestid.'&activityid='.$request->activity_id.'&sessionid='.$request->session_id,
                ]
            ]
        ];
        //die(var_dump($request));
        $Init = new Init('items', $security, $consumer_secret, $request);
        $signedRequest = $Init->generate();

        return view('test')->with([
            'signedRequest'   => $signedRequest,
            'testdescription' => $TestDescription
        ]);
    }


    // complete test
    public function completetest(Request $request)
    {
        $contacttestid = $request->contacttestid;
        $SQL1 = "CompleteTest ".$contacttestid;
        //die(var_dump($SQL1));
        DB::statement("exec ".$SQL1);

        //$responses = $this->getresponse($request);

        $consumer_key = 'bUOZ4qYtvk1qS7jT';
        $consumer_secret = 'MWEZJVpzxqshlE8MiWwnNVhIFj9hqsBCPPmjvbjs';

        $domain = $_SERVER['SERVER_NAME'];

        $security = [
            'consumer_key' => $consumer_key,
            'domain'       => $domain
        ];
        $request = array(
            'activity_id' => $request->activity_id,
            'session_id' => $request->session_id,
            'user_id'=> [$request->session()->get('username')],
            'sort'=>'desc',
            'limit'=> 1
        );

        $action = 'get';

        $dataApi = new DataApi();
        $response = $dataApi->request(
            'https://data.learnosity.com/v1/sessions/responses',
            $security,
            $consumer_secret,
            $request,
            $action

        );
        $report = json_decode($response->getBody(), true)['data'][0];

        $itemReference = '';
        $score = '';
        $indevResponse = '';
        $activityType = 'N';
        $prevItem = '';
        $currItem = '';
        $checkSeq = 1;

        foreach ($report['responses'] as $key => $val) {
            if($checkSeq == 1) $prevItem = $val['item_reference'];
            $currItem = $val['item_reference'];

            if($prevItem != $currItem) {
                $itemReference = substr($itemReference,0,strlen($itemReference)-1);
                $score = substr($score,0,strlen($score)-1);
                $indevResponse = substr($indevResponse,0,strlen($indevResponse)-1);
                $indevResponse = str_replace('"', '', $indevResponse);

                $SQL2 = 'UpdateResponse_aappl20 ' . $activityType . "," . $contacttestid . ",'" . $indevResponse . "','" . $score . "','" . $report['dt_started'] . "','" . $report['dt_completed'] . "','" . $itemReference . "'";

                //echo '<th> ' . $SQL2 . '</th><br/>';

                DB::statement("exec ".$SQL2);
                $itemReference = '';
                $score = '';
                $indevResponse = '';
            }


            $itemReference = $itemReference.$val['item_reference'].'#';
            if($val['score'] == null) {
                $score = $score . '0' . '#';
            } else {
                $score = $score . $val['score'] . '#';
            }

            if (strrpos($val['item_reference'], 'ILS')) {
                $indevResponse = $indevResponse . 'http://learnositymediaprocessed.s3.amazonaws.com/' . $val['response']['location'] . '.mp3#';
                $activityType = 'N';
            } else if (strrpos($val['item_reference'], 'PW')) {
                if($val['response']['value'][0] == null) {
                    $indevResponse = $indevResponse . 'null' . '#';
                } else {
                    $indevResponse = $indevResponse . $val['response']['value'] . '#';
                }
                $activityType = 'N';
            } else {
                if (count($val['response']['value']) > 1) {
                    foreach ($val['response']['value'] as $key => $indiv) {
                        $indevResponse = $indevResponse . json_encode($indiv) . '$';
                        //$indevResponse = $indevResponse . serialize($indiv) . '$';
                        //echo '<th> ' . json_encode($indiv) . '</th><br/>';
                    }
                    $indevResponse = substr($indevResponse,0,strlen($indevResponse)-1);
                    $indevResponse = $indevResponse . '#';
                    $activityType = 'M';
                } else {
                    if($val['response']['value'][0] == null) {
                        $indevResponse = $indevResponse . ' ' . '#';
                    } else {
                        $indevResponse = $indevResponse . json_encode($val['response']['value'][0]) . '#';
                    }
                    $activityType = 'N';
                }
            }


            //if ($score == '##') $score = '0#0#0';
            if ($score == '#') $score = '0#';

            if(count($report['responses']) == $checkSeq) {
                $itemReference = substr($itemReference,0,strlen($itemReference)-1);
                $score = substr($score,0,strlen($score)-1);
                $indevResponse = substr($indevResponse,0,strlen($indevResponse)-1);
                $indevResponse = str_replace('"', '', $indevResponse);

                /*echo '<th> ' . $report['dt_started'] . '</th><br/>';
                echo '<th> ' . $report['dt_completed'] . '</th><br/>';
                echo '<th> ' . $itemReference . '</th><br/>';
                echo '<th> ' . $score . '</th><br/>';
                echo '<th> ' . $indevResponse . '</th><br/>';
                echo '<th> ' . $prevItem . '</th><br/>';
                echo '<th> ' . $currItem . '</th><br/>';*/
                $SQL2 = 'UpdateResponse_aappl20 ' . $activityType . "," . $contacttestid . ",'" . $indevResponse . "','" . $score . "','" . $report['dt_started'] . "','" . $report['dt_completed'] . "','" . $itemReference . "'";

                //echo '<th> ' . $SQL2 . '</th><br/>';

                DB::statement("exec ".$SQL2);
                $itemReference = '';
                $score = '';
                $indevResponse = '';
            }



            //return dd($SQL2);

            $prevItem = $val['item_reference'];
            $checkSeq = $checkSeq + 1;

        }


        //return dd($SQL2);
        //return json_decode($report, true)['data'];

        return redirect('/');
    }

    // complete test
    public function getresponse(Request $request)
    {


        $consumer_key = 'bUOZ4qYtvk1qS7jT';
        $consumer_secret = 'MWEZJVpzxqshlE8MiWwnNVhIFj9hqsBCPPmjvbjs';

        $UserName  = Auth::user()->username;
        $domain = $_SERVER['SERVER_NAME'];

        $security = [
            'consumer_key' => $consumer_key,
            'domain'       => $domain
        ];
        $request = array(
            'activity_id' => $request->activity_id,
            'session_id' => $request->session_id,
            'user_id'=> $UserName,
            'sort'=>'asc',
            'limit'=> 50
        );

        $action = 'get';

        $dataApi = new DataApi();
        $response = $dataApi->request(
            'https://data.learnosity.com/v1/sessions/responses',
            $security,
            $consumer_secret,
            $request,
            $action

        );

        die(var_dump($request));
        function myCallback($data)
        {
            // Do something with $data
            die(var_dump($data));
        }
    }
}
