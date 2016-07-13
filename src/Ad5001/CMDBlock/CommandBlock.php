<?php
namespace Ad5001\CMDBlock;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\block\Solid;
use pocketmine\Server;
use Ad5001\CMDBlock\Main;
use Ad5001\CMDBlock\CMDBlockSender;

class CommandBlock extends Solid implements Redstone{

	protected $id = 137;

	public function __construct($meta = 0, string $command = ""){
		$this->meta = $meta;
		$this->namedtag->Command = $command;
	}

	public function getName(){
		return "Command Block";
	}

	public function getHardness(){
		return -1;
	}

	public function getResistance(){
		return 18000000;
	}

	public function isBreakable(Item $item){
		return false;
	}

	public function canBeActivated(){
		return true;
	}

	public function getToolType(){
		return Tool::TYPE_NONE;
	}


	protected function recalculateBoundingBox(){

		if(($this->getDamage() & 0x04) > 0){
			return null;
		}

		$i = ($this->getDamage() & 0x03);
		if($i === 2 and $i === 0){
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z + 0.375,
				$this->x + 1,
				$this->y + 1.5,
				$this->z + 0.625
			);
		}else{
			return new AxisAlignedBB(
				$this->x + 0.375,
				$this->y,
				$this->z,
				$this->x + 0.625,
				$this->y + 1.5,
				$this->z + 1
			);
		}
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $block->getSide(0);
		if($down->getId() === self::AIR) return false;
		$faces = [3, 0, 1, 2];
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0] & 0x03;
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}

	public function getDrops(Item $item){
		return [
			[0, 0, 0],
		];
	}
    
    public function getCommand() {
        return $this->namedtag->Command;
    }
    
    public function setCommand(string $command) {
        $this->namedtag->Command = $command;
        return $this->namedtag->Command == $command;
    }

	public function onActivate(Item $item, Player $player){
        if($player->isCreative() and $player->hasPermission("cmdblock.create")) {
            Main::interact($player, $this);
        }
		return true;
	}
	
	public function onRedstoneUpdate($type,$power){
        if($this->isPowered() and $this->namedtag->Command !== "") {
            Server::getInstance()->dispatchCommand(new CMDBlockSender(new Vector3($this->x,$this->y,$this->z), $this->level));
        }
	}
}
