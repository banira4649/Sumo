<?php

declare(strict_types=1);

namespace banira4649\Sumo;

use banira4649\Sumo\event\EventListener;
use pocketmine\plugin\PluginBase;
use pocketmine\player\{Player, GameMode};
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\World;
use pocketmine\world\WorldManager;
use banira4649\Sumo\command\{SumoCommand, EntryCommand, EntrylistCommand, SumoarenaCommand};
use banira4649\Sumo\game\Game;
use Symfony\Component\Filesystem\Path;

class Main extends PluginBase{
    public const WORLD_NAME = "sumo";

    public WorldManager $worldManager;
    public Vector3 $sumoPos0;
    public Vector3 $sumoPos1;
    public Vector3 $sumoPos2;
    public Game $game;
    public ?World $stage;

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
            new SumoCommand("sumo", $this),
            new EntryCommand("entry", $this),
            new EntrylistCommand("entrylist", $this),
            new SumoarenaCommand("sumoarena", $this),
        ]);
        $this->worldManager = $this->getServer()->getWorldManager();
        $this->worldManager->loadWorld(self::WORLD_NAME);
        $this->stage = $this->worldManager->getWorldByName(self::WORLD_NAME);
        $stageData = new Config(
            Path::join(Server::getInstance()->getDataPath(), "worlds", self::WORLD_NAME, 'config.json'),
            Config::JSON,
            ["spawn" => [0, 0, 0], "pos1" => [0, 0, 0], "pos2" => [0, 0, 0]]
        );
        $this->sumoPos0 = new Vector3(
            $stageData->get("spawn")[0],
            $stageData->get("spawn")[1],
            $stageData->get("spawn")[2]
        );
        $this->sumoPos1 = new Vector3(
            $stageData->get("pos1")[0],
            $stageData->get("pos1")[1],
            $stageData->get("pos1")[2]
        );
        $this->sumoPos2 = new Vector3(
            $stageData->get("pos2")[0],
            $stageData->get("pos2")[1],
            $stageData->get("pos2")[2]
        );
        $this->game = new Game($this);
    }

    public static function resetPlayer(Player $player): void{
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->setHealth($player->getMaxHealth());
        $player->getXpManager()->setXpLevel(0);
        $player->getXpManager()->setXpProgress(0);
        $player->setOnFire(0);
        $player->setGameMode(GameMode::ADVENTURE());
    }
}
