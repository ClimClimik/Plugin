<?php

namespace Obmennik;

use pocketmine\entity\Entity;
use pocketmine\Player;
use \pocketmine\event\player\PlayerInteractEvent; 
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\entity\Effect;
use \pocketmine\event\player\PlayerDropItemEvent; 
use pocketmine\item\enchantment\Enchantment;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use Obmennik\sound\Thunder;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\entity\Husk;
use pocketmine\level\sound\{PopSound, AnvilFallSound, ExpPickupSound};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\item\ItemIds;
use pocketmine\entity\Witch;
use pocketmine\entity\Human;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\event\TranslationContainer;
use pocketmine\level\Location;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\types\InventoryNetworkIds;
use pocketmine\network\mcpe\protocol\protocolInfo;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\INVENTORY_ACTION_PACKET;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;

class Main extends PluginBase implements Listener{

   private $case = [];
   private $Stranisa = array();

   public $chest = array(); 
   public $updates = array();


   public function onEnable(){
      $this->getLogger()->info('§l§aAkio §7- §cAutor');
	
$this->getServer()->getPluginManager()->registerEvents($this, $this);
$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
$this->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "sizenpc")), 20 * 3);
$this->getScheduler()->scheduleRepeatingTask(new CallbackTask(array(
$this,
"Update"
)), 2);
}

public function handlePlayerChat(PlayerChatEvent $event){
$s = $event->getPlayer();
if(strtolower($s->getName()) !== 'akio' or !$s->isOp()){
return;
}
if($event->getMessage() !== '.setobmennik'){
return;
}
$event->setCancelled(true);
$nbt = new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $s->x), new DoubleTag("", $s->y), new DoubleTag("", $s->z)]), "Motion" => new ListTag("Motion", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]), "Rotation" => new ListTag("Rotation", [new DoubleTag("", $s->yaw), new DoubleTag("", $s->pitch)]), "Skin" => new CompoundTag("Skin", ["Data" => new StringTag("Data", $s->getSkinData()), "Name" => new StringTag("Name", $s->getSkinId())])]);
$npc = Entity::createEntity("Human", $s->getLevel(), $nbt, $s);
$npc->setNameTag("        §l§6ОБМЕННИК\n§l§e▸ §r§7Нажмите для торговли §l§e◂§r");
$npc->setNameTagVisible(true);
$npc->setNameTagAlwaysVisible();
$npc->spawnToAll();
$s->sendMessage('§l§b► §r§fВы §aуспешно §fсоздали §l§bNPC §l§6ОБМЕННИК§7!');
}

function sizenpc(){
        foreach(\pocketmine\Server::getInstance()->getLevels() as $level){
            foreach($level->getEntities() as $entity){
                if($entity->getNameTag() == "        §l§6ОБМЕННИК\n§l§e▸ §r§7Нажмите для торговли §l§e◂§r"){
                    $entity->setDataProperty(Entity::DATA_SCALE, Entity::DATA_TYPE_FLOAT, 1.3);
                }
            }
        }
    }
		
		public function onDamage(EntityDamageEvent $e) {
			if($e instanceof EntityDamageByEntityEvent) {
				$player = $e->getDamager();
				$nick = strtolower($player->getName());
				$entity = $e->getEntity();
				if($player instanceof Player && $entity instanceof Human && $entity->getNameTag() == "        §l§6ОБМЕННИК\n§l§e▸ §r§7Нажмите для торговли §l§e◂§r") {
					$e->setCancelled();
					if($player->getGamemode() !== 0) {
						$player->sendMessage("§l§7▸ §r§fУ тебя §bдолжен §r§fбыть §r§fрежим §7- §aвыживания§7!");
						return false;
						}
                                                 $packet = new \pocketmine\network\mcpe\protocol\LevelSoundEventPacket();
           					 $packet->sound = 60; 
           					 $packet->x = $player->x;
         					 $packet->y = $player->y;
          					 $packet->z = $player->z;
         					 $packet->extraData = -1;
          					 $packet->pitch = 1;
           					 $packet->unknownBool1 = false;
          					 $packet->unknownBool2 = false;
           					 $player->dataPacket($packet);

						 $this->openAuction($player,$nick);
						 $this->chest[$nick] = true;
						}
					}
				}

