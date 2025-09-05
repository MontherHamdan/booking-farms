<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\FarmOwnerWalletService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected FarmOwnerWalletService $walletService;

    public function __construct(FarmOwnerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Platform settings dashboard
     */
    public function index()
    {
        $settings = [
            'transfer_frequency_days' => PlatformSetting::getTransferFrequencyDays(),
            'minimum_transfer_amount' => PlatformSetting::getMinimumTransferAmount(),
            'default_commission_rate' => PlatformSetting::getDefaultCommissionRate(),
            'minimum_commission_rate' => PlatformSetting::getMinimumCommissionRate(),
            'maximum_commission_rate' => PlatformSetting::getMaximumCommissionRate(),
        ];

        $paymentStats = $this->walletService->getPaymentStatistics();

        return view('admin.settings.index', compact('settings', 'paymentStats'));
    }

    /**
     * Update payment settings
     */
    public function updatePaymentSettings(Request $request)
    {
        $request->validate([
            'transfer_frequency_days' => 'required|integer|min:1|max:365',
            'minimum_transfer_amount' => 'required|numeric|min:1|max:10000',
        ]);

        try {
            PlatformSetting::setTransferFrequencyDays($request->transfer_frequency_days);
            PlatformSetting::setMinimumTransferAmount($request->minimum_transfer_amount);

            return redirect()->back()->with('success', 'Payment settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Update commission settings
     */
    public function updateCommissionSettings(Request $request)
    {
        $request->validate([
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'minimum_commission_rate' => 'required|numeric|min:0|max:100',
            'maximum_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            // Validate rate hierarchy
            if ($request->minimum_commission_rate >= $request->maximum_commission_rate) {
                return redirect()->back()->with('error', 'Minimum rate must be less than maximum rate.');
            }

            if ($request->default_commission_rate < $request->minimum_commission_rate || 
                $request->default_commission_rate > $request->maximum_commission_rate) {
                return redirect()->back()->with('error', 'Default rate must be between minimum and maximum rates.');
            }

            // Update settings
            PlatformSetting::set(PlatformSetting::MINIMUM_COMMISSION_RATE, $request->minimum_commission_rate, 'Minimum allowed commission rate');
            PlatformSetting::set(PlatformSetting::MAXIMUM_COMMISSION_RATE, $request->maximum_commission_rate, 'Maximum allowed commission rate');
            PlatformSetting::set(PlatformSetting::DEFAULT_COMMISSION_RATE, $request->default_commission_rate, 'Default commission rate for new farm owners');

            return redirect()->back()->with('success', 'Commission settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update commission settings: ' . $e->getMessage());
        }
    }
}