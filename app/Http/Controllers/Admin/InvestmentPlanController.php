<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvestmentPlan;
use Illuminate\Support\Facades\Validator;

class InvestmentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = InvestmentPlan::withCount('investments')->paginate(10);
        return view('admin.investment-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.investment-plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'min_amount' => 'required|numeric|min:0.01',
            'max_amount' => 'required|numeric|min:0.01|gt:min_amount',
            'daily_return_rate' => 'required|numeric|min:0.01|max:100',
            'duration_days' => 'required|integer|min:1',
            'max_investors' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Calculate total return rate
        $totalReturnRate = $request->daily_return_rate * $request->duration_days;

        InvestmentPlan::create([
            'name' => $request->name,
            'description' => $request->description,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'daily_return_rate' => $request->daily_return_rate,
            'duration_days' => $request->duration_days,
            'total_return_rate' => $totalReturnRate,
            'referral_bonus_rate' => 0, // Referral bonuses are now configured globally
            'max_investors' => $request->max_investors,
            'status' => $request->status,
        ]);

        // Log admin activity
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'plan_name' => $request->name,
                'admin_action' => 'create_plan'
            ])
            ->log('Admin created new investment plan');

        return redirect()->route('admin.investment-plans.index')
            ->with('success', 'Investment plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InvestmentPlan $investmentPlan)
    {
        $investmentPlan->load('investments.user');

        $stats = [
            'total_investors' => $investmentPlan->investments()->distinct('user_id')->count(),
            'total_invested' => $investmentPlan->investments()->sum('amount'),
            'active_investments' => $investmentPlan->investments()->where('status', 'active')->count(),
        ];

        return view('admin.investment-plans.show', [
            'plan' => $investmentPlan,
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvestmentPlan $investmentPlan)
    {
        return view('admin.investment-plans.edit', ['plan' => $investmentPlan]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvestmentPlan $investmentPlan)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'min_amount' => 'required|numeric|min:0.01',
            'max_amount' => 'required|numeric|min:0.01|gt:min_amount',
            'daily_return_rate' => 'required|numeric|min:0.01|max:100',
            'duration_days' => 'required|integer|min:1',
            'max_investors' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Calculate total return rate
        $totalReturnRate = $request->daily_return_rate * $request->duration_days;

        $investmentPlan->update([
            'name' => $request->name,
            'description' => $request->description,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'daily_return_rate' => $request->daily_return_rate,
            'duration_days' => $request->duration_days,
            'total_return_rate' => $totalReturnRate,
            'max_investors' => $request->max_investors,
            'status' => $request->status,
        ]);

        // Log admin activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($investmentPlan)
            ->withProperties([
                'plan_name' => $request->name,
                'admin_action' => 'update_plan'
            ])
            ->log('Admin updated investment plan');

        return redirect()->route('admin.investment-plans.index')
            ->with('success', 'Investment plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvestmentPlan $investmentPlan)
    {
        // Check if plan has active investments
        if ($investmentPlan->investments()->where('status', 'active')->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete plan with active investments.');
        }

        // Log admin activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($investmentPlan)
            ->withProperties([
                'plan_name' => $investmentPlan->name,
                'admin_action' => 'delete_plan'
            ])
            ->log('Admin deleted investment plan');

        $investmentPlan->delete();

        return redirect()->route('admin.investment-plans.index')
            ->with('success', 'Investment plan deleted successfully.');
    }
}
