<?php

namespace CryptoUnifier\JetstreamPlus\Http\Controllers;

use CryptoUnifier\Helpers\{IpAddress, Agent};

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController as InertiaUserProfileController;

class UserProfileController extends InertiaUserProfileController
{
    /**
     * Get the current sessions.
     *
     * @return \Illuminate\Support\Collection
     */
    public function sessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return collect(
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function ($session) use ($request) {
            $agent = Agent::make($session->user_agent);

            return (object) [
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform'   => $agent->platformName(),
                    'browser'    => $agent->browserName(),
                ],
                'ip_address'        => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active'       => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                // Jetstream Plus
                'location'          => IpAddress::find($session->ip_address)->location,
            ];
        });
    }
}
