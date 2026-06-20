<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountListing;

class PlanController extends Controller
{
    public function index()
    {
        $pageTitle = 'Subscription Plans';
        $plans = Plan::withCount('users')->latest()->paginate(getPaginate());
        return view('admin.plan.index', compact('pageTitle', 'plans'));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'name'  => 'required',
            'price' => 'required|numeric|min:0',
        ]);

        if ($id) {
            $plan = Plan::findOrFail($id);
            $msg  = 'Plan updated successfully';
        } else {
            $plan = new Plan();
            $msg  = 'Plan created successfully';
        }

        $plan->name  = $request->name;
        $plan->price = $request->price;
        
        $features = [];
        if ($request->features) {
            $featuresArray = explode("\n", $request->features);
            foreach ($featuresArray as $feature) {
                $feature = trim($feature);
                if ($feature) {
                    $features[] = $feature;
                }
            }
        }
        $plan->features = $features;

        $plan->save();

        $notify[] = ['success', $msg];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Plan::changeStatus($id);
    }
}
