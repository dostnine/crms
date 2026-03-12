<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\psto;
use App\Models\Unit;
use Inertia\Inertia;
use App\Models\Assignatorees;
use App\Models\CSFForm;
use App\Models\SubUnit;
use App\Models\Services;
use App\Models\UnitPsto;
use App\Models\Dimension;
use App\Models\SubUnitPsto;
use App\Models\SubUnitType;
use App\Models\UnitSubUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerAttributeRating;
use App\Models\CustomerComment;
use App\Models\CustomerCCRating;
use App\Models\Customer;
use App\Models\User;
use App\Http\Resources\Unit as UnitResource;
use App\Models\CustomerRecommendationRating;
use App\Http\Resources\Services as ServiceResource;
use App\Http\Resources\SubUnit as SubUnitResource;
use App\Http\Resources\UnitPSTO as UnitPSTOResource;
use App\Http\Resources\SubUnitPSTO as SubUnitPSTOResource;
use App\Http\Resources\UnitSubUnit as UnitSubUnitResource;
use App\Http\Resources\CustomerAttributeRatings as CARResource;

class ReportController extends Controller
{
    private function getUnitData(Request $request, $withSubUnits = false)
    {
        $user = Auth::user();

        $query = Unit::where('id', $request->unit_id);
        if ($withSubUnits) {
            $query->with('sub_units');
        }
        $units = $query->get();
        $unit = UnitResource::collection($units);

        //get unit pstos
        $unit_pstos = UnitPSTO::where('unit_id', $request->unit_id)->get();
        $psto_ids = $unit_pstos->pluck('psto_id');
        $unit_pstos = psto::whereIn('id', $psto_ids)
                    ->where('region_id', $user->region_id)
                    ->get();

        //get sub unit pstos
        $sub_unit_pstos = SubUnitPSTO::where('sub_unit_id', $request->sub_unit_id)->get();
        $psto_ids = $sub_unit_pstos->pluck('psto_id');
        $sub_unit_pstos = psto::whereIn('id', $psto_ids)
                    ->where('region_id', $user->region_id)
                    ->get();

        $sub_unit_types = SubUnitType::where('sub_unit_id', $request->sub_unit_id)->get();

        $sub_unit = $request->sub_unit_id ? SubUnit::where('id', $request->sub_unit_id)->get() : collect();

        return [
            'unit' => $unit,
            'unit_pstos' => $unit_pstos,
            'sub_unit_pstos' => $sub_unit_pstos,
            'sub_unit_types' => $sub_unit_types,
            'sub_unit' => $sub_unit,
        ];
    }

    public function index(Request $request )
    {
        //get user
        $user = Auth::user();

        //get assignatoree list
        $assignatorees = Assignatorees::all();

        //get all users for prepared by dropdown
        $users = User::all();

        $dimensions = Dimension::all();
        $service = Services::findOrFail($request->service_id);

        $unitData = $this->getUnitData($request, true);

        return Inertia::render('CSI/Index')
            ->with('assignatorees', $assignatorees)
            ->with('dimensions', $dimensions)
            ->with('service', $service)
            ->with('unit', $unitData['unit'])
            ->with('unit_pstos', $unitData['unit_pstos'])
            ->with('sub_unit_pstos', $unitData['sub_unit_pstos'])
            ->with('sub_unit_types', $unitData['sub_unit_types'])
            ->with('user', $user)
            ->with('sub_unit', $unitData['sub_unit'])
            ->with('users', $users);
    }


    public function view(Request $request )
    {
        //get user
        $user = Auth::user();

        $dimensions = Dimension::all();
        $service = Services::findOrFail($request->service_id);

        $unitData = $this->getUnitData($request, true);

        return Inertia::render('Libraries/Service-Units/Views/View')
            ->with('dimensions', $dimensions)
            ->with('service', $service)
            ->with('unit', $unitData['unit'])
            ->with('unit_pstos', $unitData['unit_pstos'])
            ->with('sub_unit_pstos', $unitData['sub_unit_pstos'])
            ->with('sub_unit_types', $unitData['sub_unit_types'])
            ->with('user', $user);

    }


    public function generateReports(Request $request)
    {
        // Get PSTO ID from either unit or sub-unit selection
        $psto_id = $request->selected_unit_psto ?: $request->selected_sub_unit_psto;

        // Get authenticated user
        $user = Auth::user();

        // For GET requests, convert array data back to objects
        if ($request->isMethod('get')) {
            $request = $this->convertArraysToObjects($request);
        }

        // Route to appropriate generation method based on CSI type
        switch ($request->csi_type) {
            case 'By Date':
                return $this->generateCSIByUnitByDate($request, $user->region_id, $psto_id);
            case 'By Month':
                return $this->generateCSIByUnitMonthly($request, $user->region_id, $psto_id);
            case 'By Quarter':
                return $this->generateCSIByQuarter($request, $user->region_id, $psto_id);
            case 'By Year/Annual':
                return $this->generateCSIByUnitYearly($request, $user->region_id, $psto_id);
            default:
                abort(400, 'Invalid CSI type specified');
        }
    }

    /**
     * Convert array data to objects for GET requests and return modified request
     */
    private function convertArraysToObjects(Request $request): Request
    {
        $data = $request->all();
        $modifiedData = $data;

        if (isset($data['service']) && is_array($data['service'])) {
            $modifiedData['service'] = (object) $data['service'];
        }

        if (isset($data['unit']) && is_array($data['unit'])) {
            $modifiedData['unit'] = (object) $data['unit'];
        }

        return new Request($modifiedData, $request->query->all(), $request->attributes->all(),
                          $request->cookies->all(), $request->files->all(), $request->server->all());
    }








