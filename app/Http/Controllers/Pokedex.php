<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Pokedex extends Controller
{
    public function __invoke(Request $request)
    {
        // Obtener el número el límite de resultados
        $limit = $request->limit ?? 8;
        $responses = [];

        for ($i = 1; $i <= $limit; $i++) {
            $id = rand(1, 900);

            // Obtener datos del Pokémon (usando cache)
            $pokemon = $this->getPokemonById($id);

            // Imagen animada de Black/White si existe
            $pokemon['image'] = $pokemon['sprites']['versions']['generation-v']['black-white']['animated']['front_default']
                ?? $pokemon['sprites']['front_default'];

            // Obtener datos de especie y cadena evolutiva (con cache)
            $species = $this->getSpeciesData($pokemon['species']['url']);
            $evoChain = $this->getEvolutionChain($species['evolution_chain']['url'] ?? null);

            // Obtener evoluciones
            $evolutionNames = $this->extractEvolutionNames($evoChain['chain'] ?? []);
            $pokemon['evolutions'] = $this->getEvolutionsData($evolutionNames, $pokemon['name']);

            $responses[] = $pokemon;
        }

        return view('welcome', compact('responses'));
    }

    protected function getPokemonById(int $id): array
    {
        return Cache::remember("pokemon_$id", now()->addHours(6), function () use ($id) {
            return Http::get("https://pokeapi.co/api/v2/pokemon/$id")->json();
        });
    }

    protected function getPokemonByName(string $name): array
    {
        return Cache::remember('pokemon_name_'.strtolower($name), now()->addHours(6), function () use ($name) {
            $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$name}");

            if ($response->successful()) {
                return $response->json();
            }

            return []; // Devolver un array vacío si falla
        });
    }

    protected function getSpeciesData(string $url): array
    {
        return Cache::remember('species_'.md5($url), now()->addHours(6), function () use ($url) {
            return Http::get($url)->json();
        });
    }

    protected function getEvolutionChain(string $url): array
    {
        return Cache::remember('evo_chain_'.md5($url), now()->addHours(6), function () use ($url) {
            return Http::get($url)->json();
        });
    }

    protected function extractEvolutionNames(array $chain): array
    {
        $names = [];

        $traverse = function ($node) use (&$traverse, &$names) {
            if (! isset($node['species']['name'])) {
                return;
            }
            $names[] = $node['species']['name'];

            foreach ($node['evolves_to'] ?? [] as $evo) {
                $traverse($evo);
            }
        };

        $traverse($chain);

        return $names;
    }

    protected function getEvolutionsData(array $names, string $currentName): array
    {
        $evolutions = [];

        foreach ($names as $name) {
            if (strtolower($name) !== strtolower($currentName)) {
                $evoData = $this->getPokemonByName($name);

                $evolutions[] = [
                    'name' => $evoData['name'] ?? '',
                    'image' => $evoData['sprites']['front_default'] ?? '',
                    'stats' => $evoData['stats'] ?? [],
                ];
            }
        }

        return $evolutions;
    }
}
