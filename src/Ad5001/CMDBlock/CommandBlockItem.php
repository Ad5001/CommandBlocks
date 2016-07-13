<?php
namespace Ad5001\CMDBlock;
use pocketmine\block\Block;
use pocketmine\Item\item;

class Bed extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(137);
		parent::__construct(137, 0, $count, "Command Block");
	}

	public function getMaxStackSize(){
		return 64;
	}
}