    public function generateCSIByUnitByDate($request, $region_id, $psto_id)
    {
        $unitData = $this->getUnitData($request, true);
        $sub_unit = $unitData['sub_unit'];
        $unit_pstos = $unitData['unit_pstos'];
        $sub_unit_pstos = $unitData['sub_unit_pstos'];
        $sub_unit_types = $unitData['sub_unit_types'];

        //get user
        $user = Auth::user();
        //get assignatoree list
        $assignatorees = Assignatorees::all();

        //get users lists
        $users = User::all();

      
        
        $service_id = $request->service['id'];
        $unit_id = $request->unit_id;
        $sub_unit_id = $request->selected_sub_unit;
        $client_type = $request->client_type;
        $sub_unit_type = $request->sub_unit_type;

       // search and check list of forms query
       $customer_ids = $this->querySearchCSF($region_id, $service_id, $unit_id ,$sub_unit_id , $psto_id, $client_type, $sub_unit_type );

       $cc_query = CustomerCCRating::whereBetween('created_at', [$request->date_from, $request->date_to])
            ->whereIn('customer_id',$customer_ids)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            });

        //calculate CC
        $cc_data = $this->calculateCC($cc_query);

        // $date_range = CustomerAttributeRating::whereIn('customer_id',$customer_ids)
        //                                      ->whereBetween('created_at', [$request->date_from, $request->date_to])->get(); 
        
        $date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$request->date_from, $request->date_to])
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            })
            ->get();


        $customer_recommendation_ratings = CustomerRecommendationRating::whereIn('customer_id',$customer_ids)
            ->whereBetween('created_at', [$request->date_from, $request->date_to])
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            })
            ->get();   
                       
        $dimensions = Dimension::all();
        $dimension_count = $dimensions->count();

        // total number of respondents/customer
        $total_respondents = $date_range->groupBy('customer_id')->count();

        // total number of respondents/customer who rated VS/S
        $total_vss_respondents = $date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();
        
        // total number of promoters or respondents who rated 7-10 in recommendation rating
        $total_promoters = $customer_recommendation_ratings->whereBetween('recommend_rate_score', [7, 10])->groupBy('customer_id')->count();
        
        // total number of detractors or respondents who rated 0-6 in recommendation rating
        $total_detractors = $customer_recommendation_ratings->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();

        $ilsr_grand_total =0;

        // loop for getting importance ls rating grand total for ws rating calculation
        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            $vi_total = $date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $i_total = $date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $mi_total = $date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $li_total = $date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $nai_total = $date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->count();

            $x_vi_total = $vi_total * 5; 
            $x_i_total = $i_total * 4; 
            $x_mi_total = $mi_total * 3; 
            $x_li_total = $li_total * 2; 
            $x_nai_total = $nai_total * 1;
            $x_importance_total = $x_vi_total + $x_i_total + $x_mi_total + $x_li_total + $x_nai_total  ; 

            // Importance Likert Scale RAting 
            if($x_importance_total != 0){
                $ilsr_total = $x_importance_total / $total_respondents;
                $ilsr_grand_total =  $ilsr_grand_total + $ilsr_total;
            }

        }

        // PART I : CUSTOMER RATING OF SERVICE QUALITY 

        //set initial value of buttom side total scores
        $y_totals = [];
        $grand_vs_total = 0;
        $grand_s_total = 0;
        $grand_n_total = 0;
        $grand_vd_total = 0;
        $grand_d_total = 0;
        $grand_total = 0;
        
        //set initial value of right side total scores
        $x_vs_total = 0; 
        $x_s_total = 0; 
        $x_n_total = 0; 
        $x_d_total = 0; 
        $x_vd_total = 0; 
        $x_grand_total = 0 ; 

        $likert_scale_rating_totals = [];
        $lsr_total= 0;
        $lsr_grand_total= 0;

         // PART II : IMPORTANCE OF THIS ATTRIBUTE 

        //set importance rating score 
        $importance_rate_score_totals = [];
        $x_importance_totals = [];
        $x_importance_total=0; 

        $x_vi_total = 0; 
        $x_i_total =0; 
        $x_mi_total =0; 
        $x_li_total = 0; 
        $x_nai_total = 0;

        $importance_ilsr_totals = [];
        $ilsr_total = 0;

        $gap_totals = [];
        $gap_total = 0 ;
        $gap_grand_total=0;
        $ss_total= 0;
        $ss_totals = [];
        $wf_total= 0;
        $wf_totals = [];
        $ws_total = 0;
        $ws_totals = [];
        $ws_grand_total = 0;

        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            //PART II
            $vs_total = $date_range->where('rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $s_total = $date_range->where('rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $n_total = $date_range->where('rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $d_total = $date_range->where('rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $vd_total = $date_range->where('rate_score', 1)->where('dimension_id', $dimensionId)->count();          
       
            $x_vs_total = $vs_total * 5; 
            $x_s_total = $s_total * 4; 
            $x_n_total = $n_total * 3; 
            $x_d_total = $d_total * 2; 
            $x_vd_total = $vd_total * 1; 

             // sum of all repondent with rate_score 1-5
             $x_respondents_total =  $vs_total +   $x_s_total + $n_total +  $d_total +  $vd_total;
            $x_grand_total = $x_vs_total + $x_s_total + $x_n_total + $x_d_total + $x_vd_total  ; 
         
            // right side total score divided by total repondents or customers
            if($x_grand_total != 0){
                if($dimensionId == 6){
                    $lsr_total = $x_grand_total / $x_respondents_total;
                }
                else{
                    $lsr_total = $x_grand_total / $total_respondents;
                }
            }
           
            // SS = lsr with 3 decimals
            $ss_total = number_format($lsr_total, 3);
            $ss_totals[$dimensionId] = [
                'ss_total' => $ss_total,
            ];

            //likert sclae rating grandtotal
            $lsr_grand_total =  $lsr_grand_total + $lsr_total;
            $x_totals[$dimensionId] = [
                'x_total_score' => $x_grand_total,
            ];

            $lsr_total = number_format($lsr_total, 2);

            $likert_scale_rating_totals[$dimensionId] = [
                'lsr_total' => $lsr_total,
            ];

            $y_totals[$dimensionId] = [
                'vs_total' => $vs_total,
                's_total' => $s_total,
                'n_total' => $n_total,
                'd_total' => $d_total,
                'vd_total' => $vd_total,
            ];

            $grand_vs_total+=$vs_total;
            $grand_s_total+=$s_total;
            $grand_n_total+=$n_total;
            $grand_d_total+=$d_total;
            $grand_vd_total+=$vd_total;       
                     
            // PART III
            $vi_total = $date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $i_total = $date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $mi_total = $date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $li_total = $date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $nai_total = $date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->count();
        
            $importance_rate_score_totals[$dimensionId] = [
                'vi_total' => $vi_total,
                'i_total' => $i_total,
                'mi_total' => $mi_total,
                'li_total' => $li_total,
                'nai_total' => $nai_total,
            ];

            $x_vi_total = $vi_total * 5; 
            $x_i_total = $i_total * 4; 
            $x_mi_total = $mi_total * 3; 
            $x_li_total = $li_total * 2; 
            $x_nai_total = $nai_total * 1;
            $x_importance_total = $x_vi_total + $x_i_total + $x_mi_total + $x_li_total + $x_nai_total  ; 
            
            //right side total importance rate scores 
            $x_importance_totals[$dimensionId] = [
                'x_importance_total_score' => $x_importance_total,
            ];
            
            // Likert Scale RAting 
            if($x_importance_total != 0){
                $ilsr_total = $x_importance_total / $total_respondents;
            }
            $ilsr_total = number_format($ilsr_total, 2);

            $importance_ilsr_totals[$dimensionId] = [
                'ilsr_total' => $ilsr_total,
            ];
 
            // GAP = attributes total score minus importance of attributes total score
            $gap_total=  $ilsr_total - $lsr_total;
            $gap_total = number_format($gap_total, 2);

            $gap_totals[$dimensionId] = [
                'gap_total' => $gap_total,
            ];

            $gap_grand_total += $gap_total;
            $gap_grand_total = number_format($gap_grand_total, 2);

            // WF = (importance LS Rating divided by importance grand total  of ls rating) * 100
            if($ilsr_total != 0){
                $wf_total =  ($ilsr_total / $ilsr_grand_total) * 100;
            }
            $wf_total = number_format($wf_total, 2);
            $wf_totals[$dimensionId] = [
                'wf_total' => $wf_total,
            ];

            // WS = (SS * WF) / 100  
            $ws_total = ($ss_total * $wf_total) / 100;   

            $ws_total = number_format($ws_total, 2);
            $ws_grand_total +=  $ws_total;
            $ws_grand_total = number_format($ws_grand_total, 2);
            $ws_totals[$dimensionId] = [
                'ws_total' => $ws_total,
            ];
        }

        // round off Likert Scale Rating grand total and control decimal to 2 
        $lsr_grand_total = ($lsr_grand_total/ $dimension_count);
        $lsr_grand_total = number_format($lsr_grand_total, 2);    

        // table below total score
        $grand_vs_total =   $grand_vs_total * 5;
        $grand_s_total =   $grand_s_total * 5;
        $grand_n_total =   $grand_n_total * 5;
        $grand_d_total =   $grand_d_total * 5;
        $grand_vd_total =   $grand_vd_total * 5;

        $x_grand_total =  $grand_vs_total +  $grand_s_total + $grand_n_total +  $grand_d_total +   $grand_vd_total;

        //Percentage of Respondents/Customers who rated VS/S: 
        // = total no. of respondents / total no. respondets who rated vs/s * 100
        $percentage_vss_respondents  = 0;
        if($total_respondents != 0 || $total_vss_respondents != 0){
            $percentage_vss_respondents  = ($total_vss_respondents / $total_respondents) * 100;
        }
        $percentage_vss_respondents = number_format( $percentage_vss_respondents , 2);

        $customer_satisfaction_rating = 0;
        if($total_respondents != 0 || $total_vss_respondents != 0){
            $customer_satisfaction_rating = (($grand_vs_total+$grand_s_total)/$x_grand_total) * 100;
        }
        $customer_satisfaction_rating = number_format( $customer_satisfaction_rating , 2);

        // Customer Satisfaction Index (CSI) = (ws grand total / 5) * 100
        $customer_satisfaction_index = 0;
        if($ws_grand_total != 0){
            $customer_satisfaction_index = ($ws_grand_total/5) * 100;
        }
        $customer_satisfaction_index = number_format($customer_satisfaction_index, 2);

        if($customer_satisfaction_index > 100){
            $customer_satisfaction_index = number_format(100 , 2);
        }

        //Percentage of Promoters = number of promoters / total respondents
        $percentage_promoters = 0;
        if($total_respondents != 0){
            $percentage_promoters = number_format((($total_promoters / $total_respondents) * 100), 2);
        }

        //Percentage of Promoters = number of promoters / total respondents
        $percentage_detractors = 0;
        if($total_respondents != 0){
            $percentage_detractors = number_format((($total_detractors / $total_respondents) * 100),2);
        }

        // Net Promotion Scores(NPS) = Percentage of Promoters−Percentage of Detractors
        $net_promoter_score =  number_format(($percentage_promoters - $percentage_detractors),2);
  

        //send response to front end
        return Inertia::render('CSI/Index')    
            ->with('user', $user)
            ->with('assignatorees', $assignatorees)
            ->with('users', $users)
            ->with('cc_data', $cc_data) 
            ->with('sub_unit', $sub_unit)
            ->with('unit_pstos', $unit_pstos)
            ->with('sub_unit_pstos', $sub_unit_pstos)
            ->with('sub_unit_types', $sub_unit_types)
            ->with('dimensions', $dimensions)
            ->with('service', $request->service)
            ->with('unit', $request->unit)
            ->with('y_totals',$y_totals)
            ->with('grand_vs_total',$grand_vs_total)
            ->with('grand_s_total',$grand_s_total)
            ->with('grand_n_total',$grand_n_total)
            ->with('grand_d_total',$grand_d_total)
            ->with('grand_vd_total',$grand_vd_total)
            ->with('x_totals',$x_totals)
            ->with('x_grand_total',$x_grand_total)
            ->with('likert_scale_rating_totals',$likert_scale_rating_totals)
            ->with('lsr_grand_total',$lsr_grand_total)
            ->with('importance_rate_score_totals',$importance_rate_score_totals)
            ->with('x_importance_totals', $x_importance_totals)
            ->with('importance_ilsr_totals', $importance_ilsr_totals)
            ->with('gap_totals', $gap_totals)
            ->with('gap_grand_total', $gap_grand_total)
            ->with('wf_totals', $wf_totals)
            ->with('ss_totals', $ss_totals)
            ->with('wf_totals', $wf_totals)
            ->with('ws_totals', $ws_totals)
            ->with('total_respondents', $total_respondents)
            ->with('total_vss_respondents', $total_vss_respondents)
            ->with('percentage_vss_respondents', $percentage_vss_respondents)
            ->with('customer_satisfaction_rating', $customer_satisfaction_rating)
            ->with('customer_satisfaction_index', $customer_satisfaction_index)
            ->with('net_promoter_score', $net_promoter_score)
            ->with('percentage_promoters', $percentage_promoters)
            ->with('percentage_detractors', $percentage_detractors)
            ->with('request', $request);    
    }   


    public function generateCSIByUnitMonthly($request, $region_id, $psto_id)
    {
        $unitData = $this->getUnitData($request, true);
        $sub_unit = $unitData['sub_unit'];
        $unit_pstos = $unitData['unit_pstos'];
        $sub_unit_pstos = $unitData['sub_unit_pstos'];
        $sub_unit_types = $unitData['sub_unit_types'];

        //get user
        $user = Auth::user();
         //get assignatoree list
         $assignatorees = Assignatorees::all();

         //get users lists
        $users = User::all();

        $date_range = null;
        $customer_recommendation_ratings = null;
        $respondents_list = null;

        $service_id = $request->service['id'];
        $unit_id = $request->unit_id;
        $sub_unit_id = $request->selected_sub_unit;
        $client_type = $request->client_type; 
        $sub_unit_type = $request->sub_unit_type; 

        // search and check list of forms query  
        $customer_ids = $this->querySearchCSF($region_id, $service_id, $unit_id ,$sub_unit_id , $psto_id, $client_type, $sub_unit_type );
      
        $numericMonth = Carbon::parse("1 {$request->selected_month}")->format('m');

        $cc_query = CustomerCCRating::whereMonth('created_at', $numericMonth)
                                    ->whereYear('created_at', $request->selected_year)
                                    ->whereIn('customer_id',$customer_ids)
                                    ->when($request->sex, function ($query, $sex) {
                                        $query->whereHas('customer', function ($query) use ($sex) {
                                            $query->where('sex', $sex);
                                        });
                                    })
                                    ->when($request->age_group, function ($query, $age_group) {
                                        $query->whereHas('customer', function ($query) use ($age_group) {
                                            $query->where('age_group', $age_group);
                                        });
                                    });

        //calculate Citizen's Charter
        $cc_data = $this->calculateCC($cc_query);


        //$date_range = CustomerAttributeRating::whereMonth('created_at', $numericMonth)->get();
        $date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                             ->whereMonth('created_at', $numericMonth)
                                             ->when($request->sex, function ($query, $sex) {
                                                $query->whereHas('customer', function ($query) use ($sex) {
                                                    $query->where('sex', $sex);
                                                });
                                            })
                                            ->when($request->age_group, function ($query, $age_group) {
                                                $query->whereHas('customer', function ($query) use ($age_group) {
                                                    $query->where('age_group', $age_group);
                                                });
                                            })
                                            ->get();

        $customer_recommendation_ratings = CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
                                            ->whereMonth('created_at', $numericMonth)
                                            ->when($request->sex, function ($query, $sex) {
                                                $query->whereHas('customer', function ($query) use ($sex) {
                                                    $query->where('sex', $sex);
                                                });
                                            })
                                            ->when($request->age_group, function ($query, $age_group) {
                                                $query->whereHas('customer', function ($query) use ($age_group) {
                                                    $query->where('age_group', $age_group);
                                                });
                                            })
                                            ->get();
        // List of Respondents/Customers
        $respondents_list = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                                    ->whereMonth('created_at', $numericMonth)->get();
           
        // Dimensions or attributes
        $dimensions = Dimension::all();
        $dimension_count = $dimensions->count();

        // total number of respondents/customer
        $total_respondents = $date_range->groupBy('customer_id')->count();

        // total number of respondents/customer who rated VS/S
        $total_vss_respondents = $date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();
        
        // total number of promoters or respondents who rated 7-10 in recommendation rating
        $total_promoters = $customer_recommendation_ratings->whereBetween('recommend_rate_score', [7, 10])->groupBy('customer_id')->count();
        
        // total number of detractors or respondents who rated 0-6 in recommendation rating
        $total_detractors = $customer_recommendation_ratings->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();

        $ilsr_grand_total =0;
        // loop for getting importance ls rating grand total for ws rating calculation
        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            $vi_total = $date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $i_total = $date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $mi_total = $date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $li_total = $date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $nai_total = $date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->count();

            $x_vi_total = $vi_total * 5; 
            $x_i_total = $i_total * 4; 
            $x_mi_total = $mi_total * 3; 
            $x_li_total = $li_total * 2; 
            $x_nai_total = $nai_total * 1;
            $x_importance_total = $x_vi_total + $x_i_total + $x_mi_total + $x_li_total + $x_nai_total  ; 

            // Importance Likert Scale RAting 
            if($x_importance_total != 0){
                $ilsr_total = $x_importance_total / $total_respondents;
                $ilsr_grand_total =  $ilsr_grand_total + $ilsr_total;
            }

        }

        // PART II : CUSTOMER RATING OF SERVICE QUALITY 

        //set initial value of buttom side total scores
        $y_totals = [];
        $grand_na_total = 0;
        $grand_vs_total = 0;
        $grand_s_total = 0;
        $grand_n_total = 0;
        $grand_vd_total = 0;
        $grand_d_total = 0;
        $grand_total = 0;
        
        //set initial value of right side total scores
        $x_vs_total = 0; 
        $x_s_total = 0; 
        $x_n_total = 0; 
        $x_d_total = 0; 
        $x_vd_total = 0; 
        $x_grand_total = 0 ; 

        $likert_scale_rating_totals = [];
        $lsr_total= 0;
        $lsr_grand_total= 0;

         // PART II : IMPORTANCE OF THIS ATTRIBUTE 

        //set importance rating score 
        $importance_rate_score_totals = [];
        $x_importance_totals = [];
        $x_importance_total=0; 

        $x_vi_total = 0; 
        $x_i_total =0; 
        $x_mi_total =0; 
        $x_li_total = 0; 
        $x_nai_total = 0;

        $importance_ilsr_totals = [];
        $ilsr_total = 0;

        $gap_totals = [];
        $gap_total = 0 ;
        $gap_grand_total=0;
        $ss_total= 0;
        $ss_totals = [];
        $wf_total= 0;
        $wf_totals = [];
        $ws_total= 0;
        $ws_totals = [];
        $ws_grand_total = 0;

        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            //PART I :

            $na_total = $date_range->where('rate_score', 6)->where('dimension_id', $dimensionId)->count(); 

            $vs_total = $date_range->where('rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $s_total = $date_range->where('rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $n_total = $date_range->where('rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $d_total = $date_range->where('rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $vd_total = $date_range->where('rate_score', 1)->where('dimension_id', $dimensionId)->count(); 

            //check if there are respondent who rate 3 or below and add it if theres

            // calculation for total score per dimension
            $x_vs_total = $vs_total * 5; 
            $x_s_total = $s_total * 4; 
            $x_n_total = $n_total * 3; 
            $x_d_total = $d_total * 2; 
            $x_vd_total = $vd_total * 1; 

            // sum of all repondent with rate_score 1-5
            $x_respondents_total =  $vs_total +   $x_s_total + $n_total +  $d_total +  $vd_total;
            $x_grand_total = $x_vs_total + $x_s_total + $x_n_total + $x_d_total + $x_vd_total; 
  
            // right side total score divided by total repondents or customers
            if($x_grand_total != 0){
                if($dimensionId == 6){
                    $lsr_total = $x_grand_total / $x_respondents_total;
                }
                else{
                    $lsr_total = $x_grand_total / $total_respondents;
                }
            }
           
            // SS = lsr with 3 decimals
            $ss_total = number_format($lsr_total, 3);
            $ss_totals[$dimensionId] = [
                'ss_total' => $ss_total,
            ];

            //likert sclae rating grandtotal

            $lsr_grand_total =  $lsr_grand_total + $lsr_total;
            $x_totals[$dimensionId] = [
                'x_total_score' => $x_grand_total,
            ];

            $lsr_total = number_format($lsr_total, 2);

            $likert_scale_rating_totals[$dimensionId] = [
                'lsr_total' => $lsr_total,
            ];

            $y_totals[$dimensionId] = [
                'vs_total' => $vs_total,
                's_total' => $s_total,
                'n_total' => $n_total,
                'd_total' => $d_total,
                'vd_total' => $vd_total,
            ];
         
            $grand_na_total+=$na_total;  

            $grand_vs_total+=$vs_total;
            $grand_s_total+=$s_total;
            $grand_n_total+=$n_total;
            $grand_d_total+=$d_total;
            $grand_vd_total+=$vd_total;       
                     
            // PART II :
            $vi_total = $date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $i_total = $date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $mi_total = $date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $li_total = $date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $nai_total = $date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->count();
        
            $importance_rate_score_totals[$dimensionId] = [
                'vi_total' => $vi_total,
                'i_total' => $i_total,
                'mi_total' => $mi_total,
                'li_total' => $li_total,
                'nai_total' => $nai_total,
            ];

            $x_vi_total = $vi_total * 5; 
            $x_i_total = $i_total * 4; 
            $x_mi_total = $mi_total * 3; 
            $x_li_total = $li_total * 2; 
            $x_nai_total = $nai_total * 1;
            $x_importance_total = $x_vi_total + $x_i_total + $x_mi_total + $x_li_total + $x_nai_total  ; 
            
            //right side total importance rate scores 
            $x_importance_totals[$dimensionId] = [
                'x_importance_total_score' => $x_importance_total,
            ];
            
            // Likert Scale RAting 
            if($x_importance_total != 0){
                $ilsr_total = $x_importance_total / $total_respondents;
            }
            $ilsr_total = number_format($ilsr_total, 2);

            $importance_ilsr_totals[$dimensionId] = [
                'ilsr_total' => $ilsr_total,
            ];
 
            // GAP = attributes total score minus importance of attributes total score
            $gap_total=  $ilsr_total - $lsr_total;
            $gap_total = number_format($gap_total, 2);

            $gap_totals[$dimensionId] = [
                'gap_total' => $gap_total,
            ];

            $gap_grand_total += $gap_total;
            $gap_grand_total = number_format($gap_grand_total, 2);

            // WF = (importance LS Rating divided by importance grand total  of ls rating) * 100
            if($ilsr_total != 0){
                $wf_total =  ($ilsr_total / $ilsr_grand_total) * 100;
            }
            $wf_total = number_format($wf_total, 2);
            $wf_totals[$dimensionId] = [
                'wf_total' => $wf_total,
            ];

            // WS = (SS * WF) / 100  
            $ws_total = ($ss_total * $wf_total) / 100;   
            $ws_grand_total = $ws_grand_total + $ws_total;
            $ws_total = number_format($ws_total, 2);
            $ws_grand_total = number_format($ws_grand_total, 2);
            $ws_totals[$dimensionId] = [
                'ws_total' => $ws_total,
            ];

          

        }

        //Calculate total number of respondents/customer who rated VS/S
        // Formula ----> get the sum of total respondents for each dimension who rated VS or S and divide it to dimension total count
        // here is 9 because I include the overall data in the dimensions

        $vss_total = $grand_vs_total +  $grand_s_total + $grand_na_total;
        $total_vss_respondents = $vss_total / $dimension_count;     
        $total_vss_respondents = round($total_vss_respondents);      

        // round off Likert Scale Rating grand total and control decimal to 2 
        $lsr_grand_total = ($lsr_grand_total/ $dimension_count);
        $lsr_grand_total = number_format($lsr_grand_total, 2);      
        

        // table below TOTAL SCORES
        $grand_vs_total =   $grand_vs_total * 5;
        $grand_s_total =   $grand_s_total * 4;
        $grand_n_total =   $grand_n_total * 3;
        $grand_d_total =   $grand_d_total * 2;
        $grand_vd_total =   $grand_vd_total * 1;


        $x_grand_total =  $grand_vs_total +  $grand_s_total + $grand_n_total +  $grand_d_total +   $grand_vd_total;

 
        //Percentage of Respondents/Customers who rated VS/S: 
        // = total no. of respondents / total no. respondets who rated vs/s * 100
        $percentage_vss_respondents  = 0;
        if($total_respondents != 0){
            $percentage_vss_respondents  = ($total_vss_respondents/$total_respondents) * 100;
        }
        $percentage_vss_respondents = number_format( $percentage_vss_respondents , 2);

        $customer_satisfaction_rating = 0;
        if($total_vss_respondents != 0){
            $customer_satisfaction_rating = (($grand_vs_total+$grand_s_total)/$x_grand_total) * 100;
        }
        $customer_satisfaction_rating = number_format( $customer_satisfaction_rating , 2);

        // Customer Satisfaction Index (CSI) = (ws grand total / 5) * 100
        $customer_satisfaction_index = 0;
        if($ws_grand_total != 0){
            $customer_satisfaction_index = ($ws_grand_total/5) * 100;
        }
        $customer_satisfaction_index = number_format($customer_satisfaction_index , 2);

        if($customer_satisfaction_index > 100){
            $customer_satisfaction_index = number_format(100 , 2);
        }

        //Percentage of Promoters = number of promoters / total respondents
        $percentage_promoters = 0;
        if($total_promoters != 0){
            $percentage_promoters = number_format((($total_promoters / $total_respondents) * 100), 2);
        }

        //Percentage of Promoters = number of promoters / total respondents
        $percentage_detractors = 0;
        if($total_detractors != 0){
            $percentage_detractors = number_format((($total_detractors / $total_respondents) * 100),2);
        }

        // Net Promotion Scores(NPS) = Percentage of Promoters−Percentage of Detractors
        $net_promoter_score =  number_format(($percentage_promoters - $percentage_detractors),2);
        //Respondents list
        $data = CARResource::collection($respondents_list);

        //comments and  complaints
        $comment_list = CustomerComment::whereIn('customer_id', $customer_ids)
                                    ->whereMonth('created_at', $numericMonth)
                                    ->whereYear('created_at', $request->selected_year)->get();
        
        $comments = $comment_list->where('comment','!=','')->pluck('comment'); 

        $total_comments = $comment_list->where('comment','!=','')->count();
        $total_complaints = $comment_list->where('is_complaint',1)->count();


        //send response to front end
        return Inertia::render('CSI/Index')
            ->with('user', $user)
            ->with('cc_data', $cc_data)
            ->with('assignatorees', $assignatorees)
            ->with('users', $users)
            ->with('sub_unit', $sub_unit)
            ->with('unit_pstos', $unit_pstos)
            ->with('sub_unit_pstos', $sub_unit_pstos)
            ->with('sub_unit_types', $sub_unit_types)
            ->with('dimensions', $dimensions)
            ->with('service', $request->service)
            ->with('unit', $request->unit)
            ->with('respondents_list',$data)
            ->with('y_totals',$y_totals)
            ->with('grand_vs_total',$grand_vs_total)
            ->with('grand_s_total',$grand_s_total)
            ->with('grand_n_total',$grand_n_total)
            ->with('grand_d_total',$grand_d_total)
            ->with('grand_vd_total',$grand_vd_total)
            ->with('x_totals',$x_totals)
            ->with('x_grand_total',$x_grand_total)
            ->with('likert_scale_rating_totals',$likert_scale_rating_totals)
            ->with('lsr_grand_total',$lsr_grand_total)
            ->with('importance_rate_score_totals',$importance_rate_score_totals)
            ->with('x_importance_totals', $x_importance_totals)
            ->with('importance_ilsr_totals', $importance_ilsr_totals)
            ->with('gap_totals', $gap_totals)
            ->with('gap_grand_total', $gap_grand_total)
            ->with('wf_totals', $wf_totals)
            ->with('ss_totals', $ss_totals)
            ->with('wf_totals', $wf_totals)
            ->with('ws_totals', $ws_totals)
            ->with('total_respondents', $total_respondents)
            ->with('total_vss_respondents', $total_vss_respondents)
            ->with('percentage_vss_respondents', $percentage_vss_respondents)
            ->with('customer_satisfaction_rating', $customer_satisfaction_rating)
            ->with('customer_satisfaction_index', $customer_satisfaction_index)
            ->with('net_promoter_score', $net_promoter_score)
            ->with('percentage_promoters', $percentage_promoters)
            ->with('percentage_detractors', $percentage_detractors)
            ->with('total_comments', $total_comments)
            ->with('total_complaints', $total_complaints)
            ->with('comments', $comments)
            ->with('request', $request);    
    }   
   
    // QUARTERLY || FIRST, SECOND , THIRD AND FOURTH QUARTER
    public function generateCSIByQuarter($request, $region_id, $psto_id)
    {
        $unitData = $this->getUnitData($request);
        $sub_unit = $unitData['sub_unit'];
        $unit_pstos = $unitData['unit_pstos'];
        $sub_unit_pstos = $unitData['sub_unit_pstos'];
        $sub_unit_types = $unitData['sub_unit_types'];

        //get user
        $user = Auth::user();
        //get assignatoree list
        $assignatorees = Assignatorees::all();

        //get users lists
        $users = User::all();
        
        $date_range = null;
        $customer_recommendation_ratings = null;
        $respondents_list = null; 
            
        $service_id = $request->service;
        $unit_id = $request->unit_id;
        $sub_unit_id = $request->selected_sub_unit;
        $client_type = $request->client_type; 
        $sub_unit_type = $request->sub_unit_type; 

       $startDate = null;
       $endDate = null;
       $numeric_first_month = 0;
       $numeric_second_month = 0;
       $numeric_third_month = 0;

        // Retrieve records for the specified quarter and year
        switch($request->selected_quarter){
            case 'FIRST QUARTER':
                $startDate = Carbon::create($request->selected_year, 1, 1)->startOfDay();
                $endDate = Carbon::create($request->selected_year, 3, 31)->endOfDay();

                $numeric_first_month = 1;
                $numeric_second_month = 2;
                $numeric_third_month = 3;

            break;
            case 'SECOND QUARTER':
                $startDate = Carbon::create($request->selected_year, 4, 1)->startOfDay();
                $endDate = Carbon::create($request->selected_year, 6, 31)->endOfDay();

                $numeric_first_month = 4;
                $numeric_second_month = 5;
                $numeric_third_month = 6;
            break;
            case 'THIRD QUARTER':
                $startDate = Carbon::create($request->selected_year, 7, 1)->startOfDay();
                $endDate = Carbon::create($request->selected_year, 9, 31)->endOfDay();

                $numeric_first_month = 7;
                $numeric_second_month = 8;
                $numeric_third_month = 9;
            break;
            case 'FOURTH QUARTER':
                $startDate = Carbon::create($request->selected_year, 10, 1)->startOfDay();
                $endDate = Carbon::create($request->selected_year, 12, 31)->endOfDay();

                $numeric_first_month = 10;
                $numeric_second_month = 11;
                $numeric_third_month = 12;
            break;

            default:
                dd('no quarter selected'); 
        }  
        
        // search and check list of forms query  and get customers list ids
        $customer_ids = $this->querySearchCSF($region_id, $service_id, $unit_id ,$sub_unit_id , $psto_id, $client_type, $sub_unit_type );

        // get CC Data
        $cc_query = $this->getCitizenCharterByQuarter($request, $customer_ids, $startDate ,$endDate);

        //calculate CC Data
        $cc_data = $this->calculateCC($cc_query);

        // get Customer Attribute Rating with specific quarter date range
        $date_range = $this->getCustomerAttributeRatingByQuarter($request,$customer_ids,$startDate ,$endDate);
        // get first month of Specific Quarter Selected 
        $first_month = $this->getCustomerAttributeRatingByQuarterWithMonth($request, $customer_ids, $numeric_first_month);
        // get second month of Specific Quarter Selected 
        $second_month = $this->getCustomerAttributeRatingByQuarterWithMonth($request, $customer_ids, $numeric_second_month);
        // get third month of Specific Quarter Selected 
        $third_month = $this->getCustomerAttributeRatingByQuarterWithMonth($request, $customer_ids, $numeric_third_month);

        // get Customer Recommendation Rating with specific quarter date range 
        $customer_recommendation_ratings = $this->getCustomerRecommendationRatingByQuarter($request, $customer_ids,$startDate ,$endDate);

        // get First Month Customer Recommendation Rating with specific quarter date range 
        $first_month_crr = $this->getCustomerRecommendationRatingByQuarterWithMonth($request, $customer_ids, $numeric_first_month);
        // get First Month Customer Recommendation Rating with specific quarter date range 
        $second_month_crr = $this->getCustomerRecommendationRatingByQuarterWithMonth($request, $customer_ids, $numeric_second_month);
        // get First Month Customer Recommendation Rating with specific quarter date range 
        $third_month_crr = $this->getCustomerRecommendationRatingByQuarterWithMonth($request, $customer_ids, $numeric_third_month);

        // get respondents list
        $respondents_list = $this->getRespondents($request, $customer_ids,$startDate ,$endDate);            

        $dimensions = Dimension::all();
        $dimension_count = $dimensions->count();
        $grand_total_raw_points = 0;
        $vs_grand_total_score =0;
        $s_grand_total_score = 0;
        $ndvd_grand_total_score = 0;
        $grand_total_score =0;

        $vs_grand_total_raw_points = 0;
        $s_grand_total_raw_points = 0;
        $ndvd_grand_total_raw_points = 0;
        $lsr_grand_total = 0;
        $lsr_average = 0;

        //Importance total raw points  
        $vi_grand_total_raw_points = 0;
        $i_grand_total_raw_points = 0;
        $misinai_grand_total_raw_points = 0;

        //Importance grand total score
        $vi_grand_total_score=0;
        $i_grand_total_score =0;
        $misinai_grand_total_score = 0;

        $first_month_vs_grand_total = 0;
        $second_month_vs_grand_total =  0;
        $third_month_vs_grand_total =  0;

        $first_month_s_grand_total = 0;
        $second_month_s_grand_total =  0;
        $third_month_s_grand_total =  0;

        $first_month_ndvd_grand_total = 0;
        $second_month_ndvd_grand_total =  0;
        $third_month_ndvd_grand_total =  0;

        $first_month_na_grand_total = 0;
        $second_month_na_grand_total =  0;
        $third_month_na_grand_total =  0;

        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            //PART I

            // First Month total responses with its dimensions and rate score
            $first_month_na_total = $first_month->where('rate_score', 6)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();

            $first_month_vs_total = $first_month->where('rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $first_month_s_total = $first_month->where('rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $first_month_n_total = $first_month->where('rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $first_month_d_total = $first_month->where('rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $first_month_vd_total = $first_month->where('rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          

            $first_month_grand_total =  $first_month_vs_total + $first_month_s_total + $first_month_n_total + $first_month_d_total + $first_month_vd_total ; 

            // Second Month total responses with its dimensions and rate score
            $second_month_na_total = $second_month->where('rate_score', 6)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();

            $second_month_vs_total = $second_month->where('rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $second_month_s_total = $second_month->where('rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $second_month_n_total = $second_month->where('rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $second_month_d_total = $second_month->where('rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $second_month_vd_total = $second_month->where('rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          

            $second_month_grand_total =  $second_month_vs_total + $second_month_s_total + $second_month_n_total + $second_month_d_total + $second_month_vd_total ; 

            // Third Month total responses with its dimensions and rate score
            $third_month_na_total = $third_month->where('rate_score', 6)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();

            $third_month_vs_total = $third_month->where('rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $third_month_s_total = $third_month->where('rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $third_month_n_total = $third_month->where('rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $third_month_d_total = $third_month->where('rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $third_month_vd_total = $third_month->where('rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          

            $third_month_grand_total =  $third_month_vs_total + $third_month_s_total + $third_month_n_total + $third_month_d_total + $third_month_vd_total ; 

            // Quarterly Very Satisfied total with specific dimension or attribute
            $vs_totals[$dimensionId] = [
                'first_month_vs_total' => $first_month_vs_total,
                'second_month_vs_total' => $second_month_vs_total,
                'third_month_vs_total' => $third_month_vs_total,
            ];
            // Quarterly Satisfied total with specific dimension or attribute
            $s_totals[$dimensionId] = [
                'first_month_s_total' => $first_month_s_total,
                'second_month_s_total' => $second_month_s_total,
                'third_month_s_total' => $third_month_s_total,
            ];

             // Quarterly Neutral total with specific dimension or attribute
            $n_totals[$dimensionId] = [
                'first_month_n_total' => $first_month_n_total,
                'second_month_n_total' => $second_month_n_total,
                'third_month_n_total' => $third_month_n_total,
            ];

            // Quarterly Disatisfied total with specific dimension or attribute
            $d_totals[$dimensionId] = [
                'first_month_d_total' => $first_month_d_total,
                'second_month_d_total' => $second_month_d_total,
                'third_month_d_total' => $third_month_d_total,
            ];

             // Quarterly Very Disatisfied total with specific dimension or attribute
            $vd_totals[$dimensionId] = [
                'first_month_vd_total' => $first_month_vd_total,
                'second_month_vd_total' => $second_month_vd_total,
                'third_month_vd_total' => $third_month_vd_total,
            ];

            // Quarterly grand totals with specific dimension or attribute
            $grand_totals[$dimensionId] = [
                'first_month_grand_total' => $first_month_grand_total,
                'second_month_grand_total' => $second_month_grand_total,
                'third_month_grand_total' => $third_month_grand_total,
            ];

            //Total Raw Points totals
            $vs_total_raw_points = $first_month_vs_total + $second_month_vs_total + $third_month_vs_total;
            $s_total_raw_points = $first_month_s_total + $second_month_s_total + $third_month_s_total;
            $n_total_raw_points = $first_month_n_total + $second_month_n_total + $third_month_n_total;
            $d_total_raw_points =$first_month_d_total + $second_month_d_total + $third_month_d_total;
            $vd_total_raw_points =$first_month_vd_total + $second_month_vd_total + $third_month_vd_total;
            $total_raw_points = $vs_total_raw_points + $s_total_raw_points + $n_total_raw_points +  $d_total_raw_points +  $vd_total_raw_points;           

            $vs_grand_total_raw_points += $vs_total_raw_points;
            $s_grand_total_raw_points +=  $s_total_raw_points;

            $ndvd_grand_total_raw_points +=  $n_total_raw_points + $d_total_raw_points + $vd_total_raw_points;
            $grand_total_raw_points+= $total_raw_points;

            $trp_totals[$dimensionId] = [
                'vs_total_raw_points' => $vs_total_raw_points,
                's_total_raw_points' => $s_total_raw_points,
                'n_total_raw_points' => $n_total_raw_points,
                'd_total_raw_points' => $d_total_raw_points,
                'vd_total_raw_points' => $vd_total_raw_points,
                'total_raw_points' => $total_raw_points,
            ];

            //Part 1 Quarter 1 total scores
            $x_vs_total = $vs_total_raw_points * 5; 
            $x_s_total = $s_total_raw_points * 4; 
            $x_n_total = $n_total_raw_points * 3; 
            $x_d_total = $d_total_raw_points * 3; 
            $x_vd_total = $vd_total_raw_points * 1; 
            $x_total_score =  $x_vs_total +  $x_s_total +  $x_n_total +  $x_d_total + $x_vd_total;
            
            $vs_grand_total_score += $x_vs_total ;
            $s_grand_total_score += $x_s_total ;
            $ndvd_grand_total_score += $x_n_total +  $x_d_total + $x_vd_total ;
            $grand_total_score += $x_total_score ;

            $p1_total_scores[$dimensionId] = [ 
                'x_vs_total' => $x_vs_total,
                'x_s_total' => $x_s_total,
                'x_n_total' => $x_n_total,
                'x_d_total' => $x_d_total,
                'x_vd_total' => $x_vd_total, 
                'x_total_score' => $x_total_score,
            ];

            // Likert Scale Rating = total score / grand total of total raw points
            if($total_raw_points != 0 ){
                $vs_lsr_total =   number_format(($x_vs_total  /  $total_raw_points),2);
                $s_lsr_total =   number_format(($x_s_total /  $total_raw_points),2);
                $n_lsr_total =   number_format(($x_n_total /  $total_raw_points),2);
                $d_lsr_total =   number_format(($x_d_total /  $total_raw_points),2);
                $vd_lsr_total =  number_format(($x_vd_total /  $total_raw_points),2);
                $lsr_total =  number_format(($vs_lsr_total +  $s_lsr_total  +  $n_lsr_total  +  $d_lsr_total  +  $vd_lsr_total),2);
                $lsr_grand_total +=  $lsr_total;
                $lsr_grand_total =number_format(($lsr_grand_total), 2);
                $lsr_average =  number_format(($lsr_grand_total / $dimensionId), 2);
            }
            else{
                $vs_lsr_total =  0;
                $s_lsr_total =  0;
                $n_lsr_total =  0;
                $d_lsr_total = 0;
                $vd_lsr_total =  0;
                $lsr_total = 0;
                $lsr_grand_total = 0;
                $lsr_average =  0;
            }

            $lsr_totals[$dimensionId] = [
                'vs_lsr_total' => $vs_lsr_total,
                's_lsr_total' => $s_lsr_total,
                'n_lsr_total' => $n_lsr_total,
                'd_lsr_total' => $d_lsr_total,
                'vd_lsr_total' => $vd_lsr_total,
                'lsr_total' => $lsr_total,
            ];
            
              // PART II
              // first month total responses with its dimensions and rate score
              $first_month_vi_total = $first_month->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $first_month_i_total = $first_month->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $first_month_mi_total = $first_month->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $first_month_si_total = $first_month->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $first_month_nai_total = $first_month->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
  
              $first_month_i_grand_total =  $first_month_vi_total + $first_month_i_total + $first_month_mi_total + $first_month_si_total + $first_month_nai_total ; 
  
              //  second_month importance total responses with its dimensions and rate score
              $second_month_vi_total = $second_month->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $second_month_i_total = $second_month->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $second_month_mi_total = $second_month->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $second_month_si_total = $second_month->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $second_month_nai_total = $second_month->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
  
              $second_month_i_grand_total =  $second_month_vi_total + $second_month_i_total + $second_month_mi_total + $second_month_si_total + $second_month_nai_total ; 
  
              //  third month total responses with its dimensions and rate score
              $third_month_vi_total = $third_month->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $third_month_i_total = $third_month->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $third_month_mi_total = $third_month->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $third_month_si_total = $third_month->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $third_month_nai_total = $third_month->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
  
              $third_month_i_grand_total =  $third_month_vi_total + $third_month_i_total + $third_month_mi_total + $third_month_si_total + $third_month_nai_total ; 

                // Very Important total with specific dimention or attribute
                $vi_totals[$dimensionId] = [
                    'first_month_vi_total' => $first_month_vi_total,
                    'second_month_vi_total' => $second_month_vi_total,
                    'third_month_vi_total' => $third_month_vi_total,
                ];
                //Important total with specific dimention or attribute
                $i_totals[$dimensionId] = [
                    'first_month_i_total' => $first_month_i_total,
                    'second_month_i_total' => $second_month_i_total,
                    'third_month_i_total' => $third_month_i_total,
                ];
                // Moderately Important total with specific dimention or attribute
                $mi_totals[$dimensionId] = [
                    'first_month_mi_total' => $first_month_mi_total,
                    'second_month_mi_total' => $second_month_mi_total,
                    'third_month_mi_total' => $third_month_mi_total,
                ];
                // slightly Important total with specific dimention or attribute
                $si_totals[$dimensionId] = [
                    'first_month_si_total' => $first_month_si_total,
                    'second_month_si_total' => $second_month_si_total,
                    'third_month_si_total' => $third_month_si_total,
                ];

                $nai_totals[$dimensionId] = [
                    'first_month_nai_total' => $first_month_nai_total,
                    'second_month_nai_total' => $second_month_nai_total,
                    'third_month_nai_total' => $third_month_nai_total,
                ];

                $i_grand_totals[$dimensionId] = [
                    'first_month_i_grand_total' => $first_month_i_grand_total,
                    'second_month_i_grand_total' => $second_month_i_grand_total,
                    'third_month_i_grand_total' => $third_month_i_grand_total,
                ];

                
            //Importance Total Raw Points totals
            $vi_total_raw_points = $first_month_vi_total + $second_month_vi_total + $third_month_vi_total;
            $i_total_raw_points = $first_month_i_total + $second_month_i_total + $third_month_i_total;
            $mi_total_raw_points = $first_month_mi_total + $second_month_mi_total + $third_month_mi_total;
            $si_total_raw_points = $first_month_si_total + $second_month_si_total + $third_month_si_total;
            $nai_total_raw_points = $first_month_nai_total + $second_month_nai_total + $third_month_nai_total;
            $total_raw_points = $vi_total_raw_points + $i_total_raw_points + $mi_total_raw_points +  $si_total_raw_points +  $nai_total_raw_points;           

            $vi_grand_total_raw_points += $vi_total_raw_points;
            $i_grand_total_raw_points +=  $i_total_raw_points;
            $misinai_grand_total_raw_points +=  $mi_total_raw_points + $si_total_raw_points + $nai_total_raw_points;
            $grand_total_raw_points+= $total_raw_points;

           
            $i_trp_totals[$dimensionId] = [
                'vi_total_raw_points' => $vi_total_raw_points,
                'i_total_raw_points' => $i_total_raw_points,
                'mi_total_raw_points' => $mi_total_raw_points,
                'si_total_raw_points' => $si_total_raw_points,
                'nai_total_raw_points' => $nai_total_raw_points,
                'total_raw_points' => $total_raw_points,
            ];

        

            //Part 1 Quarter 1 total scores
            $x_vi_total = $vi_total_raw_points * 5; 
            $x_i_total = $i_total_raw_points * 4; 
            $x_mi_total = $mi_total_raw_points * 3; 
            $x_si_total = $si_total_raw_points * 3; 
            $x_nai_total = $nai_total_raw_points * 1; 
            $x_i_total_score =  $x_vi_total +  $x_i_total +  $x_mi_total +  $x_si_total + $x_nai_total;
            

            $vi_grand_total_score += $x_vi_total ;
            $i_grand_total_score += $x_si_total ;
            $misinai_grand_total_score += $x_mi_total +  $x_si_total + $x_nai_total ;

            $i_total_scores[$dimensionId] = [ 
                'x_vi_total' => $x_vi_total,
                'x_i_total' => $x_i_total,
                'x_mi_total' => $x_mi_total,
                'x_si_total' => $x_si_total,
                'x_nai_total' => $x_nai_total, 
                'x_i_total_score' => $x_i_total_score,
            ];

            // Calculate all respondents who rated VS 
            $first_month_vs_grand_total +=  $first_month_vs_total;
            $second_month_vs_grand_total +=  $second_month_vs_total;
            $third_month_vs_grand_total +=  $third_month_vs_total; 

            // Calculate all respondents who rated S
            $first_month_s_grand_total +=  $first_month_s_total;
            $second_month_s_grand_total +=  $second_month_s_total;
            $third_month_s_grand_total +=  $third_month_s_total; 

            // Calculate all respondents who rated NDVD
            $first_month_ndvd_total = $first_month_n_total + $first_month_d_total + $first_month_vd_total;
            $second_month_ndvd_total = $second_month_n_total + $second_month_d_total + $second_month_vd_total;
            $third_month_ndvd_total = $third_month_n_total + $third_month_d_total + $third_month_vd_total;

            $first_month_ndvd_grand_total +=  $first_month_ndvd_total;
            $second_month_ndvd_grand_total +=  $second_month_ndvd_total;
            $third_month_ndvd_grand_total +=  $third_month_ndvd_total; 

            // Calculate all respondents who rated n/a
            $first_month_na_grand_total +=  $first_month_na_total;
            $second_month_na_grand_total +=  $second_month_na_total;
            $third_month_na_grand_total +=  $third_month_na_total; 
   
        }

    
        // Calculate all respondents 
        $first_month_grand_total =  $first_month_vs_grand_total + $first_month_s_grand_total +   $first_month_ndvd_grand_total;     
        $second_month_grand_total =  $second_month_vs_grand_total + $second_month_s_grand_total +  $second_month_ndvd_grand_total;
        $third_month_grand_total =  $third_month_vs_grand_total + $third_month_s_grand_total +  $third_month_ndvd_grand_total;

        //Calculate total number of respondents/customer who rated VS/S include N/A
        // Formula ----> get the sum of total respondents for each dimension who rated VS or S and divide it to dimension total count
        // here is 9 because I include the overall data in the dimensions

        $first_month_vss_total = $first_month_vs_grand_total +  $first_month_s_grand_total + $first_month_na_grand_total;
        $first_month_total_vss_respondents = $first_month_vss_total / 9;     
        $first_month_total_vss_respondents = round($first_month_total_vss_respondents);  

        $second_month_vss_total = $second_month_vs_grand_total +  $second_month_s_grand_total + $second_month_na_grand_total;
        $second_month_total_vss_respondents = $second_month_vss_total / 9;     
        $second_month_total_vss_respondents = round($second_month_total_vss_respondents);  

        $third_month_vss_total = $third_month_vs_grand_total +  $third_month_s_grand_total + $third_month_na_grand_total;
        $third_month_total_vss_respondents = $third_month_vss_total / 9;     
        $third_month_total_vss_respondents = round($third_month_total_vss_respondents);  

        $total_vss_respondents =  $first_month_total_vss_respondents + $second_month_total_vss_respondents +   $third_month_total_vss_respondents;

        // Total No. of Very Satisfied (VS) Responses of First Quarte
        // -- 1st month
        $first_month_total_vs_respondents = $first_month->where('rate_score',5)->groupBy('customer_id')->count();
        // -- 2nd month
        $second_month_total_vs_respondents = $second_month->where('rate_score',5)->groupBy('customer_id')->count();
        // -- 3rd month
        $third_month_total_vs_respondents = $third_month->where('rate_score',5)->groupBy('customer_id')->count();

        // Total No. of Satisfied (S) Responses
       // -- octy
       $first_month_total_s_respondents = $first_month->where('rate_score',4)->groupBy('customer_id')->count();
       // -- novust
       $second_month_total_s_respondents = $second_month->where('rate_score',4)->groupBy('customer_id')->count();
       // -- dectember
       $third_month_total_s_respondents = $third_month->where('rate_score',4)->groupBy('customer_id')->count();

        // Total No. of Other (N, D, VD) Responses
        // -- octy
        $first_month_total_ndvd_respondents = $first_month->where('rate_score','<', 4)->groupBy('customer_id')->count();
        // -- novust
        $second_month_total_ndvd_respondents = $second_month->where('rate_score','<', 4)->groupBy('customer_id')->count();
        // -- dectember
        $third_month_total_ndvd_respondents = $third_month->where('rate_score','<', 4)->groupBy('customer_id')->count();
          
        // Total No. of All Responses
        // -- octy
        $first_month_total_respondents = $first_month->groupBy('customer_id')->count();
        // -- novust
        $second_month_total_respondents = $second_month->groupBy('customer_id')->count();
        // -- dectember
        $third_month_total_respondents = $third_month->groupBy('customer_id')->count();

        //Total respondents /Customers
        $total_respondents = $date_range->groupBy('customer_id')->count();
    
        // Frst quarter total number of promoters or respondents who rated 9-10 in recommendation rating
        $total_promoters = $customer_recommendation_ratings->where('recommend_rate_score', '>','6')->groupBy('customer_id')->count();
        // 1st month
        $first_month_total_promoters = $first_month_crr->whereBetween('recommend_rate_score', [7, 10])->groupBy('customer_id')->count();
        // 2nd Month
        $second_month_total_promoters = $second_month_crr->whereBetween('recommend_rate_score', [7, 10])->groupBy('customer_id')->count();
        // 3rd month
        $third_month_total_promoters = $third_month_crr->whereBetween('recommend_rate_score', [7, 10])->groupBy('customer_id')->count();
        
        // total number of detractors or respondents who rated 0-6 in recommendation rating
        $total_detractors = $customer_recommendation_ratings->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();
       // 1st month
        $first_month_total_detractors = $first_month_crr->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();
        // 2nd Month
        $second_month_total_detractors = $second_month_crr->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();
        // 3rd month
        $third_month_total_detractors = $third_month_crr->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();
  
        //Percentage of Respondents/Customers who rated VS/S = total no. of respondents / total no. respondets who rated vs/s * 100
        $percentage_vss_respondents  = 0;
        if($total_respondents != 0){
            $percentage_vss_respondents  = ($total_vss_respondents / $total_respondents) * 100;
        }
        $percentage_vss_respondents = number_format( $percentage_vss_respondents , 2);

        $customer_satisfaction_rating = 0;
        if($total_vss_respondents != 0){
            $customer_satisfaction_rating = (($vs_grand_total_score + $s_grand_total_score)/$grand_total_score) * 100;
        }
        $customer_satisfaction_rating = number_format( $customer_satisfaction_rating , 2);

        //Percentage of Promoters = number of promoters / total respondents
        $percentage_promoters = 0;
        //  Percentage promoters
        $first_month_percentage_promoters = 0.00;
        $second_month_percentage_promoters = 0.00;
        $third_month_percentage_promoters = 0.00;

        // Percentage of promoter
        $first_month_percentage_detractors = 0.00;
        $second_month_percentage_detractors = 0.00;
        $third_month_percentage_detractors = 0.00;

        //nps
        $first_month_net_promoter_score =  0.00;
        $second_month_net_promoter_score = 0.00;
        $third_month_net_promoter_score =  0.00;

        //average
        $ave_net_promoter_score = 0.00;
        $average_percentage_promoters =  0.00;
        $average_percentage_detractors =  0.00;


        if($total_respondents != 0 ){
            $percentage_promoters = number_format((($total_promoters / $total_respondents) * 100), 2);
            if($first_month_total_respondents !=0){
                $first_month_percentage_promoters = number_format((($first_month_total_promoters / $first_month_total_respondents) * 100), 2);
                $first_month_percentage_detractors = number_format((($first_month_total_detractors / $first_month_total_respondents) * 100),2);
            }

            if($second_month_total_respondents !=0){
                $second_month_percentage_promoters = number_format((($second_month_total_promoters / $second_month_total_respondents) * 100), 2);
                $second_month_percentage_detractors = number_format((($second_month_total_detractors / $second_month_total_respondents) * 100),2);
            }
           
            if($third_month_total_respondents != 0 ){
                $third_month_percentage_promoters = number_format((($third_month_total_promoters / $third_month_total_respondents) * 100), 2);
                $third_month_percentage_detractors = number_format((($third_month_total_detractors / $third_month_total_respondents) * 100),2);
            }
          
        
            //Percentage of Promoters = number of promoters / total respondents
            $percentage_detractors = number_format((($total_detractors / $total_respondents) * 100),2);

            // Net Promotion Scores(NPS) = Percentage of Promoters−Percentage of Detractors
            $first_month_net_promoter_score =  number_format(($first_month_percentage_promoters - $first_month_percentage_detractors),2);
            $second_month_net_promoter_score =  number_format(($second_month_percentage_promoters - $second_month_percentage_detractors),2);
            $third_month_net_promoter_score =  number_format(($third_month_percentage_promoters - $third_month_percentage_detractors),2);

            $ave_net_promoter_score =  number_format((($first_month_net_promoter_score + $second_month_net_promoter_score + $third_month_net_promoter_score)/ 3),2);
            $average_percentage_promoters =  number_format((($first_month_percentage_promoters + $second_month_percentage_promoters + $third_month_percentage_promoters)/ 3),2);
            $average_percentage_detractors =  number_format((($first_month_percentage_detractors + $second_month_percentage_detractors + $third_month_percentage_detractors)/ 3),2);

        }

        // get Monthly CSI 
        
        $first_month_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, $numeric_first_month);
        $second_month_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, $numeric_second_month);
        $third_month_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, $numeric_third_month);

        $customer_satisfaction_index = number_format((($first_month_csi + $second_month_csi +  $third_month_csi)/3), 2);
     

        if($customer_satisfaction_index > 100){
            $customer_satisfaction_index = number_format(100 , 2);
        }

         //comments and  complaints
        $comment_list = CustomerComment::whereIn('customer_id', $customer_ids)
                                        ->whereBetween('created_at', [$startDate, $endDate])
                                        ->whereYear('created_at', $request->selected_year)->get();

        $comments = $comment_list->where('comment','!=','')->pluck('comment'); 


        $total_comments = $comment_list->where('comment','!=','')->count();
        $total_complaints = $comment_list->where('is_complaint',1)->count();

        //Respondents list
        $data = CARResource::collection($respondents_list);

        //send response to front end
        return Inertia::render('CSI/Index')
            ->with('cc_data', $cc_data)
            ->with('user', $user)
            ->with('assignatorees', $assignatorees)
            ->with('users', $users)
            ->with('sub_unit', $sub_unit)
            ->with('unit_pstos', $unit_pstos)
            ->with('sub_unit_pstos', $sub_unit_pstos)
            ->with('sub_unit_types', $sub_unit_types)
            ->with('dimensions', $dimensions)
            ->with('service', $request->service)
            ->with('unit', $request->unit)
            ->with('respondents_list',$data)
            ->with('trp_totals', $trp_totals)
            ->with('grand_total_raw_points', $grand_total_raw_points)
            ->with('vs_grand_total_raw_points', $vs_grand_total_raw_points)
            ->with('s_grand_total_raw_points', $s_grand_total_raw_points)
            ->with('ndvd_grand_total_raw_points', $ndvd_grand_total_raw_points)
            ->with('p1_total_scores', $p1_total_scores)
            ->with('vs_grand_total_score', $vs_grand_total_score) 
            ->with('s_grand_total_score', $s_grand_total_score)
            ->with('ndvd_grand_total_score', $ndvd_grand_total_score) 
            ->with('grand_total_score', $grand_total_score) 
            ->with('lsr_totals', $lsr_totals)
            ->with('lsr_grand_total', $lsr_grand_total)
            ->with('lsr_average', $lsr_average ) 
            ->with('vs_totals', $vs_totals)
            ->with('s_totals', $s_totals)
            ->with('n_totals', $n_totals)
            ->with('d_totals', $d_totals)
            ->with('vd_totals', $vd_totals)
            ->with('grand_totals', $grand_totals)
            ->with('first_month_total_vs_respondents', $first_month_total_vs_respondents)
            ->with('second_month_total_vs_respondents', $second_month_total_vs_respondents)
            ->with('third_month_total_vs_respondents', $third_month_total_vs_respondents)
            ->with('first_month_total_s_respondents', $first_month_total_s_respondents)
            ->with('second_month_total_s_respondents', $second_month_total_s_respondents)
            ->with('third_month_total_s_respondents', $third_month_total_s_respondents)
            ->with('first_month_total_ndvd_respondents', $first_month_total_ndvd_respondents)
            ->with('second_month_total_ndvd_respondents', $second_month_total_ndvd_respondents)
            ->with('third_month_total_ndvd_respondents', $third_month_total_ndvd_respondents)
            ->with('first_month_total_respondents', $first_month_total_respondents)
            ->with('second_month_total_respondents', $second_month_total_respondents)
            ->with('third_month_total_respondents', $third_month_total_respondents)
            ->with('total_respondents', $total_respondents)
            ->with('total_vss_respondents', $total_vss_respondents)
            ->with('percentage_vss_respondents', $percentage_vss_respondents)
            ->with('total_promoters', $total_promoters)
            ->with('total_detractors', $total_detractors)
            ->with('vi_totals', $vi_totals)
            ->with('i_totals', $i_totals)
            ->with('mi_totals', $mi_totals)
            ->with('si_totals', $si_totals)
            ->with('nai_totals', $nai_totals)
            ->with('i_grand_totals', $i_grand_totals)
            ->with('i_trp_totals', $i_trp_totals)
            ->with('i_grand_total_raw_points', $i_grand_total_raw_points)
            ->with('vi_grand_total_raw_points', $vi_grand_total_raw_points)
            ->with('s_grand_total_raw_points', $s_grand_total_raw_points)
            ->with('misinai_grand_total_raw_points', $misinai_grand_total_raw_points)
            ->with('i_total_scores', $i_total_scores)
            ->with('vi_grand_total_score', $vi_grand_total_score) 
            ->with('i_grand_total_score', $i_grand_total_score) 
            ->with('misinai_grand_total_score', $misinai_grand_total_score)
            ->with('percentage_promoters', $percentage_promoters)
            ->with('first_month_percentage_promoters', $first_month_percentage_promoters)
            ->with('second_month_percentage_promoters', $second_month_percentage_promoters)
            ->with('third_month_percentage_promoters', $third_month_percentage_promoters)
            ->with('average_percentage_promoters', $average_percentage_promoters)
            ->with('first_month_percentage_detractors', $first_month_percentage_detractors)
            ->with('second_percentage_detractors', $second_month_percentage_detractors)
            ->with('third_month_percentage_detractors', $third_month_percentage_detractors) 
            ->with('average_percentage_detractors', $average_percentage_detractors)
            ->with('first_month_net_promoter_score', $first_month_net_promoter_score)
            ->with('second_month_net_promoter_score', $second_month_net_promoter_score)
            ->with('third_month_net_promoter_score', $third_month_net_promoter_score)
            ->with('ave_net_promoter_score', $ave_net_promoter_score)
            ->with('customer_satisfaction_rating', $customer_satisfaction_rating)
            ->with('csi', $customer_satisfaction_index)
            ->with('first_month_csi', $first_month_csi)
            ->with('second_month_csi', $second_month_csi)
            ->with('third_month_csi', $third_month_csi)
            ->with('first_month_vs_grand_total', $first_month_vs_grand_total)
            ->with('second_month_vs_grand_total', $second_month_vs_grand_total)
            ->with('third_month_vs_grand_total', $third_month_vs_grand_total)
            ->with('first_month_s_grand_total', $first_month_s_grand_total)
            ->with('second_month_s_grand_total', $second_month_s_grand_total)
            ->with('third_month_s_grand_total', $third_month_s_grand_total)
            ->with('first_month_ndvd_grand_total', $first_month_ndvd_grand_total)
            ->with('second_month_ndvd_grand_total', $second_month_ndvd_grand_total)
            ->with('third_month_ndvd_grand_total', $third_month_ndvd_grand_total)
            ->with('first_month_grand_total', $first_month_grand_total)
            ->with('second_month_grand_total', $second_month_grand_total)
            ->with('third_month_grand_total', $third_month_grand_total)
            ->with('total_comments', $total_comments)
            ->with('total_complaints', $total_complaints)
            ->with('comments', $comments);
    }

    public function getCitizenCharterByQuarter($request, $customer_ids, $startDate ,$endDate)
    {
         $cc_query = CustomerCCRating::whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->whereIn('customer_id',$customer_ids)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            })->get();
        return  $cc_query;
    }

    public function getCustomerAttributeRatingByQuarter($request, $customer_ids , $startDate ,$endDate ){
        $query = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->whereYear('created_at', $request->selected_year)
        ->when($request->sex, function ($query, $sex) {
            $query->whereHas('customer', function ($query) use ($sex) {
                $query->where('sex', $sex);
            });
        })
        ->when($request->age_group, function ($query, $age_group) {
            $query->whereHas('customer', function ($query) use ($age_group) {
                $query->where('age_group', $age_group);
            });
        })->get(); 

        return  $query;
   }


   public function getCustomerAttributeRatingByQuarterWithMonth($request,$customer_ids, $numeric_month){
        $query = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            })->get(); 

        return  $query;
    }


    public function getCustomerRecommendationRatingByQuarter($request, $customer_ids,$startDate, $endDate){
        $query = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->whereYear('created_at', $request->selected_year)
        ->when($request->sex, function ($query, $sex) {
            $query->whereHas('customer', function ($query) use ($sex) {
                $query->where('sex', $sex);
            });
        })
        ->when($request->age_group, function ($query, $age_group) {
            $query->whereHas('customer', function ($query) use ($age_group) {
                $query->where('age_group', $age_group);
            });
        })->get(); 

        return  $query;
   }

   
   public function getCustomerRecommendationRatingByQuarterWithMonth($request,$customer_ids, $numeric_month){
        $fisrt_month_crr =  CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
        ->whereMonth('created_at', $numeric_month)
        ->whereYear('created_at', $request->selected_year)
        ->when($request->sex, function ($query, $sex) {
            $query->whereHas('customer', function ($query) use ($sex) {
                $query->where('sex', $sex);
            });
        })
        ->when($request->age_group, function ($query, $age_group) {
            $query->whereHas('customer', function ($query) use ($age_group) {
                $query->where('age_group', $age_group);
            });
        })->get();

        return  $fisrt_month_crr;
    }

    public function getRespondents($request, $customer_ids,$startDate, $endDate){
        $respondents_list = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereYear('created_at', $request->selected_year)
                    ->when($request->sex, function ($query, $sex) {
                        $query->whereHas('customer', function ($query) use ($sex) {
                            $query->where('sex', $sex);
                        });
                    })
                    ->when($request->age_group, function ($query, $age_group) {
                        $query->whereHas('customer', function ($query) use ($age_group) {
                            $query->where('age_group', $age_group);
                        });
                    })->get();

        return  $respondents_list;
   }


 
    // YEARLY || ANNUALLY PER UNIT
    public function generateCSIByUnitYearly($request, $region_id, $psto_id)
    {
        $unitData = $this->getUnitData($request, true);
        $sub_unit = $unitData['sub_unit'];
        $unit_pstos = $unitData['unit_pstos'];
        $sub_unit_pstos = $unitData['sub_unit_pstos'];
        $sub_unit_types = $unitData['sub_unit_types'];

        //get user
        $user = Auth::user();
         //get assignatoree list
         $assignatorees = Assignatorees::all();

         //get users lists
        $users = User::all();

        $date_range = [];
        $q1_date_range = [];
        $q2_date_range = [];
        $q3_date_range = [];
        $q4_date_range = [];
        $customer_recommendation_ratings = null;
        $respondents_list = null;

        $ws_grand_total = 0;
          
        $service_id = $request->service['id'];
        $unit_id = $request->unit_id;
        $sub_unit_id = $request->selected_sub_unit;
        $client_type = $request->client_type; 
        $sub_unit_type = $request->sub_unit_type; 

       // search and check list of forms query  
       $customer_ids = $this->querySearchCSF( $region_id, $service_id, $unit_id ,$sub_unit_id , $psto_id, $client_type, $sub_unit_type );
            
        // Citizen's Charter
        $cc_query = CustomerCCRating::whereYear('created_at', $request->selected_year)
                                    ->whereIn('customer_id',$customer_ids)
                                    ->when($request->sex, function ($query, $sex) {
                                        $query->whereHas('customer', function ($query) use ($sex) {
                                            $query->where('sex', $sex);
                                        });
                                    })
                                    ->when($request->age_group, function ($query, $age_group) {
                                        $query->whereHas('customer', function ($query) use ($age_group) {
                                            $query->where('age_group', $age_group);
                                        });
                                    });

       //calculate CC
        $cc_data = $this->calculateCC($cc_query);

        // Retrieve records for the specified quarter and year
        $q1_start_date = Carbon::create($request->selected_year, 1, 1)->startOfDay();
        $q1_end_date = Carbon::create($request->selected_year, 3, 31)->endOfDay();

        $q2_start_date = Carbon::create($request->selected_year, 4, 1)->startOfDay();
        $q2_end_date = Carbon::create($request->selected_year, 6, 31)->endOfDay();

        $q3_start_date = Carbon::create($request->selected_year, 7, 1)->startOfDay();
        $q3_end_date = Carbon::create($request->selected_year, 9, 31)->endOfDay();

        $q4_start_date = Carbon::create($request->selected_year, 10, 1)->startOfDay();
        $q4_end_date = Carbon::create($request->selected_year, 12, 31)->endOfDay();

        $q1_date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q1_start_date, $q1_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })
                                                ->get(); 
        $q2_date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q2_start_date, $q2_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get(); 
        $q3_date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q3_start_date, $q3_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get();
        $q4_date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q4_start_date, $q4_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get();
        
        $date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get(); 

        $customer_recommendation_ratings = CustomerRecommendationRating::whereYear('created_at', $request->selected_year)
                                                ->whereIn('customer_id', $customer_ids)
                                                ->get();

        $q1_crr =  CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q1_start_date, $q1_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get();

        $q2_crr =  CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q2_start_date, $q2_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get();
                                                    
        $q3_crr =  CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q3_start_date, $q3_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get();

        $q4_crr =  CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
                                                ->whereBetween('created_at', [$q4_start_date, $q4_end_date])
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get();


        $respondents_list = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                                ->whereYear('created_at', $request->selected_year)
                                                ->when($request->sex, function ($query, $sex) {
                                                    $query->whereHas('customer', function ($query) use ($sex) {
                                                        $query->where('sex', $sex);
                                                    });
                                                })
                                                ->when($request->age_group, function ($query, $age_group) {
                                                    $query->whereHas('customer', function ($query) use ($age_group) {
                                                        $query->where('age_group', $age_group);
                                                    });
                                                })->get();     
          
        

        $dimensions = Dimension::all();
        $dimension_count = $dimensions->count();
        $grand_total_raw_points = 0;
        $vs_grand_total_score =0;
        $s_grand_total_score = 0;
        $ndvd_grand_total_score = 0;
        $grand_total_score =0;

        $vs_grand_total_raw_points = 0;
        $s_grand_total_raw_points = 0;
        $ndvd_grand_total_raw_points = 0;
        $lsr_grand_total = 0 ;
        $lsr_average = 0;

        //Importance total raw points  
        $vi_grand_total_raw_points = 0;
        $i_grand_total_raw_points = 0;
        $misinai_grand_total_raw_points = 0;
        //Importance grand total score
        $vi_grand_total_score=0;
        $i_grand_total_score =0;
        $misinai_grand_total_score = 0;
        $overall_total_scores = 0;

        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            //PART I

            //  Quarter 1  with specific year total responses with its dimensions and rate score
            $q1_vs_total = $q1_date_range->where('rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q1_s_total = $q1_date_range->where('rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q1_n_total = $q1_date_range->where('rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q1_d_total = $q1_date_range->where('rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q1_vd_total = $q1_date_range->where('rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          

            $q1_grand_total =  $q1_vs_total + $q1_s_total + $q1_n_total + $q1_d_total + $q1_vd_total ; 
     
            //  Quarter 2  with specific year total responses with its dimensions and rate score
            $q2_vs_total = $q2_date_range->where('rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q2_s_total = $q2_date_range->where('rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q2_n_total = $q2_date_range->where('rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q2_d_total = $q2_date_range->where('rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
            $q2_vd_total = $q2_date_range->where('rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          

            $q2_grand_total =  $q2_vs_total + $q2_s_total + $q2_n_total + $q2_d_total + $q2_vd_total ; 

             //  Quarter 3  with specific year total responses with its dimensions and rate score
             $q3_vs_total = $q3_date_range->where('rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q3_s_total = $q3_date_range->where('rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q3_n_total = $q3_date_range->where('rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q3_d_total = $q3_date_range->where('rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q3_vd_total = $q3_date_range->where('rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
 
             $q3_grand_total =  $q3_vs_total + $q3_s_total + $q3_n_total + $q3_d_total + $q3_vd_total ; 
     
             //  Quarter 4  with specific year total responses with its dimensions and rate score
             $q4_vs_total = $q4_date_range->where('rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q4_s_total = $q4_date_range->where('rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q4_n_total = $q4_date_range->where('rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q4_d_total = $q4_date_range->where('rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
             $q4_vd_total = $q4_date_range->where('rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
 
             $q4_grand_total =  $q4_vs_total + $q4_s_total + $q4_n_total + $q4_d_total + $q4_vd_total ; 
            

            // Quarters Very Satisfied total with specific dimention or attribute
            $vs_totals[$dimensionId] = [
                'q1_vs_total' => $q1_vs_total,
                'q2_vs_total' => $q2_vs_total,
                'q3_vs_total' => $q3_vs_total,
                'q4_vs_total' => $q4_vs_total,
            ];
             // Quarters Satisfied total with specific dimention or attribute
            $s_totals[$dimensionId] = [
                'q1_s_total' => $q1_s_total,
                'q2_s_total' => $q2_s_total,
                'q3_s_total' => $q3_s_total,
                'q4_s_total' => $q4_s_total,
            ];
            // Quarters Neither total with specific dimention or attribute
            $n_totals[$dimensionId] = [
                'q1_n_total' => $q1_n_total,
                'q2_n_total' => $q2_n_total,
                'q3_n_total' => $q3_n_total,
                'q4_n_total' => $q4_n_total,
            ];
            // Quarters Dissatisfied total with specific dimention or attribute
            $d_totals[$dimensionId] = [
                'q1_d_total' => $q1_d_total,
                'q2_d_total' => $q2_d_total,
                'q3_d_total' => $q3_d_total,
                'q4_d_total' => $q4_d_total,
            ];
            // Quarters Very Dissatisfied total with specific dimention or attribute
            $vd_totals[$dimensionId] = [
                'q1_vd_total' => $q1_vd_total,
                'q2_vd_total' => $q2_vd_total,
                'q3_vd_total' => $q3_vd_total,
                'q4_vd_total' => $q4_vd_total,
            ];

            // Quarters grand total with specific dimention or attribute
            $grand_totals[$dimensionId] = [
                'q1_grand_total' => $q1_grand_total,
                'q2_grand_total' => $q2_grand_total,
                'q3_grand_total' => $q3_grand_total,
                'q4_grand_total' => $q4_grand_total,
            ];

            //Total Raw Points totals
            $vs_total_raw_points = $q1_vs_total + $q2_vs_total + $q3_vs_total + $q4_vs_total;
            $s_total_raw_points = $q1_s_total + $q2_s_total + $q3_s_total + $q4_s_total;
            $n_total_raw_points = $q1_n_total + $q2_n_total + $q3_n_total + $q4_n_total;
            $d_total_raw_points = $q1_n_total + $q2_n_total + $q3_n_total + $q4_n_total;
            $vd_total_raw_points = $q1_vd_total + $q2_vd_total + $q3_vd_total + $q4_vd_total;
            $total_raw_points = $vs_total_raw_points + $s_total_raw_points + $n_total_raw_points +  $d_total_raw_points +  $vd_total_raw_points;           

            $vs_grand_total_raw_points += $vs_total_raw_points;
            $s_grand_total_raw_points +=  $s_total_raw_points;
            $ndvd_grand_total_raw_points +=  $n_total_raw_points + $d_total_raw_points + $vd_total_raw_points;
            $grand_total_raw_points+= $total_raw_points;

            $trp_totals[$dimensionId] = [
                'vs_total_raw_points' => $vs_total_raw_points,
                's_total_raw_points' => $s_total_raw_points,
                'n_total_raw_points' => $n_total_raw_points,
                'd_total_raw_points' => $d_total_raw_points,
                'vd_total_raw_points' => $vd_total_raw_points,
                'total_raw_points' => $total_raw_points,
            ];

            //Part 1 Quarter 1 total scores
            $x_vs_total = $vs_total_raw_points * 5; 
            $x_s_total = $s_total_raw_points * 4; 
            $x_n_total = $n_total_raw_points * 3; 
            $x_d_total = $d_total_raw_points * 3; 
            $x_vd_total = $vd_total_raw_points * 1; 
            $x_total_score =  $x_vs_total +  $x_s_total +  $x_n_total +  $x_d_total + $x_vd_total;
            
            $vs_grand_total_score += $x_vs_total ;
            $s_grand_total_score += $x_s_total ;
            $ndvd_grand_total_score += $x_n_total +  $x_d_total + $x_vd_total ;
            $grand_total_score += $x_total_score ;

            $p1_total_scores[$dimensionId] = [ 
                'x_vs_total' => $x_vs_total,
                'x_s_total' => $x_s_total,
                'x_n_total' => $x_n_total,
                'x_d_total' => $x_d_total,
                'x_vd_total' => $x_vd_total, 
                'x_total_score' => $x_total_score,
            ];

            // Likert Scale Rating = total score / grand total of total raw points
            if($total_raw_points != 0 ){
                $vs_lsr_total =   number_format(($x_vs_total  /  $total_raw_points),2);
                $s_lsr_total =    number_format(($x_s_total /  $total_raw_points),2);
                $n_lsr_total =   number_format(($x_n_total /  $total_raw_points),2);
                $d_lsr_total =   number_format(($x_d_total /  $total_raw_points),2);
                $vd_lsr_total =   number_format(($x_vd_total /  $total_raw_points),2);
                $lsr_total =  number_format(($vs_lsr_total +  $s_lsr_total  +  $n_lsr_total  +  $d_lsr_total  +  $vd_lsr_total),2);
                $lsr_grand_total +=  $lsr_total;
                $lsr_grand_total =number_format(($lsr_grand_total),2);
                $lsr_average =  number_format(($lsr_grand_total / $dimensionId), 2);
            }
            else{
                $vs_lsr_total =  0;
                $s_lsr_total =  0;
                $n_lsr_total =  0;
                $d_lsr_total = 0;
                $vd_lsr_total =  0;
                $lsr_total = 0;
                $lsr_grand_total +=  0;
                $lsr_average =  0;
            }

            $lsr_totals[$dimensionId] = [
                'vs_lsr_total' => $vs_lsr_total,
                's_lsr_total' => $s_lsr_total,
                'n_lsr_total' => $n_lsr_total,
                'd_lsr_total' => $d_lsr_total,
                'vd_lsr_total' => $vd_lsr_total,
                'lsr_total' => $lsr_total,
            ];
            
            // PART II
              // Quarter 1 total responses with its dimensions and rate score
              $q1_vi_total = $q1_date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q1_i_total = $q1_date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q1_mi_total = $q1_date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q1_si_total = $q1_date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q1_nai_total = $q1_date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
  
              $q1_i_grand_total =  $q1_vi_total + $q1_i_total + $q1_mi_total + $q1_si_total + $q1_nai_total ; 
  
              // Quarter 2 total responses with its dimensions and rate score
              $q2_vi_total = $q2_date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q2_i_total = $q2_date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q2_mi_total = $q2_date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q2_si_total = $q2_date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q2_nai_total = $q2_date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
  
              $q2_i_grand_total =  $q2_vi_total + $q2_i_total + $q2_mi_total + $q2_si_total + $q2_nai_total ; 
  
              //  Quarter 3 total responses with its dimensions and rate score
              $q3_vi_total = $q3_date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q3_i_total = $q3_date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q3_mi_total = $q3_date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q3_si_total = $q3_date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q3_nai_total = $q3_date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          
  
              $q3_i_grand_total =  $q3_vi_total + $q3_i_total + $q3_mi_total + $q3_si_total + $q3_nai_total ;
              
              //  Quarter 4 total responses with its dimensions and rate score
              $q4_vi_total = $q4_date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q4_i_total = $q4_date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q4_mi_total = $q4_date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q4_si_total = $q4_date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();
              $q4_nai_total = $q4_date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->groupBy('customer_id')->count();          

              $q4_i_grand_total =  $q4_vi_total + $q4_i_total + $q4_mi_total + $q4_si_total + $q4_nai_total ;
                // Very Important total with specific dimention or attribute
                $vi_totals[$dimensionId] = [
                    'q1_vi_total' => $q1_vi_total,
                    'q2_vi_total' => $q2_vi_total,
                    'q3_vi_total' => $q3_vi_total,
                    'q4_vi_total' => $q4_vi_total,
                ];
                //Important total with specific dimention or attribute
                $i_totals[$dimensionId] = [
                    'q1_i_total' => $q1_i_total,
                    'q2_i_total' => $q2_i_total,
                    'q3_i_total' => $q3_i_total,
                    'q4_i_total' => $q4_i_total,
                ];
                // Moderately Important total with specific dimention or attribute
                $mi_totals[$dimensionId] = [
                    'q1_mi_total' => $q1_mi_total,
                    'q2_mi_total' => $q2_mi_total,
                    'q3_mi_total' => $q3_mi_total,
                    'q4_mi_total' => $q4_mi_total,
                ];
                // slightly Important total with specific dimention or attribute
                $si_totals[$dimensionId] = [
                    'q1_si_total' => $q1_si_total,
                    'q2_si_total' => $q2_si_total,
                    'q3_si_total' => $q3_si_total,
                    'q4_si_total' => $q4_si_total,
                ];

                $nai_totals[$dimensionId] = [
                    'q1_nai_total' => $q1_nai_total,
                    'q2_nai_total' => $q2_nai_total,
                    'q3_nai_total' => $q3_nai_total,
                    'q4_nai_total' => $q4_nai_total,
                ];

                $i_grand_totals[$dimensionId] = [
                    'q1_i_grand_total' => $q1_i_grand_total,
                    'q2_i_grand_total' => $q2_i_grand_total,
                    'q3_i_grand_total' => $q3_i_grand_total,
                    'q4_i_grand_total' => $q4_i_grand_total,
                ];

                
            //Importance Total Raw Points totals
            $vi_total_raw_points = $q1_vi_total + $q2_vi_total + $q3_vi_total + $q4_vi_total;
            $i_total_raw_points = $q1_i_total + $q2_i_total + $q3_i_total + $q4_i_total;
            $mi_total_raw_points =  $q1_mi_total + $q2_mi_total + $q3_mi_total + $q4_mi_total;
            $si_total_raw_points = $q1_si_total + $q2_si_total + $q3_si_total + $q4_si_total;
            $nai_total_raw_points = $q1_nai_total + $q2_nai_total + $q3_nai_total + $q4_nai_total;
            $importance_total_raw_points = $vi_total_raw_points + $i_total_raw_points + $mi_total_raw_points +  $si_total_raw_points +  $nai_total_raw_points;           

            $vi_grand_total_raw_points += $vi_total_raw_points;
            $s_grand_total_raw_points +=  $i_total_raw_points;
            $misinai_grand_total_raw_points +=  $mi_total_raw_points + $si_total_raw_points + $nai_total_raw_points;
            $i_grand_total_raw_points+= $total_raw_points;

            $i_trp_totals[$dimensionId] = [
                'vi_total_raw_points' => $vi_total_raw_points,
                'i_total_raw_points' => $i_total_raw_points,
                'mi_total_raw_points' => $mi_total_raw_points,
                'si_total_raw_points' => $si_total_raw_points,
                'nai_total_raw_points' => $nai_total_raw_points,
                'importance_total_raw_points' => $importance_total_raw_points,
            ];

            //Part 1 Quarter 1 total scores
            $x_vi_total = $vi_total_raw_points * 5; 
            $x_i_total = $i_total_raw_points * 4; 
            $x_mi_total = $mi_total_raw_points * 3; 
            $x_si_total = $si_total_raw_points * 3; 
            $x_nai_total = $nai_total_raw_points * 1; 
            $x_i_total_score =  $x_vi_total +  $x_i_total +  $x_mi_total +  $x_si_total + $x_nai_total;
            $overall_total_scores += $x_i_total_score;
            
            $vi_grand_total_score += $x_vi_total ;
            $i_grand_total_score += $x_si_total ;
            $misinai_grand_total_score += $x_mi_total +  $x_si_total + $x_nai_total ;

            $i_total_scores[$dimensionId] = [ 
                'x_vi_total' => $x_vi_total,
                'x_i_total' => $x_i_total,
                'x_mi_total' => $x_mi_total,
                'x_si_total' => $x_si_total,
                'x_nai_total' => $x_nai_total, 
                'x_i_total_score' => $x_i_total_score,
            ];

        
        }


        //ALL quarters Total No. of Very Satisfied (VS) Responses of First Quarte
        $q1_total_vs_respondents = $q1_date_range->where('rate_score',5)->groupBy('customer_id')->count();
        $q2_total_vs_respondents = $q2_date_range->where('rate_score',5)->groupBy('customer_id')->count();
        $q3_total_vs_respondents = $q3_date_range->where('rate_score',5)->groupBy('customer_id')->count();
        $q4_total_vs_respondents = $q4_date_range->where('rate_score',5)->groupBy('customer_id')->count();

        // Total No. of Satisfied (S) Responses
        $q1_total_s_respondents = $q1_date_range->where('rate_score',4)->groupBy('customer_id')->count();
        $q2_total_s_respondents = $q2_date_range->where('rate_score',4)->groupBy('customer_id')->count();
        $q3_total_s_respondents = $q3_date_range->where('rate_score',4)->groupBy('customer_id')->count();
        $q4_total_s_respondents = $q4_date_range->where('rate_score',4)->groupBy('customer_id')->count();

        // Total No. of Other (N, D, VD) Responses
        $q1_total_ndvd_respondents = $q1_date_range->where('rate_score','<',4)->groupBy('customer_id')->count();
        $q2_total_ndvd_respondents = $q2_date_range->where('rate_score','<',4)->groupBy('customer_id')->count();
        $q3_total_ndvd_respondents = $q3_date_range->where('rate_score','<',4)->groupBy('customer_id')->count();
        $q4_total_ndvd_respondents = $q4_date_range->where('rate_score','<',4)->groupBy('customer_id')->count();
          
        // Total No. of All Responses
        $q1_total_ndvd_respondents = $q1_date_range->groupBy('customer_id')->count();
        $q2_total_ndvd_respondents = $q2_date_range->groupBy('customer_id')->count();
        $q3_total_ndvd_respondents = $q3_date_range->groupBy('customer_id')->count();
        $q4_total_ndvd_respondents = $q4_date_range->groupBy('customer_id')->count();
          

        //Total respondents /Customers
        $total_respondents = $date_range->groupBy('customer_id')->count();
        $q1_total_respondents = $q1_date_range->groupBy('customer_id')->count();
        $q2_total_respondents = $q2_date_range->groupBy('customer_id')->count();
        $q3_total_respondents = $q3_date_range->groupBy('customer_id')->count();
        $q4_total_respondents = $q4_date_range->groupBy('customer_id')->count();

        // total number of respondents/customer who rated VS/S
        $total_vss_respondents = $date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();
        $q1_total_vss_respondents = $q1_date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();
        $q2_total_vss_respondents = $q2_date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();
        $q3_total_vss_respondents = $q3_date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();
        $q4_total_vss_respondents = $q4_date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();

        // total number of respondents/customer who rated VS
        $total_vs_respondents = $date_range->where('rate_score', '=','5')->groupBy('customer_id')->count();
        $q1_total_vs_respondents = $q1_date_range->where('rate_score', '=','5')->groupBy('customer_id')->count();
        $q2_total_vs_respondents = $q2_date_range->where('rate_score', '=','5')->groupBy('customer_id')->count();
        $q3_total_vs_respondents = $q3_date_range->where('rate_score', '=','5')->groupBy('customer_id')->count();
        $q4_total_vs_respondents = $q4_date_range->where('rate_score', '=','5')->groupBy('customer_id')->count();

        // total number of respondents/customer who rated S
        $total_s_respondents = $date_range->where('rate_score', '=','4')->groupBy('customer_id')->count();
        $q1_total_s_respondents = $q1_date_range->where('rate_score', '=','4')->groupBy('customer_id')->count();
        $q2_total_s_respondents = $q2_date_range->where('rate_score', '=','4')->groupBy('customer_id')->count();
        $q3_total_s_respondents = $q3_date_range->where('rate_score', '=','4')->groupBy('customer_id')->count();
        $q4_total_s_respondents = $q4_date_range->where('rate_score', '=','4')->groupBy('customer_id')->count();
    
        // Frst quarter total number of promoters or respondents who rated 7-10 in recommendation rating
        $total_promoters = $customer_recommendation_ratings->where('recommend_rate_score', '>','6')->groupBy('customer_id')->count();
        $q1_total_promoters = $q1_crr->where('recommend_rate_score', '>','6')->groupBy('customer_id')->count();
        $q2_total_promoters = $q2_crr->where('recommend_rate_score', '>','6')->groupBy('customer_id')->count();
        $q3_total_promoters = $q3_crr->where('recommend_rate_score', '>','6')->groupBy('customer_id')->count();
        $q4_total_promoters = $q4_crr->where('recommend_rate_score', '>','6')->groupBy('customer_id')->count();
        
        // total number of detractors or respondents who rated 0-6 in recommendation rating
        $total_detractors = $customer_recommendation_ratings->where('recommend_rate_score', '<','7')->groupBy('customer_id')->count();
        $q1_total_detractors = $q1_crr->where('recommend_rate_score', '<','7')->groupBy('customer_id')->count();
        $q2_total_detractors = $q2_crr->where('recommend_rate_score', '<','7')->groupBy('customer_id')->count();
        $q3_total_detractors = $q3_crr->where('recommend_rate_score', '<','7')->groupBy('customer_id')->count();
        $q4_total_detractors = $q4_crr->where('recommend_rate_score', '<','7')->groupBy('customer_id')->count();

  
        //Percentage of Respondents/Customers who rated VS/S = total no. of respondents / total no. respondets who rated vs/s * 100
        $percentage_vss_respondents  = 0;
        if($total_respondents != 0){
            $percentage_vss_respondents  = ($total_vss_respondents / $total_respondents) * 100;
        }
        $percentage_vss_respondents = number_format( $percentage_vss_respondents , 2);

         // CSAT = ((Total No. of Very Satisfied (VS) Responses + Total No. of Satisfied (S) Responses) / grand total respondents) * 100
        $customer_satisfaction_rating = 0;
        if($total_vss_respondents != 0){
            $customer_satisfaction_rating = (($vs_grand_total_score + $s_grand_total_score)/$grand_total_score) * 100;
        }
        $customer_satisfaction_rating = number_format( $customer_satisfaction_rating , 2);

        //Percentage of Promoters = number of promoters / total respondents
        $percentage_promoters = 0;
        //  Percentage promoters
        $q1_percentage_promoters = 0.00;
        $q2_percentage_promoters = 0.00;
        $q3_percentage_promoters = 0.00;
        $q4_percentage_promoters = 0.00;

        // Percentage of promoter
        $q1_percentage_detractors = 0.00;
        $q2_percentage_detractors = 0.00;
        $q3_percentage_detractors = 0.00;
        $q4_percentage_detractors = 0.00;

        // Net Promoter Score
        $q1_net_promoter_score =  0.00;
        $q2_net_promoter_score = 0.00;
        $q3_net_promoter_score =  0.00;
        $q4_net_promoter_score =  0.00;

        // average
        $ave_net_promoter_score = 0.00;
        $average_percentage_promoters =  0.00;
        $average_percentage_detractors =  0.00;

        if($total_respondents != 0){
            $percentage_promoters = number_format((($total_promoters / $total_respondents) * 100), 2);
            if($q1_total_respondents != 0){
                $q1_percentage_promoters = number_format((($q1_total_promoters / $q1_total_respondents) * 100), 2);
                $q1_percentage_detractors = number_format((($q1_total_detractors / $q1_total_respondents) * 100),2);
            }
            if($q2_total_respondents != 0){
                $q2_percentage_promoters = number_format((($q2_total_promoters / $q2_total_respondents) * 100), 2);
                $q2_percentage_detractors = number_format((($q2_total_detractors / $q2_total_respondents) * 100),2);
            }
            if($q3_total_respondents != 0){
                $q3_percentage_promoters = number_format((($q3_total_promoters / $q3_total_respondents) * 100), 2);
                $q3_percentage_detractors = number_format((($q3_total_detractors / $q3_total_respondents) * 100),2);
            }
            if($q4_total_respondents != 0){
                $q4_percentage_promoters = number_format((($q4_total_promoters / $q4_total_respondents) * 100), 2);
                $q4_percentage_detractors = number_format((($q4_total_detractors / $q4_total_respondents) * 100),2);
            }

            //Percentage of Promoters = number of promoters / total respondents
            $percentage_detractors = number_format((($total_detractors / $total_respondents) * 100),2);

            // Net Promotion Scores(NPS) = Percentage of Promoters−Percentage of Detractors
            $q1_net_promoter_score =  number_format(($q1_percentage_promoters - $q1_percentage_detractors),2);
            $q2_net_promoter_score =  number_format(($q2_percentage_promoters - $q2_percentage_detractors),2);
            $q3_net_promoter_score =  number_format(($q3_percentage_promoters - $q3_percentage_detractors),2);
            $q4_net_promoter_score =  number_format(($q4_percentage_promoters - $q4_percentage_detractors),2);

            $ave_net_promoter_score =  number_format((($q1_net_promoter_score + $q2_net_promoter_score + $q3_net_promoter_score + $q4_net_promoter_score)/ 4),2);
            $average_percentage_promoters =  number_format((($q1_percentage_promoters + $q2_percentage_promoters + $q3_percentage_promoters + $q4_percentage_promoters)/ 4),2);
            $average_percentage_detractors =  number_format((($q1_percentage_detractors + $q2_percentage_detractors + $q3_percentage_detractors + $q4_percentage_detractors)/ 4),2);


        }

        // Calculate yearly CSI
        $ws_grand_total = $ws_grand_total ?? 0;
      
        $customer_satisfaction_index = 0;
        if($ws_grand_total != 0){
            $customer_satisfaction_index = ($ws_grand_total / 5) * 100;
        }
        $customer_satisfaction_index = number_format($customer_satisfaction_index, 2);

        if($customer_satisfaction_index > 100){
            $customer_satisfaction_index = number_format(100 , 2);
        }

  
        // get Yearly CSI
        $jan_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 1);
        $feb_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 2);
        $mar_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 3);

        $q1_csi = 0;
        $q1_csi = number_format(($jan_csi +  $feb_csi + $mar_csi) / 3, 2);

        $apr_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 4);
        $may_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 5);
        $jun_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 6);

        $q2_csi = 0;
        $q2_csi = number_format(($apr_csi +  $may_csi + $jun_csi) / 3, 2);

        $jul_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 7);
        $aug_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 8);
        $sep_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 9);

        $q3_csi = 0;
        $q3_csi = number_format(($jul_csi +  $aug_csi + $sep_csi) / 3, 2);

        $oct_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 10);
        $nov_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 11);
        $dec_csi = $this->getMonthlyCSI($request, $region_id, $psto_id, 12);

        $q4_csi = 0;
        $q4_csi = number_format(($oct_csi +  $nov_csi + $dec_csi) / 3, 2);

      

        // Calculate average CSI for the year
        $average_csi = number_format(($q1_csi + $q2_csi + $q3_csi + $q4_csi) / 4, 2);


         //comments and  complaints
         $comment_list = CustomerComment::whereIn('customer_id', $customer_ids)
                                        ->whereYear('created_at', $request->selected_year)->get();

        $comments = $comment_list->where('comment','!=','')->pluck('comment'); 

        $total_comments = $comment_list->where('comment','!=','')->count();
        $total_complaints = $comment_list->where('is_complaint',1)->count();

        //Respondents list
        $data = CARResource::collection($respondents_list);

        //send response to front end
        return Inertia::render('CSI/Index')
            ->with('user', $user)
            ->with('cc_data', $cc_data)
            ->with('assignatorees', $assignatorees)
            ->with('users', $users)
            ->with('sub_unit', $sub_unit)
            ->with('unit_pstos', $unit_pstos)
            ->with('sub_unit_pstos', $sub_unit_pstos)
            ->with('sub_unit_types', $sub_unit_types)
            ->with('dimensions', $dimensions)
            ->with('service', $request->service)
            ->with('unit', $request->unit)
            ->with('respondents_list',$data)
            ->with('vs_totals', $vs_totals)
            ->with('s_totals', $s_totals)
            ->with('n_totals', $n_totals)
            ->with('d_totals', $d_totals)
            ->with('vd_totals', $vd_totals)
            ->with('grand_totals', $grand_totals)
            ->with('trp_totals', $trp_totals)
            ->with('grand_total_raw_points', $grand_total_raw_points)
            ->with('vs_grand_total_raw_points', $vs_grand_total_raw_points)
            ->with('s_grand_total_raw_points', $s_grand_total_raw_points)
            ->with('ndvd_grand_total_raw_points', $ndvd_grand_total_raw_points)
            ->with('p1_total_scores', $p1_total_scores)
            ->with('vs_grand_total_score', $vs_grand_total_score) 
            ->with('s_grand_total_score', $s_grand_total_score) 
            ->with('ndvd_grand_total_score', $ndvd_grand_total_score) 
            ->with('grand_total_score', $grand_total_score) 
            ->with('lsr_totals', $lsr_totals)
            ->with('lsr_grand_total', $lsr_grand_total)
            ->with('lsr_average', $lsr_average ) 
            ->with('q1_total_vs_respondents', $q1_total_vs_respondents)
            ->with('q2_total_vs_respondents', $q2_total_vs_respondents)
            ->with('q3_total_vs_respondents', $q3_total_vs_respondents)
            ->with('q4_total_vs_respondents', $q4_total_vs_respondents)
            ->with('q1_total_s_respondents', $q1_total_s_respondents)
            ->with('q2_total_s_respondents', $q2_total_s_respondents)
            ->with('q3_total_s_respondents', $q3_total_s_respondents)
            ->with('q4_total_s_respondents', $q4_total_s_respondents)
            ->with('q1_total_ndvd_respondents', $q1_total_ndvd_respondents)
            ->with('q2_total_ndvd_respondents', $q2_total_ndvd_respondents)
            ->with('q3_total_ndvd_respondents', $q3_total_ndvd_respondents)
            ->with('q4_total_ndvd_respondents', $q4_total_ndvd_respondents)
            ->with('q1_total_respondents', $q1_total_respondents)
            ->with('q2_total_respondents', $q2_total_respondents)
            ->with('q3_total_respondents', $q3_total_respondents)
            ->with('q4_total_respondents', $q4_total_respondents)
            ->with('total_respondents', $total_respondents)
            ->with('q1_total_vss_respondents', $q1_total_vss_respondents)
            ->with('q2_total_vss_respondents', $q2_total_vss_respondents)
            ->with('q3_total_vss_respondents', $q3_total_vss_respondents)
            ->with('q4_total_vss_respondents', $q4_total_vss_respondents)
            ->with('total_vss_respondents', $total_vss_respondents)
            ->with('percentage_vss_respondents', $percentage_vss_respondents)
            ->with('total_promoters', $total_promoters)
            ->with('total_detractors', $total_detractors)
            ->with('vi_totals', $vi_totals)
            ->with('i_totals', $i_totals)
            ->with('mi_totals', $mi_totals)
            ->with('si_totals', $si_totals)
            ->with('nai_totals', $nai_totals)
            ->with('i_grand_totals', $i_grand_totals)
            ->with('i_trp_totals', $i_trp_totals)
            ->with('i_grand_total_raw_points', $i_grand_total_raw_points)
            ->with('vi_grand_total_raw_points', $vi_grand_total_raw_points)
            ->with('s_grand_total_raw_points', $s_grand_total_raw_points)
            ->with('misinai_grand_total_raw_points', $misinai_grand_total_raw_points)
            ->with('i_total_scores', $i_total_scores)
            ->with('vi_grand_total_score', $vi_grand_total_score) 
            ->with('i_grand_total_score', $i_grand_total_score) 
            ->with('misinai_grand_total_score', $misinai_grand_total_score)
            ->with('percentage_promoters', $percentage_promoters)
            ->with('q1_percentage_promoters', $q1_percentage_promoters)
            ->with('q2_percentage_promoters', $q2_percentage_promoters)
            ->with('q3_percentage_promoters', $q3_percentage_promoters)
            ->with('q4_percentage_promoters', $q4_percentage_promoters)
            ->with('average_percentage_promoters', $average_percentage_promoters)
            ->with('q1_percentage_detractors', $q1_percentage_detractors)
            ->with('q2_percentage_detractors', $q2_percentage_detractors)
            ->with('q3_percentage_detractors', $q3_percentage_detractors) 
            ->with('q4_percentage_detractors', $q4_percentage_detractors) 
            ->with('average_percentage_detractors', $average_percentage_detractors)
            ->with('q1_net_promoter_score', $q1_net_promoter_score)
            ->with('q2_net_promoter_score', $q2_net_promoter_score)
            ->with('q3_net_promoter_score', $q3_net_promoter_score)
            ->with('q4_net_promoter_score', $q4_net_promoter_score)
            ->with('ave_net_promoter_score', $ave_net_promoter_score)
            ->with('customer_satisfaction_rating', $customer_satisfaction_rating)
            ->with('q1_csi', $q1_csi)
            ->with('q2_csi', $q2_csi)
            ->with('q3_csi', $q3_csi)
            ->with('q4_csi', $q4_csi)
            ->with('csi', $average_csi)
            ->with('total_comments', $total_comments)
            ->with('total_complaints', $total_complaints)
            ->with('comments', $comments);
    }



    


    public function querySearchCSF($region_id, $service_id, $unit_id , $sub_unit_id , $psto_id, $client_type, $sub_unit_type )
    {

        $customer_ids = CSFForm::when($region_id, function ($query, $region_id) {
            $query->where('region_id', $region_id);
        })
        ->when($service_id, function ($query, $service_id) {
            $query->where('service_id', $service_id);
        })
        ->when($unit_id, function ($query, $unit_id) {
            $query->where('unit_id', $unit_id);
        })
        ->when($sub_unit_id, function ($query, $sub_unit_id) {
            $query->where('sub_unit_id', $sub_unit_id);
        })
        ->when($psto_id, function ($query, $psto_id) {
            $query->where('psto_id', $psto_id);
        })
        ->when($client_type, function ($query, $client_type) use ($region_id, $service_id, $unit_id) {
            // IF HR UNIT
            if($region_id == 10 && $service_id == 2 && $unit_id == 8){
                if($client_type == "Internal"){
                    $query->where('client_type', "Internal Employees");
                }
                else if($client_type == "External"){
                    $query->where(function ($q) {
                        $q->where('client_type', "General Public")
                          ->orWhere('client_type', "Government Employees")
                          ->orWhere('client_type', "Business/Organization");
                    });
                }
            }
        })
        ->when($sub_unit_type, function ($query, $sub_unit_type) {
            if($sub_unit_type['type_name']){
                $query->where('sub_unit_type', $sub_unit_type['type_name']);
            }
          
        })
        ->select('customer_id')
        ->get();


        return  $customer_ids;
    
    }


    public function getMonthlyCSI($request, $region_id, $psto_id, $month)
    {

        $service_id = $request->service['id'];
        $unit_id = $request->unit_id;
        $sub_unit_id = $request->selected_sub_unit;
        $client_type = $request->client_type; 
        $sub_unit_type = $request->sub_unit_type; 

       // search and check list of forms query  
       $customer_ids = $this->querySearchCSF( $region_id, $service_id, $unit_id ,$sub_unit_id , $psto_id, $client_type, $sub_unit_type );

       // Citizen's Charter
       $cc_query = CustomerCCRating::whereMonth('created_at', $month)
                                    ->whereYear('created_at', $request->selected_year)
                                    ->whereIn('customer_id',$customer_ids)
                                    ->when($request->sex, function ($query, $sex) {
                                        $query->whereHas('customer', function ($query) use ($sex) {
                                            $query->where('sex', $sex);
                                        });
                                    })
                                    ->when($request->age_group, function ($query, $age_group) {
                                        $query->whereHas('customer', function ($query) use ($age_group) {
                                            $query->where('age_group', $age_group);
                                        });
                                    });

        //calculate CC
        $cc_data = $this->calculateCC($cc_query);

        $date_range = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
                                             ->whereMonth('created_at', $month)->get();
           
        // Dimensions or attributes
        $dimensions = Dimension::all();
        $dimension_count = $dimensions->count();

        // total number of respondents/customer
        $total_respondents = $date_range->groupBy('customer_id')->count();

        // total number of respondents/customer who rated VS/S
        $total_vss_respondents = $date_range->where('rate_score', '>','3')->groupBy('customer_id')->count();

        $ilsr_grand_total =0;
        // loop for getting importance ls rating grand total for ws rating calculation
        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            $vi_total = $date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $i_total = $date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $mi_total = $date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $li_total = $date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $nai_total = $date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->count();

            $x_vi_total = $vi_total * 5; 
            $x_i_total = $i_total * 4; 
            $x_mi_total = $mi_total * 3; 
            $x_li_total = $li_total * 2; 
            $x_nai_total = $nai_total * 1;
            $x_importance_total = $x_vi_total + $x_i_total + $x_mi_total + $x_li_total + $x_nai_total  ; 

            // Importance Likert Scale RAting 
            if($x_importance_total != 0){
                $ilsr_total = $x_importance_total / $total_respondents;
                $ilsr_grand_total =  $ilsr_grand_total + $ilsr_total;
            }

        }

        // PART I : CUSTOMER RATING OF SERVICE QUALITY 

        //set initial value of buttom side total scores
        $y_totals = [];
        $grand_vs_total = 0;
        $grand_s_total = 0;
        $grand_n_total = 0;
        $grand_vd_total = 0;
        $grand_d_total = 0;
        $grand_total = 0;
        
        //set initial value of right side total scores
        $x_vs_total = 0; 
        $x_s_total = 0; 
        $x_n_total = 0; 
        $x_d_total = 0; 
        $x_vd_total = 0; 
        $x_grand_total = 0 ; 

        $likert_scale_rating_totals = [];
        $lsr_total= 0;
        $lsr_grand_total= 0;

         // PART II : IMPORTANCE OF THIS ATTRIBUTE 

        //set importance rating score 
        $importance_rate_score_totals = [];
        $x_importance_totals = [];
        $x_importance_total=0; 

        $x_vi_total = 0; 
        $x_i_total =0; 
        $x_mi_total =0; 
        $x_li_total = 0; 
        $x_nai_total = 0;

        $importance_ilsr_totals = [];
        $ilsr_total = 0;

        $gap_totals = [];
        $gap_total = 0 ;
        $gap_grand_total=0;
        $ss_total= 0;
        $ss_totals = [];
        $wf_total= 0;
        $wf_totals = [];
        $ws_total= 0;
        $ws_totals = [];
        $ws_grand_total = 0;

        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            //PART I
            $vs_total = $date_range->where('rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $s_total = $date_range->where('rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $n_total = $date_range->where('rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $d_total = $date_range->where('rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $vd_total = $date_range->where('rate_score', 1)->where('dimension_id', $dimensionId)->count();          
       
            $x_vs_total = $vs_total * 5; 
            $x_s_total = $s_total * 4; 
            $x_n_total = $n_total * 3; 
            $x_d_total = $d_total * 2; 
            $x_vd_total = $vd_total * 1; 

             // sum of all repondent with rate_score 1-5
             $x_respondents_total =  $vs_total +   $x_s_total + $n_total +  $d_total +  $vd_total;
            $x_grand_total = $x_vs_total + $x_s_total + $x_n_total + $x_d_total + $x_vd_total  ; 
         
            // right side total score divided by total repondents or customers
            if($x_grand_total != 0){
                if($dimensionId == 6){
                    $lsr_total = $x_grand_total / $x_respondents_total;
                }
                else{
                    $lsr_total = $x_grand_total / $total_respondents;
                }
            }
           
            // SS = lsr with 3 decimals
            $ss_total = number_format($lsr_total, 3);
            $ss_totals[$dimensionId] = [
                'ss_total' => $ss_total,
            ];

            //likert sclae rating grandtotal

            $lsr_grand_total =  $lsr_grand_total + $lsr_total;
            $x_totals[$dimensionId] = [
                'x_total_score' => $x_grand_total,
            ];

            $lsr_total = number_format($lsr_total, 2);

            $likert_scale_rating_totals[$dimensionId] = [
                'lsr_total' => $lsr_total,
            ];

            $y_totals[$dimensionId] = [
                'vs_total' => $vs_total,
                's_total' => $s_total,
                'n_total' => $n_total,
                'd_total' => $d_total,
                'vd_total' => $vd_total,
            ];

            $grand_vs_total+=$vs_total;
            $grand_s_total+=$s_total;
            $grand_n_total+=$n_total;
            $grand_d_total+=$d_total;
            $grand_vd_total+=$vd_total;       
                     
            // PART II
            $vi_total = $date_range->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $i_total = $date_range->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $mi_total = $date_range->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $li_total = $date_range->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $nai_total = $date_range->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->count();
        
            $importance_rate_score_totals[$dimensionId] = [
                'vi_total' => $vi_total,
                'i_total' => $i_total,
                'mi_total' => $mi_total,
                'li_total' => $li_total,
                'nai_total' => $nai_total,
            ];

            $x_vi_total = $vi_total * 5; 
            $x_i_total = $i_total * 4; 
            $x_mi_total = $mi_total * 3; 
            $x_li_total = $li_total * 2; 
            $x_nai_total = $nai_total * 1;
            $x_importance_total = $x_vi_total + $x_i_total + $x_mi_total + $x_li_total + $x_nai_total  ; 
            
            //right side total importance rate scores 
            $x_importance_totals[$dimensionId] = [
                'x_importance_total_score' => $x_importance_total,
            ];
            
            // Likert Scale RAting 
            if($x_importance_total != 0){
                $ilsr_total = $x_importance_total / $total_respondents;
            }
            $ilsr_total = number_format($ilsr_total, 2);

            $importance_ilsr_totals[$dimensionId] = [
                'ilsr_total' => $ilsr_total,
            ];
 
            // GAP = attributes total score minus importance of attributes total score
            $gap_total=  $ilsr_total - $lsr_total;
            $gap_total = number_format($gap_total, 2);

            $gap_totals[$dimensionId] = [
                'gap_total' => $gap_total,
            ];

            $gap_grand_total += $gap_total;
            $gap_grand_total = number_format($gap_grand_total, 2);

            // WF = (importance LS Rating divided by importance grand total  of ls rating) * 100
            if($ilsr_total != 0){
                $wf_total =  ($ilsr_total / $ilsr_grand_total) * 100;
            }
            $wf_total = number_format($wf_total, 2);
            $wf_totals[$dimensionId] = [
                'wf_total' => $wf_total,
            ];

            // WS = (SS * WF) / 100  
            $ws_total = ($ss_total * $wf_total) / 100;   
            $ws_grand_total = $ws_grand_total + $ws_total;
            $ws_total = number_format($ws_total, 2);
            $ws_grand_total = number_format($ws_grand_total, 2);
            $ws_totals[$dimensionId] = [
                'ws_total' => $ws_total,
            ];
        }

        // round off Likert Scale Rating grand total and control decimal to 2 
        $lsr_grand_total = number_format($lsr_grand_total, 2);      

        // table below total score
        $grand_vs_total =   $grand_vs_total * 5;
        $grand_s_total =   $grand_s_total * 5;
        $grand_n_total =   $grand_n_total * 5;
        $grand_d_total =   $grand_d_total * 5;
        $grand_vd_total =   $grand_vd_total * 5;

        $x_grand_total =  $grand_vs_total +  $grand_s_total + $grand_n_total +  $grand_d_total +   $grand_vd_total;

        //Percentage of Respondents/Customers who rated VS/S: 
        // = total no. of respondents / total no. respondets who rated vs/s * 100
        $percentage_vss_respondents  = 0;
        if($total_respondents != 0){
            $percentage_vss_respondents  = ($total_respondents/$total_vss_respondents) * 100;
        }
        $percentage_vss_respondents = number_format( $percentage_vss_respondents , 2);

        $customer_satisfaction_rating = 0;
        if($total_vss_respondents != 0){
            $customer_satisfaction_rating = ($total_respondents/$total_vss_respondents) * 100;
        }
        $customer_satisfaction_rating = number_format( $customer_satisfaction_rating , 2);

        // Customer Satisfaction Index (CSI) = (ws grand total / 5) * 100
        $customer_satisfaction_index = 0;
        if($ws_grand_total != 0){
            $customer_satisfaction_index = ($ws_grand_total/5) * 100;
        }
        $customer_satisfaction_index = number_format( $customer_satisfaction_index , 2);
        
        if($customer_satisfaction_index > 100){
            $customer_satisfaction_index = number_format(100 , 2);
        }

        return $customer_satisfaction_index;
    }   


    // all services and its units view page
    public function all_units()
    {
        //$user = Auth::user();
        $service_units = Services::all();

        //all Services and its units
        $data = ServiceResource::collection($service_units);
        return Inertia::render('CSI/AllServicesUnits/Index')
            ->with('services_units', $data);
    
    }

    private function getAllServicesUnitsByRegion($regionId)
    {
        $services = Services::with([
            'units.pstos' => function ($query) use ($regionId) {
                $query->where('region_id', $regionId);
            },
            'units.sub_units',
            'units.sub_units.pstos' => function ($query) use ($regionId) {
                $query->where('region_id', $regionId);
            },
            'units.sub_units.sub_unit_types',
        ])->get();

        return ServiceResource::collection($services);
    }

    private function getCustomerFilterIds(Request $request)
    {
        if (!$request->client_type && !$request->sex && !$request->age_group) {
            return null;
        }

        $query = Customer::query()
            ->when($request->client_type, function ($q, $clientType) {
                if ($clientType === 'Internal') {
                    $q->where('client_type', 'Internal Employees');
                } elseif ($clientType === 'External') {
                    $q->where(function ($sub) {
                        $sub->where('client_type', 'General Public')
                            ->orWhere('client_type', 'Government Employees')
                            ->orWhere('client_type', 'Business/Organization');
                    });
                } else {
                    $q->where('client_type', $clientType);
                }
            })
            ->when($request->sex, function ($q, $sex) {
                $q->where('sex', $sex);
            })
            ->when($request->age_group, function ($q, $ageGroup) {
                $q->where('age_group', $ageGroup);
            });

        return $query->pluck('id');
    }

    private function getRespondentProfileSummary($customerIds)
    {
        $ids = collect($customerIds)->unique()->values();
        if ($ids->isEmpty()) {
            return [
                'total' => 0,
                'male' => 0,
                'female' => 0,
                'prefer_not_to_say' => 0,
                'internal' => 0,
                'external' => 0,
                'totals' => [
                    'external' => 0,
                    'internal' => 0,
                    'overall' => 0,
                ],
                'sex_table' => [],
                'age_table' => [],
            ];
        }

        $customers = Customer::whereIn('id', $ids)->select('sex', 'age_group', 'client_type')->get();

        $normalizeSex = function ($sex) {
            $value = strtolower(trim((string) $sex));
            if ($value === 'male') {
                return 'Male';
            }
            if ($value === 'female') {
                return 'Female';
            }
            return 'Did not specify';
        };

        $normalizeAge = function ($ageGroup) {
            $raw = strtolower(trim((string) $ageGroup));
            if ($raw === '' || str_contains($raw, 'prefer') || str_contains($raw, 'did not')) {
                return 'Did not specify';
            }

            if ($raw === '19 or lower') {
                return '19 or lower';
            }
            if ($raw === '20-34') {
                return '20-34';
            }
            if ($raw === '35-49') {
                return '35-49';
            }
            if ($raw === '50-64') {
                return '50-64';
            }
            if ($raw === '60+') {
                return '60+';
            }

            preg_match_all('/\d+/', $raw, $matches);
            $nums = array_map('intval', $matches[0] ?? []);
            if (count($nums) === 0) {
                return 'Did not specify';
            }

            $low = min($nums);
            $high = max($nums);

            if ($high <= 19) {
                return '19 or lower';
            }
            if ($low <= 34 && $high >= 20) {
                return '20-34';
            }
            if ($low <= 49 && $high >= 35) {
                return '35-49';
            }
            if ($low <= 64 && $high >= 50) {
                return '50-64';
            }
            if ($high >= 60) {
                return '60+';
            }

            return 'Did not specify';
        };

        $sexLabels = ['Male', 'Female', 'Did not specify'];
        $ageLabels = ['19 or lower', '20-34', '35-49', '50-64', '60+', 'Did not specify'];

        $sexCounts = ['external' => array_fill_keys($sexLabels, 0), 'internal' => array_fill_keys($sexLabels, 0), 'overall' => array_fill_keys($sexLabels, 0)];
        $ageCounts = ['external' => array_fill_keys($ageLabels, 0), 'internal' => array_fill_keys($ageLabels, 0), 'overall' => array_fill_keys($ageLabels, 0)];

        $externalTotal = 0;
        $internalTotal = 0;
        $overallTotal = $customers->count();

        foreach ($customers as $customer) {
            $group = null;
            if ($customer->client_type === 'Internal Employees') {
                $group = 'internal';
                $internalTotal++;
            } else {
                $group = 'external';
                $externalTotal++;
            }

            $sexKey = $normalizeSex($customer->sex);
            $ageKey = $normalizeAge($customer->age_group);

            $sexCounts['overall'][$sexKey]++;
            $ageCounts['overall'][$ageKey]++;

            if ($group !== null) {
                $sexCounts[$group][$sexKey]++;
                $ageCounts[$group][$ageKey]++;
            }
        }

        $buildRows = function ($labels, $counts) use ($externalTotal, $internalTotal, $overallTotal) {
            return collect($labels)->map(function ($label) use ($counts, $externalTotal, $internalTotal, $overallTotal) {
                $extCount = (int) ($counts['external'][$label] ?? 0);
                $intCount = (int) ($counts['internal'][$label] ?? 0);
                $overallCount = (int) ($counts['overall'][$label] ?? 0);

                $extPct = $extCount > 0 && $externalTotal > 0 ? number_format(($extCount / $externalTotal) * 100, 2) : '-';
                $intPct = $intCount > 0 && $internalTotal > 0 ? number_format(($intCount / $internalTotal) * 100, 2) : '-';
                $overallPct = $overallCount > 0 && $overallTotal > 0 ? number_format(($overallCount / $overallTotal) * 100, 2) : '-';

                return [
                    'label' => $label,
                    'external' => ['pct' => $extPct, 'count' => $extCount],
                    'internal' => ['pct' => $intPct, 'count' => $intCount],
                    'overall' => ['pct' => $overallPct, 'count' => $overallCount],
                ];
            })->values()->all();
        };

        return [
            'total' => $customers->count(),
            'male' => $sexCounts['overall']['Male'],
            'female' => $sexCounts['overall']['Female'],
            'prefer_not_to_say' => $sexCounts['overall']['Did not specify'],
            'internal' => $internalTotal,
            'external' => $externalTotal,
            'totals' => [
                'external' => $externalTotal,
                'internal' => $internalTotal,
                'overall' => $customers->count(),
            ],
            'sex_table' => $buildRows($sexLabels, $sexCounts),
            'age_table' => $buildRows($ageLabels, $ageCounts),
        ];
    }

    private function getRespondentProfileSummaryFromUnitCustomers($unitCustomers)
    {
        $unitCustomers = collect($unitCustomers);
        if ($unitCustomers->isEmpty()) {
            return [
                'total' => 0,
                'male' => 0,
                'female' => 0,
                'prefer_not_to_say' => 0,
                'internal' => 0,
                'external' => 0,
                'totals' => [
                    'external' => 0,
                    'internal' => 0,
                    'overall' => 0,
                ],
                'sex_table' => [],
                'age_table' => [],
            ];
        }

        $uniqueCustomerIds = $unitCustomers->pluck('customer_id')->unique()->values();
        $customersById = Customer::whereIn('id', $uniqueCustomerIds)
            ->select('id', 'sex', 'age_group', 'client_type')
            ->get()
            ->keyBy('id');

        $normalizeSex = function ($sex) {
            $value = strtolower(trim((string) $sex));
            if ($value === 'male') {
                return 'Male';
            }
            if ($value === 'female') {
                return 'Female';
            }
            return 'Did not specify';
        };

        $normalizeAge = function ($ageGroup) {
            $raw = strtolower(trim((string) $ageGroup));
            if ($raw === '' || str_contains($raw, 'prefer') || str_contains($raw, 'did not')) {
                return 'Did not specify';
            }

            if ($raw === '19 or lower') {
                return '19 or lower';
            }
            if ($raw === '20-34') {
                return '20-34';
            }
            if ($raw === '35-49') {
                return '35-49';
            }
            if ($raw === '50-64') {
                return '50-64';
            }
            if ($raw === '60+') {
                return '60+';
            }

            preg_match_all('/\d+/', $raw, $matches);
            $nums = array_map('intval', $matches[0] ?? []);
            if (count($nums) === 0) {
                return 'Did not specify';
            }

            $low = min($nums);
            $high = max($nums);

            if ($high <= 19) {
                return '19 or lower';
            }
            if ($low <= 34 && $high >= 20) {
                return '20-34';
            }
            if ($low <= 49 && $high >= 35) {
                return '35-49';
            }
            if ($low <= 64 && $high >= 50) {
                return '50-64';
            }
            if ($high >= 60) {
                return '60+';
            }

            return 'Did not specify';
        };

        $sexLabels = ['Male', 'Female', 'Did not specify'];
        $ageLabels = ['19 or lower', '20-34', '35-49', '50-64', '60+', 'Did not specify'];

        $sexCounts = ['external' => array_fill_keys($sexLabels, 0), 'internal' => array_fill_keys($sexLabels, 0), 'overall' => array_fill_keys($sexLabels, 0)];
        $ageCounts = ['external' => array_fill_keys($ageLabels, 0), 'internal' => array_fill_keys($ageLabels, 0), 'overall' => array_fill_keys($ageLabels, 0)];

        $externalTotal = 0;
        $internalTotal = 0;
        $overallTotal = 0;

        foreach ($unitCustomers as $row) {
            $customer = $customersById[$row->customer_id] ?? null;
            $overallTotal++;

            if (!$customer) {
                $sexKey = 'Did not specify';
                $ageKey = 'Did not specify';
                $group = 'external';
                $externalTotal++;

                $sexCounts['overall'][$sexKey]++;
                $ageCounts['overall'][$ageKey]++;
                $sexCounts[$group][$sexKey]++;
                $ageCounts[$group][$ageKey]++;
                continue;
            }

            $group = null;
            if ($customer->client_type === 'Internal Employees') {
                $group = 'internal';
                $internalTotal++;
            } else {
                $group = 'external';
                $externalTotal++;
            }

            $sexKey = $normalizeSex($customer->sex);
            $ageKey = $normalizeAge($customer->age_group);

            $sexCounts['overall'][$sexKey]++;
            $ageCounts['overall'][$ageKey]++;

            if ($group !== null) {
                $sexCounts[$group][$sexKey]++;
                $ageCounts[$group][$ageKey]++;
            }
        }

        $buildRows = function ($labels, $counts) use ($externalTotal, $internalTotal, $overallTotal) {
            return collect($labels)->map(function ($label) use ($counts, $externalTotal, $internalTotal, $overallTotal) {
                $extCount = (int) ($counts['external'][$label] ?? 0);
                $intCount = (int) ($counts['internal'][$label] ?? 0);
                $overallCount = (int) ($counts['overall'][$label] ?? 0);

                $extPct = $extCount > 0 && $externalTotal > 0 ? number_format(($extCount / $externalTotal) * 100, 2) : '-';
                $intPct = $intCount > 0 && $internalTotal > 0 ? number_format(($intCount / $internalTotal) * 100, 2) : '-';
                $overallPct = $overallCount > 0 && $overallTotal > 0 ? number_format(($overallCount / $overallTotal) * 100, 2) : '-';

                return [
                    'label' => $label,
                    'external' => ['pct' => $extPct, 'count' => $extCount],
                    'internal' => ['pct' => $intPct, 'count' => $intCount],
                    'overall' => ['pct' => $overallPct, 'count' => $overallCount],
                ];
            })->values()->all();
        };

        return [
            'total' => $overallTotal,
            'male' => $sexCounts['overall']['Male'],
            'female' => $sexCounts['overall']['Female'],
            'prefer_not_to_say' => $sexCounts['overall']['Did not specify'],
            'internal' => $internalTotal,
            'external' => $externalTotal,
            'totals' => [
                'external' => $externalTotal,
                'internal' => $internalTotal,
                'overall' => $overallTotal,
            ],
            'sex_table' => $buildRows($sexLabels, $sexCounts),
            'age_table' => $buildRows($ageLabels, $ageCounts),
        ];
    }

    private function buildServiceCategoryTotals($region_id, $startDate, $endDate, $customerFilterIds = null)
    {
        $serviceCustomerBase = CsfForm::query()
            ->where('region_id', $region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->select('service_id', 'customer_id')
            ->distinct();

        $respondentRows = DB::query()
            ->fromSub(clone $serviceCustomerBase, 'sc')
            ->select('service_id', DB::raw('COUNT(*) as total_respo'))
            ->groupBy('service_id')
            ->get();

        $averageRows = CustomerAttributeRating::query()
            ->joinSub(clone $serviceCustomerBase, 'sc', function ($join) {
                $join->on('customer_attribute_ratings.customer_id', '=', 'sc.customer_id');
            })
            ->whereBetween('customer_attribute_ratings.created_at', [$startDate, $endDate])
            ->where('customer_attribute_ratings.rate_score', '!=', 6)
            ->select(
                'sc.service_id',
                'customer_attribute_ratings.customer_id',
                DB::raw('AVG(customer_attribute_ratings.rate_score) as avg_score')
            )
            ->groupBy('sc.service_id', 'customer_attribute_ratings.customer_id')
            ->get();

        $recommendationRows = CustomerRecommendationRating::query()
            ->joinSub(clone $serviceCustomerBase, 'sc', function ($join) {
                $join->on('customer_recommendation_ratings.customer_id', '=', 'sc.customer_id');
            })
            ->whereBetween('customer_recommendation_ratings.created_at', [$startDate, $endDate])
            ->select(
                'sc.service_id',
                'customer_recommendation_ratings.recommend_rate_score',
                DB::raw('COUNT(DISTINCT customer_recommendation_ratings.customer_id) as total')
            )
            ->groupBy('sc.service_id', 'customer_recommendation_ratings.recommend_rate_score')
            ->get();

        $service_totals = [];
        foreach ([1, 2, 3] as $serviceId) {
            $service_totals[$serviceId] = [
                'total_respo' => 0,
                'strongly_agree_agree_count' => 0,
                'total_ratings' => 0,
                'pct_strongly_agree_agree' => '0.00',
                'nps' => '0.00',
                'lsr' => '0.00',
            ];
        }

        foreach ($respondentRows as $row) {
            if (isset($service_totals[$row->service_id])) {
                $service_totals[$row->service_id]['total_respo'] = (int) $row->total_respo;
            }
        }

        $averageScoresByService = [];
        foreach ($averageRows as $row) {
            $averageScoresByService[$row->service_id][] = (float) $row->avg_score;
        }

        $recommendationCountsByService = [];
        foreach ($recommendationRows as $row) {
            $recommendationCountsByService[$row->service_id][(int) $row->recommend_rate_score] = (int) $row->total;
        }

        foreach ([1, 2, 3] as $serviceId) {
            $totalRespo = (int) $service_totals[$serviceId]['total_respo'];
            $bucketData = $this->buildAverageBucketCounts($averageScoresByService[$serviceId] ?? []);
            $bucketCounts = $bucketData['counts'];
            $bucketTotal = $bucketData['total'];
            $vssCount = (int) (($bucketCounts[5] ?? 0) + ($bucketCounts[4] ?? 0));
            $pct = $bucketTotal > 0 ? ($vssCount / $bucketTotal) * 100 : 0;
            $service_totals[$serviceId]['strongly_agree_agree_count'] = $vssCount;
            $service_totals[$serviceId]['total_ratings'] = $bucketTotal;
            $service_totals[$serviceId]['pct_strongly_agree_agree'] = number_format($pct, 2);

            $recommendationCounts = $recommendationCountsByService[$serviceId] ?? [];
            $recommendationTotal = array_sum($recommendationCounts);
            $promoters = 0;
            $detractors = 0;
            foreach ($recommendationCounts as $score => $count) {
                if ($score >= 7 && $score <= 10) {
                    $promoters += $count;
                } elseif ($score >= 0 && $score <= 6) {
                    $detractors += $count;
                }
            }
            $nps = 0;
            if ($recommendationTotal > 0) {
                $percentage_promoters = ($promoters / $recommendationTotal) * 100;
                $percentage_detractors = ($detractors / $recommendationTotal) * 100;
                $nps = $percentage_promoters - $percentage_detractors;
            }
            $service_totals[$serviceId]['nps'] = number_format($nps, 2);

            $avgScores = $averageScoresByService[$serviceId] ?? [];
            $lsr = count($avgScores) > 0 ? (array_sum($avgScores) / count($avgScores)) : 0;
            $service_totals[$serviceId]['lsr'] = number_format($lsr, 2);
        }

        return $service_totals;
    }

    private function bucketAverageScore($avgScore)
    {
        if ($avgScore >= 4.5) {
            return 5;
        }
        if ($avgScore >= 3.5) {
            return 4;
        }
        if ($avgScore >= 2.5) {
            return 3;
        }
        if ($avgScore >= 1.5) {
            return 2;
        }
        return 1;
    }

    private function buildAverageBucketCounts($averageScores)
    {
        $counts = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];

        foreach ($averageScores as $avgScore) {
            $bucket = $this->bucketAverageScore((float) $avgScore);
            $counts[$bucket]++;
        }

        return [
            'counts' => $counts,
            'total' => array_sum($counts),
        ];
    }


    public function generateAllUnitReports(Request $request)
    {
        //dd($request->all());
        if($request->csi_type == "By Month"){
            return $this->generateCSIAllUnitMonthly($request); 
        }
        else if($request->csi_type == "By Quarter"){
            if($request->selected_quarter == "FIRST QUARTER"){
                return $this->generateCSIAllUnitFirstQuarter($request);
            }
            else if($request->selected_quarter == "SECOND QUARTER"){
                return $this->generateCSIAllUnitSecondQuarter($request);
            }
            else if($request->selected_quarter == "THIRD QUARTER"){
                return $this->generateCSIAllUnitThirdQuarter($request);
            }
            else if($request->selected_quarter == "FOURTH QUARTER"){
                return $this->generateCSIAllUnitFourthQuarter($request);
            }
          
        }
        else if($request->csi_type == "By Year/Annual"){
            return $this->generateCSIAllUnitYearly($request);  
        }
    
    }

    public function generateCSIAllUnitMonthly($request)
    {
        //get user
        $user = Auth::user();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        $numeric_month = Carbon::parse("1 {$request->selected_month}")->format('m');

        // Get customer IDs from CSFForm for the region filtered by month and year
        $csf_forms = CSFForm::where('region_id', $user->region_id)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');
        $unit_customers = CSFForm::where('region_id', $user->region_id)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->whereNull('sub_unit_id')
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct()
            ->get();
        $respondent_profile = $this->getRespondentProfileSummaryFromUnitCustomers($unit_customers);

        //PART I: Citizens Charter - filter by customer IDs in the region and by month/year
        $cc_query = CustomerCCRating::whereMonth('created_at', $numeric_month)
                                    ->whereYear('created_at', $request->selected_year)
                                    ->whereIn('customer_id', $csf_forms)
                                    ->when($request->sex, function ($query, $sex) {
                                        $query->whereHas('customer', function ($query) use ($sex) {
                                            $query->where('sex', $sex);
                                        });
                                    })
                                    ->when($request->age_group, function ($query, $age_group) {
                                        $query->whereHas('customer', function ($query) use ($age_group) {
                                            $query->where('age_group', $age_group);
                                        });
                                    });
        $cc_data = $this->calculateCC($cc_query);

        // PART II:
        // --dimensions
        // --services and units
        // --totals

        $dimensions = Dimension::all();
        $dimension_count = $dimensions->count();

        $services_units = $this->getAllServicesUnitsByRegion($user->region_id);

        // Get all units data for all services
        $all_units_data = $this->getAllUnitsData($request, $user->region_id, $numeric_month);

        //get monthly CSI
        $monthly_csi = $this->getAllUnitMonthlyCSI($request, $user->region_id, $numeric_month);
        
        // Get NPS and LSR data
        $nps_data = $this->getAllUnitNPS($request, $user->region_id, $numeric_month);
        $lsr_data = $this->getAllUnitLSR($request, $user->region_id, $numeric_month);

         //send response to front end
         return Inertia::render('CSI/AllServicesUnits/Index')
                    ->with('services_units', $services_units)
                    ->with('cc_data', $cc_data)
                    ->with('all_units_data', $all_units_data)
                    ->with('csi_total', $monthly_csi)
                    ->with('nps_total', $nps_data['nps'])
                    ->with('lsr_total', $lsr_data)
                    ->with('total_respondents', $all_units_data['grand_total_respondents'])
                    ->with('total_vss_respondents', $all_units_data['grand_total_vss_respondents'])
                    ->with('percentage_vss_respondents', $all_units_data['grand_percentage_vss_respondents'])
                    ->with('respondent_profile', $respondent_profile)
                    ->with('region', $user->region)
                    ->with('request', $request);
    }

    /**
     * Generate CSI All Unit First Quarter Report
     */
    public function generateCSIAllUnitFirstQuarter($request)
    {
        $user = Auth::user();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        // Define quarter date range (January - March)
        $startDate = Carbon::create($request->selected_year, 1, 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, 3, 31)->endOfDay();
        $numeric_months = [1, 2, 3];

        // Get customer IDs from CSFForm for the region filtered by quarter and year
        $csf_forms = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');
        $unit_customers = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->whereNull('sub_unit_id')
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct()
            ->get();
        $respondent_profile = $this->getRespondentProfileSummaryFromUnitCustomers($unit_customers);

        // CC Data with sex and age_group filtering
        $cc_query = CustomerCCRating::whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->whereIn('customer_id', $csf_forms)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            });
        $cc_data = $this->calculateCC($cc_query);

        // Get all units data
        $all_units_data = $this->getAllUnitsDataByQuarter($request, $user->region_id, $numeric_months);

        $services_units = $this->getAllServicesUnitsByRegion($user->region_id);

        // Calculate quarterly CSI
        $quarterly_csi = $this->getAllUnitQuarterlyCSI($request, $user->region_id, $numeric_months);
        
        // Get NPS and LSR data
        $nps_data = $this->getAllUnitNPSByQuarter($request, $user->region_id, $numeric_months);
        $lsr_data = $this->getAllUnitLSRByQuarter($request, $user->region_id, $numeric_months);

        return Inertia::render('CSI/AllServicesUnits/Index')
            ->with('services_units', $services_units)
            ->with('cc_data', $cc_data)
            ->with('all_units_data', $all_units_data)
            ->with('csi_total', $quarterly_csi)
            ->with('nps_total', $nps_data['nps'])
            ->with('lsr_total', $lsr_data)
            ->with('total_respondents', $all_units_data['grand_total_respondents'])
            ->with('total_vss_respondents', $all_units_data['grand_total_vss_respondents'])
            ->with('percentage_vss_respondents', $all_units_data['grand_percentage_vss_respondents'])
            ->with('respondent_profile', $respondent_profile)
            ->with('region', $user->region)
            ->with('request', $request);
    }

    /**
     * Generate CSI All Unit Second Quarter Report
     */
    public function generateCSIAllUnitSecondQuarter($request)
    {
        $user = Auth::user();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        // Define quarter date range (April - June)
        $startDate = Carbon::create($request->selected_year, 4, 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, 6, 30)->endOfDay();
        $numeric_months = [4, 5, 6];

        // Get customer IDs from CSFForm for the region filtered by quarter and year
        $csf_forms = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');
        $unit_customers = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->whereNull('sub_unit_id')
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct()
            ->get();
        $respondent_profile = $this->getRespondentProfileSummaryFromUnitCustomers($unit_customers);

        // CC Data with sex and age_group filtering
        $cc_query = CustomerCCRating::whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->whereIn('customer_id', $csf_forms)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            });
        $cc_data = $this->calculateCC($cc_query);

        // Get all units data
        $all_units_data = $this->getAllUnitsDataByQuarter($request, $user->region_id, $numeric_months);

        $services_units = $this->getAllServicesUnitsByRegion($user->region_id);

        // Calculate quarterly CSI
        $quarterly_csi = $this->getAllUnitQuarterlyCSI($request, $user->region_id, $numeric_months);
        
        // Get NPS and LSR data
        $nps_data = $this->getAllUnitNPSByQuarter($request, $user->region_id, $numeric_months);
        $lsr_data = $this->getAllUnitLSRByQuarter($request, $user->region_id, $numeric_months);

        return Inertia::render('CSI/AllServicesUnits/Index')
            ->with('services_units', $services_units)
            ->with('cc_data', $cc_data)
            ->with('all_units_data', $all_units_data)
            ->with('csi_total', $quarterly_csi)
            ->with('nps_total', $nps_data['nps'])
            ->with('lsr_total', $lsr_data)
            ->with('total_respondents', $all_units_data['grand_total_respondents'])
            ->with('total_vss_respondents', $all_units_data['grand_total_vss_respondents'])
            ->with('percentage_vss_respondents', $all_units_data['grand_percentage_vss_respondents'])
            ->with('respondent_profile', $respondent_profile)
            ->with('region', $user->region)
            ->with('request', $request);
    }

    /**
     * Generate CSI All Unit Third Quarter Report
     */
    public function generateCSIAllUnitThirdQuarter($request)
    {
        $user = Auth::user();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        // Define quarter date range (July - September)
        $startDate = Carbon::create($request->selected_year, 7, 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, 9, 30)->endOfDay();
        $numeric_months = [7, 8, 9];

        // Get customer IDs from CSFForm for the region filtered by quarter and year
        $csf_forms = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');
        $unit_customers = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->whereNull('sub_unit_id')
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct()
            ->get();
        $respondent_profile = $this->getRespondentProfileSummaryFromUnitCustomers($unit_customers);

        // CC Data with sex and age_group filtering
        $cc_query = CustomerCCRating::whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->whereIn('customer_id', $csf_forms)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            });
        $cc_data = $this->calculateCC($cc_query);

        // Get all units data
        $all_units_data = $this->getAllUnitsDataByQuarter($request, $user->region_id, $numeric_months);

        $services_units = $this->getAllServicesUnitsByRegion($user->region_id);

        // Calculate quarterly CSI
        $quarterly_csi = $this->getAllUnitQuarterlyCSI($request, $user->region_id, $numeric_months);
        
        // Get NPS and LSR data
        $nps_data = $this->getAllUnitNPSByQuarter($request, $user->region_id, $numeric_months);
        $lsr_data = $this->getAllUnitLSRByQuarter($request, $user->region_id, $numeric_months);

        return Inertia::render('CSI/AllServicesUnits/Index')
            ->with('services_units', $services_units)
            ->with('cc_data', $cc_data)
            ->with('all_units_data', $all_units_data)
            ->with('csi_total', $quarterly_csi)
            ->with('nps_total', $nps_data['nps'])
            ->with('lsr_total', $lsr_data)
            ->with('total_respondents', $all_units_data['grand_total_respondents'])
            ->with('total_vss_respondents', $all_units_data['grand_total_vss_respondents'])
            ->with('percentage_vss_respondents', $all_units_data['grand_percentage_vss_respondents'])
            ->with('respondent_profile', $respondent_profile)
            ->with('region', $user->region)
            ->with('request', $request);
    }

    /**
     * Generate CSI All Unit Fourth Quarter Report
     */
    public function generateCSIAllUnitFourthQuarter($request)
    {
        $user = Auth::user();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        // Define quarter date range (October - December)
        $startDate = Carbon::create($request->selected_year, 10, 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, 12, 31)->endOfDay();
        $numeric_months = [10, 11, 12];

        // Get customer IDs from CSFForm for the region filtered by quarter and year
        $csf_forms = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');
        $unit_customers = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->whereNull('sub_unit_id')
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct()
            ->get();
        $respondent_profile = $this->getRespondentProfileSummaryFromUnitCustomers($unit_customers);

        // CC Data with sex and age_group filtering
        $cc_query = CustomerCCRating::whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->whereIn('customer_id', $csf_forms)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            });
        $cc_data = $this->calculateCC($cc_query);

        // Get all units data
        $all_units_data = $this->getAllUnitsDataByQuarter($request, $user->region_id, $numeric_months);

        $services_units = $this->getAllServicesUnitsByRegion($user->region_id);

        // Calculate quarterly CSI
        $quarterly_csi = $this->getAllUnitQuarterlyCSI($request, $user->region_id, $numeric_months);
        
        // Get NPS and LSR data
        $nps_data = $this->getAllUnitNPSByQuarter($request, $user->region_id, $numeric_months);
        $lsr_data = $this->getAllUnitLSRByQuarter($request, $user->region_id, $numeric_months);

        return Inertia::render('CSI/AllServicesUnits/Index')
            ->with('services_units', $services_units)
            ->with('cc_data', $cc_data)
            ->with('all_units_data', $all_units_data)
            ->with('csi_total', $quarterly_csi)
            ->with('nps_total', $nps_data['nps'])
            ->with('lsr_total', $lsr_data)
            ->with('total_respondents', $all_units_data['grand_total_respondents'])
            ->with('total_vss_respondents', $all_units_data['grand_total_vss_respondents'])
            ->with('percentage_vss_respondents', $all_units_data['grand_percentage_vss_respondents'])
            ->with('respondent_profile', $respondent_profile)
            ->with('region', $user->region)
            ->with('request', $request);
    }

    /**
     * Generate CSI All Unit Yearly Report
     */
    public function generateCSIAllUnitYearly($request)
    {
        $user = Auth::user();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        // Define year date range
        $startDate = Carbon::create($request->selected_year, 1, 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, 12, 31)->endOfDay();
        $numeric_months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        // Get customer IDs from CSFForm for the region filtered by year
        $csf_forms = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');
        $unit_customers = CSFForm::where('region_id', $user->region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->whereNull('sub_unit_id')
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct()
            ->get();
        $respondent_profile = $this->getRespondentProfileSummaryFromUnitCustomers($unit_customers);

        // CC Data with sex and age_group filtering
        $cc_query = CustomerCCRating::whereBetween('created_at', [$startDate, $endDate])
            ->whereYear('created_at', $request->selected_year)
            ->whereIn('customer_id', $csf_forms)
            ->when($request->sex, function ($query, $sex) {
                $query->whereHas('customer', function ($query) use ($sex) {
                    $query->where('sex', $sex);
                });
            })
            ->when($request->age_group, function ($query, $age_group) {
                $query->whereHas('customer', function ($query) use ($age_group) {
                    $query->where('age_group', $age_group);
                });
            });
        $cc_data = $this->calculateCC($cc_query);

        // Get all units data
        $all_units_data = $this->getAllUnitsDataByQuarter($request, $user->region_id, $numeric_months);

        $services_units = $this->getAllServicesUnitsByRegion($user->region_id);

        // Calculate yearly CSI (average of all months)
        $yearly_csi = $this->getAllUnitQuarterlyCSI($request, $user->region_id, $numeric_months);
        
        // Get NPS and LSR data
        $nps_data = $this->getAllUnitNPSByQuarter($request, $user->region_id, $numeric_months);
        $lsr_data = $this->getAllUnitLSRByQuarter($request, $user->region_id, $numeric_months);

        return Inertia::render('CSI/AllServicesUnits/Index')
            ->with('services_units', $services_units)
            ->with('cc_data', $cc_data)
            ->with('all_units_data', $all_units_data)
            ->with('csi_total', $yearly_csi)
            ->with('nps_total', $nps_data['nps'])
            ->with('lsr_total', $lsr_data)
            ->with('total_respondents', $all_units_data['grand_total_respondents'])
            ->with('total_vss_respondents', $all_units_data['grand_total_vss_respondents'])
            ->with('percentage_vss_respondents', $all_units_data['grand_percentage_vss_respondents'])
            ->with('respondent_profile', $respondent_profile)
            ->with('region', $user->region)
            ->with('request', $request);
    }

    /**
     * Get all units data by quarter (for quarterly and yearly reports)
     */
    private function getAllUnitsDataByQuarter($request, $region_id, $numeric_months)
    {
        $customerFilterIds = $this->getCustomerFilterIds($request);
        $startDate = Carbon::create($request->selected_year, min($numeric_months), 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, max($numeric_months), 1)->endOfMonth()->endOfDay();

        $unitCustomersBase = CsfForm::query()
            ->where('region_id', $region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct();

        $respondentRows = DB::query()
            ->fromSub(clone $unitCustomersBase, 'uc')
            ->select('service_id', 'unit_id', DB::raw('COUNT(*) as total_respo'))
            ->groupBy('service_id', 'unit_id')
            ->get();

        $ratingRows = CustomerAttributeRating::query()
            ->joinSub(clone $unitCustomersBase, 'uc', function ($join) {
                $join->on('customer_attribute_ratings.customer_id', '=', 'uc.customer_id');
            })
            ->whereBetween('customer_attribute_ratings.created_at', [$startDate, $endDate])
            ->select(
                'uc.service_id',
                'uc.unit_id',
                'customer_attribute_ratings.rate_score',
                DB::raw('COUNT(DISTINCT customer_attribute_ratings.customer_id) as total')
            )
            ->groupBy('uc.service_id', 'uc.unit_id', 'customer_attribute_ratings.rate_score')
            ->get();

        $averageRows = CustomerAttributeRating::query()
            ->joinSub(clone $unitCustomersBase, 'uc', function ($join) {
                $join->on('customer_attribute_ratings.customer_id', '=', 'uc.customer_id');
            })
            ->whereBetween('customer_attribute_ratings.created_at', [$startDate, $endDate])
            ->where('customer_attribute_ratings.rate_score', '!=', 6)
            ->select(
                'uc.service_id',
                'uc.unit_id',
                'customer_attribute_ratings.customer_id',
                DB::raw('AVG(customer_attribute_ratings.rate_score) as avg_score')
            )
            ->groupBy('uc.service_id', 'uc.unit_id', 'customer_attribute_ratings.customer_id')
            ->get();

        $vssRows = CustomerAttributeRating::query()
            ->joinSub(clone $unitCustomersBase, 'uc', function ($join) {
                $join->on('customer_attribute_ratings.customer_id', '=', 'uc.customer_id');
            })
            ->whereBetween('customer_attribute_ratings.created_at', [$startDate, $endDate])
            ->whereIn('customer_attribute_ratings.rate_score', [4, 5])
            ->select(
                'uc.service_id',
                'uc.unit_id',
                DB::raw('COUNT(DISTINCT customer_attribute_ratings.customer_id) as total_vss_respo')
            )
            ->groupBy('uc.service_id', 'uc.unit_id')
            ->get();

        $recommendationRows = CustomerRecommendationRating::query()
            ->joinSub(clone $unitCustomersBase, 'uc', function ($join) {
                $join->on('customer_recommendation_ratings.customer_id', '=', 'uc.customer_id');
            })
            ->whereBetween('customer_recommendation_ratings.created_at', [$startDate, $endDate])
            ->select(
                'uc.service_id',
                'uc.unit_id',
                'customer_recommendation_ratings.recommend_rate_score',
                DB::raw('COUNT(DISTINCT customer_recommendation_ratings.customer_id) as total')
            )
            ->groupBy('uc.service_id', 'uc.unit_id', 'customer_recommendation_ratings.recommend_rate_score')
            ->get();

        $respondentsByUnit = [];
        foreach ($respondentRows as $row) {
            $respondentsByUnit[$row->service_id][$row->unit_id] = (int) $row->total_respo;
        }

        $ratingCountsByUnit = [];
        foreach ($ratingRows as $row) {
            $ratingCountsByUnit[$row->service_id][$row->unit_id][(int) $row->rate_score] = (int) $row->total;
        }

        $averageScoresByUnit = [];
        foreach ($averageRows as $row) {
            $averageScoresByUnit[$row->service_id][$row->unit_id][] = (float) $row->avg_score;
        }

        $vssByUnit = [];
        foreach ($vssRows as $row) {
            $vssByUnit[$row->service_id][$row->unit_id] = (int) $row->total_vss_respo;
        }

        $recommendationCountsByUnit = [];
        foreach ($recommendationRows as $row) {
            $recommendationCountsByUnit[$row->service_id][$row->unit_id][(int) $row->recommend_rate_score] = (int) $row->total;
        }

        // Get all customer IDs from CSF forms for CC calculation
        $allCustomerIds = CsfForm::where('region_id', $region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id')
            ->unique()
            ->values();

        // Get all CC ratings for these customers in one query
        $allCCRatings = CustomerCCRating::whereIn('customer_id', $allCustomerIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $services = Services::all();
        $units_data = [];
        $grand_total_respondents = 0;
        $grand_total_vss_respondents = 0;
        $grand_total_promoters = 0;
        $grand_total_detractors = 0;
        $grand_total_recommendation_respondents = 0;
        $grand_total_recommendation_respondents = 0;
        
        // Grand totals for rating percentages
        $grand_strongly_agree_count = 0;
        $grand_agree_count = 0;
        $grand_neither_count = 0;
        $grand_disagree_count = 0;
        $grand_strongly_disagree_count = 0;
        $grand_na_count = 0;

        // Service totals for SERVICE CATEGORY TOTALS SUMMARY (by service ID)
        // Service ID 1 = Office of the Regional Director
// Service ID 2 = Finance and Administrative Support Services
// Service ID 3 = Technical Operation Services
        $service_totals = [];
        foreach ([1, 2, 3] as $serviceId) {
            $service_totals[$serviceId] = [
                'total_respo' => 0,
                'strongly_agree_agree_count' => 0,
                'total_ratings' => 0,
                'pct_strongly_agree_agree' => 0,
            ];
        }

        // Grand CC totals - Calculate from ALL customer IDs (same as calculateCC function)
        // NOT by summing per-unit values, as that would miss customers who don't have unit-specific forms
        $grand_cc1_ans1 = $allCCRatings->where('cc_id', 1)->where('answer', 1)->count();
        $grand_cc1_ans2 = $allCCRatings->where('cc_id', 1)->where('answer', 2)->count();
        $grand_cc1_ans3 = $allCCRatings->where('cc_id', 1)->where('answer', 3)->count();
        $grand_cc1_ans4 = $allCCRatings->where('cc_id', 1)->where('answer', 4)->count();
        $grand_cc2_ans1 = $allCCRatings->where('cc_id', 2)->where('answer', 1)->count();
        $grand_cc2_ans2 = $allCCRatings->where('cc_id', 2)->where('answer', 2)->count();
        $grand_cc2_ans3 = $allCCRatings->where('cc_id', 2)->where('answer', 3)->count();
        $grand_cc2_ans4 = $allCCRatings->where('cc_id', 2)->where('answer', 4)->count();
        $grand_cc2_ans5 = $allCCRatings->where('cc_id', 2)->where('answer', 5)->count();
        $grand_cc3_ans1 = $allCCRatings->where('cc_id', 3)->where('answer', 1)->count();
        $grand_cc3_ans2 = $allCCRatings->where('cc_id', 3)->where('answer', 2)->count();
        $grand_cc3_ans3 = $allCCRatings->where('cc_id', 3)->where('answer', 3)->count();
        $grand_cc3_ans4 = $allCCRatings->where('cc_id', 3)->where('answer', 4)->count();

        foreach ($services as $service) {
            $service_units = Unit::where('services_id', $service->id)->get();
            
            foreach ($service_units as $unit) {
                $serviceId = $service->id;
                $unitId = $unit->id;
                $unitScoreCounts = $ratingCountsByUnit[$serviceId][$unitId] ?? [];
                $averageScores = $averageScoresByUnit[$serviceId][$unitId] ?? [];
                $bucketData = $this->buildAverageBucketCounts($averageScores);
                $bucketCounts = $bucketData['counts'];
                $bucketTotal = $bucketData['total'];

                // Total number of respondents (unique customers)
                $total_respo = (int) ($respondentsByUnit[$serviceId][$unitId] ?? 0);

                // Total number of respondents by average rating bucket (exclude N/A)
                $strongly_agree_count = (int) ($bucketCounts[5] ?? 0);
                $agree_count = (int) ($bucketCounts[4] ?? 0);
                $neither_count = (int) ($bucketCounts[3] ?? 0);
                $disagree_count = (int) ($bucketCounts[2] ?? 0);
                $strongly_disagree_count = (int) ($bucketCounts[1] ?? 0);
                $na_count = max($total_respo - $bucketTotal, 0);
                $total_vss_respo = (int) ($vssByUnit[$serviceId][$unitId] ?? 0);
                $percentage_vss_respo = $total_respo > 0
                    ? ($total_vss_respo / $total_respo) * 100
                    : 0;

                $recommendationCounts = $recommendationCountsByUnit[$serviceId][$unitId] ?? [];
                $recommendation_total = array_sum($recommendationCounts);
                $promoters = 0;
                $detractors = 0;
                foreach ($recommendationCounts as $score => $count) {
                    if ($score >= 7 && $score <= 10) {
                        $promoters += $count;
                    } elseif ($score >= 0 && $score <= 6) {
                        $detractors += $count;
                    }
                }
                $nps = 0;
                if ($recommendation_total > 0) {
                    $percentage_promoters = ($promoters / $recommendation_total) * 100;
                    $percentage_detractors = ($detractors / $recommendation_total) * 100;
                    $nps = $percentage_promoters - $percentage_detractors;
                }

                $lsr = 0;
                if (count($averageScores) > 0) {
                    $lsr = array_sum($averageScores) / count($averageScores);
                }

                // Total count for percentage calculation (respondent-based, exclude N/A)
                $total_ratings = $bucketTotal;

                // Calculate percentages
                $pct_strongly_agree = $total_ratings > 0 ? ($strongly_agree_count / $total_ratings) * 100 : 0;
                $pct_agree = $total_ratings > 0 ? ($agree_count / $total_ratings) * 100 : 0;
                $pct_neither = $total_ratings > 0 ? ($neither_count / $total_ratings) * 100 : 0;
                $pct_disagree = $total_ratings > 0 ? ($disagree_count / $total_ratings) * 100 : 0;
                $pct_strongly_disagree = $total_ratings > 0 ? ($strongly_disagree_count / $total_ratings) * 100 : 0;
                $pct_na = 0;

                $cc1_ans1 = 0;
                $cc1_ans2 = 0;
                $cc1_ans3 = 0;
                $cc1_ans4 = 0;
                $cc2_ans1 = 0;
                $cc2_ans2 = 0;
                $cc2_ans3 = 0;
                $cc2_ans4 = 0;
                $cc2_ans5 = 0;
                $cc3_ans1 = 0;
                $cc3_ans2 = 0;
                $cc3_ans3 = 0;
                $cc3_ans4 = 0;

                // Get sub-units for this unit (simplified for quarterly/yearly - no sub-unit data)
                $sub_units_data = [];

                $units_data[$serviceId][$unitId] = [
                    'unit_name' => $unit->unit_name,
                    'total_respo' => $total_respo,
                    'sub_units_data' => $sub_units_data,
                    'unit_pstos_data' => [],
                    'total_vss_respo' => $total_vss_respo,
                    'percentage_vss_respo' => number_format($percentage_vss_respo, 2),
                    'csi' => number_format(0, 2),
                    'nps' => number_format($nps, 2),
                    'lsr' => number_format($lsr, 2),
                    'strongly_agree_count' => $strongly_agree_count,
                    'agree_count' => $agree_count,
                    'neither_count' => $neither_count,
                    'disagree_count' => $disagree_count,
                    'strongly_disagree_count' => $strongly_disagree_count,
                    'na_count' => $na_count,
                    'pct_strongly_agree' => number_format($pct_strongly_agree, 2),
                    'pct_agree' => number_format($pct_agree, 2),
                    'pct_neither' => number_format($pct_neither, 2),
                    'pct_disagree' => number_format($pct_disagree, 2),
                    'pct_strongly_disagree' => number_format($pct_strongly_disagree, 2),
                    'pct_na' => number_format($pct_na, 2),
                    'strongly_agree_agree_count' => $strongly_agree_count + $agree_count,
                    'cc_data' => [
                        'cc1_ans1' => $cc1_ans1,
                        'cc1_ans2' => $cc1_ans2,
                        'cc1_ans3' => $cc1_ans3,
                        'cc1_ans4' => $cc1_ans4,
                        'cc2_ans1' => $cc2_ans1,
                        'cc2_ans2' => $cc2_ans2,
                        'cc2_ans3' => $cc2_ans3,
                        'cc2_ans4' => $cc2_ans4,
                        'cc2_ans5' => $cc2_ans5,
                        'cc3_ans1' => $cc3_ans1,
                        'cc3_ans2' => $cc3_ans2,
                        'cc3_ans3' => $cc3_ans3,
                        'cc3_ans4' => $cc3_ans4,
                    ],
                ];

                $grand_total_respondents += $total_respo;
                $grand_total_vss_respondents += $total_vss_respo;
                $grand_total_promoters += $promoters;
                $grand_total_detractors += $detractors;
                $grand_total_recommendation_respondents += $recommendation_total;
                
                $grand_strongly_agree_count += $strongly_agree_count;
                $grand_agree_count += $agree_count;
                $grand_neither_count += $neither_count;
                $grand_disagree_count += $disagree_count;
                $grand_strongly_disagree_count += $strongly_disagree_count;
                $grand_na_count += $na_count;

                // Update service totals
                if (isset($service_totals[$serviceId])) {
                    $service_totals[$serviceId]['total_respo'] += $total_respo;
                    $service_totals[$serviceId]['strongly_agree_agree_count'] += $strongly_agree_count + $agree_count;
                    $service_totals[$serviceId]['total_ratings'] += $total_ratings;
                }
            }
        }

        // Grand percentage for VSS
        $grand_percentage_vss_respondents = 0;
        if ($grand_total_respondents > 0 && $grand_total_vss_respondents > 0) {
            $grand_percentage_vss_respondents = ($grand_total_vss_respondents / $grand_total_respondents) * 100;
        }

        // Grand NPS
        $grand_nps = 0;
        if ($grand_total_recommendation_respondents > 0) {
            $grand_percentage_promoters = ($grand_total_promoters / $grand_total_recommendation_respondents) * 100;
            $grand_percentage_detractors = ($grand_total_detractors / $grand_total_recommendation_respondents) * 100;
            $grand_nps = $grand_percentage_promoters - $grand_percentage_detractors;
        }

        // Grand CC totals
        $grand_cc1_total = $grand_cc1_ans1 + $grand_cc1_ans2 + $grand_cc1_ans3 + $grand_cc1_ans4;
        $grand_cc2_total = $grand_cc2_ans1 + $grand_cc2_ans2 + $grand_cc2_ans3 + $grand_cc2_ans4 + $grand_cc2_ans5;
        $grand_cc3_total = $grand_cc3_ans1 + $grand_cc3_ans2 + $grand_cc3_ans3 + $grand_cc3_ans4;

        // Grand total ratings for percentage calculation (respondent-based)
        $grand_total_ratings = max($grand_total_respondents - $grand_na_count, 0);
        
        // Grand percentages for rating categories
        $grand_pct_strongly_agree = $grand_total_ratings > 0 ? ($grand_strongly_agree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_agree = $grand_total_ratings > 0 ? ($grand_agree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_neither = $grand_total_ratings > 0 ? ($grand_neither_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_disagree = $grand_total_ratings > 0 ? ($grand_disagree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_strongly_disagree = $grand_total_ratings > 0 ? ($grand_strongly_disagree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_na = 0;

        // Recalculate service category summary using unique respondents per service
        $service_totals = $this->buildServiceCategoryTotals($region_id, $startDate, $endDate, $customerFilterIds);
        $grand_strongly_agree_agree_count = 0;
        $grand_total_non_na_respondents = 0;
        foreach ([1, 2, 3] as $serviceId) {
            $grand_strongly_agree_agree_count += (int) ($service_totals[$serviceId]['strongly_agree_agree_count'] ?? 0);
            $grand_total_non_na_respondents += (int) ($service_totals[$serviceId]['total_ratings'] ?? 0);
        }
        $grand_pct_strongly_agree_agree = $grand_total_respondents > 0
            ? ($grand_strongly_agree_agree_count / $grand_total_respondents) * 100
            : 0;

        return [
            'units_data' => $units_data,
            'grand_total_respondents' => $grand_total_respondents,
            'grand_total_vss_respondents' => $grand_total_vss_respondents,
            'grand_percentage_vss_respondents' => number_format($grand_percentage_vss_respondents, 2),
            'grand_nps' => number_format($grand_nps, 2),
            'grand_pct_strongly_agree' => number_format($grand_pct_strongly_agree, 2),
            'grand_pct_agree' => number_format($grand_pct_agree, 2),
            'grand_pct_neither' => number_format($grand_pct_neither, 2),
            'grand_pct_disagree' => number_format($grand_pct_disagree, 2),
            'grand_pct_strongly_disagree' => number_format($grand_pct_strongly_disagree, 2),
            'grand_pct_na' => number_format($grand_pct_na, 2),
            'grand_cc_data' => [
                'cc1_ans1' => $grand_cc1_ans1,
                'cc1_ans2' => $grand_cc1_ans2,
                'cc1_ans3' => $grand_cc1_ans3,
                'cc1_ans4' => $grand_cc1_ans4,
                'cc1_total' => $grand_cc1_total,
                'cc2_ans1' => $grand_cc2_ans1,
                'cc2_ans2' => $grand_cc2_ans2,
                'cc2_ans3' => $grand_cc2_ans3,
                'cc2_ans4' => $grand_cc2_ans4,
                'cc2_ans5' => $grand_cc2_ans5,
                'cc2_total' => $grand_cc2_total,
                'cc3_ans1' => $grand_cc3_ans1,
                'cc3_ans2' => $grand_cc3_ans2,
                'cc3_ans3' => $grand_cc3_ans3,
                'cc3_ans4' => $grand_cc3_ans4,
                'cc3_total' => $grand_cc3_total,
            ],
            'service_totals' => $service_totals,
            'grand_strongly_agree_agree_count' => $grand_strongly_agree_agree_count,
            'grand_pct_strongly_agree_agree' => number_format($grand_pct_strongly_agree_agree, 2),
            'grand_total_non_na_respondents' => $grand_total_non_na_respondents,
        ];
    }

    /**
     * Get quarterly/yearly CSI for all units
     */
    private function getAllUnitQuarterlyCSI($request, $region_id, $numeric_months)
    {
        $startDate = Carbon::create($request->selected_year, min($numeric_months), 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, max($numeric_months), 1)->endOfMonth()->endOfDay();
        return number_format($this->calculateCsiForPeriod($region_id, $startDate, $endDate), 2);
    }

    /**
     * Get quarterly/yearly NPS for all units
     */
    private function getAllUnitNPSByQuarter($request, $region_id, $numeric_months)
    {
        $startDate = Carbon::create($request->selected_year, min($numeric_months), 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, max($numeric_months), 1)->endOfMonth()->endOfDay();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        $customer_ids = CsfForm::where('region_id', $region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id')
            ->unique()
            ->values();

        $total_respondents = CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('customer_id')
            ->count('customer_id');
        $total_promoters = CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereBetween('recommend_rate_score', [7, 10])
            ->distinct('customer_id')
            ->count('customer_id');
        $total_detractors = CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereBetween('recommend_rate_score', [0, 6])
            ->distinct('customer_id')
            ->count('customer_id');
        
        $percentage_promoters = $total_respondents > 0 ? ($total_promoters / $total_respondents) * 100 : 0;
        $percentage_detractors = $total_respondents > 0 ? ($total_detractors / $total_respondents) * 100 : 0;
        $nps = $percentage_promoters - $percentage_detractors;

        return [
            'nps' => number_format($nps, 2),
            'percentage_promoters' => number_format($percentage_promoters, 2),
            'percentage_detractors' => number_format($percentage_detractors, 2),
            'total_respondents' => $total_respondents,
            'promoters' => $total_promoters,
            'detractors' => $total_detractors,
        ];
    }

    /**
     * Get quarterly/yearly LSR for all units
     */
    private function getAllUnitLSRByQuarter($request, $region_id, $numeric_months)
    {
        $startDate = Carbon::create($request->selected_year, min($numeric_months), 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, max($numeric_months), 1)->endOfMonth()->endOfDay();
        $customerFilterIds = $this->getCustomerFilterIds($request);

        $customer_ids = CsfForm::where('region_id', $region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id')
            ->unique()
            ->values();

        $avgScore = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('rate_score', '!=', 6)
            ->select('customer_id', DB::raw('AVG(rate_score) as avg_score'))
            ->groupBy('customer_id')
            ->get()
            ->avg('avg_score');

        return number_format($avgScore ?: 0, 2);
    }

    private function calculateCsiForPeriod($region_id, $startDate, $endDate)
    {
        $customerFilterIds = $this->getCustomerFilterIds(request());
        $customer_ids = CsfForm::where('region_id', $region_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id')
            ->unique()
            ->values();

        if ($customer_ids->isEmpty()) {
            return 0;
        }

        $total_respondents = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('customer_id')
            ->count('customer_id');

        if ($total_respondents === 0) {
            return 0;
        }

        $dimension_count = Dimension::count();

        $serviceRows = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('dimension_id', 'rate_score', DB::raw('COUNT(*) as total'))
            ->groupBy('dimension_id', 'rate_score')
            ->get();

        $importanceRows = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('dimension_id', 'importance_rate_score', DB::raw('COUNT(*) as total'))
            ->groupBy('dimension_id', 'importance_rate_score')
            ->get();

        $serviceCounts = [];
        foreach ($serviceRows as $row) {
            $serviceCounts[$row->dimension_id][$row->rate_score] = (int) $row->total;
        }

        $importanceCounts = [];
        foreach ($importanceRows as $row) {
            $importanceCounts[$row->dimension_id][$row->importance_rate_score] = (int) $row->total;
        }

        $ilsrByDimension = [];
        $ilsr_grand_total = 0;

        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            $vi_total = (int) ($importanceCounts[$dimensionId][5] ?? 0);
            $i_total = (int) ($importanceCounts[$dimensionId][4] ?? 0);
            $mi_total = (int) ($importanceCounts[$dimensionId][3] ?? 0);
            $li_total = (int) ($importanceCounts[$dimensionId][2] ?? 0);
            $nai_total = (int) ($importanceCounts[$dimensionId][1] ?? 0);

            $x_importance_total = ($vi_total * 5) + ($i_total * 4) + ($mi_total * 3) + ($li_total * 2) + $nai_total;
            $ilsr = $x_importance_total > 0 ? ($x_importance_total / $total_respondents) : 0;

            $ilsrByDimension[$dimensionId] = $ilsr;
            $ilsr_grand_total += $ilsr;
        }

        $ws_grand_total = 0;

        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            $vs_total = (int) ($serviceCounts[$dimensionId][5] ?? 0);
            $s_total = (int) ($serviceCounts[$dimensionId][4] ?? 0);
            $n_total = (int) ($serviceCounts[$dimensionId][3] ?? 0);
            $d_total = (int) ($serviceCounts[$dimensionId][2] ?? 0);
            $vd_total = (int) ($serviceCounts[$dimensionId][1] ?? 0);

            $x_grand_total = ($vs_total * 5) + ($s_total * 4) + ($n_total * 3) + ($d_total * 2) + $vd_total;
            $x_respondents_total = $vs_total + $s_total + $n_total + $d_total + $vd_total;

            $lsr = 0;
            if ($x_grand_total > 0) {
                if ($dimensionId == 6 && $x_respondents_total > 0) {
                    $lsr = $x_grand_total / $x_respondents_total;
                } else {
                    $lsr = $x_grand_total / $total_respondents;
                }
            }

            $wf = 0;
            if ($ilsr_grand_total > 0) {
                $wf = ($ilsrByDimension[$dimensionId] / $ilsr_grand_total) * 100;
            }

            $ws_grand_total += ($lsr * $wf) / 100;
        }

        $csi = $ws_grand_total > 0 ? ($ws_grand_total / 5) * 100 : 0;
        return $csi > 100 ? 100 : $csi;
    }

    /**
     * Get all units data for all services with per-unit calculations
     */
private function getAllUnitsData($request, $region_id, $numeric_month)
{
        $customerFilterIds = $this->getCustomerFilterIds($request);
        $startDate = Carbon::create($request->selected_year, $numeric_month, 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, $numeric_month, 1)->endOfMonth()->endOfDay();
        // Pre-fetch ALL data in bulk queries to avoid N+1 problem
        // This is much faster than querying for each unit/sub-unit individually
        
        // Get all CSF forms for the region and time period
        $allCsfForms = CsfForm::where('region_id', $region_id)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->select('service_id', 'unit_id', 'customer_id')
            ->distinct()
            ->get()
            ->groupBy(function($form) {
                return $form->service_id . '_' . $form->unit_id;
            });

        // Get all customer IDs from CSF forms
        $allCustomerIds = CsfForm::where('region_id', $region_id)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id')
            ->unique()
            ->values();

        // Get all attribute ratings for these customers in one query
        $allAttributeRatings = CustomerAttributeRating::whereIn('customer_id', $allCustomerIds)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->get();

        // Get all recommendation ratings in one query
        $allRecommendationRatings = CustomerRecommendationRating::whereIn('customer_id', $allCustomerIds)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->get();

        // Get all CC ratings in one query
        $allCCRatings = CustomerCCRating::whereIn('customer_id', $allCustomerIds)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->get();

        $services = Services::all();
        $units_data = [];
        $grand_total_respondents = 0;
        $grand_total_vss_respondents = 0;
        $grand_total_promoters = 0;
        $grand_total_detractors = 0;

        // Grand totals for rating percentages
        $grand_strongly_agree_count = 0;
        $grand_agree_count = 0;
        $grand_neither_count = 0;
        $grand_disagree_count = 0;
        $grand_strongly_disagree_count = 0;
        $grand_na_count = 0;

        // Service totals for SERVICE CATEGORY TOTALS SUMMARY (by service ID)
        $service_totals = [];
        foreach ([1, 2, 3] as $serviceId) {
            $service_totals[$serviceId] = [
                'total_respo' => 0,
                'strongly_agree_agree_count' => 0,
                'total_ratings' => 0,
                'pct_strongly_agree_agree' => 0,
            ];
        }

        // Grand CC totals - Calculate from ALL customer IDs (same as calculateCC function)
        // NOT by summing per-unit values, as that would miss customers who don't have unit-specific forms
        $grand_cc1_ans1 = $allCCRatings->where('cc_id', 1)->where('answer', 1)->count();
        $grand_cc1_ans2 = $allCCRatings->where('cc_id', 1)->where('answer', 2)->count();
        $grand_cc1_ans3 = $allCCRatings->where('cc_id', 1)->where('answer', 3)->count();
        $grand_cc1_ans4 = $allCCRatings->where('cc_id', 1)->where('answer', 4)->count();
        $grand_cc2_ans1 = $allCCRatings->where('cc_id', 2)->where('answer', 1)->count();
        $grand_cc2_ans2 = $allCCRatings->where('cc_id', 2)->where('answer', 2)->count();
        $grand_cc2_ans3 = $allCCRatings->where('cc_id', 2)->where('answer', 3)->count();
        $grand_cc2_ans4 = $allCCRatings->where('cc_id', 2)->where('answer', 4)->count();
        $grand_cc2_ans5 = $allCCRatings->where('cc_id', 2)->where('answer', 5)->count();
        $grand_cc3_ans1 = $allCCRatings->where('cc_id', 3)->where('answer', 1)->count();
        $grand_cc3_ans2 = $allCCRatings->where('cc_id', 3)->where('answer', 2)->count();
        $grand_cc3_ans3 = $allCCRatings->where('cc_id', 3)->where('answer', 3)->count();
        $grand_cc3_ans4 = $allCCRatings->where('cc_id', 3)->where('answer', 4)->count();

        $dimensions = Dimension::all();
        $dimension_count = $dimensions->count();

        foreach ($services as $service) {
            $service_units = Unit::where('services_id', $service->id)->get();

            foreach ($service_units as $unit) {
                // Get customer IDs for this unit from pre-fetched data
                $key = $service->id . '_' . $unit->id;
                $unitForms = $allCsfForms->get($key, collect());
                $customer_ids = $unitForms->pluck('customer_id')->unique()->values();
                
                // Total number of respondents (unique customers)
                $total_respo = $customer_ids->count();

                // Get attribute ratings for this unit from pre-fetched data
                $attribute_ratings = $allAttributeRatings->whereIn('customer_id', $customer_ids);

                // Total number of respondents who rated 5 or 4
                $total_vss_respo = $attribute_ratings->whereIn('rate_score', [4, 5])->groupBy('customer_id')->count();

                // Percentage of respondents who rated 5 or 4
                $percentage_vss_respo = 0;
                if ($total_respo > 0 && $total_vss_respo > 0) {
                    $percentage_vss_respo = ($total_vss_respo / $total_respo) * 100;
                }

                // Get recommendation ratings from pre-fetched data
                $recommendation_ratings = $allRecommendationRatings->whereIn('customer_id', $customer_ids);

                $recommendation_respondents = $recommendation_ratings->groupBy('customer_id')->count();

                // Calculate NPS per unit
                $promoters = $recommendation_ratings->whereBetween('recommend_rate_score', [7, 10])->groupBy('customer_id')->count();
                $detractors = $recommendation_ratings->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();

                $nps = 0;
                if ($recommendation_respondents > 0) {
                    $percentage_promoters = ($promoters / $recommendation_respondents) * 100;
                    $percentage_detractors = ($detractors / $recommendation_respondents) * 100;
                    $nps = $percentage_promoters - $percentage_detractors;
                }

                // Calculate LSR per unit
                $lsr = 0;
                $lsr_ratings = $attribute_ratings->where('rate_score', '!=', 6);
                if ($lsr_ratings->count() > 0) {
                    $average_scores = $lsr_ratings
                        ->groupBy('customer_id')
                        ->map(function ($rows) {
                            return $rows->avg('rate_score');
                        })
                        ->values();
                    $lsr = $average_scores->count() > 0 ? $average_scores->avg() : 0;
                }

                // Calculate CSI per unit
                $unit_csi = $this->calculateUnitCSI($attribute_ratings, $total_respo, $dimension_count);

                // Calculate percentage breakdown using respondent average scores (exclude N/A)
                $averageScores = $attribute_ratings
                    ->where('rate_score', '!=', 6)
                    ->groupBy('customer_id')
                    ->map(function ($rows) {
                        return $rows->avg('rate_score');
                    })
                    ->values();
                $bucketData = $this->buildAverageBucketCounts($averageScores);
                $bucketCounts = $bucketData['counts'];
                $bucketTotal = $bucketData['total'];

                $strongly_agree_count = (int) ($bucketCounts[5] ?? 0);
                $agree_count = (int) ($bucketCounts[4] ?? 0);
                $neither_count = (int) ($bucketCounts[3] ?? 0);
                $disagree_count = (int) ($bucketCounts[2] ?? 0);
                $strongly_disagree_count = (int) ($bucketCounts[1] ?? 0);
                $na_count = max($total_respo - $bucketTotal, 0);

                $total_ratings = $bucketTotal;

                $pct_strongly_agree = $total_ratings > 0 ? ($strongly_agree_count / $total_ratings) * 100 : 0;
                $pct_agree = $total_ratings > 0 ? ($agree_count / $total_ratings) * 100 : 0;
                $pct_neither = $total_ratings > 0 ? ($neither_count / $total_ratings) * 100 : 0;
                $pct_disagree = $total_ratings > 0 ? ($disagree_count / $total_ratings) * 100 : 0;
                $pct_strongly_disagree = $total_ratings > 0 ? ($strongly_disagree_count / $total_ratings) * 100 : 0;
                $pct_na = 0;

                // Get CC data from pre-fetched data
                $cc_ratings = $allCCRatings->whereIn('customer_id', $customer_ids);

                $cc1_ans1 = $cc_ratings->where('cc_id', 1)->where('answer', 1)->count();
                $cc1_ans2 = $cc_ratings->where('cc_id', 1)->where('answer', 2)->count();
                $cc1_ans3 = $cc_ratings->where('cc_id', 1)->where('answer', 3)->count();
                $cc1_ans4 = $cc_ratings->where('cc_id', 1)->where('answer', 4)->count();
                $cc2_ans1 = $cc_ratings->where('cc_id', 2)->where('answer', 1)->count();
                $cc2_ans2 = $cc_ratings->where('cc_id', 2)->where('answer', 2)->count();
                $cc2_ans3 = $cc_ratings->where('cc_id', 2)->where('answer', 3)->count();
                $cc2_ans4 = $cc_ratings->where('cc_id', 2)->where('answer', 4)->count();
                $cc2_ans5 = $cc_ratings->where('cc_id', 2)->where('answer', 5)->count();
                $cc3_ans1 = $cc_ratings->where('cc_id', 3)->where('answer', 1)->count();
                $cc3_ans2 = $cc_ratings->where('cc_id', 3)->where('answer', 2)->count();
                $cc3_ans3 = $cc_ratings->where('cc_id', 3)->where('answer', 3)->count();
                $cc3_ans4 = $cc_ratings->where('cc_id', 3)->where('answer', 4)->count();

                // Get sub-units - simplified for now (no detailed sub-unit data in optimized version)
                $sub_units_data = [];

                $units_data[$service->id][$unit->id] = [
                    'unit_name' => $unit->unit_name,
                    'total_respo' => $total_respo,
                    'sub_units_data' => $sub_units_data,
                    'unit_pstos_data' => [],
                    'total_vss_respo' => $total_vss_respo,
                    'percentage_vss_respo' => number_format($percentage_vss_respo, 2),
                    'csi' => number_format($unit_csi, 2),
                    'nps' => number_format($nps, 2),
                    'lsr' => number_format($lsr, 2),
                    'strongly_agree_count' => $strongly_agree_count,
                    'agree_count' => $agree_count,
                    'neither_count' => $neither_count,
                    'disagree_count' => $disagree_count,
                    'strongly_disagree_count' => $strongly_disagree_count,
                    'na_count' => $na_count,
                    'pct_strongly_agree' => number_format($pct_strongly_agree, 2),
                    'pct_agree' => number_format($pct_agree, 2),
                    'pct_neither' => number_format($pct_neither, 2),
                    'pct_disagree' => number_format($pct_disagree, 2),
                    'pct_strongly_disagree' => number_format($pct_strongly_disagree, 2),
                    'pct_na' => number_format($pct_na, 2),
                    'strongly_agree_agree_count' => $strongly_agree_count + $agree_count,
                    'cc_data' => [
                        'cc1_ans1' => $cc1_ans1,
                        'cc1_ans2' => $cc1_ans2,
                        'cc1_ans3' => $cc1_ans3,
                        'cc1_ans4' => $cc1_ans4,
                        'cc2_ans1' => $cc2_ans1,
                        'cc2_ans2' => $cc2_ans2,
                        'cc2_ans3' => $cc2_ans3,
                        'cc2_ans4' => $cc2_ans4,
                        'cc2_ans5' => $cc2_ans5,
                        'cc3_ans1' => $cc3_ans1,
                        'cc3_ans2' => $cc3_ans2,
                        'cc3_ans3' => $cc3_ans3,
                        'cc3_ans4' => $cc3_ans4,
                    ],
                ];

                $grand_total_respondents += $total_respo;
                $grand_total_vss_respondents += $total_vss_respo;
                $grand_total_promoters += $promoters;
                $grand_total_detractors += $detractors;
                $grand_total_recommendation_respondents += $recommendation_respondents;

                $grand_strongly_agree_count += $strongly_agree_count;
                $grand_agree_count += $agree_count;
                $grand_neither_count += $neither_count;
                $grand_disagree_count += $disagree_count;
                $grand_strongly_disagree_count += $strongly_disagree_count;
                $grand_na_count += $na_count;

                // Update service totals
                $serviceId = $service->id;
                if (isset($service_totals[$serviceId])) {
                    $service_totals[$serviceId]['total_respo'] += $total_respo;
                    $service_totals[$serviceId]['strongly_agree_agree_count'] += $strongly_agree_count + $agree_count;
                    $service_totals[$serviceId]['total_ratings'] += $total_ratings;
                }
            }
        }

        // Grand percentage for VSS
        $grand_percentage_vss_respondents = 0;
        if ($grand_total_respondents > 0 && $grand_total_vss_respondents > 0) {
            $grand_percentage_vss_respondents = ($grand_total_vss_respondents / $grand_total_respondents) * 100;
        }

        // Grand NPS
        $grand_nps = 0;
        if ($grand_total_recommendation_respondents > 0) {
            $grand_percentage_promoters = ($grand_total_promoters / $grand_total_recommendation_respondents) * 100;
            $grand_percentage_detractors = ($grand_total_detractors / $grand_total_recommendation_respondents) * 100;
            $grand_nps = $grand_percentage_promoters - $grand_percentage_detractors;
        }

        // Grand CC totals
        $grand_cc1_total = $grand_cc1_ans1 + $grand_cc1_ans2 + $grand_cc1_ans3 + $grand_cc1_ans4;
        $grand_cc2_total = $grand_cc2_ans1 + $grand_cc2_ans2 + $grand_cc2_ans3 + $grand_cc2_ans4 + $grand_cc2_ans5;
        $grand_cc3_total = $grand_cc3_ans1 + $grand_cc3_ans2 + $grand_cc3_ans3 + $grand_cc3_ans4;

        $grand_total_ratings = max($grand_total_respondents - $grand_na_count, 0);

        $grand_pct_strongly_agree = $grand_total_ratings > 0 ? ($grand_strongly_agree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_agree = $grand_total_ratings > 0 ? ($grand_agree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_neither = $grand_total_ratings > 0 ? ($grand_neither_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_disagree = $grand_total_ratings > 0 ? ($grand_disagree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_strongly_disagree = $grand_total_ratings > 0 ? ($grand_strongly_disagree_count / $grand_total_ratings) * 100 : 0;
        $grand_pct_na = 0;

        // Recalculate service category summary using unique respondents per service
        $service_totals = $this->buildServiceCategoryTotals($region_id, $startDate, $endDate, $customerFilterIds);
        $grand_strongly_agree_agree_count = 0;
        $grand_total_non_na_respondents = 0;
        foreach ([1, 2, 3] as $serviceId) {
            $grand_strongly_agree_agree_count += (int) ($service_totals[$serviceId]['strongly_agree_agree_count'] ?? 0);
            $grand_total_non_na_respondents += (int) ($service_totals[$serviceId]['total_ratings'] ?? 0);
        }
        $grand_pct_strongly_agree_agree = $grand_total_respondents > 0
            ? ($grand_strongly_agree_agree_count / $grand_total_respondents) * 100
            : 0;

        return [
            'units_data' => $units_data,
            'grand_total_respondents' => $grand_total_respondents,
            'grand_total_vss_respondents' => $grand_total_vss_respondents,
            'grand_percentage_vss_respondents' => number_format($grand_percentage_vss_respondents, 2),
            'grand_nps' => number_format($grand_nps, 2),
            'grand_pct_strongly_agree' => number_format($grand_pct_strongly_agree, 2),
            'grand_pct_agree' => number_format($grand_pct_agree, 2),
            'grand_pct_neither' => number_format($grand_pct_neither, 2),
            'grand_pct_disagree' => number_format($grand_pct_disagree, 2),
            'grand_pct_strongly_disagree' => number_format($grand_pct_strongly_disagree, 2),
            'grand_pct_na' => number_format($grand_pct_na, 2),
            'grand_cc_data' => [
                'cc1_ans1' => $grand_cc1_ans1,
                'cc1_ans2' => $grand_cc1_ans2,
                'cc1_ans3' => $grand_cc1_ans3,
                'cc1_ans4' => $grand_cc1_ans4,
                'cc1_total' => $grand_cc1_total,
                'cc2_ans1' => $grand_cc2_ans1,
                'cc2_ans2' => $grand_cc2_ans2,
                'cc2_ans3' => $grand_cc2_ans3,
                'cc2_ans4' => $grand_cc2_ans4,
                'cc2_ans5' => $grand_cc2_ans5,
                'cc2_total' => $grand_cc2_total,
                'cc3_ans1' => $grand_cc3_ans1,
                'cc3_ans2' => $grand_cc3_ans2,
                'cc3_ans3' => $grand_cc3_ans3,
                'cc3_ans4' => $grand_cc3_ans4,
                'cc3_total' => $grand_cc3_total,
            ],
            'service_totals' => $service_totals,
            'grand_strongly_agree_agree_count' => $grand_strongly_agree_agree_count,
            'grand_pct_strongly_agree_agree' => number_format($grand_pct_strongly_agree_agree, 2),
            'grand_total_non_na_respondents' => $grand_total_non_na_respondents,
        ];
    }

    /**
     * Calculate CSI for a single unit using Weighted Sum method
     */
    private function calculateUnitCSI($attribute_ratings, $total_respondents, $dimension_count)
    {
        if ($total_respondents == 0 || $attribute_ratings->count() == 0) {
            return 0;
        }

        $ilsr_grand_total = 0;
        $ws_grand_total = 0;

        // Calculate importance and service quality ratings for each dimension
        for ($dimensionId = 1; $dimensionId <= $dimension_count; $dimensionId++) {
            // Importance ratings
            $vi_total = $attribute_ratings->where('importance_rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $i_total = $attribute_ratings->where('importance_rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $mi_total = $attribute_ratings->where('importance_rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $li_total = $attribute_ratings->where('importance_rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $nai_total = $attribute_ratings->where('importance_rate_score', 1)->where('dimension_id', $dimensionId)->count();

            $x_vi_total = $vi_total * 5; 
            $x_i_total = $i_total * 4; 
            $x_mi_total = $mi_total * 3; 
            $x_li_total = $li_total * 2; 
            $x_nai_total = $nai_total * 1;
            $x_importance_total = $x_vi_total + $x_i_total + $x_mi_total + $x_li_total + $x_nai_total;

            // Importance Likert Scale Rating 
            $ilsr = 0;
            if ($x_importance_total != 0) {
                $ilsr = $x_importance_total / $total_respondents;
                $ilsr_grand_total += $ilsr;
            }

            // Service quality ratings
            $vs_total = $attribute_ratings->where('rate_score', 5)->where('dimension_id', $dimensionId)->count();
            $s_total = $attribute_ratings->where('rate_score', 4)->where('dimension_id', $dimensionId)->count();
            $n_total = $attribute_ratings->where('rate_score', 3)->where('dimension_id', $dimensionId)->count();
            $d_total = $attribute_ratings->where('rate_score', 2)->where('dimension_id', $dimensionId)->count();
            $vd_total = $attribute_ratings->where('rate_score', 1)->where('dimension_id', $dimensionId)->count();

            $x_vs_total = $vs_total * 5; 
            $x_s_total = $s_total * 4; 
            $x_n_total = $n_total * 3; 
            $x_d_total = $d_total * 2; 
            $x_vd_total = $vd_total * 1; 
            $x_respondents_total = $vs_total + $s_total + $n_total + $d_total + $vd_total;
            $x_grand_total = $x_vs_total + $x_s_total + $x_n_total + $x_d_total + $x_vd_total;

            // Likert Scale Rating (Service Satisfaction)
            $lsr = 0;
            if ($x_grand_total != 0) {
                if ($dimensionId == 6) {
                    $lsr = $x_grand_total / $x_respondents_total;
                } else {
                    $lsr = $x_grand_total / $total_respondents;
                }
            }

            // Weighted Factor
            $wf = 0;
            if ($ilsr_grand_total > 0) {
                $wf = ($ilsr / $ilsr_grand_total) * 100;
            }

            // Weighted Score
            $ws = ($lsr * $wf) / 100;
            $ws_grand_total += $ws;
        }

        // CSI = (WS grand total / 5) * 100
        $csi = 0;
        if ($ws_grand_total > 0) {
            $csi = ($ws_grand_total / 5) * 100;
        }

        if ($csi > 100) {
            $csi = 100;
        }

        return $csi;
    }

    /**
     * Get NPS for all units
     */
    private function getAllUnitNPS($request, $region_id, $numeric_month)
    {
        $customerFilterIds = $this->getCustomerFilterIds($request);
        // Get all customer IDs for the region filtered by month and year
        $customer_ids = CsfForm::where('region_id', $region_id)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');

        // Get recommendation ratings for the month filtered by month and year
        $recommendation_ratings = CustomerRecommendationRating::whereIn('customer_id', $customer_ids)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->get();

        $total_respondents = $recommendation_ratings->groupBy('customer_id')->count();
        
        // Promoters: 7-10, Detractors: 0-6
        $promoters = $recommendation_ratings->whereBetween('recommend_rate_score', [7, 10])->groupBy('customer_id')->count();
        $detractors = $recommendation_ratings->whereBetween('recommend_rate_score', [0, 6])->groupBy('customer_id')->count();

        $percentage_promoters = 0;
        $percentage_detractors = 0;
        $nps = 0;

        if ($total_respondents > 0) {
            $percentage_promoters = ($promoters / $total_respondents) * 100;
            $percentage_detractors = ($detractors / $total_respondents) * 100;
            $nps = $percentage_promoters - $percentage_detractors;
        }

        return [
            'nps' => number_format($nps, 2),
            'percentage_promoters' => number_format($percentage_promoters, 2),
            'percentage_detractors' => number_format($percentage_detractors, 2),
            'total_respondents' => $total_respondents,
            'promoters' => $promoters,
            'detractors' => $detractors,
        ];
    }

    /**
     * Get Likert Scale Rating for all units
     */
    private function getAllUnitLSR($request, $region_id, $numeric_month)
    {
        $customerFilterIds = $this->getCustomerFilterIds($request);
        // Get all customer IDs for the region
        $customer_ids = CsfForm::where('region_id', $region_id)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->when($customerFilterIds !== null, function ($query) use ($customerFilterIds) {
                $query->whereIn('customer_id', $customerFilterIds);
            })
            ->pluck('customer_id');

        $avgScore = CustomerAttributeRating::whereIn('customer_id', $customer_ids)
            ->whereMonth('created_at', $numeric_month)
            ->whereYear('created_at', $request->selected_year)
            ->where('rate_score', '!=', 6)
            ->select('customer_id', DB::raw('AVG(rate_score) as avg_score'))
            ->groupBy('customer_id')
            ->get()
            ->avg('avg_score');

        return number_format($avgScore ?: 0, 2);
    }

    public function getAllUnitMonthlyCSI($request, $region_id, $numeric_month)
    {        
        $startDate = Carbon::create($request->selected_year, $numeric_month, 1)->startOfDay();
        $endDate = Carbon::create($request->selected_year, $numeric_month, 1)->endOfMonth()->endOfDay();
        return number_format($this->calculateCsiForPeriod($region_id, $startDate, $endDate), 2);
    }  

    public function calculateCC($cc_query)
    {  
           // Clone the original query builder instance
        $cc_query_clone = clone $cc_query;

        // CC 1 
        $cc_query = clone $cc_query_clone;
        $cc1_ans4 = $cc_query->where('cc_id', 1)->where('answer', 4)->count();
        $cc_query = clone $cc_query_clone;
        $cc1_ans3 = $cc_query->where('cc_id', 1)->where('answer', 3)->count();
        $cc_query = clone $cc_query_clone;
        $cc1_ans2 = $cc_query->where('cc_id', 1)->where('answer', 2)->count();
        $cc_query = clone $cc_query_clone;
        $cc1_ans1 = $cc_query->where('cc_id', 1)->where('answer', 1)->count();

        // CC 2 
        $cc_query = clone $cc_query_clone;
        $cc2_ans5 = $cc_query->where('cc_id', 2)->where('answer', 5)->count();
        $cc_query = clone $cc_query_clone;
        $cc2_ans4 = $cc_query->where('cc_id', 2)->where('answer', 4)->count();
        $cc_query = clone $cc_query_clone;
        $cc2_ans3 = $cc_query->where('cc_id', 2)->where('answer', 3)->count();
        $cc_query = clone $cc_query_clone;
        $cc2_ans2 = $cc_query->where('cc_id', 2)->where('answer', 2)->count();
        $cc_query = clone $cc_query_clone;
        $cc2_ans1 = $cc_query->where('cc_id', 2)->where('answer', 1)->count();

        // CC 3
        $cc_query = clone $cc_query_clone;
        $cc3_ans4 = $cc_query->where('cc_id', 3)->where('answer', 4)->count();
        $cc_query = clone $cc_query_clone;
        $cc3_ans3 = $cc_query->where('cc_id', 3)->where('answer', 3)->count();
        $cc_query = clone $cc_query_clone;
        $cc3_ans2 = $cc_query->where('cc_id', 3)->where('answer', 2)->count();
        $cc_query = clone $cc_query_clone;
        $cc3_ans1 = $cc_query->where('cc_id', 3)->where('answer', 1)->count();

        // Calculate total counts for percentage calculation
        $cc1_total = $cc1_ans1 + $cc1_ans2 + $cc1_ans3 + $cc1_ans4;
        $cc2_total = $cc2_ans1 + $cc2_ans2 + $cc2_ans3 + $cc2_ans4 + $cc2_ans5;
        $cc3_total = $cc3_ans1 + $cc3_ans2 + $cc3_ans3 + $cc3_ans4;

        // Calculate percentages
        $cc1_ans1_pct = $cc1_total > 0 ? number_format(($cc1_ans1 / $cc1_total) * 100, 2) : 0;
        $cc1_ans2_pct = $cc1_total > 0 ? number_format(($cc1_ans2 / $cc1_total) * 100, 2) : 0;
        $cc1_ans3_pct = $cc1_total > 0 ? number_format(($cc1_ans3 / $cc1_total) * 100, 2) : 0;
        $cc1_ans4_pct = $cc1_total > 0 ? number_format(($cc1_ans4 / $cc1_total) * 100, 2) : 0;

        $cc2_ans1_pct = $cc2_total > 0 ? number_format(($cc2_ans1 / $cc2_total) * 100, 2) : 0;
        $cc2_ans2_pct = $cc2_total > 0 ? number_format(($cc2_ans2 / $cc2_total) * 100, 2) : 0;
        $cc2_ans3_pct = $cc2_total > 0 ? number_format(($cc2_ans3 / $cc2_total) * 100, 2) : 0;
        $cc2_ans4_pct = $cc2_total > 0 ? number_format(($cc2_ans4 / $cc2_total) * 100, 2) : 0;
        $cc2_ans5_pct = $cc2_total > 0 ? number_format(($cc2_ans5 / $cc2_total) * 100, 2) : 0; // N/A

        $cc3_ans1_pct = $cc3_total > 0 ? number_format(($cc3_ans1 / $cc3_total) * 100, 2) : 0;
        $cc3_ans2_pct = $cc3_total > 0 ? number_format(($cc3_ans2 / $cc3_total) * 100, 2) : 0;
        $cc3_ans3_pct = $cc3_total > 0 ? number_format(($cc3_ans3 / $cc3_total) * 100, 2) : 0;
        $cc3_ans4_pct = $cc3_total > 0 ? number_format(($cc3_ans4 / $cc3_total) * 100, 2) : 0;

        // cc 1-3 data with counts and percentages
        $cc1_data = [
            'cc1_ans4' => $cc1_ans4,
            'cc1_ans3' => $cc1_ans3,
            'cc1_ans2' => $cc1_ans2,
            'cc1_ans1' => $cc1_ans1,
            'cc1_ans4_pct' => $cc1_ans4_pct,
            'cc1_ans3_pct' => $cc1_ans3_pct,
            'cc1_ans2_pct' => $cc1_ans2_pct,
            'cc1_ans1_pct' => $cc1_ans1_pct,
            'cc1_total' => $cc1_total,
        ];

        $cc2_data = [
            'cc2_ans5' => $cc2_ans5,
            'cc2_ans4' => $cc2_ans4,
            'cc2_ans3' => $cc2_ans3,
            'cc2_ans2' => $cc2_ans2,
            'cc2_ans1' => $cc2_ans1,
            'cc2_ans5_pct' => $cc2_ans5_pct,
            'cc2_ans4_pct' => $cc2_ans4_pct,
            'cc2_ans3_pct' => $cc2_ans3_pct,
            'cc2_ans2_pct' => $cc2_ans2_pct,
            'cc2_ans1_pct' => $cc2_ans1_pct,
            'cc2_total' => $cc2_total,
        ];

        $cc3_data = [
            'cc3_ans4' => $cc3_ans4,
            'cc3_ans3' => $cc3_ans3,
            'cc3_ans2' => $cc3_ans2,
            'cc3_ans1' => $cc3_ans1,
            'cc3_ans4_pct' => $cc3_ans4_pct,
            'cc3_ans3_pct' => $cc3_ans3_pct,
            'cc3_ans2_pct' => $cc3_ans2_pct,
            'cc3_ans1_pct' => $cc3_ans1_pct,
            'cc3_total' => $cc3_total,
        ];

        //cc data all in one

        $cc_data =[
            'cc1_data' => $cc1_data,
            'cc2_data' => $cc2_data,
            'cc3_data' => $cc3_data,
        ];

        return $cc_data;
    }

}
