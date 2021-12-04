<?php

declare(strict_types=1);

namespace banira4649\Sumo\commands;

use pocketmine\command\{Command, CommandSender};
use pocketmine\player\Player;

class EntryCommand extends Command{

    public function __construct(string $name, $main){
        parent::__construct($name, "SUMOトーナメントにエントリーします");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commmandLabel, array $args){
        if($sender instanceof Player){
            if($this->main->game->getStatus() === 2){
                $sender->sendMessage("§cトーナメントが進行中です");
                return true;
            }
            if($this->main->game->getStatus() === 0){
                $sender->sendMessage("§c現在エントリーは行われていません");
                return true;
            }
            if($this->main->game->isEntried($sender)){
                $sender->sendMessage("§cあなたはすでにエントリー済みです");
                return true;
            }
            $sender->sendMessage("§l§f[§3SUMO§f] §eエントリー§fが完了しました！");
            $this->main->game->addPlayer($sender);
            return true;
        }
    }

}
