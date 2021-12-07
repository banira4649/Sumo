<?php

declare(strict_types=1);

namespace banira4649\Sumo\commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\player\Player;

class EntrylistCommand extends Command{

    public function __construct(string $name, $main){
        parent::__construct($name, "SUMOトーナメントにエントリーしたプレイヤーの一覧を確認します");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if($this->main->game->getStatus() === 0){
            $sender->sendMessage("§c現在エントリーは行われていません");
            return true;
        }
        $list = [];
        foreach($this->main->game->getPlayers() as $players){
            $list[] = $players->getName();
        }
        $sender->sendMessage("§aエントリー中のプレイヤー: "."\n"." "."§b".implode(", ", $list));
        return true;
    }

}
