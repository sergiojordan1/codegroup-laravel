<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\ConfirmedPlayer;
use App\Models\Player;
use Illuminate\Support\Facades\Validator;

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
            'game_date' => 'required|date_format:Y-m-d H:i:s',
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
            'game_date' => 'sometimes|required|date_format:Y-m-d H:i:s',
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
     * Confirm players 
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request)
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

        $gameId = $request->input('game_id');
        $playerId = $request->input('player_id');

        $game = Game::findOrFail($gameId);

        foreach ($playerId as $id) {
            $confirmedPlayer = ConfirmedPlayer::firstOrNew(['game_id' => $gameId, 'player_id' => $id]);

            if (!$confirmedPlayer->exists) {
                $confirmedPlayer->save();
            }
        }

        return response()->json([
            'message' => 'Players confirmed!!'
        ], 200);
    }

    /**
     * List confirmed players by game id
     *
     * @param int $idGame
     * @return \Illuminate\Http\JsonResponse
     */
    public function listConfirmed($idGame)
    {
        $game = Game::findOrFail($idGame);

        $confirmedPlayers = Player::whereIn('id', function($query) use ($idGame) {
            $query->select('player_id')
                  ->from('confirmed_players')
                  ->where('game_id', $idGame);
        })->get();

        return response()->json($confirmedPlayers, 200);
    }

    /**
     * Create balanced teams
     *
     * @param int $idGame
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTeams($idGame)
    {
        $game = Game::findOrFail($idGame);

        // Get confirmed players
        $players = Player::whereIn('id', function($query) use ($idGame) {
            $query->select('player_id')
                  ->from('confirmed_players')
                  ->where('game_id', $idGame);
        })->get();

        // Separate goalkeepers and field players by level
        $goalkeepers = $players->where('is_goalkeeper', true)->sortByDesc('level');
        $fieldPlayers = $players->where('is_goalkeeper', false)->sortByDesc('level');

        // Verify if have at least 2 goalkeepers
        $requiredGoalkeepers = 2;
        if ($goalkeepers->count() < $requiredGoalkeepers) {
            return response()->json([
                'message' => 'You need at least 2 goalkeepers to create teams!'
            ], 400);
        }

        // Verify if have at least 10 players to create 2 teams
        $requiredFieldPlayers = 10;
        if ($fieldPlayers->count() < $requiredFieldPlayers) {
            return response()->json([
                'message' => 'You need at least 10 field players to create teams!'
            ], 400);
        }

        // Define the number of teams
        $numTeams = min(floor($goalkeepers->count() / 1), floor($fieldPlayers->count() / 5));

        // Initialize teams
        $teams = [];
        for ($i = 0; $i < $numTeams; $i++) {
            $teams["time " . ($i + 1)] = [
                'goalkeeper' => null,
                'players' => []
            ];
        }

        // Distribute goalkeepers
        $i = 0;
        foreach ($goalkeepers as $gk) {
            if ($i < $numTeams) {
                $teams["time " . ($i + 1)]['goalkeeper'] = [
                    'name' => $gk->name,
                    'level' => $gk->level,
                    'is_goalkeeper' => $gk->is_goalkeeper
                ];
                $i++;
            }
        }

        // Distribute field players
        $i = 0;
        foreach ($fieldPlayers as $fp) {
            if (count($teams["time " . (($i % $numTeams) + 1)]['players']) < 5) {
                $teams["time " . (($i % $numTeams) + 1)]['players'][] = [
                    'name' => $fp->name,
                    'level' => $fp->level,
                    'is_goalkeeper' => $fp->is_goalkeeper
                ];
                $i++;
            }
        }

        // Check for remaining players and goalkeepers
        $remainingGoalkeepers = $goalkeepers->slice($numTeams);
        $remainingPlayers = $fieldPlayers->slice($numTeams * 5);

        // Create an extra team if there are remaining players or goalkeepers
        if ($remainingGoalkeepers->isNotEmpty() || $remainingPlayers->isNotEmpty()) {
            $extraTeam = [
                'goalkeeper' => null,
                'players' => []
            ];

            // Add an extra goalkeeper if available
            if ($remainingGoalkeepers->isNotEmpty()) {
                $extraTeam['goalkeeper'] = [
                    'name' => $remainingGoalkeepers->first()->name,
                    'level' => $remainingGoalkeepers->first()->level,
                    'is_goalkeeper' => $remainingGoalkeepers->first()->is_goalkeeper
                ];

                // Remove the added goalkeeper
                $remainingGoalkeepers = $remainingGoalkeepers->slice(1);
            }

            // Add remaining players to the extra team
            foreach ($remainingPlayers as $player) {
                $extraTeam['players'][] = [
                    'name' => $player->name,
                    'level' => $player->level,
                    'is_goalkeeper' => $player->is_goalkeeper
                ];
            }

            // Add the extra team to the teams array
            $teams["time " . ($numTeams + 1)] = $extraTeam;
        }

        // Return the formed teams along with game information
        return response()->json([
            'game' => [
                'location' => $game->location,
                'game_date' => $game->game_date
            ],
            'teams' => $teams
        ], 200);
    }
}