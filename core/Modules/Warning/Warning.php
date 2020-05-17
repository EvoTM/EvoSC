<?php

namespace EvoSC\Modules\Warning;


use EvoSC\Classes\ManiaLinkEvent;
use EvoSC\Classes\Module;
use EvoSC\Interfaces\ModuleInterface;
use EvoSC\Models\AccessRight;
use EvoSC\Models\Player;

class Warning extends Module implements ModuleInterface
{
    /**
     * @inheritDoc
     */
    public static function start(string $mode, bool $isBoot = false)
    {
        AccessRight::createIfMissing('warn_player', 'Warn a player.');

        ManiaLinkEvent::add('warn', [self::class, 'warnPlayer'], 'warn_player');
    }

    public static function warnPlayer(Player $player, string $targetLogin, string $message)
    {
        $target = Player::whereLogin($targetLogin)->first();

        if ($target) {
            warningMessage("You have been warned by $player ", secondary($message))->send($target);
        }
    }
}