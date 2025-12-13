<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\FacebookPage;

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

        auth()->user()->update([
            'facebook_id' => $fbUser->id,
            'facebook_token' => encrypt($fbUser->token),
        ]);

        return redirect()->route('dashboard');
    }
    public function getPages()
    {
        $user = auth()->user();

        if (!$user->facebook_token) {
            return back()->withErrors('Facebook belum terhubung');
        }

        $response = Http::withToken(
            decrypt($user->facebook_token)
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
                    'user_id' => $user->id,
                    'page_id' => $page['id'],
                ],
                [
                    'page_name'        => $page['name'],
                    'page_access_token' => encrypt($page['access_token']),
                ]
            );
        }

        return redirect()->route('dashboard')->with('success', 'Fanspage berhasil diambil');
    }
    public function store(Request $request)
    {
        $page = FacebookPage::where('page_id', $request->page_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        Http::withToken(decrypt($page->page_access_token))
            ->post("https://graph.facebook.com/v19.0/{$page->page_id}/feed", [
                'message' => $request->message,
            ]);

        return back()->with('success', 'Post berhasil dikirim');
    }
}
