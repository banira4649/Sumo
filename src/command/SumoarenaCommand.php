<?php

declare(strict_types=1);

namespace banira4649\Sumo\command;

use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use banira4649\Sumo\Main;

class SumoarenaCommand extends Command{

    private Main $main;

    public function __construct(string $name, Main $main){
        $this->setPermission(DefaultPermissions::ROOT_USER);
        parent::__construct($name, "SUMOアリーナに入場します");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!$this->testPermission($sender)) return false;
        if(!$sender instanceof Player) return false;
        if($this->main->game->isCombat($sender)){
			$this->main->game->win($this->main->game->getEnemy($sender));
		}
        $this->main->game->joinArena($sender);
        return true;
    }

}
