<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Week;
use App\Models\Track;
use App\Players\Player;
use App\Rules\PlayerUrl;
use App\Services\UserService;
use App\Exceptions\PlayerException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Afficher une piste spécifique.
     */
    public function show(Request $request, Week $week, Track $track, Player $player): View
    {
        return view('app.tracks.show', [
            'week' => $week->loadCount('tracks'),
            'track' => $track->loadCount('likes'),
            'tracks_count' => $week->tracks_count,
            'position' => $week->getTrackPosition($track),
            'liked' => $request->user()->likes()->whereTrackId($track->id)->exists(),
            'embed' => $player->embed($track->player, $track->player_track_id),
        ]);
    }

    /**
     * Afficher le formulaire de création d'une piste.
     */
    public function create(UserService $user): View
    {
        // Récupérer toutes les catégories
        $categories = Category::all();

        return view('app.tracks.create', [
            'week' => Week::current(),
            'remaining_tracks_count' => $user->remainingTracksCount(),
            'categories' => $categories,
        ]);
    }

    /**
     * Enregistrer une nouvelle piste.
     */
    public function store(Request $request, Player $player): RedirectResponse
    {
        $this->authorize('create', Track::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', new PlayerUrl()],
            'category_id' => ['required', 'exists:categories,id'], // Validation de la catégorie
        ]);

        DB::beginTransaction();

        // Créer et associer la nouvelle piste avec ses données
        $track = new Track($validated);
        $track->user()->associate($request->user());
        $track->week()->associate(Week::current());
        $track->category()->associate($validated['category_id']); // Associer la catégorie

        try {
            // Récupérer les détails de la piste depuis le fournisseur (YT, SC)
            $details = $player->details($track->url);

            // Associer les détails de la piste
            $track->player = $details->player_id;
            $track->player_track_id = $details->track_id;
            $track->player_thumbnail_url = $details->thumbnail_url;

            // Enregistrer la piste
            $track->save();

            DB::commit();
        } catch (PlayerException $th) {
            DB::rollBack();
            throw $th;
        }

        return redirect()->route('app.tracks.show', [
            'week' => $track->week->uri,
            'track' => $track,
        ]);
    }

    /**
     * Afficher la liste des pistes par semaine avec leurs catégories.
     */
    public function showWeekTracks(Week $week): View
    {
        // Charger les pistes avec la relation 'category'
        $tracks = Track::with('category')->where('week_id', $week->id)->get();

        return view('app.weeks.show', [
            'week' => $week,
            'tracks' => $tracks,
        ]);
    }

    /**
     * Basculer le like d'une piste.
     */
    public function like(Request $request, Week $week, Track $track): RedirectResponse
    {
        $user = $request->user();

        $track->likes()->toggle([
            $user->id => ['liked_at' => now()]
        ]);

        return redirect()->route('app.tracks.show', [
            'week' => $week->uri,
            'track' => $track,
        ]);
    }
}
