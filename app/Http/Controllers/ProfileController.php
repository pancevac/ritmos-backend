<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show($id)
    {
        $profile = User::with([
            'playlists',    // Load playlist
            'tracks.media', // Load tracks records with media (song path)
            'media'         // Load user profile image
        ])
            ->activated()
            ->where('id', $id)
            ->first();

        if (!$profile) {
            return response()->json(['error' => 'No user profile found.'], 404);
        }

        return $profile;
    }

    /**
     * Update user.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $profile = User::activated()->where('id', $id)->first();

        if (!$profile) {
            return response()->json(['error' => 'Unknown user.'], 404);
        }

        $this->validate($request, [
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', Rule::unique('users')->ignore($profile->id)],
            'password' => ['string', 'min:6'],
        ]);

        $result = $profile->update($request->only(['name', 'email', 'password']));

        return $result ?
            response()->json([$profile->fresh()]) :
            response()->json(['error' => 'User can not be updated!']);
    }

    /**
     * Handle uploading image for user profile.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function uploadImage(Request $request, $id)
    {
        $this->validate($request, [
            'image' => 'required|image',
        ]);

        $profile = User::activated()->where('id', $id)->first();

        if (!$profile) {
            return response()->json(['error' => 'Unknown user.'], 404);
        }

        // Save image after validation
        $profile->addMediaFromRequest('image')
            ->usingName($profile->name)
            ->toMediaCollection('profile');

        return response()->json([
            'success' => 'Successful uploaded image.'
        ]);
    }

    /**
     * Deactivate user.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivate($id)
    {
        $profile = User::activated()->where('id', $id)->first();

        if (!$profile) {
            return response()->json(['error' => 'Unknown user.']);
        }

        if ($result = $profile->update(['activated' => false])) {
            return response()->json(['success', 'User successfully deactivated!']);
        }

    }
}
