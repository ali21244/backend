<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;

use App\Models\Package;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function getClients(Request $request)
    {
        $query = User::where('type', 'user');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $clients = $query->withCount(['packages as total_packages'])
            ->withSum('packages as total_spent', 'price')
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'location' => $client->location,
                    'status' => 'active',
                    'total_packages' => $client->total_packages ?? 0,
                    'total_spent' => round($client->total_spent ?? 0, 2),
                    'created_at' => $client->created_at,
                    'last_activity' => $client->updated_at,
                ];
            });

        return response()->json(['success' => true, 'data' => $clients]);
    }

    public function getStats()
    {
        $now = now();

        return response()->json([
            'success' => true,
            'data' => [
                'total_clients' => User::where('type', 'user')->count(),
                'active_shipments' => Package::where('status', 'in_transit')->count(),
                'total_revenue' => round(Package::sum('price'), 2),
                'pending_issues' => 3, // Placeholder
                'monthly_revenue' => round(Package::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('price'), 2),
                'new_clients_this_month' => User::where('type', 'user')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
                'packages_this_month' => Package::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
            ]
        ]);
    }

    public function createClientPackage(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'country' => 'required|string',
            'weight' => 'required|numeric',
            'price' => 'required|numeric',
            'shipping_method' => 'required|string',
            'status' => 'required|string',
            'estimated_arrival' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $client = User::findOrFail($validated['client_id']);

        $package = $client->packages()->create([
            'description' => $validated['description'],
            'country' => $validated['country'],
            'origin_country' => $validated['country'], // Optional logic; adapt if needed
            'destination_country' => $validated['country'],
            'weight' => $validated['weight'],
            'shipping_method' => $validated['shipping_method'],
            'price' => $validated['price'],
            'status' => $validated['status'],
'estimated_delivery' => Carbon::parse($validated['estimated_arrival'])->format('Y-m-d H:i:s'),
            'special_instructions' => $validated['notes'] ?? null,
            'tracking_number' => strtoupper(Str::random(12)),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $package->id,
                'client_id' => $client->id,
                'tracking_number' => $package->tracking_number,
                'description' => $package->description,
                'weight' => $package->weight,
                'price' => $package->price,
                'country' => $package->country,
                'shipping_method' => $package->shipping_method,
                'status' => $package->status,
                'estimated_arrival' => $package->estimated_delivery,
                'notes' => $package->special_instructions,
                'created_at' => $package->created_at,
            ],
            'message' => 'Package created successfully'
        ]);
    }

    public function getClientPackages(User $client)
    {
        $packages = $client->packages()->get()->map(function ($pkg) use ($client) {
            return [
                'id' => $pkg->id,
                'client_id' => $client->id,
                'tracking_number' => $pkg->tracking_number,
                'description' => $pkg->description,
                'weight' => $pkg->weight,
                'price' => $pkg->price,
                'country' => $pkg->country,
                'shipping_method' => $pkg->shipping_method,
                'status' => $pkg->status,
                'estimated_arrival' => $pkg->estimated_delivery,
                'notes' => $pkg->special_instructions,
                'created_at' => $pkg->created_at,
            ];
        });

        return response()->json(['success' => true, 'data' => $packages]);
    }

    public function getAllShipments()
    {
        $shipments = Package::with('user:id,name,email')
            ->get()
            ->map(function ($pkg) {
                return [
                    'id' => $pkg->id,
                    'package_id' => 'PKG-' . str_pad($pkg->id, 3, '0', STR_PAD_LEFT),
                    'client_id' => $pkg->user->id,
                    'client_name' => $pkg->user->name,
                    'client_email' => $pkg->user->email,
                    'description' => $pkg->description,
                    'country' => $pkg->country,
                    'shipping_method' => $pkg->shipping_method,
                    'weight' => $pkg->weight,
                    'price' => $pkg->price,
                    'status' => $pkg->status,
                    'tracking_number' => $pkg->tracking_number,
                    'created_at' => $pkg->created_at,
                    'arrived_at' => $pkg->arrived_at,
                ];
            });

        return response()->json(['success' => true, 'data' => $shipments]);
    }

    public function updatePackageStatus(Request $request, Package $package)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $package->status = $validated['status'];

      

        $package->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $package->id,
                'status' => $package->status,
                'updated_at' => $package->updated_at,
            ],
            'message' => 'Package status updated successfully'
        ]);
    }
  public function updateProfile(Request $request)
{

    $admin = auth()->user();
Log::info('Authenticated admin', ['user' => auth()->user()]);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
 'email' => [
            'required',
            'email',
            Rule::unique('users', 'email')->ignore($admin->id),
        ],    ]);

    $admin->update($validated);

    return response()->json([
        'success' => true,
        'data' => $admin,
        'message' => 'Admin profile updated successfully',
    ]);
}

public function changePassword(Request $request)
{
    $admin = auth()->user();

    $validated = $request->validate([
        'new_password' => 'required|string|min:8|confirmed',
    ]);

    $admin->password = bcrypt($validated['new_password']);
    $admin->save();

    return response()->json([
        'success' => true,
        'message' => 'Password updated successfully',
    ]);
}

public function verifyAuth(Request $request)
{
    $admin = auth()->user();

    return response()->json([
        'success' => true,
        'data' => [
            'authenticated' => true,
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->type, // assuming 'type' is the role
            ]
        ]
    ]);
}


}
