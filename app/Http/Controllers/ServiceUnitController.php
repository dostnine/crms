<?php

namespace App\Http\Controllers;
use App\Models\Unit;
use Inertia\Inertia;
use App\Models\SubUnit;
use App\Models\Services;
use App\Models\UnitPsto;
use App\Models\psto;
use App\Models\SubUnitPsto;
use App\Models\UnitSubUnit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UnitPSTO as UnitPSTOResource;
use App\Http\Resources\Services as ServicesUnitsResource;
use App\Http\Resources\SubUnitPSTO as SubUnitPSTOResource;
use App\Http\Resources\UnitSubUnit as UnitSubUnitResource;


class ServiceUnitController extends Controller
{
    public function index()
    {
        $service_units = Services::all();

        $data = ServicesUnitsResource::collection($service_units);
        $user = Auth::user();

        return Inertia::render('Libraries/Service-Units/Index')
            ->with('service_units', $data)
            ->with('user',  $user);
    }

    // The blank Customer Satisfaction Feedback form for clients who answer on
    // paper, as a PDF. It replaces the copy that was kept on Google Drive.
    public function printManualCSFForm()
    {
        return \PDF::loadView('reports.csf-manual-form')
            ->setPaper('a4', 'portrait')
            ->stream('CSF-Form-Manual.pdf');
    }

    public function getServiceUnits(Request $request)
    {
        $service_units = Unit::where('services_id',$request->code)
            ->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'unit_name' => $item->unit_name
                ];
            });

        return $service_units;
    }

    public function getUnitPstos(Request $request)
    {
        //dd($request->all());
        //get user
        $user = Auth::user();

        $unit_pstos = UnitPsto::where('unit_id',$request->code)
            ->get()->map(function ($item) {
                return [
                    'psto_id' => $item->psto_id,
                ];
            });
    

        $pstos = psto::whereIn('id',$unit_pstos)
                    ->where('region_id', $request->region_id)
                    ->get();

        return $pstos;
    }


    


    public function store(Request $request)
    {
        //dd($request->all());
        $service = new Services();
        $service->services_name = strtoupper($request->service_name);
        $service->slug = Str::slug($request->service_name, '-');
        $service->save();

        return redirect()->back()->with('success', 'Service added successfully');
    }

    
    public function storeUnit(Request $request)
    {
        //dd($request->all());
        $unit = new Unit();
        $unit->services_id = $request->service_id;
        $unit->unit_name = strtoupper($request->unit_name);
        $unit->save();

        return redirect()->back()->with('success', 'Unit added successfully');
    }



    public function unit_index(Request $request)
    {
        //get user
        $user = Auth::user();

        $service = $request->service;
        $unit = $request->unit;

        // get unit sub units
        $unit_sub_units = UnitSubUnit::where('unit_id',$request->unit['id'])->get();
        $unit_sub_units = UnitSubUnitResource::collection($unit_sub_units);

        $sub_units = $unit_sub_units->pluck('sub_unit');

        return Inertia::render('Libraries/Service-Units/Views/SubUnitView')
        ->with('unit', $unit)
        ->with('service', $service)
        ->with('sub_units', $sub_units)
        ->with('user',  $user);
 
    }

    public function psto_index(Request $request)
    {
        //get user
        $user = Auth::user();

        $service = $request->service;
        $unit = $request->unit;

        // get unit sub units
        $unit_sub_units = UnitSubUnit::where('unit_id',$request->unit['id'])->get();
        $unit_sub_units = UnitSubUnitResource::collection($unit_sub_units);

        $sub_units = $unit_sub_units->pluck('sub_unit');

        $unit_pstos = $this->unit_psto($request);
        $sub_unit_pstos = $this->sub_unit_psto($request);


        return Inertia::render('Libraries/Service-Units/Views/SubUnitView')
        ->with('unit', $unit)
        ->with('service', $service)
        ->with('sub_units', $sub_units)
        ->with('unit_pstos', $unit_pstos)
        ->with('sub_unit_pstos', $sub_unit_pstos)
        ->with('user',  $user);
 
    }
    



    public function sub_unit_psto_index(Request $request)
    {
        //get user
        $user = Auth::user();

        $service = $request->service;
        $unit = $request->unit;
        $sub_unit = $request->sub_unit;

        if($sub_unit){
            $unit_pstos = $this->unit_psto($request);
            $sub_unit_pstos = $this->sub_unit_psto($request);
            return Inertia::render('Libraries/Service-Units/Views/PSTOView')
                ->with('unit', $unit)
                ->with('service', $service)
                ->with('unit_pstos', $unit_pstos)
                ->with('sub_unit_pstos', $sub_unit_pstos)
                ->with('user',  $user);
        }
        else{
            return [];
        }

       
    }


    

    public function unit_psto_index(Request $request)
    {
        //get user
        $user = Auth::user();

        $service = $request->service;
        $unit = $request->unit;
        $sub_unit = $request->sub_unit;

        $unit_pstos = $this->unit_psto($request);
        $sub_unit_pstos = $this->sub_unit_psto($request);
        return Inertia::render('Libraries/Service-Units/Views/PSTOView')
            ->with('unit', $unit)
            ->with('service', $service)
            ->with('unit_pstos', $unit_pstos)
            ->with('sub_unit_pstos', $sub_unit_pstos)
            ->with('user',  $user);

       
    }



    public function unit_psto($request)
    {
        //get user
        $user = Auth::user();

        $service = $request->service;
        $unit = $request->unit;

        $unit_pstos = UnitPsto::where('unit_id',$request->unit['id'])->get();
        $unit_pstos = UnitPSTOResource::collection($unit_pstos);
 
        $unit_pstos = $unit_pstos->pluck('psto');

        return $unit_pstos;
    }

    public function sub_unit_psto($request)
    {
        //dd($request->all());

        //get user
        $user = Auth::user();

        $service = $request->service;
        $unit = $request->unit;
        $sub_unit = $request->sub_unit;

        if($sub_unit){  
            $sub_unit_pstos = SubUnitPsto::where('sub_unit_id',$request->sub_unit['id'])->get();
            $sub_unit_pstos = SubUnitPSTOResource::collection($sub_unit_pstos);
     
            $sub_unit_pstos = $sub_unit_pstos->pluck('psto');
    
            return $sub_unit_pstos;
        }
        else{
            return [];
        }

       
    }

    public function sub_unit_type($request)
    {
        //dd($request->all());

        //get user
        $user = Auth::user();

        $service = $request->service;
        $unit = $request->unit;
        $sub_unit = $request->sub_unit;

        if($sub_unit){  
            $sub_unit_types = SubUnitType::where('sub_unit_id',$request->sub_unit['id'])->get();
            $sub_unit_types = SubUnitTypeResource::collection($sub_unit_types);
     
            $sub_unit_types = $sub_unit_types->pluck('type_name');
    
            return $sub_unit_types;
        }
        else{
            return [];
        }

       
    }


    
}
