<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralLevelSetting;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReferralSettingsController extends Controller
{
    /**
     * Display the referral level settings.
     */
    public function index()
    {
        $levels = ReferralLevelSetting::ordered()->get();

        $stats = [
            'total_levels' => $levels->count(),
            'active_levels' => $levels->where('is_active', true)->count(),
            'total_percentage' => $levels->where('is_active', true)->sum('percentage'),
        ];

        return view('admin.referral-settings.index', compact('levels', 'stats'));
    }

    /**
     * Update all referral level settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'levels' => 'required|array',
            'levels.*.id' => 'required|exists:referral_level_settings,id',
            'levels.*.percentage' => 'required|numeric|min:0|max:100',
            'levels.*.is_active' => 'boolean',
            'levels.*.description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            foreach ($request->levels as $levelData) {
                $level = ReferralLevelSetting::find($levelData['id']);
                $level->update([
                    'percentage' => $levelData['percentage'],
                    'is_active' => isset($levelData['is_active']) ? true : false,
                    'description' => $levelData['description'] ?? $level->description,
                ]);
            }

            DB::commit();

            // Clear the referral service cache
            ReferralService::clearCache();

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'admin_action' => 'update_referral_levels',
                    'levels_updated' => count($request->levels),
                ])
                ->log('Admin updated referral level settings');

            return redirect()->route('admin.referral-settings.index')
                ->with('success', 'Referral level settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Add a new referral level.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|integer|min:1|unique:referral_level_settings,level',
            'percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ReferralLevelSetting::create([
            'level' => $request->level,
            'percentage' => $request->percentage,
            'is_active' => true,
            'description' => $request->description ?? "Level {$request->level}",
        ]);

        // Clear the referral service cache
        ReferralService::clearCache();

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'admin_action' => 'add_referral_level',
                'level' => $request->level,
                'percentage' => $request->percentage,
            ])
            ->log('Admin added new referral level');

        return redirect()->route('admin.referral-settings.index')
            ->with('success', 'New referral level added successfully.');
    }

    /**
     * Delete a referral level.
     */
    public function destroy(ReferralLevelSetting $referralSetting)
    {
        $level = $referralSetting->level;

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'admin_action' => 'delete_referral_level',
                'level' => $level,
            ])
            ->log('Admin deleted referral level');

        $referralSetting->delete();

        // Clear the referral service cache
        ReferralService::clearCache();

        return redirect()->route('admin.referral-settings.index')
            ->with('success', "Referral level {$level} deleted successfully.");
    }
}