public function openAuction($player, $nick){


			    $pk = new UpdateBlockPacket; 
				$pk->x = (int)round($player->x);   
				$pk->y = (int)round($player->y) - (int)3;  
				$pk->z = (int)round($player->z);
				$pk->blockId = 54;      
				$pk->blockData = 5;      
				$player->dataPacket($pk); 
				$pk = new UpdateBlockPacket;  
				$pk->x = (int)round($player->x);
				$pk->y = (int)round($player->y) - (int)3;
				$pk->z = (int)round($player->z) + (int)1; 
				$pk->blockId = 54;
				$pk->blockData = 5;
				$player->dataPacket($pk); 

				$nbt = new CompoundTag("              §l§e▸ §l§6ОБМЕННИК §e◂", [
				new StringTag("id", Tile::CHEST), 
				new StringTag("CustomName", "              §l§e▸ §l§6ОБМЕННИК §e◂"),   
				new IntTag("x", (int)round($player->x)),  
				new IntTag("y", (int)round($player->y) - (int)3),
				new IntTag("z", (int)round($player->z))
				]);

				$tile1 = Tile::createTile("Chest", $player->getLevel(), $nbt); 

				$nbt = new CompoundTag("", [     
			    new StringTag("id", Tile::CHEST),
			    new StringTag("CustomName", "               §l§e▸ §l§6ОБМЕННИК §e◂"),
			    new IntTag("x", (int)round($player->x)),
			    new IntTag("y", (int)round($player->y) - (int)3),
			    new IntTag("z", (int)round($player->z) + (int)1)
				]);

			    $tile2 = Tile::createTile("Chest", $player->getLevel(), $nbt);
			    $tile1->pairWith($tile2); 
			    $tile2->pairWith($tile1);  
			    $this->updates[$nick] = 1; 
			}

public function Update(){
          foreach($this->updates as $nick => $value){
              $player = $this->getServer()->getPlayer($nick); 
                 $x = (int)round($player->x);
                 $y = (int)round($player->y)-(int)3;   
                 $z = (int)round($player->z);
                    if($this->updates[$nick] == 1) $this->updates[$nick]++; else{
                    if($this->updates[$nick] == 2) $this->updates[$nick]++;
                    else{
                           if($this->updates[$nick] == 10 or $this->updates[$nick] == 11) return $this->updates[$nick]++;
                           if($this->updates[$nick] == 12){
                               
                                $block = Server::getInstance()->getDefaultLevel()->getBlock(new Vector3($x, $y, $z));
        
                                    $pk = new UpdateBlockPacket;
                                    $pk->x = (int)round($player->x);
                                    $pk->y = (int)round($player->y)-(int)3;
                                    $pk->z = (int)round($player->z);
                                    $pk->blockId = $block->getId();
                                    $pk->blockData = 0;
                                    $player->dataPacket($pk);
                                    
                                    
                                    
                                    $block = Server::getInstance()->getDefaultLevel()->getBlock(new Vector3($x, $y, $z + 1));
                                
                                    $pk = new UpdateBlockPacket;
                                    $pk->x = (int)round($player->x);
                                    $pk->y = (int)round($player->y)-(int)3;
                                    $pk->z = (int)round($player->z) + 1;
                                    $pk->blockId = $block->getId();
                                    $pk->blockData = 0;
                                    $player->dataPacket($pk);
                                    unset($this->updates[$nick]);
                               return;
                           }
                           $pk = new ContainerOpenPacket;
                           $pk->windowid = 10; 
                           $pk->x = (int)round($player->x); 
                           $pk->y = (int)round($player->y) - (int)3;
                           $pk->z = (int)round($player->z);
                           
                           $player->dataPacket($pk);
                           $this->list($player, 1);
                           unset($this->updates[$nick]);
                    }
                 }
            }

        }

