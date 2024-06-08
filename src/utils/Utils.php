<?php

declare(strict_types=1);

namespace banira4649\Sumo\utils;

use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class Utils{

    public const PREFIX_SUMO = "§l§f[§bSUMO§f] ";

    public static function sendSound(Player $player, string $soundName, float $volume, float $pitch, bool $all = false): void{
        $packet = new PlaySoundPacket();
        $packet->x = $player->getPosition()->getX();
        $packet->y = $player->getPosition()->getY();
        $packet->z = $player->getPosition()->getZ();
		$packet->soundName = $soundName;
        $packet->volume = $volume;
        $packet->pitch = $pitch;
		if($all){
	        NetworkBroadcastUtils::broadcastPackets($player->getWorld()->getPlayers(), [$packet]);
		}else{
            NetworkBroadcastUtils::broadcastPackets([$player], [$packet]);
		}
    }

}
