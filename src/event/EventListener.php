<?php

declare(strict_types=1);

namespace banira4649\Sumo\event;

use banira4649\Sumo\Main;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class EventListener implements Listener{

    private Main $main;

    public function __construct(Main $main){
        $this->main = $main;
    }

    public function playerJoinEvent(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        foreach($this->main->game->getPlayers() as $players){
            if($players->getName() === $player->getName()){
                $this->main->game->removePlayer($players);
                $this->main->game->addPlayer($player);
            }
        }
    }

    public function playerQuitEvent(PlayerQuitEvent $event): void{
        $player = $event->getPlayer();
        if($this->main->game->isCombat($player)){
            $this->main->game->win($this->main->game->getEnemy($player));
        }
    }

    public function playerExhaustEvent(PlayerExhaustEvent $event): void{
        if($event->getPlayer()->getWorld() === $this->main->game->getStage()){
            $event->cancel();
        }
    }

    public function entityDamageEvent(EntityDamageEvent $event): void{
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if($entity->getWorld() === $this->main->game->getStage()){
                $this->main->getScheduler()->scheduleDelayedTask(
                    new ClosureTask(function() use ($entity): void{
                        $entity->setHealth($entity->getMaxHealth());
                    }),
                    1);
                if($event->getCause() === EntityDamageEvent::CAUSE_VOID){
                    $event->cancel();
                    if($this->main->game->isCombat($entity)){
                        $this->main->game->win($this->main->game->getEnemy($entity));
                    }else{
                        $entity->teleport($this->main->sumoPos0);
                    }
                }
                if(!$this->main->game->isCombat($entity)){
                    $event->cancel();
                }elseif($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK){
                    $event->cancel();
                }
            }
        }
    }

    public function entityTeleportEvent(EntityTeleportEvent $event): void{
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if($this->main->game->isCombat($entity)){
                if($event->getFrom()->getWorld() !== $event->getFrom()->getWorld()){
                    $this->main->game->win($this->main->game->getEnemy($entity));
                }
            }
        }
    }
}