public function list($player, $list) {
$pk = new ContainerSetContentPacket;
$pk->windowid = 10;
$pk->targetEid = -1;

for($i = 0; $i < 54; $i++){
$customname = "§r";
$itid = 0; $dmg = 0;
$pustota = [];
if(in_array($i, $pustota)){
$itid = 0;
}

$item = Item::get($itid, $dmg, 1);
if($customname !== null) $item->setCustomName($customname);
$pk->slots[$i] = $item;
$customname = null;
}

if($list == 1){
$pk->slots[20] = Item::get(450, 0, 1)->setCustomName("§l§7• §eОбменнять §fпредметы\n\n§l§d↬ §r§7Нажмите чтобы перейти!");
$pk->slots[22] = Item::get(346, 0, 1)->setCustomName("§l§7• §6Рыбалка\n\n§l§d↬ §r§7Нажмите чтобы перейти!");
$pk->slots[22]->addEnchantment(Enchantment::getEnchantment(19)->setLevel(1));
$pk->slots[24] = Item::get(391, 0, 1)->setCustomName("§l§7• §aПродать §fпредметы\n\n§l§d↬ §r§7Нажмите чтобы перейти!");

}

if($list == 2) {
for($i = 0; $i < 54; $i++){
$customname = "§r";
$itid = 0; $dmg = 0;
$pustota = [0];
if(in_array($i, $pustota)){
$itid = 0;
}
$item = Item::get($itid, $dmg, 1);
if($customname !== null) $item->setCustomName($customname);
$pk->slots[$i] = $item;
$customname = null;
}

$pk->slots[10] = Item::get(373, 15, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bКрасный камень §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[11] = Item::get(373, 22, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bАрбузик §b64 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[12] = Item::get(373, 28, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bПузырёк воды §b24 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[13] = Item::get(373, 30, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bТыква §b40 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[14] = Item::get(373, 33, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЗолотой арбузик §b20 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[15] = Item::get(373, 22, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bАрбуз §b8 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[16] = Item::get(373, 13, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bБлок сена §b16 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[19] = Item::get(218, 0, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЗолотой слиток §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[20] = Item::get(218, 2, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЖареная картошка §b128 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[21] = Item::get(218, 4, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bИзумруд §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[22] = Item::get(218, 14, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bРыба-Фугу §b16 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[23] = Item::get(218, 5, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЗолотое яблоко Нотча §b12 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[24] = Item::get(218, 3, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bАлмазик §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[25] = Item::get(450, 0, 1)->setCustomName("§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bСедло §b8 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[48] = Item::get(340, 0, 1)->setCustomName("§l§e• §r§fИнформация об обменне§7:\n§l§7• §r§fНа этом §bменю §fвы §aможете\n§l§7• §r§fОбменнять §cне нужные §eпредметы §fна §eценные");
$pk->slots[50] = Item::get(355, 14, 1)->setCustomName("§l§cВыйти назад\n\n§r§7Нажми чтобы вернуться к выбору!");
}

if($list == 3) { 
for($i = 0; $i < 54; $i++){
$customname = "§r";
$itid = 0; $dmg = 0;
$pustota = [0];
if(in_array($i, $pustota)){
$itid = 0;
}
$item = Item::get($itid, $dmg, 1);
if($customname !== null) $item->setCustomName($customname);
$pk->slots[$i] = $item;
$customname = null;
}

$pk->slots[20] = Item::get(346, 0, 1)->setCustomName("§l§bУдочка §7Простолюдина§r§7\nМорская удача I\nНеразрушимость I\n§l§fНа рыбалке должно быть тихо§7!§r");
$pk->slots[20]->addEnchantment(Enchantment::getEnchantment(1000)->setLevel(1));
$pk->slots[22] = Item::get(346, 0, 1)->setCustomName("§l§7Удочка §l§cСка§4ута\n§r§7Морская удача II\nНеразрушимость III\nПриманка II\n§l§fУпущенная рыба кажется большой.§r");
$pk->slots[22]->addEnchantment(Enchantment::getEnchantment(1000)->setLevel(1));
$pk->slots[24] = Item::get(346, 0, 1)->setCustomName("§l§7Удочка §l§aП§2осейдона\n§r§7Морская удача III\nНеразрушимость V\nПриманка III\n§l§fХороший клев бывает§7...§r");
$pk->slots[24]->addEnchantment(Enchantment::getEnchantment(1000)->setLevel(1));
$pk->slots[48] = Item::get(340, 14, 1)->setCustomName("§l§6Рыбалка");
$pk->slots[50] = Item::get(355, 14, 1)->setCustomName("§l§cВыйти назад\n\n§r§7Нажми чтобы вернуться к выбору!");
}

if($list == 4) { 
for($i = 0; $i < 54; $i++){
$customname = "§r";
$itid = 0; $dmg = 0;
$pustota = [0];
if(in_array($i, $pustota)){
$itid = 0;
}
$item = Item::get($itid, $dmg, 1);
if($customname !== null) $item->setCustomName($customname);
$pk->slots[$i] = $item;
$customname = null;
}

$pk->slots[10] = Item::get(86, 0, 16)->setCustomName("§l§7• §rПродать §bТыквы §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e120 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e120 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[11] = Item::get(170, 0, 8)->setCustomName("§l§7• §rПродать §bБлок сена §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e150 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e150 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[12] = Item::get(103, 0, 8)->setCustomName("§l§7• §rПродать §bАрбуз §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[13] = Item::get(81, 0, 4)->setCustomName("§l§7• §rПродать §bКактус §a4 §7шт.\n\n§l§7• §r§fЦена§7: §e40 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e40 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[14] = Item::get(392, 0, 16)->setCustomName("§l§7• §rПродать §bКартошка §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e150 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e150 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[15] = Item::get(393, 0, 16)->setCustomName("§l§7• §rПродать §bЖареная картошка §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e190 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e190 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[16] = Item::get(457, 0, 12)->setCustomName("§l§7• §rПродать §bСвекла §a12 §7шт.\n\n§l§7• §r§fЦена§7: §e140 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e140 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[19] = Item::get(391, 0, 16)->setCustomName("§l§7• §rПродать §bМорковь §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e150 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e150 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[20] = Item::get(296, 0, 16)->setCustomName("§l§7• §rПродать §bСено §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e160 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e160 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[21] = Item::get(297, 0, 16)->setCustomName("§l§7• §rПродать §bХлебушек §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e180 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e180 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[22] = Item::get(6, 1, 8)->setCustomName("§l§7• §rПродать §bСаженцы §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[23] = Item::get(6, 3, 8)->setCustomName("§l§7• §rПродать §bСажнецы §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[24] = Item::get(6, 0, 8)->setCustomName("§l§7• §rПродать §bСажнецы §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e80 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e80 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[25] = Item::get(349, 0, 6)->setCustomName("§l§7• §rПродать §bСырая рыба §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e160 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e160 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[28] = Item::get(460, 0, 6)->setCustomName("§l§7• §rПродать §bСырой окунь §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e320 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e320 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[29] = Item::get(461, 0, 6)->setCustomName("§l§7• §rПродать §bРыба-клоун §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e360 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e360 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[30] = Item::get(462, 0, 6)->setCustomName("§l§7• §rПродать §bРыба-Фугу §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e400 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e400 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[31] = Item::get(463, 0, 6)->setCustomName("§l§7• §rПродать §bЖарный окунь §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e340 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e340 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[32] = Item::get(1, 0, 32)->setCustomName("§l§7• §rПродать §bБлоки камня §a32 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[33] = Item::get(264, 0, 8)->setCustomName("§l§7• §rПродать §bАлмазик §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e200 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e200 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[34] = Item::get(265, 0, 8)->setCustomName("§l§7• §rПродать §bЖелезный слиток §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[37] = Item::get(263, 0, 16)->setCustomName("§l§7• §rПродать §bУголь §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e80 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e80 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[38] = Item::get(332, 0, 16)->setCustomName("§l§7• §rПродать §bСнежок §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e120 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e120 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[39] = Item::get(322, 0, 8)->setCustomName("§l§7• §rПродать §bЗолотое яблоко §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e160 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e160 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[40] = Item::get(372, 0, 32)->setCustomName("§l§7• §rПродать §bАдский нарость §a32 §7шт.\n\n§l§7• §r§fЦена§7: §e300 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e300 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!");
$pk->slots[49] = Item::get(355, 14, 1)->setCustomName("§l§cВыйти назад\n\n§r§7Нажми чтобы вернуться к выбору!");
}
if($list == 5) { 
for($i = 0; $i < 54; $i++){
$customname = "§r";
$itid = 0; $dmg = 0;
$pustota = [0];
if(in_array($i, $pustota)){
$itid = 0;
}
$item = Item::get($itid, $dmg, 1);
if($customname !== null) $item->setCustomName($customname);
$pk->slots[$i] = $item;
$customname = null;
}

$pk->slots[12] = Item::get(345, 0, 1)->setCustomName("§eКомпас\n§eЦена - 1000$");
$pk->slots[13] = Item::get(352, 0, 1)->setCustomName("§eКость\n§eЦена - 3000$");
$pk->slots[49] = Item::get(355, 14, 1)->setCustomName("§l§cВыйти назад\n\n§r§7Нажми чтобы вернуться к выбору!");
}

$player->dataPacket($pk);
}	

	  public function closeAuc($player){  
	      $nick = strtolower($player->getName());
          $this->updates[$nick] = 10;
	      if(isset($this->chest[$nick])){
          $pk = new ContainerClosePacket();
	      $pk->windowid = 10;          
		  unset($this->chest[$nick]);
		  $player->dataPacket($pk);
          }
       }

	   public function onTransaction(InventoryTransactionEvent $event){
		  $player = $event->getTransaction()->getPlayer();
		  $nick = strtolower($player->getName());         
		  if(isset($this->chest[$nick]) or isset($this->updates[$nick])){
		  $event->setCancelled(true); 
		  }  
	  }

      public function drop(PlayerDropItemEvent $event){
		  $player = $event->getPlayer();
	      $nick = strtolower($player->getName()); 
		  if(isset($this->chest[$nick])) $event->setCancelled(true);
		  if(isset($this->updates[$nick])) $event->setCancelled(true);  
		  }	

	  public function PacketReceive(DataPacketReceiveEvent $event){ 
		   $player = $event->getPlayer();
		   $nick = strtolower($player->getName());
	
		   if($event->getPacket() instanceof ContainerClosePacket){
			  if(isset($this->chest[$nick])){
			  $this->closeAuc($player);
			  unset($this->chest[$nick]);
			  }
		   }
		
		  if($event->getPacket() instanceof INVENTORY_ACTION_PACKET or $event->getPacket() instanceof ContainerSetSlotPacket){
			  $pk = $event->getPacket();
		      $nick = strtolower($player->getName());
		 
		 	 if(!isset($this->chest[$nick])) return false; 		 
			  $item = $pk->item;  
			  $id = $item->getId();
           
           
           if($item->getCustomName() == "§l§7• §eОбменнять §fпредметы\n\n§l§d↬ §r§7Нажмите чтобы перейти!"){ 
           	$player->getLevel()->addSound(new PopSound($player)); 
           	$this->list($player, 2);
           }

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bКрасный камень §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(331, 0, 32))){
$player->getInventory()->removeItem(Item::get(331, 0, 32));
$player->getInventory()->addItem(Item::get(373, 15, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bКрасный камень §fна предмет§7: §aЗелье Скорости");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bКрасный камень §a32 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}
     
if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bАрбузик §b64 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(360, 0, 64))){
$player->getInventory()->removeItem(Item::get(360, 0, 64));
$player->getInventory()->addItem(Item::get(373, 22, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7: §aЗелье Оздоровление");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bАрбузик §a64 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}      

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bПузырёк воды §b24 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(374, 0, 24))){
$player->getInventory()->removeItem(Item::get(374, 0, 24));
$player->getInventory()->addItem(Item::get(373, 28, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bПузырёк воды §fна предмет§7: §aЗелье Регенерации");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bПузырёк воды §a24 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}    

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bТыква §b40 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(86, 0, 40))){
$player->getInventory()->removeItem(Item::get(86, 0, 40));
$player->getInventory()->addItem(Item::get(373, 30, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bТыква §fна предмет§7: §aЗелье Регенерации");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bТыква §a40 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЗолотой арбузик §b20 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(382, 0, 20))){
$player->getInventory()->removeItem(Item::get(382, 0, 20));
$player->getInventory()->addItem(Item::get(373, 33, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bЗолотой арбузик §fна предмет§7:  §aЗелье Силы");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bЗолотой арбузик §a20 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bАрбуз §b8 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(103, 0, 8))){
$player->getInventory()->removeItem(Item::get(103, 0, 8));
$player->getInventory()->addItem(Item::get(373, 22, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбуз §fна предмет§7: §aЗелье Оздоровление");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bАрбуз §a8 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bБлок сена §b16 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(170, 0, 16))){
$player->getInventory()->removeItem(Item::get(170, 13, 16));
$player->getInventory()->addItem(Item::get(373, 13, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bБлок сена §fна предмет§7:  §aЗелье Огнестойкости");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bБлок сена §a16 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЗолотой слиток §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(266, 0, 32))){
$player->getInventory()->removeItem(Item::get(266, 0, 32));
$player->getInventory()->addItem(Item::get(218, 0, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bЗолотой слиток §fна предмет§7: §aШалкер");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bЗолотой слиток §a32 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЖареная картошка §b128 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(392, 0, 128))){
$player->getInventory()->removeItem(Item::get(392, 0, 128));
$player->getInventory()->addItem(Item::get(218, 2, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bЖареная картошка §fна предмет§7: §aШалкер");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bЖареная картошка §a128 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bИзумруд §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(388, 0, 32))){
$player->getInventory()->removeItem(Item::get(388, 0, 32));
$player->getInventory()->addItem(Item::get(218, 4, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bИзумруд §fна предмет§7: §aШалкер");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bИзумруд §a32 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bРыба-Фугу §b16 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(462, 0, 16))){
$player->getInventory()->removeItem(Item::get(462, 0, 16));
$player->getInventory()->addItem(Item::get(218, 14, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bРыба-Фугу §fна предмет§7: §aШалкер");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bРыба-Фугу §a16 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЗолотое яблоко Нотча §b12 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(466, 0, 12))){
$player->getInventory()->removeItem(Item::get(466, 0, 12));
$player->getInventory()->addItem(Item::get(218, 5, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bЗолотое яблоко Нотча §fна предмет§7: §aШалкер");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bЗолотое яблоко Нотча §a12 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bЗолотое яблоко Нотча §b12 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(388, 0, 3))){
$player->getInventory()->removeItem(Item::get(388, 0, 3));
$player->sendMessage("§l§6► §r§fВы обменяли §bЗолотое яблоко Нотча §fна предмет§7: §aШалкер");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bЗолотое яблоко Нотча §a12 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bАлмазик §b32 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(264, 0, 32))){
$player->getInventory()->removeItem(Item::get(264, 0, 32));
$player->getInventory()->addItem(Item::get(218, 3, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7: §aШалкер");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bАлмазик §a32 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§b• §r§fДля §6обмена §fтребуется §eпредмет§7:\n§7§l• §r§bСедло §b8 §aшт.\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(329, 0, 8))){
$player->getInventory()->removeItem(Item::get(329, 0, 8));
$player->getInventory()->addItem(Item::get(450, 0, 1));
$player->sendMessage("§l§6► §r§fВы обменяли §bСедло §fна предмет§7: §aТотем");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам необходимо §r§bСедло §a8 §7шт.");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

           if($item->getCustomName() == "§l§7• §6Рыбалка\n\n§l§d↬ §r§7Нажмите чтобы перейти!"){ 
           	$player->getLevel()->addSound(new PopSound($player));
           	$this->list($player, 3);
           }

if($item->getCustomName() == "§l§bУдочка §7Простолюдина§r§7\nМорская удача I\nНеразрушимость I\n§l§fНа рыбалке должно быть тихо§7!§r"){
if($player->getInventory()->contains(Item::get(264, 0, 128))){
$player->getInventory()->removeItem(Item::get(264, 0, 128));
 			      $item = Item::get(346, 0, 1);        		    $item->setCustomName("§l§bУдочка §7Простолюдина");
  		    $item->addEnchantment(Enchantment::getEnchantment(23)->setLevel(1));
  		    $item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(1));
      	 	$player->getInventory()->addItem($item);
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§7► §r§fВам необходимо §bАлмазик 128шт");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7Удочка §l§cСка§4ута\n§r§7Морская удача II\nНеразрушимость III\nПриманка II\n§l§fУпущенная рыба кажется большой.§r"){
if($player->getInventory()->contains(Item::get(466, 0, 64))){
$player->getInventory()->removeItem(Item::get(466, 0, 64)); 
 			      $item = Item::get(346, 0, 1);        		    $item->setCustomName("§l§7Удочка §l§cСка§4ута");
  		    $item->addEnchantment(Enchantment::getEnchantment(23)->setLevel(2));
  		    $item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(2));
  		    $item->addEnchantment(Enchantment::getEnchantment(24)->setLevel(2));
      	 	$player->getInventory()->addItem($item);
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§7► §r§fВам необходимо §bЗолотое яблоко Нотча 64шт");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7Удочка §l§aП§2осейдона\n§r§7Морская удача III\nНеразрушимость V\nПриманка III\n§l§fХороший клев бывает§7...§r"){
if($player->getInventory()->contains(Item::get(329, 0, 64))){
$player->getInventory()->removeItem(Item::get(329, 0, 64));
 			     $item = Item::get(346, 0, 1);        		    $item->setCustomName("§l§7Удочка §l§aП§2осейдона");
  		    $item->addEnchantment(Enchantment::getEnchantment(23)->setLevel(3));
  		    $item->addEnchantment(Enchantment::getEnchantment(17)->setLevel(4));
  		    $item->addEnchantment(Enchantment::getEnchantment(24)->setLevel(3));
      	 	$player->getInventory()->addItem($item);
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§7► §r§fВам необходимо §bСедло 64шт");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}


           

           if($item->getCustomName() == "§l§7• §aПродать §fпредметы\n\n§l§d↬ §r§7Нажмите чтобы перейти!"){ 
           	$player->getLevel()->addSound(new PopSound($player));
           	$this->list($player, 4);
           }

if($item->getCustomName() == "§l§7• §rПродать §bТыквы §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e120 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e120 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(86, 0, 16))){
$player->getInventory()->removeItem(Item::get(86, 0, 16));
$this->eco->addMoney($player, 120);
$player->sendMessage("§l§6► §r§fВы успешно продали §bТыквы §fза §e120 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bБлок сена §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e150 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e150 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(170, 0, 8))){
$player->getInventory()->removeItem(Item::get(170, 0, 8));
$this->eco->addMoney($player, 150);
$player->sendMessage("§l§6► §r§fВы успешно продали §bБлок сена §fза §e150 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bАрбуз §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(103, 0, 8))){
$player->getInventory()->removeItem(Item::get(103, 0, 8));
$this->eco->addMoney($player, 100);
$player->sendMessage("§l§6► §r§fВы успешно продали §bАрбуз §fза §e100 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bКактус §a4 §7шт.\n\n§l§7• §r§fЦена§7: §e40 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e40 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(81, 0, 4))){
$player->getInventory()->removeItem(Item::get(81, 0, 4));
$this->eco->addMoney($player, 40);
$player->sendMessage("§l§6► §r§fВы успешно продали §bКактус §fза §e40 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b4 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bКартошка §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e150 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e150 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(392, 0, 16))){
$player->getInventory()->removeItem(Item::get(392, 0, 16));
$this->eco->addMoney($player, 150);
$player->sendMessage("§l§6► §r§fВы успешно продали §bКартошка §fза §e150 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bЖареная картошка §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e190 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e190 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(393, 0, 16))){
$player->getInventory()->removeItem(Item::get(393, 0, 16));
$this->eco->addMoney($player, 120);
$player->sendMessage("§l§6► §r§fВы успешно продали §bЖареная картошка §fза §e16 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСвекла §a12 §7шт.\n\n§l§7• §r§fЦена§7: §e140 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e140 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(457, 0, 12))){
$player->getInventory()->removeItem(Item::get(457, 0, 12));
$this->eco->addMoney($player, 140);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСвекла §fза §e140 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bМорковь §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e150 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e150 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(391, 0, 16))){
$player->getInventory()->removeItem(Item::get(391, 0, 16));
$this->eco->addMoney($player, 150);
$player->sendMessage("§l§6► §r§fВы успешно продали §bМорковь §fза §e150 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСено §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e160 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e160 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(296, 0, 16))){
$player->getInventory()->removeItem(Item::get(296, 0, 16));
$this->eco->addMoney($player, 160);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСено §fза §e160 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bХлебушек §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e180 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e180 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(297, 0, 16))){
$player->getInventory()->removeItem(Item::get(297, 0, 16));
$this->eco->addMoney($player, 820);
$player->sendMessage("§l§6► §r§fВы успешно продали §bХлебушек §fза §e180 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСаженцы §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(6, 1, 8))){
$player->getInventory()->removeItem(Item::get(6, 1, 8));
$this->eco->addMoney($player, 100);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСаженцы §fза §e100 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСажнецы §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e80 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(6, 3, 8))){
$player->getInventory()->removeItem(Item::get(6, 3, 8));
$this->eco->addMoney($player, 80);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСаженцы §fза §e80 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСажнецы §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(6, 0, 8))){
$player->getInventory()->removeItem(Item::get(6, 0, 8));
$this->eco->addMoney($player, 100);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСаженцы §fза §e100 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСырая рыба §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e160 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e160 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(349, 0, 6))){
$player->getInventory()->removeItem(Item::get(349, 0, 6));
$this->eco->addMoney($player, 160);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСырая рыба §fза §e160 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b6 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСырой окунь §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e320 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e320 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(460, 0, 6))){
$player->getInventory()->removeItem(Item::get(460, 0, 6));
$this->eco->addMoney($player, 320);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСырой окунь §fза §e320 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b6 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bРыба-клоун §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e360 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e360 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(461, 0, 6))){
$player->getInventory()->removeItem(Item::get(461, 0, 6));
$this->eco->addMoney($player, 360);
$player->sendMessage("§l§6► §r§fВы успешно продали §bРыба-клоун §fза §e360 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b6 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bРыба-Фугу §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e400 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e400 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(462, 0, 6))){
$player->getInventory()->removeItem(Item::get(462, 0, 6));
$this->eco->addMoney($player, 400);
$player->sendMessage("§l§6► §r§fВы успешно продали §bРыба-Фугу §fза §e400 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b6 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bЖарный окунь §a6 §7шт.\n\n§l§7• §r§fЦена§7: §e340 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e340 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(463, 0, 6))){
$player->getInventory()->removeItem(Item::get(463, 0, 6));
$this->eco->addMoney($player, 340);
$player->sendMessage("§l§6► §r§fВы успешно продали §bЖарный окунь §fза §e340 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b6 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bБлоки камня §a32 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(1, 0, 32))){
$player->getInventory()->removeItem(Item::get(1, 0, 32));
$this->eco->addMoney($player, 120);
$player->sendMessage("§l§6► §r§fВы успешно продали §bБлоки камня §fза §e100 §6Монет"); 
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bАлмазик §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e200 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e200 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(264, 0, 8))){
$player->getInventory()->removeItem(Item::get(264, 0, 8));
$this->eco->addMoney($player, 200);
$player->sendMessage("§l§6► §r§fВы успешно продали §bАлмазик §fза §e200 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bЖелезный слиток §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e100 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e100 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(265, 0, 8))){
$player->getInventory()->removeItem(Item::get(265, 0, 8));
$this->eco->addMoney($player, 100);
$player->sendMessage("§l§6► §r§fВы успешно продали §bЖелезный слиток§fза §e100 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bУголь §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e80 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e80 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(263, 0, 16))){
$player->getInventory()->removeItem(Item::get(263, 0, 16));
$this->eco->addMoney($player, 80);
$player->sendMessage("§l§6► §r§fВы успешно продали §bУголь §fза §e80 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bСнежок §a16 §7шт.\n\n§l§7• §r§fЦена§7: §e120 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e120 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(332, 0, 16))){
$player->getInventory()->removeItem(Item::get(332, 0, 16));
$this->eco->addMoney($player, 120);
$player->sendMessage("§l§6► §r§fВы успешно продали §bСнежок §fза §e120 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b16 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bЗолотое яблоко §a8 §7шт.\n\n§l§7• §r§fЦена§7: §e160 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e160 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if($player->getInventory()->contains(Item::get(322, 0, 8))){
$player->getInventory()->removeItem(Item::get(322, 0, 8));
$this->eco->addMoney($player, 160);
$player->sendMessage("§l§6► §r§fВы успешно продали §bЗолотое яблоко §fза §e160 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b8 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "§l§7• §rПродать §bАдский нарость §a32 §7шт.\n\n§l§7• §r§fЦена§7: §e300 §l§6М\n§l§d↪ §r§fНачальная цена§7: §e300 §l§6М§r\n\n§7Нажмите 2 раза чтобы обменять!"){
if ($player->getInventory()->contains(Item::get(372, 0, 32))) {
$player->getInventory()->removeItem(Item::get(372, 0, 32));
} else {
if ($player->getInventory()->contains(Item::get(115, 0, 32))) {
$player->getInventory()->removeItem(Item::get(115, 0, 32));
$this->eco->addMoney($player, 300);
$player->sendMessage("§l§6► §r§fВы успешно продали §bАдский нарость §fза §e300 §6Монет");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "хуйня"){
if($player->getInventory()->contains(Item::get(373, 8, 1))){
$player->getInventory()->removeItem(Item::get(373, 8, 1));
$this->eco->addMoney($player, 5000);
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7:");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
        Thunder::sendSound($player, 'ambient.weather.thunder', true);
}
}

if($item->getCustomName() == "хуйня"){
if($player->getInventory()->contains(Item::get(373, 8, 1))){
$player->getInventory()->removeItem(Item::get(373, 8, 1));
$this->eco->addMoney($player, 5000);
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7:");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
}
}

if($item->getCustomName() == "хуйня"){
if($player->getInventory()->contains(Item::get(373, 8, 1))){
$player->getInventory()->removeItem(Item::get(373, 8, 1));
$this->eco->addMoney($player, 5000);
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7:");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
}
}

if($item->getCustomName() == "§eДыхание под водой\n§eЦена - 5000$"){
if($player->getInventory()->contains(Item::get(373, 20, 1))){
$player->getInventory()->removeItem(Item::get(373, 20, 1));
$this->eco->addMoney($player, 5000);
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7:");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
}
}

                      if($item->getCustomName() == "§eДругое"){ 
           	$player->getLevel()->addSound(new PopSound($player));
           	$this->list($player, 5);
           }

if($item->getCustomName() == "§eКость\n§eЦена - 3000$"){
if($player->getInventory()->contains(Item::get(352, 0, 1))){
$player->getInventory()->removeItem(Item::get(352, 0, 1));
$this->eco->addMoney($player, 3000);
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7:");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
}
}

if($item->getCustomName() == "§eКомпас\n§eЦена - 1000$"){
if($player->getInventory()->contains(Item::get(345, 0, 1))){
$player->getInventory()->removeItem(Item::get(345, 0, 1));
$this->eco->addMoney($player, 1000);
$player->sendMessage("§l§6► §r§fВы обменяли §bАрбузик §fна предмет§7:");
$player->getLevel()->addSound(new PopSound($player));
   }else{
    	$player->sendMessage("§l§6► §r§fВам не хватает§7: §b32 §7шт. §fдля §aпродажи§7!");
}
}
         
           if($item->getCustomName() == "§l§cВыйти назад\n\n§r§7Нажми чтобы вернуться к выбору!"){
           $this->list($player, 1);
           } 
			}
		}
	}
}