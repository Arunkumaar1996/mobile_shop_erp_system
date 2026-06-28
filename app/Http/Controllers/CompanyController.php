<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function edit()
    {
        $this->authorize('view-settings');
        
        // Fetch first record or create dummy default
        $company = Company::firstOrCreate([], [
            'name' => 'Mobile Shop ERP',
            'email' => 'contact@mobileshop.com',
            'phone' => '+1234567890',
            'website' => 'www.mobileshop.com',
            'address' => '456 Main Avenue, Retail City',
            'tax_number' => 'GSTIN12345ABC',
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);

        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
        $this->authorize('edit-settings');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $company = Company::first();

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('company', 'public');
        }

        $company->update($data);

        ActivityLog::log('Updated Company Settings', 'Company', $company->id, [
            'name' => $company->name
        ]);

        return redirect()->back()->with('success', 'Company settings updated successfully.');
    }
}
