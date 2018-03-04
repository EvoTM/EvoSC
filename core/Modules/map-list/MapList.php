<?php

use esc\classes\File;
use esc\classes\ManiaLinkEvent;
use esc\classes\Template;
use esc\controllers\ChatController;
use esc\controllers\MapController;
use esc\models\Map;
use esc\models\Player;

class MapList
{
    public function __construct()
    {
        Template::add('maplist.show', File::get(__DIR__ . '/Templates/map-list.latte.xml'));

        ManiaLinkEvent::add('maplist.show', 'MapList::showMapList');
        ManiaLinkEvent::add('maplist.close', 'MapList::closeMapList');
        ManiaLinkEvent::add('maplist.queue', 'MapList::queueMap');
        ManiaLinkEvent::add('maplist.delete', 'MapList::deleteMap');

        ChatController::addCommand('list', 'MapList::showMapList', 'Display list of maps');
    }

    public static function showMapList(Player $player)
    {
        $maps = Map::all();
        $queuedMaps = MapController::getQueue()->sortBy('timeRequested')->take(15);

        Template::show($player, 'maplist.show', ['maps' => $maps, 'player' => $player, 'queuedMaps' => $queuedMaps]);
    }

    public static function closeMapList(Player $player)
    {
        Template::hide($player, 'maplist.show');
    }

    public static function queueMap(Player $player, $mapId)
    {
        $map = Map::where('id', intval($mapId))->first();

        if ($map) {
            MapController::queueMap($player, $map);
            Template::hide($player, 'maplist.show');
        } else {
            ChatController::message($player, 'Invalid map selected');
        }

        self::closeMapList($player);
    }

    public static function deleteMap(Player $player, $mapId)
    {
        if (!$player->isAdmin()) {
            ChatController::messageNew($player, 'You do not have access to that command');
            return;
        }

        $map = Map::where('id', intval($mapId))->first();

        if ($map) {
            MapController::deleteMap($map);
            self::closeMapList($player);
        }
    }
}