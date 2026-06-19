<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function show()
    {
        $settings = Setting::first() ?? Setting::create();
        return $this->successResponse($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'string',
            'store_address' => 'nullable|string',
            'store_phone' => 'nullable|string',
            'store_email' => 'nullable|email',
            'tax_enabled' => 'boolean',
            'tax_rate' => 'numeric|min:0|max:100',
            'tax_type' => 'in:inclusive,exclusive',
            'max_cashier_discount_pct' => 'numeric|min:0|max:100',
            'max_manager_discount_pct' => 'numeric|min:0|max:100',
        ]);

        $settings = Setting::first() ?? Setting::create();
        $settings->update($validated);

        return $this->successResponse($settings, 'Settings updated');
    }
}
