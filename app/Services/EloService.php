<?php
namespace App\Services;

use App\Models\Player;

class EloService
{
    public function delta(Player $winner, Player $loser): int
    {
        $expected = 1 / (1 + pow(10, ($loser->rating - $winner->rating) / 400));
        $delta = (int) round(40 * (1 - $expected));
        $gap = $winner->rating - $loser->rating;

        if ($gap < -110) {
            $delta = max($delta, 55);
        } elseif ($gap < -49) {
            $delta = max($delta, 40);
        } elseif ($gap < 0) {
            $delta = max($delta, 30);
        } else {
            $delta = max($delta, 15);
        }

        return $delta;
    }

    public function levelFor(int $rating): string
    {
        foreach (config('padelio.levels') as $key => $level) {
            if ($rating >= $level['min'] && $rating <= $level['max']) {
                return $key;
            }
        }
        return 'upper-intermediate';
    }
}
