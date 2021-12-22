<?php

declare(strict_types=1);

namespace banira4649\Sumo;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\WorldManager;
use banira4649\Sumo\commands\{SumoCommand, EntryCommand, EntrylistCommand, SumoarenaCommand};
use banira4649\Sumo\game\Game;

class Main extends PluginBase implements \pocketmine\event\Listener{

    public WorldManager $worldManager;
    public Vector3 $sumoPos0;
    public Vector3 $sumoPos1;
    public Vector3 $sumoPos2;
    public Game $game;

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
			new SumoCommand("sumo", $this),
            new EntryCommand("entry", $this),
            new EntrylistCommand("entrylist", $this),
            new SumoarenaCommand("sumoarena", $this)
		]);
        $this->worldManager = $this->getServer()->getWorldManager();
        $this->worldManager->loadWorld("sumo");
        $this->stage = $this->worldManager->getWorldByName("sumo");
        $this->sumoPos0 = new Vector3(274.5, 31.5, 255.5);
        $this->sumoPos1 = new Vector3(255.5, 31.5, 259.5);
        $this->sumoPos2 = new Vector3(255.5, 31.5, 251.5);
        $this->game = new Game($this);
    }

    public function playerJoinEvent(\pocketmine\event\player\PlayerJoinEvent $event){
        $player = $event->getPlayer();
        foreach($this->game->getPlayers() as $players){
            if($players->getName() === $player->getName()){
                $this->game->removePlayer($players);
                $this->game->addPlayer($player);
            }
        }
    }

    public function playerQuitEvent(\pocketmine\event\player\PlayerQuitEvent $event){
        $player = $event->getPlayer();
		if($this->game->isCombat($player)){
			$this->game->win($this->game->getEnemy($player));
		}
    }

    public function playerExhaustEvent(\pocketmine\event\player\PlayerExhaustEvent $event){
        if($event->getPlayer()->getWorld() === $this->stage){
            $event->cancel();
        }
    }

    public function entityDamageEvent(\pocketmine\event\entity\EntityDamageEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if($entity->getWorld() === $this->stage){
                $this->getScheduler()->scheduleDelayedTask(new ClosureTask(
        			function () use ($entity): void{
                        $entity->setHealth($entity->getMaxHealth());
        			}
        		), 1);
                if($event->getCause() === \pocketmine\event\entity\EntityDamageEvent::CAUSE_VOID){
                    $event->cancel();
                    if($this->game->isCombat($entity)){
                        $this->game->win($this->game->getEnemy($entity));
                    }else{
                        $entity->teleport($this->sumoPos0);
                    }
                }
                if(!$this->game->isCombat($entity)){
                    $event->cancel();
                }elseif($event->getCause() !== \pocketmine\event\entity\EntityDamageEvent::CAUSE_ENTITY_ATTACK){
                    $event->cancel();
                }
            }
        }
    }

    public function entityTeleportEvent(\pocketmine\event\entity\EntityTeleportEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if($this->game->isCombat($entity)){
                if($event->getFrom()->getWorld() !== $event->getFrom()->getWorld()){
                    $this->game->win($this->game->getEnemy($entity));
                }
            }
        }
    }

}
