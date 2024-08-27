<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;

class GameController extends Controller
{
    /**
     * Create new game
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'location' => 'required|string|max:255',
            'match_date' => 'required|date_format:Y-m-d H:i:s',
            'finished' => 'required|boolean',
            'confirmed' => 'required|boolean',
        ]);

        $game = Game::create($validatedData);

        return response()->json([
            'message' => 'Game created!',
            'game' => $game
        ], 201);
    }

    /**
     * Update existing game
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'location' => 'sometimes|required|string|max:255',
            'match_date' => 'sometimes|required|date_format:Y-m-d H:i:s',
            'finished' => 'sometimes|required|boolean',
            'confirmed' => 'sometimes|required|boolean',
        ]);

        $game = Game::findOrFail($id);

        $game->update($validatedData);

        return response()->json([
            'message' => 'Game updated',
            'game' => $game
        ], 200);
    }

    /**
     * List all games
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $games = Game::all();

        return response()->json([
            'message' => 'Game list.',
            'games' => $games
        ], 200);
    }

    /**
     * Find game by id
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $game = Game::findOrFail($id);

        return response()->json([
            'message' => 'Game found!',
            'game' => $game
        ], 200);
    }

    /**
     * Remove existing game
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $game = Game::findOrFail($id);

        $game->delete();

        return response()->json([
            'message' => 'Game removed!'
        ], 200);
    }

    
    /**
     * Remove existing game
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPlayer($request)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|integer|exists:games,id',
            'player_id' => 'required|array',
            'player_id.*' => 'integer|exists:players,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        // Obtém os dados de entrada
        $gameId = $request->input('game_id');
        $playerIds = $request->input('player_id');
        
        // Busca o jogo pelo ID
        $game = Game::findOrFail($gameId);

        foreach ($playerIds as $playerId) {
            $confirmedPlayer = ConfirmedPlayer::firstOrNew(['game_id' => $gameId, 'player_id' => $playerId]);

            // Adiciona a confirmação
            if (!$confirmedPlayer->exists) {
                $confirmedPlayer->save();
            }
        }

        // Retorna uma mensagem de sucesso
        return response()->json([
            'message' => 'Jogadores confirmados com sucesso!'
        ], 200);
    }
}