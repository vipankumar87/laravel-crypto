<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FeeSettingsController extends Controller
{
    /**
     * Display the fee settings.
     */
    public function index()
    {
        $settings = FeeSetting::all();

        // Sample calculation for $100 investment
        $sampleAmount = 100;
        $sampleFees = FeeSetting::calculateTotalFees($sampleAmount);

        return view('admin.fee-settings.index', compact('settings', 'sampleAmount', 'sampleFees'));
    }

    /**
     * Update all fee settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:fee_settings,id',
            'settings.*.value' => 'required|numeric|min:0',
            'settings.*.type' => 'required|in:percentage,flat',
            'settings.*.is_active' => 'boolean',
            'settings.*.description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            foreach ($request->settings as $settingData) {
                $setting = FeeSetting::find($settingData['id']);

                // Validate percentage is not over 100
                if ($settingData['type'] === 'percentage' && $settingData['value'] > 100) {
                    throw new \Exception("Percentage fee cannot exceed 100% for {$setting->label}");
                }

                $setting->update([
                    'value' => $settingData['value'],
                    'type' => $settingData['type'],
                    'is_active' => isset($settingData['is_active']) ? true : false,
                    'description' => $settingData['description'] ?? $setting->description,
                ]);
            }

            DB::commit();

            // Clear the fee settings cache
            FeeSetting::clearCache();

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'admin_action' => 'update_fee_settings',
                    'settings_updated' => count($request->settings),
                ])
                ->log('Admin updated fee settings');

            return redirect()->route('admin.fee-settings.index')
                ->with('success', 'Fee settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Add a new fee setting.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:fee_settings,name|regex:/^[a-z_]+$/',
            'label' => 'required|string|max:100',
            'value' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,flat',
            'description' => 'nullable|string|max:255',
        ], [
            'name.regex' => 'The name must be lowercase letters and underscores only (e.g., custom_fee).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate percentage is not over 100
        if ($request->type === 'percentage' && $request->value > 100) {
            return redirect()->back()
                ->with('error', 'Percentage fee cannot exceed 100%')
                ->withInput();
        }

        FeeSetting::create([
            'name' => $request->name,
            'label' => $request->label,
            'value' => $request->value,
            'type' => $request->type,
            'is_active' => true,
            'description' => $request->description,
        ]);

        // Clear the fee settings cache
        FeeSetting::clearCache();

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'admin_action' => 'add_fee_setting',
                'name' => $request->name,
                'value' => $request->value,
                'type' => $request->type,
            ])
            ->log('Admin added new fee setting');

        return redirect()->route('admin.fee-settings.index')
            ->with('success', 'New fee setting added successfully.');
    }

    /**
     * Delete a fee setting.
     */
    public function destroy(FeeSetting $feeSetting)
    {
        // Prevent deletion of core fee settings
        if (in_array($feeSetting->name, ['platform_fee', 'transaction_fee'])) {
            return redirect()->route('admin.fee-settings.index')
                ->with('error', 'Cannot delete core fee settings. You can disable them instead.');
        }

        $name = $feeSetting->label;

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'admin_action' => 'delete_fee_setting',
                'name' => $feeSetting->name,
            ])
            ->log('Admin deleted fee setting');

        $feeSetting->delete();

        // Clear the fee settings cache
        FeeSetting::clearCache();

        return redirect()->route('admin.fee-settings.index')
            ->with('success', "{$name} deleted successfully.");
    }

    /**
     * Calculate fees via AJAX (for live preview)
     */
    public function calculate(Request $request)
    {
        $amount = (float) $request->input('amount', 0);

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid amount',
            ]);
        }

        $fees = FeeSetting::calculateTotalFees($amount);

        return response()->json([
            'success' => true,
            'data' => [
                'amount' => number_format($amount, 2),
                'platform_fee' => number_format($fees['platform_fee'], 2),
                'transaction_fee' => number_format($fees['transaction_fee'], 2),
                'total_fees' => number_format($fees['total_fees'], 2),
                'net_amount' => number_format($fees['net_amount'], 2),
            ],
        ]);
    }
}
