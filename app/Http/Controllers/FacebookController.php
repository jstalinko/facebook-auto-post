<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\FacebookPage;
use App\Models\FacebookUser;
use App\Models\FacebookPostLog;
use App\Jobs\PostToFacebookPage;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class FacebookController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')
            ->scopes([
                'pages_show_list',
                'pages_manage_metadata',
                'pages_read_engagement',
                'pages_manage_posts',
            ])
            ->redirect();
    }

    public function callback()
    {
        $fbUser = Socialite::driver('facebook')->stateless()->user();

        FacebookUser::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'facebook_id' => $fbUser->id,
            ],
            [
                'name' => $fbUser->name ?? $fbUser->nickname ?? 'Facebook User',
                'access_token' => encrypt($fbUser->token),
            ]
        );

        return redirect()->route('dashboard');
    }
    public function getPages()
    {
        $data = request()->validate([
            'facebook_user_id' => ['required', 'integer', Rule::exists('facebook_users', 'id')->where('user_id', auth()->id())],
        ]);

        $facebookUser = FacebookUser::findOrFail($data['facebook_user_id']);

        if (!$facebookUser->access_token) {
            return back()->withErrors('Facebook belum terhubung');
        }

        $response = Http::withToken(
            decrypt($facebookUser->access_token)
        )->get('https://graph.facebook.com/v19.0/me/accounts');

        if (!$response->successful()) {
            return back()->withErrors($response->json());
        }

        foreach ($response->json('data') as $page) {
            if (!in_array('CREATE_CONTENT', $page['tasks'] ?? [])) {
                continue;
            }

            FacebookPage::updateOrCreate(
                [
                    'user_id' => $facebookUser->user_id,
                    'page_id' => $page['id'],
                ],
                [
                    'facebook_user_id' => $facebookUser->id,
                    'page_name'        => $page['name'],
                    'page_access_token' => encrypt($page['access_token']),
                ]
            );
        }

        return redirect()->route('dashboard')->with('success', 'Fanspage berhasil diambil');
    }
    public function store(Request $request)
    {
        // Hapus dd() setelah pengujian.
        // dd($request->scheduled_at);

        $rules = [
            'page_id' => ['required', 'string'],
            'type' => ['required', Rule::in(['text', 'photo', 'video'])],
            'message' => ['nullable', 'string'],

            'scheduled_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'media' => ['nullable', 'file'],
        ];

        $validated = $request->validate($rules);

        $page = FacebookPage::where('page_id', $validated['page_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $mediaPath = null;
        if (in_array($validated['type'], ['photo', 'video']) && $request->file('media')) {
            $mediaPath = $request->file('media')->store('facebook-media');
        }

        // PERBAIKAN 2: Jika scheduled_at ada, konversi ke format database (Y-m-d H:i:s)
        $scheduledAt = null;
        if (!empty($validated['scheduled_at'])) {
            $scheduledAt = Carbon::createFromFormat('Y-m-d\TH:i', $validated['scheduled_at'])->toDateTimeString();
        }

        $log = FacebookPostLog::create([
            'user_id' => auth()->id(),
            'facebook_user_id' => $page->facebook_user_id,
            'facebook_page_id' => $page->id,
            'type' => $validated['type'],
            'message' => $validated['message'] ?? null,
            'media_path' => $mediaPath,
            // Menggunakan $scheduledAt yang sudah dikonversi
            'scheduled_at' => $scheduledAt,
            'status' => empty($scheduledAt) ? 'queued' : 'scheduled',
        ]);

        $job = new PostToFacebookPage(
            $page->id,
            $validated['type'],
            $validated['message'] ?? null,
            $mediaPath,
            $log->id,
        );

        if (!empty($scheduledAt)) {
            // PERBAIKAN 3: Jika menggunakan $scheduledAt dari database,
            // Carbon::parse() seharusnya bekerja dengan baik. Namun,
            // jika Anda ingin memastikan format yang tepat untuk delay,
            // lebih baik menggunakan Carbon::createFromFormat seperti di atas.
            // Tapi karena $scheduledAt sudah di-set ke database format Y-m-d H:i:s,
            // kita bisa parse dari situ atau dari input asli.
            $scheduled = Carbon::createFromFormat('Y-m-d\TH:i', $validated['scheduled_at']);
            dispatch($job)->delay($scheduled);
        } else {
            dispatch($job);
        }

        return back()->with('success', 'Post berhasil dikirim ke antrian');
    }

    public function bulkPost(Request $request)
    {
        $validated = $request->validate([
            'page_ids' => ['required', 'array'],
            'page_ids.*' => ['string'],
            'type' => ['required', Rule::in(['text', 'photo', 'video'])],
            'message' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'media' => ['nullable', 'file'],
        ]);

        $pages = FacebookPage::whereIn('page_id', $validated['page_ids'])
            ->where('user_id', auth()->id())
            ->get();

        $scheduled = !empty($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : null;

        foreach ($pages as $page) {
            $mediaPath = null;
            if (in_array($validated['type'], ['photo', 'video']) && $request->file('media')) {
                $mediaPath = $request->file('media')->store('facebook-media');
            }

            $log = FacebookPostLog::create([
                'user_id' => auth()->id(),
                'facebook_user_id' => $page->facebook_user_id,
                'facebook_page_id' => $page->id,
                'type' => $validated['type'],
                'message' => $validated['message'] ?? null,
                'media_path' => $mediaPath,
                'scheduled_at' => $scheduled,
                'status' => $scheduled ? 'scheduled' : 'queued',
            ]);

            $job = new PostToFacebookPage(
                $page->id,
                $validated['type'],
                $validated['message'] ?? null,
                $mediaPath,
                $log->id,
            );

            if ($scheduled) {
                dispatch($job)->delay($scheduled);
            } else {
                dispatch($job);
            }
        }

        return back()->with('success', 'Bulk post berhasil dikirim ke antrian');
    }
}
