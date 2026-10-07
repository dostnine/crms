<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\Unit;
use App\Models\Services;
use App\Models\Region;
use App\Models\CSFForm;
use App\Models\SubUnit;
use App\Models\Customer;
use App\Models\Dimension;
use App\Models\CcQuestion;
use Illuminate\Http\Request;
use App\Models\CustomerComment;
use App\Models\UnitPsto;
use App\Models\psto;
use App\Models\CustomerCCRating;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\DB;
use App\Models\SubUnitPsto;
use App\Models\SubUnitType;
use App\Models\ShowDateCsfForm;
use App\Models\CustomerAttributeRating;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\SurveyFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomerRecommendationRating;
use App\Models\CustomerOtherAttributeIndication;

use App\Http\Resources\Unit as UnitResource;
use App\Http\Resources\SubUnit as SubUnitResource;
use App\Http\Resources\UnitPSTO as UnitPSTOResource;
use App\Http\Resources\SubUnitPSTO as SubUnitPSTOResource;
use App\Http\Resources\SubUnitType as SubUnitTypeResource;
use App\Http\Resources\ShowDateCSFForm as ShowDateCSFFormResource;

use App\Models\CustomerSignature;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class SurveyFormController extends Controller
{
    public function index(Request $request)
    {
        // get the data if the date will be displayed or not
        $date_display = ShowDateCsfForm::all();

        $cc_questions = CcQuestion::all();
        $dimensions = Dimension::all();
        $unit = Unit::where('id', $request->unit_id)->get();
        $sub_unit = SubUnit::where('unit_id', $request->unit_id)
                           ->where('id', $request->sub_unit_id)->get();
        $unit_psto = UnitPsto::where('unit_id', $request->unit_id)
                             ->where('psto_id', $request->psto_id)->get();
        $sub_unit_psto = SubUnitPsto::where('sub_unit_id', $request->sub_unit_id)
                                     ->where('psto_id', $request->psto_id)->get();
        
        $unit = UnitResource::collection($unit);
        $sub_unit = SubUnitResource::collection($sub_unit);
        $unit_psto = UnitPSTOResource::collection($unit_psto);
        $sub_unit_psto = SubUnitPSTOResource::collection($sub_unit_psto);

        return Inertia::render('Survey-Forms/Index')
            ->with('cc_questions', $cc_questions)
            ->with('dimensions', $dimensions)
            ->with('unit', $unit)
            ->with('sub_unit', $sub_unit)
            ->with('unit_psto', $unit_psto)
            ->with('sub_unit_psto', $sub_unit_psto)
            ->with('date_display', $date_display);  
    }



    // SurveyFormRequest
    public function store(SurveyFormRequest $request)
    {       
        //dd($request->all());
        try{
            DB::beginTransaction();    
           
            //Save Customer
            $customer = $this->saveCustomer($request);

            // Validate dimension_form data
            $dimensionData = request()->validate([
                'dimension_form.id.*' => ['required', 'exists:dimensions,id'],
                'dimension_form.rate_score.*' => ['required', 'max:1'],
                'dimension_form.importance_rate_score.*' => ['required', 'max:1'],
            ]);
    
            // Associate ratings with dimensions for the customer
            foreach ($dimensionData['dimension_form']['id'] as $index => $dimensionId) {
                CustomerAttributeRating::create([
                    'created_at' =>  $request->date,
                    'updated_at' =>  $request->date,
                    'customer_id' => $customer->id,
                    'dimension_id' => $dimensionId,
                    'rate_score' => $dimensionData['dimension_form']['rate_score'][$index],
                    'importance_rate_score' => $dimensionData['dimension_form']['importance_rate_score'][$index],
                ]);
            }
    
            // Validate CC
            $ccData = request()->validate([
                'cc_form.id.*' => ['required', 'exists:cc_questions,id'],
                'cc_form.answer.*' => ['required', 'max:1'],
            ]);
    
            // Associate ratings with cc for the customer
            foreach ($ccData['cc_form']['id'] as $index => $ccId) {
                CustomerCCRating::create([
                    'created_at' =>  $request->date,
                    'updated_at' =>  $request->date,
                    'customer_id' => $customer->id,
                    'cc_id' => $ccId,
                    'answer' => $ccData['cc_form']['answer'][$index],
                ]);
            }
    
            // Save Comment
            if($request->comment){
                $this->saveComment($request, $customer);
            }
           
            // Save Customer Recommendation Rating
            $this->saveCustomerRecommendationRating($request, $customer);

            // SAve Other Attributes Indication
            // $this->saveCustomerOtherAttributeIndication($request, $customer);

            // Save csf form
            $this->saveCSFForm($request, $customer);


            DB::commit();
           
            return Inertia::render('Survey-Forms/ThankYou')
                ->with('message', "Successfully Submitted Thank you.")
                ->with('status', "success")
                ->with('current_url', $request->current_url);
            
            return Inertia::redirect('msg_index');

        } catch (\Exception $e) {
            DB::rollBack();
            //return $e;
            $msg = $e->getMessage();
     
            return back()->with([
                'message' => $msg ,
                'status' => "error",
            ]);

        }

        
   
    }

    public function saveCSFForm($request, $customer){
        $csf_form = new CSFForm();
        $csf_form->customer_id = $customer->id;
        $csf_form->region_id = $request->region_id;
        $csf_form->service_id = $request->service_id;
        $csf_form->unit_id = $request->unit_id;
        if($request->sub_unit_id != "null"){
            $csf_form->sub_unit_id = $request->sub_unit_id;
        }
        $csf_form->psto_id = $request->psto_id;
        $csf_form->client_type = $request->client_type;
        $csf_form->sub_unit_type = $request->sub_unit_type;
        if($request->date){
            $csf_form->created_at = $request->date;
            $csf_form->updated_at = $request->date;
        }
        
        $csf_form->save();

        return $csf_form;
    }

    public function saveCustomer($request){
        $customer = new Customer();
        $customer->email = $request->email;
        $customer->name = $request->name;
        $customer->client_type = $request->client_type;
        $customer->sex = $request->sex;
        $customer->age_group = $request->age_group;
        if($request->date){
            $customer->created_at = $request->date;
            $customer->updated_at = $request->date;
        }
         // 'signature_path' => $request->signature,
        $customer->save();

        return $customer;
    }

    public function saveComment($request, $customer){
         $comment = CustomerComment::create(
            [
                'created_at' => $request->date,
                'updated_at' => $request->date,
                'customer_id' => $customer->id,
                'comment' =>  $request->comment,
                'is_complaint' =>  $request->is_complaint,
            ]
         );
        return $comment;
    }

    public function saveCustomerRecommendationRating($request, $customer){
        $recommentdation_rating = CustomerRecommendationRating::create(
                [
                    'created_at' =>  $request->date,
                    'updated_at' =>  $request->date,
                    'customer_id' => $customer->id,
                    'recommend_rate_score' =>  $request->recommend_rate_score,      
                ]
            );
        return $recommentdation_rating;
    }

    public function saveCustomerOtherAttributeIndication($request, $customer){
        $customer_indication = CustomerOtherAttributeIndication::create(
                [
                    'created_at' =>  $request->date,
                    'updated_at' =>  $request->date,
                    'customer_id' => $customer->id,   
                    'indication' =>  $request->indication,           
                ]
            );
       return $customer_indication;
   }


   public function regions_index(Request $request){
        $regions = Region::all();
        return Inertia::render('Region')
                      ->with('regions', $regions );
   }

   public function services_index(Request $request){
        $services = Services::all();
        //selected region
        $region = Region::where('id',$request->region_id)->first();

        return Inertia::render('Services')
                        ->with('region_id', $request->region_id )
                        ->with('region', $region )
                        ->with('services', $services );
    }

    public function service_units_index(Request $request){
        // dd($request->all());
        $service_units = Unit::where('services_id', $request->service_id)->get();
        //selected region
        $region = Region::where('id',$request->region_id)->first();
        //selected service
        $service = Services::where('id', $request->service_id)->first();

        return Inertia::render('Units')
                        ->with('region_id', $request->region_id)
                        ->with('region', $region)
                        ->with('service_id', $request->service_id)
                        ->with('service', $service)
                        ->with('service_units', $service_units);
    }

    public function getUnitSubunits(Request $request){
        $sub_units = SubUnit::where('unit_id', $request->unit_id)->get();
        //selected region
        $region = Region::where('id',$request->region_id)->first();
        //selected unit
        $unit = Unit::where('id', $request->unit_id)->first();

        if(sizeof($sub_units) > 0){
            return Inertia::render('SubUnits')
                        ->with('region_id', $request->region_id)
                        ->with('region', $region)
                        ->with('service_id', $request->service_id)
                        ->with('unit_id', $request->unit_id)
                        ->with('unit', $unit)
                        ->with('sub_units', $sub_units);
        }
        
        else{
            //check if this unit has psto
            $unit_pstos = UnitPSTO::where('unit_id', $request->unit_id)->get();
            $psto_ids = $unit_pstos->pluck('psto_id');
            $pstos = psto::where('region_id',$request->region_id)
                    ->whereIn('id',$psto_ids) 
                    ->get();

            if(sizeof($pstos) > 0){
                return Inertia::render('PSTOs')
                            ->with('region_id', $request->region_id)
                            ->with('region', $region)
                            ->with('service_id', $request->service_id)
                            ->with('unit_id', $request->unit_id)
                            ->with('unit', $unit)
                            ->with('sub_unit_id', $request->sub_unit_id)
                            ->with('pstos', $pstos);
            }
            else{
                // redirect to url of csf form
                $url = '/services/csf?region_id='.$request->region_id.
                '&service_id='.$request->service_id.
                '&unit_id='.$request->unit_id;

                return Inertia::location($url);
            }


        }

       
    }

    public function getSubUnitPSTO(Request $request){
        $sub_unit_pstos = SubUnitPSTO::where('sub_unit_id', $request->sub_unit_id)->get();
        $psto_ids = $sub_unit_pstos->pluck('psto_id');

        $pstos = psto::whereIn('id',$psto_ids)
                    ->where('region_id', $request->region_id)
                    ->get();
        $pstos = psto::whereIn('id',$psto_ids)
        ->where('region_id', $request->region_id)
        ->get();
        
         //selected region
         $region = Region::where('id',$request->region_id)->first();    
        //selected sub-unit
        $sub_unit = SubUnit::where('id', $request->sub_unit_id)->first();
 

        if(sizeof($pstos) > 0){
            return Inertia::render('PSTOs')
                        ->with('region_id', $request->region_id)
                        ->with('region', $region)
                        ->with('service_id', $request->service_id)
                        ->with('unit_id', $request->unit_id)
                        ->with('sub_unit_id', $request->sub_unit_id)
                        ->with('sub_unit', $sub_unit)
                        ->with('pstos', $pstos);
        }
        
        else{
            // redirect to url of csf form

            $url = '/services/csf?region_id='.$request->region_id.
                                '&service_id='.$request->service_id.
                                '&unit_id='.$request->unit_id.
                                '&sub_unit_id='.$request->sub_unit_id.
                                '&psto_id='.$request->psto_id;

            return Inertia::location($url);
        }

       
    }


    public function getSubUnitTypes(Request $request){
        $types = SubUnitType::where('sub_unit_id', $request->sub_unit_id)
                    ->where('region_id', $request->region_id)->get();
        //selected region
        $region = Region::where('id',$request->region_id)->first();    
        //selected sub-unit
        $sub_unit = SubUnit::where('id',$request->sub_unit_id)->first();

        if(sizeof($types) > 0){
            return Inertia::render('SubUnitTypes')
                        ->with('region_id', $request->region_id)
                        ->with('region', $region)
                        ->with('service_id', $request->service_id)
                        ->with('unit_id', $request->unit_id)
                        ->with('sub_unit_id', $request->sub_unit_id)
                        ->with('sub_unit', $sub_unit)
                        ->with('types', $types);
        }
        
        else{
            // redirect to url of csf form

            $url = '/services/csf?region_id='.$request->region_id.
                                '&service_id='.$request->service_id.
                                '&unit_id='.$request->unit_id.
                                '&sub_unit_id='.$request->sub_unit_id.
                                '&type_id='.$request->type_id;

            return Inertia::location($url);
        }


    }

    /**
     * Everything the survey asks before the form opens, as one tree: region,
     * service, unit, then a sub unit and a PSTO or a type where the unit has
     * them. Each end of the tree carries the address of its CSF form.
     *
     * The Citizen's Charter book (public/charter) fills the dropdowns of its
     * "Feedback" form from this. The choices and the addresses must stay the
     * same as the step pages above (regions_index to getSubUnitTypes) and
     * their Vue pages hand out, oddities included, so a survey started from
     * the book is recorded exactly like one started from the landing page.
     */
    public function options()
    {
        $services = Services::all();
        $units = Unit::all()->groupBy('services_id');
        $sub_units = SubUnit::all()->groupBy('unit_id');
        $pstos = psto::all();
        $unit_pstos = UnitPsto::all()->groupBy('unit_id');
        $sub_unit_pstos = SubUnitPsto::all()->groupBy('sub_unit_id');
        $types = SubUnitType::all();

        $form = fn (array $query) => '/services/csf?' . collect($query)->map(fn ($value, $key) => $key . '=' . $value)->implode('&');
        $step = fn (string $label, $options) => ['label' => $label, 'options' => collect($options)->values()];
        // the offices of a region, among those attached to a unit or sub unit
        $offices = fn ($region, $attached) => $pstos
            ->where('region_id', $region->id)
            ->whereIn('id', $attached ? $attached->pluck('psto_id')->all() : []);

        $regions = Region::all()->map(fn ($region) => [
            'name' => $region->name,
            'next' => $step('Service', $services->map(fn ($service) => [
                'name' => $service->services_name,
                'next' => $step('Unit', $units->get($service->id, collect())->map(function ($unit) use ($region, $service, $sub_units, $unit_pstos, $sub_unit_pstos, $types, $form, $step, $offices) {
                    $query = ['region_id' => $region->id, 'service_id' => $service->id, 'unit_id' => $unit->id];
                    $subs = $sub_units->get($unit->id, collect());

                    if ($subs->isEmpty()) {
                        $unit_offices = $offices($region, $unit_pstos->get($unit->id));
                        if ($unit_offices->isEmpty()) {
                            return ['name' => $unit->unit_name, 'url' => $form($query)];
                        }
                        // the PSTO page writes "null" for the sub unit a unit does not have
                        return ['name' => $unit->unit_name, 'next' => $step('PSTO', $unit_offices->map(fn ($office) => [
                            'name' => $office->psto_name,
                            'url' => $form($query + ['sub_unit_id' => 'null', 'psto_id' => $office->id]),
                        ]))];
                    }

                    return ['name' => $unit->unit_name, 'next' => $step('Sub unit', $subs->map(function ($sub) use ($region, $query, $sub_unit_pstos, $types, $form, $step, $offices) {
                        $query += ['sub_unit_id' => $sub->id];

                        // Driving (sub unit 3) is chosen by type, not by PSTO: see SubUnits.vue
                        if ($sub->id == 3) {
                            $sub_types = $types->where('sub_unit_id', $sub->id)->where('region_id', $region->id);
                            if ($sub_types->isEmpty()) {
                                return ['name' => $sub->sub_unit_name, 'url' => $form($query + ['type_id' => ''])];
                            }
                            return ['name' => $sub->sub_unit_name, 'next' => $step('Type', $sub_types->map(fn ($type) => [
                                'name' => $type->type_name,
                                'url' => $form($query + ['sub_unit_type' => $this->encodeURIComponent($type->type_name)]),
                            ]))];
                        }

                        $sub_offices = $offices($region, $sub_unit_pstos->get($sub->id));
                        if ($sub_offices->isEmpty()) {
                            return ['name' => $sub->sub_unit_name, 'url' => $form($query + ['psto_id' => ''])];
                        }
                        return ['name' => $sub->sub_unit_name, 'next' => $step('PSTO', $sub_offices->map(fn ($office) => [
                            'name' => $office->psto_name,
                            'url' => $form($query + ['psto_id' => $office->id]),
                        ]))];
                    }))];
                })),
            ])),
        ]);

        return response()->json($step('Region', $regions));
    }

    // The address this site is reached at from outside. CSF_PUBLIC_URL when it
    // is set: the live address, so a QR code made on an office or test
    // machine still opens the live survey on a visitor's phone. Otherwise the
    // address this request came to, which is the live one on the live server.
    private function publicRoot(Request $request)
    {
        $set = trim((string) config('services.csf.public_url'));
        if ($set !== '' && !preg_match('#^https?://#i', $set)) {
            $set = 'https://' . $set;
        }

        return rtrim($set !== '' ? $set : $request->root(), '/');
    }

    /**
     * The address of a CSF form as a QR code (an SVG picture). The "Feedback"
     * form of the Citizen's Charter book shows it beside its dropdowns, so
     * someone at the kiosk can scan it and answer the survey on their own
     * phone. `to` is the form's address on this site, as options() gives it;
     * nothing but an address of the CSF form is drawn. The code holds the
     * whole address, starting with publicRoot().
     *
     * Drawn with bacon/bacon-qr-code, which is installed for the two-factor
     * login screen (laravel/fortify requires it).
     */
    public function qr(Request $request)
    {
        $to = $request->query('to');
        abort_unless(
            is_string($to) && strlen($to) <= 300 && preg_match('#^/services/csf\?[\w=&%.()!*\'~-]*$#', $to),
            404
        );

        $writer = new Writer(new ImageRenderer(new RendererStyle(300, 2), new SvgImageBackEnd()));

        return response($writer->writeString($this->publicRoot($request) . $to), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    // Escapes a value the way JavaScript's encodeURIComponent does: the Type
    // page builds its links with it, and it leaves ! * ' ( ) as they are.
    private function encodeURIComponent($value)
    {
        return strtr(rawurlencode($value), ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')']);
    }

}
