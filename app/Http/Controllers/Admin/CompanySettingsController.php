<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\CompanyDetail;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $details = CompanyDetail::first();
        if (!$details) {
            $details = new CompanyDetail([
                'abn' => '',
                'company_name' => '',
                'address' => '',
                'logo' => '',
                'bank_details' => '',
                'bank_details_usd' => '',
            ]);
        }
        return view('admin.company-settings.edit', ['details' => $details]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'abn' => 'nullable|string|max:32',
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'bank_details' => 'nullable|string|max:10000',
            'bank_details_usd' => 'nullable|string|max:10000',
            'logo' => 'nullable|image|max:2048',
        ]);

        $details = CompanyDetail::first();
        if (!$details) {
            $details = new CompanyDetail();
        }

        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><span><div><a>';

        $data = [
            'abn'              => $request->input('abn'),
            'company_name'     => $request->input('company_name'),
            'address'          => $request->input('address'),
            'bank_details'     => $request->input('bank_details', ''),
            'bank_details_usd' => $request->input('bank_details_usd', ''),
        ];

        \Log::info('CompanySettings update', [
            'bank_details'     => substr($data['bank_details'], 0, 200),
            'bank_details_usd' => substr($data['bank_details_usd'], 0, 200),
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('public/company');
            $data['logo'] = Storage::url($logoPath);
        } else {
            $data['logo'] = $request->input('current_logo', $details->logo ?? '');
        }

        $details->fill($data);
        $details->save();

        return redirect()->back()->with('success', 'Company details updated successfully.');
    }
}
