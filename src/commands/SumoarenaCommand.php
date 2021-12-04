<?php

declare(strict_types=1);

namespace banira4649\Sumo\commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\player\Player;

class SumoarenaCommand extends Command{

    public function __construct(string $name, $main){
        parent::__construct($name, "SUMOアリーナに入場します");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if($sender instanceof Player){
            $sender->teleport($this->main->stage->getSafeSpawn());
            $sender->teleport($this->main->sumoPos0);
            $sender->sendMessage("§aSUMOアリーナに入場しました");
        }
        return true;
    }

}
