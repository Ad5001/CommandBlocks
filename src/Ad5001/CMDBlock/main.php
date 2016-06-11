<?php
namespace Ad5001\CMDBlock;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\scheduler\PluginTask;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as C;
use pocketmine\permission\Permission;
use pocketmine\utils\Config;
class Main extends PluginBase implements Listener{
     
     private $cmdeditor;
     
     
     const PREFIX = C::YELLOW . "[" . C::GREEN . "CMDBlock" . C::YELLOW . "] " . C::BLUE;
     
     
     public function onPlayerChat(PlayerChatEvent $event) { // When the player enters the command in the chat
     $this->getLogger()->debug("{$event->getPlayer()->getName()} chated");
         if(isset($this->cmdeditor[$event->getPlayer()->getName()])) {
             $this->cfg->set($this->cmdeditor[$event->getPlayer()->getName()], $event->getMessage());
             $this->cfg->save();
             $pos = explode("/", $this->cmdeditor[$event->getPlayer()->getName()]);
             $event->getPlayer()->sendMessage(self::PREFIX . "Command {$event->getMessage} as been set on {$this->cmdeditor[$event->getPlayer()->getName()]}");
             $event->setCancelled();
         }
     }
     
     
     public function onTouch(PlayerInteractEvent $event) {
         $this->getLogger()->debug("{$event->getPlayer()->getName()} taped a block");
         if($event->getPlayer()->getInventory()->getItemInHand()->getId() === 293 and $event->getPlayer()->hasPermission("cmdblock.create")) {
             $this->getLogger()->debug("{$event->getPlayer()->getName()} taped with an iron hoe and has perm cmdblock.create");
             if($event->getBlock()->getId() === 123 or $event->getBlock()->getId() === 124) {
                 $this->cmdeditor[$event->getPlayer()->getName()] = $event->getBlock()->x . "@" . $event->getBlock()->y . "@" . $event->getBlock()->z . "@" . $event->getBlock()->getLevel()->getName();
                 $event->getPlayer()->sendMessage(self::PREFIX . "Selected block on {$event->getBlock()->x}, {$event->getBlock()->y}, {$event->getBlock()->z} on world {$event->getBlock()->getLevel()->getName()}. \nPlease enter wanted command in the chat");
             }
         }
     }
     
     
     
     public function onEnable(){
          $this->getServer()->getPluginManager()->registerEvents($this,$this);
          $this->getLogger()->info("CommandBlocks enabled!");
          $this->cmdeditors = [];
          @mkdir($this->getDataFolder());
          if(file_exists($this->getDataFolder() . "Blocks.json")) {
              $this->saveResource("Blocks.json");
          }
          $this->cfg = new Config($this->getDataFolder() . "Blocks.json");
          $this->getServer()->getScheduler()->scheduleRepeatingTask(new ExeCmd($this, $this->cfg), 5);
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
class ExeCmd extends PluginTask {
    
    
    public function __construct(Main $plugin, Config $cfg) {
        parent::__construct($plugin);
        $this->m = $plugin;
        $this->cfg = $cfg;
        $this->hasRun = [];
    }
    
    
    public function onRun($tick) {
        $this->m->reloadConfig();
        foreach($this->cfg->getAll() as $block => $cmd) {
            list($x, $y, $z, $levelname) = explode("@", $block);
            if($this->m->getServer()->getLevelByName($levelname)->getBlock(new Vector3($x,$y,$z))->getId() === 124) {
                if(!$this->hasRun[$block]) { // Testing if the command already ran
                    $this->m->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
                    $this->hasRun[$block] = true;
                }
            } else { // If it's deactivate / an another block, we make it activable again.
                $this->hasRun[$block] = false;
            }
        }
        foreach($this->m->getServer()->getOnlinePlayers() as $player) { // To make them unable to break command blocks
            if(!$player->isCreative()) {
                 if($player->getLevel()->getBlock($player->getTargetBlock(7))->getId() === 123 or $player->getLevel()->getBlock($player->getTargetBlock(7))->getId() === 124) {
                     $player->addEffect(Effect::getEffectByName("FATIGUE"), 99, 5, true);
                 }
            }
        }
    }
}
