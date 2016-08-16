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
             $pos = explode("/", $this->cmdeditor[$event->getPlayer()->getName()]);
             $this->getServer()->getLevelByName($pos[3])->getBlock(new Vector3($pos[0], $pos[1], $pos[2]))->getNamedTag()->Command = (string) $event->getMessage();
             $event->getPlayer()->sendMessage(self::PREFIX . "Command {$event->getMessage()} as been set on {$pos[0]}, {$pos[1]}, {$pos[2]}, {$pos[3]}");
             $event->setCancelled();
         }
     }
     
     
     public function onDisable() {
         if(!file_exists($this->getDataFolder() . "logs.txt")) {
             file_put_contents($this->getDataFolder() . "logs.txt", ""); 
         }
         file_put_contents($this->getDataFolder() . "logs.txt",file_get_contents($this->getDataFolder() . "logs.txt") . PHP_EOL . implode(PHP_EOL, $this->logs));
     }
     
     
     public function onInteract(PlayerInteractEvent $e) {
          $player = $e->getPlayer();
          $b = $e->getBlock();
          if($b->getId() == 123 or $b->getId() == 124 and $player->isOp() and $player->isCreative()) {
         $this->cmdeditor[$player->getName()] = $b->x . "@" . $b->y . "@" . $b->z . "@" . $b->level->getName();
         $player->sendMessage(self::PREFIX . "Selected block on {$b->x}, {$b->y}, {$b->z} on world {$b->level->getName()}");
         $player->sendMessage(self::PREFIX . "Current command : {$b->getNamedTag()->Command}");
         $player->sendMessage(self::PREFIX . "Current output : {$this->lastLog[$this->cmdeditor[$player->getName()]]}");
         $player->sendMessage(self::PREFIX . "Please enter wanted command in the chat or type \"cancel\" to cancel");
          }
     }
     
     
     public function onRedstoneUpdate(\pocketmine\event\block\RedstoneBlockUpdateEvent $event) {
          if($event->getBlock()->getId() == 124) {
               if(isset($event->getBlock()->getNamedTag()->Command)) {
                    if($event->getBlock()->getNamedTag()->Command instanceof \pocketmine\nbt\tag\StringTag) {
                         $this->getServer()->dispatchCommand(new CommandBlockSender(), $event->getBlock()->getNamedTag()->Command->getValue())
                    }
               } else {
                    $event->getBlock()->getNamedTag()->Command = new \pocketmine\nbt\tag\StringTag("Command" => "");
               }
          }
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
     
     
     public static function logMsg(CommandBlockSender $block, string $message) { // Loging msg
         $this->lastLog[$block->x . "@" . $blockpos->y . "@" . $blockpos->z . "@" . $block->level->getName()] = $message;
         array_push($this->logs, $block->x . "@" . $block->y . "@" . $block->z . "@" . $block->level->getName() . "> " . $message);
     }
}
