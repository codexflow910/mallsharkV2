<?php
namespace Packages\Review\Contracts\V4;

use Packages\Product\Models\V4\User;

trait ReviewHelpers
{
    function sortReviews($reviews, $sortBy)
    {
        if ($sortBy == 'high-to-low') {
            $sortBy = 'desc';
            $reviews = collect($reviews)->sortByDesc('rating')->values()->all();
        } elseif ($sortBy == 'low-to-high') {
            $sortBy = 'asc';
            $reviews = collect($reviews)->sortBy('rating')->values()->all();
        } elseif ($sortBy == 'old-to-new') {
            $sortBy = 'asc';
            $reviews = collect($reviews)->sortBy('created_at')->values()->all();
        } elseif ($sortBy == 'new-to-old') {
            $sortBy = 'desc';
            $reviews = collect($reviews)->sortByDesc('created_at')->values()->all();
        }
        return $reviews;
    }
}