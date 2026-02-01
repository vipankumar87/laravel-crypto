<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WithdrawalSettingsController extends Controller
{
    /**
     * Display the withdrawal settings.
     */
    public function index()
    {
        $settings = WithdrawalSetting::all();
        $pendingCount = Transaction::where('status', 'pending')
            ->where('type', 'withdrawal')
            ->count();

        return view('admin.withdrawal-settings.index', compact('settings', 'pendingCount'));
    }

    /**
     * Update all withdrawal settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:withdrawal_settings,id',
            'settings.*.value' => 'required|string|max:255',
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
                $setting = WithdrawalSetting::find($settingData['id']);

                $setting->update([
                    'value' => $settingData['value'],
                    'is_active' => isset($settingData['is_active']) ? true : false,
                    'description' => $settingData['description'] ?? $setting->description,
                ]);
            }

            DB::commit();

            WithdrawalSetting::clearCache();

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'admin_action' => 'update_withdrawal_settings',
                    'settings_updated' => count($request->settings),
                ])
                ->log('Admin updated withdrawal settings');

            return redirect()->route('admin.withdrawal-settings.index')
                ->with('success', 'Withdrawal settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Add a new withdrawal setting.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:withdrawal_settings,name|regex:/^[a-z_]+$/',
            'label' => 'required|string|max:100',
            'value' => 'required|string|max:255',
            'type' => 'required|in:number,boolean,string',
            'description' => 'nullable|string|max:255',
        ], [
            'name.regex' => 'The name must be lowercase letters and underscores only (e.g., custom_setting).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        WithdrawalSetting::create([
            'name' => $request->name,
            'label' => $request->label,
            'value' => $request->value,
            'type' => $request->type,
            'is_active' => true,
            'description' => $request->description,
        ]);

        WithdrawalSetting::clearCache();

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'admin_action' => 'add_withdrawal_setting',
                'name' => $request->name,
                'value' => $request->value,
                'type' => $request->type,
            ])
            ->log('Admin added new withdrawal setting');

        return redirect()->route('admin.withdrawal-settings.index')
            ->with('success', 'New withdrawal setting added successfully.');
    }

    /**
     * Delete a withdrawal setting.
     */
    public function destroy(WithdrawalSetting $withdrawalSetting)
    {
        if (in_array($withdrawalSetting->name, WithdrawalSetting::CORE_SETTINGS)) {
            return redirect()->route('admin.withdrawal-settings.index')
                ->with('error', 'Cannot delete core withdrawal settings. You can disable them instead.');
        }

        $name = $withdrawalSetting->label;

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'admin_action' => 'delete_withdrawal_setting',
                'name' => $withdrawalSetting->name,
            ])
            ->log('Admin deleted withdrawal setting');

        $withdrawalSetting->delete();

        WithdrawalSetting::clearCache();

        return redirect()->route('admin.withdrawal-settings.index')
            ->with('success', "{$name} deleted successfully.");
    }
}
