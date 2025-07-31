<?php

namespace App\Http\Controllers\User;
use App\Models\Address;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
  // app/Http/Controllers/ProfileController.php

public function updateLocation(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'location' => 'required|string|max:255',
    ]);

    $user->location = $request->location;
    $user->save();

    return response()->json([
        'success' => true,
        'data' => $user,
        'message' => 'Location updated successfully',
    ]);
}

    public function show(Request $request)
    {
$user = auth()->user();

     return response()->json([
    'success' => true,
    'data' => $user
]);

    }

    public function update(Request $request)
    {
     
        $user = auth()->user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'    => 'required|string|max:20',
            'location' => 'required|string|max:255',
        ]);

        $user->update($request->only('name', 'email', 'phone', 'location'));

        return response()->json([
            'success' => true,
            'data' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'location'   => $user->location,
                'avatar'     => $user->avatar ?? null,
                'created_at' => $user->created_at->toIso8601String(),
                'updated_at' => $user->updated_at->toIso8601String(),
            ],
            'message' => 'Profile updated successfully'
        ]);
    }

   
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'new_password'     => 'required|min:8',
        ]);

    
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Password changed successfully'
        ]);
    }
    // Get All User Addresses
public function getAddresses(Request $request)
{
    $user = auth()->user();
    return response()->json([
        'success' => true,
        'data' => $user->addresses()->get()
    ]);
}

// Create Address
public function createAddress(Request $request)
{
    $user = auth()->user();

    $data = $request->validate([
        'name'        => 'required|string|max:255',
        'street'      => 'required|string|max:255',
        'city'        => 'required|string|max:255',
        'state'       => 'required|string|max:255',
        'country'     => 'required|string|max:255',
        'postal_code' => 'required|string|max:20',
        'phone'       => 'required|string|max:20',
        'type'        => ['required', Rule::in(['home', 'work', 'other'])],
        'is_default'  => 'boolean'
    ]);

    if (!empty($data['is_default'])) {
        $user->addresses()->update(['is_default' => false]);
    }

    $address = $user->addresses()->create($data);

    return response()->json([
        'success' => true,
        'data' => $address,
        'message' => 'Address created successfully'
    ]);
}

// Update Address
public function updateAddress(Request $request, $id)
{
    $user = auth()->user();

    $address = $user->addresses()->findOrFail($id);

    $data = $request->validate([
        'name'        => 'required|string|max:255',
        'street'      => 'required|string|max:255',
        'city'        => 'required|string|max:255',
        'state'       => 'required|string|max:255',
        'country'     => 'required|string|max:255',
        'postal_code' => 'required|string|max:20',
        'phone'       => 'required|string|max:20',
        'type'        => ['required', Rule::in(['home', 'work', 'other'])],
        'is_default'  => 'boolean'
    ]);

    if (!empty($data['is_default'])) {
        $user->addresses()->update(['is_default' => false]);
    }

    $address->update($data);

    return response()->json([
        'success' => true,
        'data' => $address,
        'message' => 'Address updated successfully'
    ]);
}

// Delete Address
public function deleteAddress(Request $request, $id)
{
    $user = auth()->user();

    $address = $user->addresses()->findOrFail($id);
    $address->delete();

    return response()->json([
        'success' => true,
        'data' => null,
        'message' => 'Address deleted successfully'
    ]);
}

}

