<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;

class PlayerController extends Controller
{
    /**
     * Create new player
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:5',
            'is_goalkeeper' => 'required|boolean'
        ]);

        $player = Player::create([
            'name' => $validatedData['name'],
            'level' => $validatedData['level'],
            'is_goalkeeper' => $validatedData['is_goalkeeper']
        ]);

        return response()->json([
            'message' => 'Player created!',
            'player' => $player
        ], 201);
    }

    /**
     * Update existing player
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'level' => 'sometimes|required|integer|min:1|max:5',
            'is_goalkeeper' => 'sometimes|required|boolean'
        ]);

        $player = Player::findOrFail($id);

        $player->update($validatedData);

        return response()->json([
            'message' => 'Player updated!',
            'player' => $player
        ], 200);
    }

    /**
     * List all players
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $players = Player::all();

        return response()->json([
            'message' => "Player list",
            'players' => $players
        ], 200);
    }

    /**
     * Find player by id
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $player = Player::findOrFail($id);

        return response()->json([
            'message' => 'Player found!',
            'player' => $player
        ], 200);
    }

    /**
     * Delete player
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $player = Player::findOrFail($id);

        $player->delete();

        return response()->json([
            'message' => 'Player deleted!'
        ], 200);
    }
}
