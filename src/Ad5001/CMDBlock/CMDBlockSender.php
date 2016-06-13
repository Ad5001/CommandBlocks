<?php
namespace Ad5001\CMDBlock;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as C;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\utils\Config;
use pocketmine\server;
use pocketmine\level\Level;
use pocketmine\event\TextContainer;
use Ad5001\CMDBlock\Main;
use pocketmine\plugin\Plugin;

class CMDBlockSender implements CommandSender {
    
    
    public function getServer() {
        return Server::getInstance();
    }
    
    private $pos;
    private $level;
    
    public function __construct(Vector3 $pos, Level $level){
		$this->perm = new PermissibleBase($this);
        $this->pos = $pos;
        $this->level = $level;
	}
    
    
    
    public function getLevel() {
        return $this->level;
    }
    
    
    
	public function getName() : string{
		return "@";
	}
    
    
    public function getPos() : Vector3 {
        return $this->pos;
    }
    
    
    
    public function isPermissionSet($name){
		return $this->perm->isPermissionSet($name);
	}

	
    
    
	public function hasPermission($name){
		return true;
	}

	
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		return $this->perm->addAttachment($plugin, $name, $value);
	}

    
    
	public function removeAttachment(PermissionAttachment $attachment){
		$this->perm->removeAttachment($attachment);
	}
    
    

	public function recalculatePermissions(){
		$this->perm->recalculatePermissions();
	}

	
	public function getEffectivePermissions(){
		return $this->perm->getEffectivePermissions();
	}

	
	public function isPlayer(){
		return false;
	}
    
    
    
    
	public function sendMessage($message){
		Main::logMsg($this, $message);
        return true;
	}
    
    
    
	public function isOp(){
		return true;
	}
    
    
    
	public function setOp($value){}

    
    
}