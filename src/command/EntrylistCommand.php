<?php

declare(strict_types=1);

namespace banira4649\Sumo\command;

use banira4649\Sumo\Main;
use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;

class EntrylistCommand extends Command{

    private Main $main;

    public function __construct(string $name, Main $main){
        $this->setPermission(DefaultPermissions::ROOT_USER);
        parent::__construct($name, "SUMOトーナメントにエントリーしたプレイヤーの一覧を確認します");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!$this->testPermission($sender)) return false;
        if(empty($this->main->game->getPlayers())){
            $sender->sendMessage("§c参加者が存在しません");
            return true;
        }
        $sender->sendMessage("§aエントリー中のプレイヤー: "."\n"." "."§b".implode(", ", $this->main->game->getNameList()));
        return true;
    }

}
