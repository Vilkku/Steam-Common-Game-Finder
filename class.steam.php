<?php

namespace SteamCommonGameFinder;

class Steam
{
    protected $apikey;

    public function __construct($apikey)
    {
        $this->apikey = $apikey;


    }

    public function getAppInfo($appid)
    {
        if (empty($appid)) {
            return false;
        }

        $url = sprintf(
            'http://store.steampowered.com/api/appdetails/?appids=%s',
            $appid
        );

        $results = json_decode(file_get_contents($url));

        if (!$results->$appid->success) {
            return false;
        }

        $data = $results->$appid->data;

        return $data;
    }

    public function getGameNames()
    {
        $url = 'http://api.steampowered.com/ISteamApps/GetAppList/v2';

        $results = json_decode(file_get_contents($url));
        $apps = $results->applist->apps;

        $names = array();
        foreach ($apps as $app) {
            $names[$app->appid] = $app->name;
        }

        return $names;
    }

    public function getOwnedGames($steamid)
    {
        if (empty($steamid)) {
            return false;
        }

        $url = sprintf(
            'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=%s&steamid=%s&include_appinfo=1&include_played_free_games=1',
            $this->apikey,
            $steamid
        );

        $results = json_decode(file_get_contents($url));

        if (!property_exists($results->response, 'games')) {
            return false;
        }

        $games = $results->response->games;

        return $games;
    }

    public function resolveVanityURL($vanityurl)
    {
        if (empty($vanityurl)) {
            return false;
        }

        $url = sprintf(
            'http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key=%s&vanityurl=%s',
            $this->apikey,
            $vanityurl
        );

        $results = json_decode(file_get_contents($url));

        if ($results->response->success != 1) {
            return false;
        }

        $steamid = $results->response->steamid;

        return $steamid;
    }
}
