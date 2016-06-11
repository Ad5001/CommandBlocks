<?php
namespace Ad5001\CMDBlock;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\permission\Permission;
use pocketmine\utils\Config;
class Main extends PluginBase implements Listener{
     
     private $cmdeditor;
     
     
     const PREFIX = C::YELLOW . "[" . C::GREEN . "CMDBlock" . C::YELLOW . "]";
     
     
     public function onPlayerChat(PlayerChatEvent $event) { // When the player enters the command in the chat
         if(isset($this->cmdeditor[$event->getPlayer()->getName()])) {
             $this->cmd->set($this->cmdeditor[$event->getPlayer()->getName()], $event->getMessage);
         }
     }
     
     
     public function onTouch(PlayerInteractEvent $event) {
         if($event->getPlayer()->getInventory()->getItemInHand()->getId() === 273 and $sender->hasPermission("cmdblock.create" and $sender->isCreative())) {
             $this->cmdeditor[$event->getPlayer()->getName()] === $event->getBlock()->x . "/" . $event->getBlock()->y . "/" . $event->getBlock()->z;
             $event->getPlayer()->sendMessage()
         }
     }
     
     
     
     public function onEnable(){
          $this->getServer()->getPluginManager()->registerEvents($this,$this);
          $this->getLogger()->info("CommandBlocks enabled!");
          $this->cmdeditors = [];
          @mkdir($this->getDataFolder());
          if(file_exists($this->getDataFolder() . "Blocks.json")) {
              $this->saveRessource("Blocks.json");
          }
          $this->cmd = new Config($this->getDataFolder() . "Blocks.json");
     }
     
     public function onCommand(CommandSender $sender, Command $command, $label, array $args){
          switch($command->getName()){
               case "cmdblock":
               $sender->sendMessage(self::PREFIX . "Take a diamond hoe, touch a redstone lamp with it, then enter the command in (the chat without the / !) (it won't be executed) .");
               return true;
               break;
          }
          return true;
     }
}
