<?php

declare(strict_types=1);

namespace banira4649\Sumo\function;

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class Ex{

    public static function sendSound($main, Player $player, string $soundName, float $volume, float $pitch, bool $all = false){
        $packet = new PlaySoundPacket();
        $packet->x = $player->getPosition()->getX();
        $packet->y = $player->getPosition()->getY();
        $packet->z = $player->getPosition()->getZ();
		$packet->soundName = $soundName;
        $packet->volume = $volume;
        $packet->pitch = $pitch;
		if($all){
	        $main->getServer()->broadcastPackets($player->getLevel()->getPlayers(), [$packet]);
		}else{
			$main->getServer()->broadcastPackets([$player], [$packet]);
		}
    }

}
