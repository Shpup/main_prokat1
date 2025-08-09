<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\TripSheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehiclesQuery = Vehicle::query();
        $tripSheetsQuery = TripSheet::where('admin_id', Auth::id());

        // Поиск
        if ($request->has('search')) {
            $search = $request->input('search');
            $vehiclesQuery->where(function ($query) use ($search) {
                $query->where('brand', 'like', "%$search%")
                    ->orWhere('model', 'like', "%$search%")
                    ->orWhere('license_plate', 'like', "%$search%");
            });
            $tripSheetsQuery->where(function ($query) use ($search) {
                $query->where('address', 'like', "%$search%")
                    ->orWhereHas('vehicle', function ($q) use ($search) {
                        $q->where('brand', 'like', "%$search%")
                            ->orWhere('model', 'like', "%$search%");
                    })
                    ->orWhereHas('driver', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            });
        }

        // Сортировка
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $vehiclesQuery->orderBy($sortField, $sortDirection);
        $tripSheetsQuery->orderBy($sortField, $sortDirection);

        $vehicles = $vehiclesQuery->paginate(10);
        $tripSheets = $tripSheetsQuery->paginate(10);
        $drivers = User::where('admin_id', Auth::id())->whereHas('roles', function ($query) {
            $query->where('name', 'driver');
        })->get();

        return view('vehicles.index', compact('vehicles', 'tripSheets', 'drivers'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $hasData = array_filter($data, fn($value) => $value !== null && $value !== '' && $value !== 0);
        if (empty($hasData)) {
            return response()->json(['error' => 'Заполните хотя бы одно поле!'], 422);
        }

        $data = array_merge([
            'brand' => $data['brand'] ?? '—',
            'model' => $data['model'] ?? '—',
            'year' => $data['year'] ?? 0,
            'license_plate' => $data['license_plate'] ?? '—',
            'status' => $data['status'] ?? 'available',
            'mileage' => $data['mileage'] ?? 0,
            'fuel_type' => $data['fuel_type'] ?? null,
            'fuel_grade' => $data['fuel_grade'] ?? '95',
            'fuel_consumption' => $data['fuel_consumption'] ?? 0,
            'diesel_consumption' => $data['diesel_consumption'] ?? 0,
            'battery_capacity' => $data['battery_capacity'] ?? 0,
            'range' => $data['range'] ?? 0,
            'hybrid_consumption' => $data['hybrid_consumption'] ?? 0,
            'hybrid_range' => $data['hybrid_range'] ?? 0,
            'comment' => $data['comment'] ?? '',
            'admin_id' => Auth::id(),
        ], $data);

        $vehicle = Vehicle::create($data);
        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->all();
        $data = array_merge([
            'brand' => $data['brand'] ?? '—',
            'model' => $data['model'] ?? '—',
            'year' => $data['year'] ?? 0,
            'license_plate' => $data['license_plate'] ?? '—',
            'status' => $data['status'] ?? 'available',
            'mileage' => $data['mileage'] ?? 0,
            'fuel_type' => $data['fuel_type'] ?? null,
            'fuel_grade' => $data['fuel_grade'] ?? '95',
            'fuel_consumption' => $data['fuel_consumption'] ?? 0,
            'diesel_consumption' => $data['diesel_consumption'] ?? 0,
            'battery_capacity' => $data['battery_capacity'] ?? 0,
            'range' => $data['range'] ?? 0,
            'hybrid_consumption' => $data['hybrid_consumption'] ?? 0,
            'hybrid_range' => $data['hybrid_range'] ?? 0,
            'comment' => $data['comment'] ?? '',
        ], $data);

        $vehicle->update($data);
        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json(['deleted' => true]);
    }

    public function storeTripSheet(Request $request)
    {
        $data = $request->validate([
            'date_time' => 'required|date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:users,id',
            'location_id' => 'nullable|exists:locations,id',
            'address' => 'nullable|string',
            'distance' => 'required|numeric|min:0',
            'status' => 'required|in:in_progress,completed,canceled',
        ]);

        $data['admin_id'] = Auth::id();
        $vehicle = Vehicle::find($data['vehicle_id']);
        $location = $vehicle ? $vehicle->location : null;

        if ($data['location_id'] && $location && $location->address) {
            $data['address'] = $location->address;
        } elseif (!$data['address']) {
            return response()->json(['error' => 'Укажите адрес, если площадка не выбрана или адрес не указан!'], 422);
        }

        $cost = $vehicle ? $this->calculateCost($vehicle, $data['distance']) : 0;

        $data['cost'] = $cost;

        $tripSheet = TripSheet::create($data);
        return response()->json($tripSheet);
    }

    public function updateTripSheet(Request $request, TripSheet $tripSheet)
    {
        $data = $request->validate([
            'date_time' => 'required|date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:users,id',
            'location_id' => 'nullable|exists:locations,id',
            'address' => 'nullable|string',
            'distance' => 'required|numeric|min:0',
            'status' => 'required|in:in_progress,completed,canceled',
        ]);

        $vehicle = Vehicle::find($data['vehicle_id']);
        $location = $vehicle ? $vehicle->location : null;

        if ($data['location_id'] && $location && $location->address) {
            $data['address'] = $location->address;
        } elseif (!$data['address']) {
            return response()->json(['error' => 'Укажите адрес, если площадка не выбрана или адрес не указан!'], 422);
        }

        $cost = $vehicle ? $this->calculateCost($vehicle, $data['distance']) : 0;

        $data['cost'] = $cost;

        $tripSheet->update($data);
        return response()->json($tripSheet);
    }

    public function destroyTripSheet(TripSheet $tripSheet)
    {
        $tripSheet->delete();
        return response()->json(['deleted' => true]);
    }

    protected function calculateCost($vehicle, $distance)
    {
        $fuelType = $vehicle->fuel_type;
        $consumption = $vehicle->fuel_consumption ?: $vehicle->diesel_consumption ?: $vehicle->hybrid_consumption ?: 0;
        $fuelPrices = [
            '92' => 56.62,
            '95' => 61.87,
            '100' => 84.10,
            'diesel' => 71.36,
            'electric' => 5.86,
        ];
        $fuelPrice = $fuelPrices[$vehicle->fuel_grade ?? $fuelType ?? '95'] ?? 61.87;
        return ($distance / 100) * $consumption * $fuelPrice;
    }
}
