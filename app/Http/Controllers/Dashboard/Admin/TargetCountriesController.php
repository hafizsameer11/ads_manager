<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\TargetCountry;
use App\Models\TargetDevice;
use Illuminate\Http\Request;

class TargetCountriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = TargetCountry::ordered()->paginate(20);
        $devices = TargetDevice::ordered()->paginate(20);
        return view('dashboard.admin.target-countries.index', compact('countries', 'devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.admin.target-countries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:target_countries,name',
            'code' => 'required|string|size:2|unique:target_countries,code|uppercase',
            'is_enabled' => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'code']);
        $data['code'] = strtoupper($data['code']); // Ensure uppercase
        $data['is_enabled'] = $request->has('is_enabled') ? true : false;

        TargetCountry::create($data);

        return redirect()->route('dashboard.admin.target-countries.index')
            ->with('success', 'Target country created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TargetCountry $targetCountry)
    {
        return view('dashboard.admin.target-countries.edit', compact('targetCountry'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TargetCountry $targetCountry)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:target_countries,name,' . $targetCountry->id,
            'code' => 'required|string|size:2|unique:target_countries,code,' . $targetCountry->id . '|uppercase',
            'is_enabled' => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'code']);
        $data['code'] = strtoupper($data['code']); // Ensure uppercase
        $data['is_enabled'] = $request->has('is_enabled') ? true : false;

        $targetCountry->update($data);

        return redirect()->route('dashboard.admin.target-countries.index')
            ->with('success', 'Target country updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TargetCountry $targetCountry)
    {
        $targetCountry->delete();

        return redirect()->route('dashboard.admin.target-countries.index')
            ->with('success', 'Target country deleted successfully.');
    }

    /**
     * Toggle the enabled status of the country.
     */
    public function toggleStatus(TargetCountry $targetCountry)
    {
        $targetCountry->update([
            'is_enabled' => !$targetCountry->is_enabled,
        ]);

        return back()->with('success', 'Country status updated successfully.');
    }

    // Device Management Methods

    /**
     * Show the form for creating a new device.
     */
    public function createDevice()
    {
        return view('dashboard.admin.target-countries.create-device');
    }

    /**
     * Store a newly created device in storage.
     */
    public function storeDevice(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:target_devices,name',
            'is_enabled' => 'nullable|boolean',
        ]);

        $data = $request->only(['name']);
        $data['is_enabled'] = $request->has('is_enabled') ? true : false;

        TargetDevice::create($data);

        return redirect()->route('dashboard.admin.target-countries.index')
            ->with('success', 'Target device created successfully.');
    }

    /**
     * Show the form for editing the specified device.
     */
    public function editDevice(TargetDevice $targetDevice)
    {
        return view('dashboard.admin.target-countries.edit-device', compact('targetDevice'));
    }

    /**
     * Update the specified device in storage.
     */
    public function updateDevice(Request $request, TargetDevice $targetDevice)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:target_devices,name,' . $targetDevice->id,
            'is_enabled' => 'nullable|boolean',
        ]);

        $data = $request->only(['name']);
        $data['is_enabled'] = $request->has('is_enabled') ? true : false;

        $targetDevice->update($data);

        return redirect()->route('dashboard.admin.target-countries.index')
            ->with('success', 'Target device updated successfully.');
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroyDevice(TargetDevice $targetDevice)
    {
        $targetDevice->delete();

        return redirect()->route('dashboard.admin.target-countries.index')
            ->with('success', 'Target device deleted successfully.');
    }

    /**
     * Toggle the enabled status of the device.
     */
    public function toggleDeviceStatus(TargetDevice $targetDevice)
    {
        $targetDevice->update([
            'is_enabled' => !$targetDevice->is_enabled,
        ]);

        return back()->with('success', 'Device status updated successfully.');
    }
}
