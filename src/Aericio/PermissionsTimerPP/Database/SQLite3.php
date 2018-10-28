<?php

declare(strict_types=1);

namespace Aericio\PermissionsTimerPP\Database;

use Aericio\PermissionsTimerPP\PermissionsTimerPP;
use pocketmine\Player;

/**
 * Class SQLite3
 * @package Aericio\PermissionsTimerPP\Database
 */
class SQLite3
{

    /** @var PermissionsTimerPP */
    private $plugin;

    /** @var \SQLite3 */
    public $db;

    /**
     * SQLite3 constructor.
     * @param PermissionsTimerPP $plugin
     */
    public function __construct(PermissionsTimerPP $plugin)
    {
        $this->plugin = $plugin;
        if (!file_exists($plugin->getDataFolder() . "players.db")) {
            $this->db = new \SQLite3($plugin->getDataFolder() . "players.db", SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        } else {
            $this->db = new \SQLite3($plugin->getDataFolder() . "players.db", SQLITE3_OPEN_READWRITE);
        }
        $this->db->exec("CREATE TABLE IF NOT EXISTS players (name VARCHAR(100) PRIMARY KEY, permissions VARCHAR);");
    }

    /**
     * @param Player $player
     * @param string $permission
     * @param int    $time
     */
    public function addPermissionTime(Player $player, string $permission, int $time)
    {
        $permissions = is_null($this->getPermissions($player)) ? [] : $this->getPermissions($player);
        if (isset($permissions[$permission])) {
            $permissions[$permission]["seconds"] += $time;
        } else {
            $permissions[$permission] = ["seconds" => $time];
        }
        $stmt = $this->db->prepare("UPDATE players SET permissions = :permissions WHERE name = :name");
        if (is_null($this->getPermissionsTime($player, $permission))) $stmt = $this->db->prepare("INSERT INTO players (name, permissions) VALUES (:name, :permissions)");
        $stmt->bindValue(":name", $player->getLowerCaseName(), SQLITE3_TEXT);
        $stmt->bindValue(":permissions", serialize($permissions), SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * @param Player $player
     * @param string $permission
     * @param int    $time
     */
    public function removePermissionTime(Player $player, string $permission, int $time)
    {
        $permissions = is_null($this->getPermissions($player)) ? [] : $this->getPermissions($player);
        if (isset($permissions[$permission])) {
            $permissions[$permission]["seconds"] -= $time;
        } else {
            $permissions[$permission] = ["seconds" => $time];
        }
        $stmt = $this->db->prepare("UPDATE players SET permissions = :permissions WHERE name = :name");
        if (is_null($this->getPermissionsTime($player, $permission))) $stmt = $this->db->prepare("INSERT INTO players (name, permissions) VALUES (:name, :permissions)");
        $stmt->bindValue(":name", $player->getLowerCaseName(), SQLITE3_TEXT);
        $stmt->bindValue(":permissions", serialize($permissions), SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * @param Player $player
     * @param string $permission
     * @param int    $time
     */
    public function setPermissionTime(Player $player, string $permission, int $time)
    {
        $permissions = is_null($this->getPermissions($player)) ? [] : $this->getPermissions($player);
        $permissions[$permission] = ["seconds" => $time + time()];
        $stmt = $this->db->prepare("UPDATE players SET permissions = :permissions WHERE name = :name");
        if (is_null($this->getPermissionsTime($player, $permission))) $stmt = $this->db->prepare("INSERT INTO players (name, permissions) VALUES (:name, :permissions)");
        $stmt->bindValue(":name", $player->getLowerCaseName(), SQLITE3_TEXT);
        $stmt->bindValue(":permissions", serialize($permissions), SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * @param Player $player
     * @param string $permission
     * @return null|void
     */
    public function deleteEntry(Player $player, string $permission)
    {
        $permissions = $this->getPermissions($player);
        if (is_null($permissions)) return null;
        if (isset($permissions[$permission])) {
            $value = array_search($permission, $permissions[$permission]);
            unset($value);
            $stmt = $this->db->prepare("UPDATE players SET permissions = :permissions WHERE name = :name");
            $stmt->bindValue(":name", $player->getLowerCaseName(), SQLITE3_TEXT);
            $stmt->bindValue(":permissions", serialize($permissions), SQLITE3_TEXT);
            $stmt->execute();
        }
        return;
    }

    /**
     * @param Player $player
     * @param string $permission
     * @return int|null
     */
    public function getPermissionsTime(Player $player, string $permission)
    {
        $permissions = $this->getPermissions($player);
        if (is_null($permissions)) return null;
        return isset($permissions[$permission]) ? $permissions[$permission]["seconds"] : 0;
    }

    /**
     * @param string $player
     * @param string $permission
     * @return int|null
     */
    public function getOfflinePermissionsTime(string $player, string $permission)
    {
        $permissions = $this->getOfflinePermissions($player);
        if (is_null($permissions)) return null;
        return isset($permissions[$permission]) ? $permissions[$permission]["seconds"] : 0;
    }

    /**
     * @param Player $player
     * @return mixed|null
     */
    public function getPermissions(Player $player)
    {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE name = :name");
        $stmt->bindValue(":name", $player->getLowerCaseName(), SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result instanceof \SQLite3Result) {
            $data = $result->fetchArray(SQLITE3_ASSOC);
            $permissions = unserialize($data["permissions"]);
            $result->finalize();
            $stmt->close();
            return $permissions;
        }
        return null;
    }

    /**
     * @param string $player
     * @return mixed|null
     */
    public function getOfflinePermissions(string $player)
    {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE name = :name");
        $stmt->bindValue(":name", strtolower($player), SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result instanceof \SQLite3Result) {
            $data = $result->fetchArray(SQLITE3_ASSOC);
            $permissions = unserialize($data["permissions"]);
            $result->finalize();
            $stmt->close();
            return $permissions;
        }
        return null;
    }

    /**
     * @param Player $player
     * @return null|Player
     */
    public function getPlayer(Player $player)
    {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE name = :name");
        $stmt->bindValue(":name", $player->getLowerCaseName(), SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result instanceof \SQLite3Result) {
            $data = $result->fetchArray(SQLITE3_ASSOC);
            $player = ($data["name"]);
            $result->finalize();
            $stmt->close();
            return $player;
        }
        return null;
    }
}