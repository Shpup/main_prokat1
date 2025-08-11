<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\TripSheet;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $sort = $request->input('sort', 'brand');
        $direction = $request->input('direction', 'asc');
        $tab = $request->input('tab', 'vehicles');

        $query = Vehicle::with('site');
        if (!Auth::user()->hasRole('admin')) {
            $query->where('admin_id', Auth::user()->admin_id);
        } else {
            $query->where('admin_id', Auth::id());
        }

        if ($request->ajax()) {
            if ($tab === 'vehicles') {
                $vehicles = $query->where(function ($query) use ($search) {
                    $query->where('brand', 'like', "%$search%")
                        ->orWhere('model', 'like', "%$search%")
                        ->orWhere('license_plate', 'like', "%$search%");
                })
                    ->orderBy($sort, $direction)
                    ->paginate(10);

                return response()->json([
                    'view' => view('vehicles.partials.table', compact('vehicles'))->render(),
                    'vehicles' => $vehicles->items()
                ]);
            } else {
                $tripSheetsQuery = TripSheet::with(['site', 'vehicle', 'driver'])
                    ->where(function ($query) use ($search) {
                        $query->where('date_time', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhereHas('vehicle', function ($q) use ($search) {
                                $q->where('brand', 'like', "%$search%")
                                    ->orWhere('model', 'like', "%$search%");
                            })
                            ->orWhereHas('driver', function ($q) use ($search) {
                                $q->where('name', 'like', "%$search%");
                            });
                    });

                if (!Auth::user()->hasRole('admin')) {
                    $tripSheetsQuery->where('trip_sheets.admin_id', Auth::user()->admin_id);
                } else {
                    $tripSheetsQuery->where('trip_sheets.admin_id', Auth::id());
                }

                if ($sort === 'brand') {
                    $tripSheetsQuery->join('vehicles', 'trip_sheets.vehicle_id', '=', 'vehicles.id')
                        ->orderBy('vehicles.brand', $direction)
                        ->select('trip_sheets.*');
                } else {
                    $tripSheetsQuery->orderBy($sort, $direction);
                }

                $tripSheets = $tripSheetsQuery->paginate(10);

                return response()->json([
                    'view' => view('trip-sheets.partials.table', compact('tripSheets'))->render()
                ]);
            }
        }

        $vehicles = $query->where(function ($query) use ($search) {
            $query->where('brand', 'like', "%$search%")
                ->orWhere('model', 'like', "%$search%")
                ->orWhere('license_plate', 'like', "%$search%");
        })
            ->orderBy($sort, $direction)
            ->paginate(10);

        $tripSheetsQuery = TripSheet::with(['site', 'vehicle', 'driver'])
            ->where(function ($query) use ($search) {
                $query->where('date_time', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhereHas('vehicle', function ($q) use ($search) {
                        $q->where('brand', 'like', "%$search%")
                            ->orWhere('model', 'like', "%$search%");
                    })
                    ->orWhereHas('driver', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            });

        if (!Auth::user()->hasRole('admin')) {
            $tripSheetsQuery->where('trip_sheets.admin_id', Auth::user()->admin_id);
        } else {
            $tripSheetsQuery->where('trip_sheets.admin_id', Auth::id());
        }

        if ($sort === 'brand') {
            $tripSheetsQuery->join('vehicles', 'trip_sheets.vehicle_id', '=', 'vehicles.id')
                ->orderBy('vehicles.brand', $direction)
                ->select('trip_sheets.*');
        } else {
            $tripSheetsQuery->orderBy($sort, $direction);
        }

        $tripSheets = $tripSheetsQuery->paginate(10);

        $sitesQuery = Site::query();
        if (!Auth::user()->hasRole('admin')) {
            $sitesQuery->where('admin_id', Auth::user()->admin_id);
        } else {
            $sitesQuery->where('admin_id', Auth::id());
        }
        $sites = $sitesQuery->get();

        $driversQuery = User::role('driver');
        if (!Auth::user()->hasRole('admin')) {
            $driversQuery->where('admin_id', Auth::user()->admin_id);
        } else {
            $driversQuery->where('admin_id', Auth::id());
        }
        $drivers = $driversQuery->get();

        return view('vehicles.index', compact('vehicles', 'tripSheets', 'sites', 'drivers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|max:255',
            'fuel_type' => 'required|in:petrol,diesel,electric,hybrid',
            'fuel_grade' => 'nullable|in:92,95,98,diesel',
            'fuel_consumption' => 'nullable|numeric|min:0',
            'diesel_consumption' => 'nullable|numeric|min:0',
            'hybrid_consumption' => 'nullable|numeric|min:0',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        $data['admin_id'] = Auth::user()->hasRole('admin') ? Auth::id() : Auth::user()->admin_id;
        $vehicle = Vehicle::create($data);
        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        if (!Auth::user()->hasRole('admin') && $vehicle->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|max:255',
            'fuel_type' => 'required|in:petrol,diesel,electric,hybrid',
            'fuel_grade' => 'nullable|in:92,95,98,diesel',
            'fuel_consumption' => 'nullable|numeric|min:0',
            'diesel_consumption' => 'nullable|numeric|min:0',
            'hybrid_consumption' => 'nullable|numeric|min:0',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        $vehicle->update($data);
        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle)
    {
        if (!Auth::user()->hasRole('admin') && $vehicle->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $vehicle->delete();
        return response()->json(['deleted' => true]);
    }

    public function storeTripSheet(Request $request)
    {
        $data = $request->validate([
            'date_time' => 'required|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:users,id',
            'site_id' => 'nullable|exists:sites,id',
            'address' => 'nullable|string|max:255',
            'distance' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:in_progress,completed,canceled',
        ]);

        $vehicle = Vehicle::find($data['vehicle_id']);
        if (!Auth::user()->hasRole('admin') && $vehicle->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $data['admin_id'] = Auth::user()->hasRole('admin') ? Auth::id() : Auth::user()->admin_id;

        if (!empty($data['site_id']) && empty($data['address'])) {
            $site = Site::find($data['site_id']);
            $data['address'] = $site->address ?? '';
        }

        $data['distance'] = $data['distance'] ?? 0;
        $data['status'] = $data['status'] ?? 'in_progress';
        $data['cost'] = $vehicle ? $this->calculateCost($vehicle, $data['distance']) : 0;

        if (!empty($data['site_id'])) {
            $site = Site::find($data['site_id']);
            if (!Auth::user()->hasRole('admin') && $site->admin_id != Auth::user()->admin_id) {
                abort(403, 'Доступ запрещен');
            }
            if (empty($data['address'])) {
                $data['address'] = $site->address ?? '';
            }
        }

        $tripSheet = TripSheet::create($data);
        return response()->json($tripSheet);
    }

    public function updateTripSheet(Request $request, TripSheet $tripSheet)
    {
        if (!Auth::user()->hasRole('admin') && $tripSheet->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $data = $request->validate([
            'date_time' => 'required|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:users,id',
            'site_id' => 'nullable|exists:sites,id',
            'address' => 'nullable|string|max:255',
            'distance' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:in_progress,completed,canceled',
        ]);

        $vehicle = Vehicle::find($data['vehicle_id']);
        if (!Auth::user()->hasRole('admin') && $vehicle->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        if (empty($data['site_id']) && empty($data['address'])) {
            return response()->json(['error' => 'Укажите адрес или выберите площадку!'], 422);
        }

        $data['distance'] = $data['distance'] ?? 0;
        $data['status'] = $data['status'] ?? 'in_progress';
        $data['cost'] = $vehicle ? $this->calculateCost($vehicle, $data['distance']) : 0;

        if (!empty($data['site_id'])) {
            $site = Site::find($data['site_id']);
            if (!Auth::user()->hasRole('admin') && $site->admin_id != Auth::user()->admin_id) {
                abort(403, 'Доступ запрещен');
            }
            if (empty($data['address'])) {
                $data['address'] = $site->address ?? '';
            }
        }

        $tripSheet->update($data);
        return response()->json($tripSheet);
    }

    public function destroyTripSheet(TripSheet $tripSheet)
    {
        if (!Auth::user()->hasRole('admin') && $tripSheet->admin_id != Auth::user()->admin_id) {
            abort(403, 'Доступ запрещен');
        }

        $tripSheet->delete();
        return response()->json(['deleted' => true]);
    }

    protected function calculateCost($vehicle, $distance)
    {
        $distance = (float) ($distance ?? 0);
        $fuelType = $vehicle->fuel_type;
        $consumption = $vehicle->fuel_consumption ?: $vehicle->diesel_consumption ?: $vehicle->hybrid_consumption ?: 0;
        $fuelPrices = [
            '92' => 58.50,
            '95' => 61.87,
            '98' => 65.30,
            'diesel' => 62.10,
        ];
        $fuelPrice = $fuelPrices[$vehicle->fuel_grade ?? $fuelType ?? '95'] ?? 61.87;
        return number_format(($distance / 100) * $consumption * $fuelPrice, 2, '.', '');
    }
}
