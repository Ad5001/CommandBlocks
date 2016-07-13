<?php
namespace Ad5001\CMDBlock;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\entity\Effect;
use pocketmine\scheduler\PluginTask;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as C;
use pocketmine\permission\Permission;
use pocketmine\utils\Config;

use Ad5001\CMDBlock\CMDBlockSender;
use Ad5001\CMDBlock\CommandBlock;
use Ad5001\CMDBlock\CommandBlockItem;

class Main extends PluginBase implements Listener{
     
     private $cmdeditor;
     
     
     const PREFIX = C::YELLOW . "[" . C::GREEN . "CMDBlock" . C::YELLOW . "] " . C::BLUE;
     
     public function onPlayerChat(PlayerChatEvent $event) { // When the player enters the command in the chat
         if(isset($this->cmdeditor[$event->getPlayer()->getName()])) {
             $this->cfg->set($this->cmdeditor[$event->getPlayer()->getName()], $event->getMessage());
             $this->cfg->save();
             $pos = explode("/", $this->cmdeditor[$event->getPlayer()->getName()]);
             $event->getPlayer()->sendMessage(self::PREFIX . "Command {$event->getMessage()} as been set on {$this->cmdeditor[$event->getPlayer()->getName()]}");
             $event->setCancelled();
         }
     }
     
     
     public function onDisable() {
         if(!file_exists($this->getDataFolder() . "logs.txt")) {
             file_put_contents($this->getDataFolder() . "logs.txt", ""); 
         }
         file_put_contents($this->getDataFolder() . "logs.txt",file_get_contents($this->getDataFolder() . "logs.txt") . PHP_EOL . implode(PHP_EOL, $this->logs));
     }
     
     
     /*public function onTouch(PlayerInteractEvent $event) {
         if($event->getPlayer()->getInventory()->getItemInHand()->getId() === 293 and $event->getPlayer()->hasPermission("cmdblock.create")) {
             $this->getLogger()->debug("{$event->getPlayer()->getName()} taped with an iron hoe and has perm cmdblock.create");
             if($event->getBlock()->getId() === 123 or $event->getBlock()->getId() === 124) {
                 $this->cmdeditor[$event->getPlayer()->getName()] = $event->getBlock()->x . "@" . $event->getBlock()->y . "@" . $event->getBlock()->z . "@" . $event->getBlock()->getLevel()->getName();
                 $event->getPlayer()->sendMessage(self::PREFIX . "Selected block on {$event->getBlock()->x}, {$event->getBlock()->y}, {$event->getBlock()->z} on world {$event->getBlock()->getLevel()->getName()}");
                     $event->getPlayer()->sendMessage(self::PREFIX . "Current command : {$this->cfg->get($this->cmdeditor[$event->getPlayer()->getName()])}");
                 $event->getPlayer()->sendMessage(self::PREFIX . "Please enter wanted command in the chat");
             }
         }
     }*/
     
     
     
     public static function interact(Player $player, CommandBlock $b) {
         $this->cmdeditor[$player->getName()] = $b->x . "@" . $b->y . "@" . $b->z . "@" . $b->level->getName();
         $player->sendMessage(self::PREFIX . "Selected block on {$b->x}, {$b->y}, {$b->z} on world {$b->level->getName()}");
         $player->sendMessage(self::PREFIX . "Current command : {$b->namedtag->Command}");
         $player->sendMessage(self::PREFIX . "Current output : {$b->namedtag->Command}");
         $player->sendMessage(self::PREFIX . "Please enter wanted command in the chat");
     }
     
     
     
     public function onEnable(){
          $this->getServer()->getPluginManager()->registerEvents($this,$this);
          $this->getLogger()->info("CommandBlocks enabled!");
          @mkdir($this->getDataFolder());
          $this->logs = explode(PHP_EOL, file_get_contents($this->getDataFolder() . "logs.txt"));
          Block::$list[137] = CommandBlock::class;
          Item::$list[137] = CommandBlockItem::class;
          if(file_exists($this->getDataFolder() . "Blocks.json")) {
              foreach(json_decode(file_get_contents($this->getDataFolder() . "Blocks.json"), true) as $block => $cmd) {
                  list($x, $y, $z, $levelname) = explode("@", $block);
                  $level = $this->getServer()->getLevelByName($levelname);
                  $b = new CommandBlock(0, $cmd);
                  $level->setBlock(new Vector3($x, $y, $z), $b);
              }
              delete($this->getDataFolder() . "Blocks.json");
          }
          // $this->cfg = new Config($this->getDataFolder() . "Blocks.json");
          // $this->getServer()->getScheduler()->scheduleRepeatingTask(new ExeCmd($this, $this->cfg), 5);
     }
     
     public function onCommand(CommandSender $sender, Command $command, $label, array $args){
          switch($command->getName()){
               case "cmdblock":
               $sender->getInventory()->addItem(Item::get(137, 0));
               $sender->sendMessage(self::PREFIX . "Here you got a command block ! Place it and then touch it , then enter the command in (the chat without the / !) (it won't be executed) .");
               return true;
               break;
          }
          return true;
     }
     
     
     public static function logMsg(CommandBlock $block, string $message) { // Loging msg
         $this->lastLog[$block->x . "@" . $blockpos->y . "@" . $blockpos->z . "@" . $block->level->getName()] = $message;
         array_push($this->logs, $block->x . "@" . $block->y . "@" . $block->z . "@" . $block->level->getName() . "> " . $message);
     }
}
/*class ExeCmd extends PluginTask {
    
    
    public function __construct(Main $plugin, Config $cfg) {
        parent::__construct($plugin);
        $this->m = $plugin;
        $this->cfg = $cfg;
        $this->hasRun = [];
    }
    
    
    public function onRun($tick) {
        $this->cfg->reload(); // to update constantly commands and blocks
        foreach($this->cfg->getAll() as $block => $cmd) {
            list($x, $y, $z, $levelname) = explode("@", $block);
            if($this->m->getServer()->getLevelByName($levelname)->getBlock(new Vector3($x,$y,$z))->getId() === 124) {
                if(!isset($this->hasRun[$block])) { // Testing if the command already ran
                    $this->m->getServer()->dispatchCommand(new CMDBlockSender(new Vector3($x,$y,$z), $this->m->getServer()->getLevelByName($levelname)), $cmd);
                    $this->hasRun[$block] = true;
                    array_push($this->m->logs, $x . "/" . $y . "/" . $z . "/" . $levelname . "> " . $cmd);
                }
            } else { // If it's deactivate / an another block, we make it activable again.
                unset($this->hasRun[$block]);
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
}*/
