<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use Illuminate\Support\Facades\Response;
use App\Models\ProductVideo;
use App\Models\Video;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index() {}

    public function fetchYoutubeVideos(Request $request)
    {
        $search = $request->query('search');
        $filter = $request->query('filter', 'all');
        $sort = $request->query('sort');
        $perPage = (int) $request->query('per_page', 12);

        $productId = $request->query('product_id');
        if ($productId && is_numeric($productId)) {
            $videoIds = ProductVideo::where('product_id', $productId)
                ->pluck('video_id')
                ->toArray();
        }
        // Build query
        $query = Video::query();

        // Search by title (optional)
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Sorting (optional)
        if ($sort === 'newest') {
            $query->orderBy('published_at', 'desc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('published_at', 'asc');
        } else {
            $query->orderBy('published_at', 'desc'); // default
        }

        if ($filter == 'selected' && isset($videoIds))
        {
            $query->whereIn('id', $videoIds);
        }
        // Paginate
        $videos = $query->paginate($perPage);

        return response()->json([
            'data' => $videos->items(),
            'current_page' => $videos->currentPage(),
            'last_page' => $videos->lastPage(),
            'per_page' => $videos->perPage(),
            'total' => $videos->total(),
            'selected_videos' => $videoIds ?? [],
        ]);
    }

    public function export(Request $request)
    {
        $orderStatus = $request->input('order_status');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $orderState = $request->input('order_state');

        $query = StoreOrder::with('shippingAddress')->where('charge_status', 'paid');

        // Most selective: Date range
        if (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Then by state
        if (!empty($orderState)) {
            $query->whereHas('shippingAddress', function ($q) use ($orderState) {
                $q->where('state', $orderState);
            });
        }

        // Finally, status
        if ($orderStatus && $orderStatus !== 'All') {
            $query->where('order_status', strtolower($orderStatus));
        }

        $orders = $query->get();

        // Generate CSV
        $csvData = [];
        $csvData[] = ['Order ID', 'From Date', 'To Date', 'Status', 'State', 'Total Amount', 'Order Date'];

        foreach ($orders as $order) {
            $shippingStateId = $order->shippingAddress->state ?? null;

            // Get state name using helper or fallback
            $state = $shippingStateId ? getStatebyId($shippingStateId) : null;
            $stateName = $state['state_name'] ?? 'N/A';

            $csvData[] = [
                $order->order_id,
                $fromDate ?: 'N/A',
                $toDate ?: 'N/A',
                ucfirst($order->order_status),
                $stateName,
                '$' . number_format($order->total_amount, 2),
                optional($order->created_at)->format('Y-m-d') ?? 'N/A',
            ];
        }

        $filename = 'orders_export_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);

        return Response::stream(function () use ($handle) {
            fpassthru($handle);
        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ]);
    }
    function parseYouTubeDuration($duration)
    {
        $interval = new DateInterval($duration);
        return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
    }

    public function syncYoutubeVideos(Request $request)
    {
        $apiKey = env('YOUTUBE_API_KEY');
        $channelId = env('YOUTUBE_CHANNEL_ID');

        $allVideos = collect();
        $pageToken = null;

        // Step 1: Loop through all pages to fetch all videos
        do {
            $params = [
                'key' => $apiKey,
                'channelId' => $channelId,
                'part' => 'snippet',
                'order' => 'date',
                'maxResults' => 50,
                'type' => 'video',
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = Http::get('https://www.googleapis.com/youtube/v3/search', $params);
            $body = $response->json();

            $items = collect($body['items'] ?? []);
            $videoIds = $items->pluck('id.videoId')->filter()->values();

            if ($videoIds->isEmpty()) break;

            // Step 2: Get full details of these videos
            $detailsResponse = Http::get('https://www.googleapis.com/youtube/v3/videos', [
                'key' => $apiKey,
                'id' => $videoIds->implode(','),
                'part' => 'snippet,contentDetails',
            ]);

            $videoDetails = collect($detailsResponse->json('items') ?? []);
            $allVideos = $allVideos->merge($videoDetails);

            // Prepare for next page
            $pageToken = $body['nextPageToken'] ?? null;

        } while ($pageToken);

        // Step 3: Fetch existing video IDs from DB
        $existingIds = Video::whereIn('video_id', $allVideos->pluck('id'))->pluck('video_id')->toArray();

        $newVideos = $allVideos->filter(fn($video) => !in_array($video['id'], $existingIds));

        // Step 5: Prepare insert array
        $insertData = $newVideos->map(function ($video) {
            return [
                'video_id' => $video['id'],
                'title' => $video['snippet']['title'],
                'thumbnail' => $video['snippet']['thumbnails']['medium']['url'] ?? '',
                'published_at' => $video['snippet']['publishedAt'],
                'duration' => $this->parseYouTubeDuration($video['contentDetails']['duration']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if (!empty($insertData)) {
            Video::insert($insertData);
        }

        return response()->json([
            'message' => 'YouTube videos synced successfully.',
            'inserted' => count($insertData),
            'skipped' => count($existingIds),
        ]);
    }

    public function toggleProductVideo(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'product_id' => 'required|integer|exists:products,product_id',
                'video_id' => 'required|integer|exists:videos,id',
                'assign' => 'required|boolean',
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error: ' . $validation->errors()->first(),
            ], 422);
        }

        $data = $validation->validated();

        if ($data['assign']) {
            // Assign the video
            ProductVideo::updateOrInsert([
                'product_id' => $data['product_id'],
                'video_id' => $data['video_id'],
            ]);
            return response()->json(['message' => 'Video assigned to product.'], 200);
        } else {
            // Remove the video
            ProductVideo::where([
                'product_id' => $data['product_id'],
                'video_id' => $data['video_id'],
            ])->delete();
            return response()->json(['message' => 'Video removed from product.'], 200);
        }
    }

    public function getAssignedVideos($productId)
    {
        $videoIds = ProductVideo::where('product_id', $productId)
            ->pluck('video_id')
            ->toArray();

        return response()->json($videoIds);
    }
}
