<?php
namespace himbeer\cmdblx;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\permission\Permission;
use pocketmine\utils\Config;
class Main extends PluginBase implements Listener{
     
     public function onEnable(){
          $this->getServer()->getPluginManager()->registerEvents($this,$this);
          $this->getLogger()->info("cmdblx enabled!");
          @mkdir($this->getDataFolder());
          $this->config = new Config ($this->getDataFolder() . "config.yml" , Config::YAML, array(
               #config file for cmdblx
          ));
          $this->saveResource("config.yml");
     }
     
     public function onCommand(CommandSender $sender, Command $command, $label, array $args){
          switch($command->getName()){
               case "cmdblx":
                   #do stuff
          }
          return true;
     }
}
