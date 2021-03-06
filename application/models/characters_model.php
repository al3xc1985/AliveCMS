<?php

/**
 * @package FusionCMS
 * @author Jesper Lindström
 * @author Xavier Geerinck
 * @author Elliott Robbins
 * @link http://fusion-hub.com
 */

class Characters_model
{
    private $db;
    private $config;
    private $realmId;

    /**
     * Initialize the realm
     * @param Array $config Database config
     */
    public function __construct($config)
    {
        $this->config = $config;

        $this->realmId = $this->config['id'];
    }

    /**
     * Connect to the database if not already connected
     */
    public function connect()
    {
        if(empty($this->db))
        {
            $this->db = &get_instance()->load->database($this->config['characters'], true);
        }
    }

    /**
     * @return CI_DB_active_record
     */
    public function getConnection()
    {
        $this->connect();

        return $this->db;
    }

    /**
     * Get characters
     * @param String $fields
     * @param Array $where
     * @return Mixed
     */
    public function getCharacters($fields, $where)
    {
        // Make sure we're connected
        $this->connect();

        $this->db->select($fields)->from(table('characters', $this->realmId))->where($where);
        $query = $this->db->get();

        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        else
        {
            return array();
        }
    }

    /**
     * Get a specific character by its guid
     * @alive
     * @param $guid
     * @param string $fields
     * @return bool
     */
    public function getCharacterByGUID($guid, $fields = "*"){

        // Make sure we're connected
        $this->connect();

        $this->db->select($fields)->from(table('characters', $this->realmId))->where("guid", $guid);
        $query = $this->db->get();

        if($query->num_rows() > 0){
            $results = $query->result_array();
            return $results[0];
        }
        else
        {
            return false;
        }

    }

    /**
     * Get the online players
     * @return Array
     */
    public function getOnlinePlayers()
    {
        return $this->getCharacters(
            columns("characters",
                array("guid", "account", "name", "race", "class", "gender", "level", "zone"),
                $this->realmId),
            array(
                column("characters", "online", false, $this->realmId) => 1
            )
        );
    }

    /**
     * Get the online player count
     * @return Int
     */
public function getOnlineCount($faction = false)
    {
        // Make sure we're connected
        $this->connect();

        switch($faction)
        {
            default:
                $query = $this->db->query("SELECT COUNT(*) as `total` FROM ".table("characters", $this->realmId)." WHERE ".column("characters", "online", false, $this->realmId)."='1'");
            break;

            case "alliance":
                $query = $this->db->query("SELECT COUNT(*) as `total` FROM ".table("characters", $this->realmId)." WHERE ".column("characters", "online", false, $this->realmId)."='1' AND ".column("characters", "race")." IN(".implode(",", get_instance()->realms->getAllianceRaces()).") AND ".column("characters", "account")." NOT IN(SELECT `id` from trinity_realm.account_access)");
            break;

            case "horde":
                $query = $this->db->query("SELECT COUNT(*) as `total` FROM ".table("characters", $this->realmId)." WHERE ".column("characters", "online", false, $this->realmId)."='1' AND ".column("characters", "race")." IN(".implode(",", get_instance()->realms->getHordeRaces()).") AND ".column("characters", "account")." NOT IN(SELECT `id` from trinity_realm.account_access)");
            break;

            case "gm":
                $query = $this->db->query("SELECT COUNT(*) as `total` FROM ".table("characters", $this->realmId)." WHERE ".column("characters", "online", false, $this->realmId)."='1' AND ".column("characters", "account")." IN(SELECT `id` from trinity_realm.account_access WHERE `RealmID` = '-1' AND `id` != '118661')");
            break;
        }

        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            // Assign the online count
            $online = $row[0]['total'];;
        }
        else 
        {
            $online = 0;
        }

        return $online;
    }

    /**
     * Count the characters that belongs to one account
     * @param Int $account
     * @return Int
     */
    public function getCharacterCount($account)
    {
        $this->connect();
        
        $query = $this->db->query("SELECT COUNT(*) as `total` FROM ".table("characters", $this->realmId)." WHERE ".column("characters", "account", false, $this->realmId)."=?", array($account));
        
        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            return $row[0]['total'];
        }
        else
        {
            return 0;
        }
    }

    /**
    * Get the characters that belongs to one account
    * @param Int $acc
    * @return Array
    */
    public function getCharactersByAccount($acc = false)
    {
        if($acc == false)
        {
            $CI = &get_instance();
            $acc = $CI->user->getId();
        }


        return $this->getCharacters(columns("characters", array("guid", "name", "race", "class", "gender", "level", "online", "money"), $this->realmId), array(column("characters", "account", false, $this->realmId) => $acc));
    }

    /**
    * Get the character guid by the name
    * @param String $name
    * @return Int
    */
    public function getGuidByName($name)
    {
        $this->connect();

        $name = utf8_encode(ucfirst(strtolower($name)));

        $query = $this->db->query("SELECT ".column("characters", "guid", true, $this->realmId)." FROM ".table('characters', $this->realmId)." WHERE ".column("characters", "name", false, $this->realmId)."=?", array($name));

        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            return $row[0]['guid'];
        }
        else
        {
            return false;
        }
    }

    /**
    * Get the character online/offline status
    * @param Int $guid
    * @return Boolean
    */
    public function isOnline($guid)
    {
        $this->connect();

        $query = $this->db->query("SELECT ".column("characters", "online", true, $this->realmId)." FROM ".table('characters', $this->realmId)." WHERE ".column("characters", "guid", false, $this->realmId)."=?", array($guid));

        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            return $row[0]['online'];
        }
        else
        {
            return false;
        }
    }

    /**
    * Get the character name by the guid
    * @param String $guid
    * @return Int
    */
    public function getNameByGuid($guid)
    {
        $this->connect();

        $query = $this->db->query("SELECT ".column("characters", "name", true, $this->realmId)." FROM ".table('characters', $this->realmId)." WHERE ".column("characters", "guid", false, $this->realmId)."=?", array($guid));

        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            return $row[0]['name'];
        }
        else
        {
            return false;
        }
    }

    /**
    * Get the character faction (alliance/horde) by the guid
    * @param String $guid
    * @return Int
    */
    public function getFaction($guid)
    {
        $this->connect();

        $query = $this->db->query("SELECT ".column("characters", "race", true, $this->realmId)." FROM ".table('characters', $this->realmId)." WHERE ".column("characters", "guid", false, $this->realmId)."=?", array($guid));

        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            $factions = array(
                1 => 1,
                2 => 2,
                3 => 1,
                4 => 1,
                5 => 2,
                6 => 2,
                7 => 1,
                8 => 2,
                9 => 2,
                10 => 2,
                11 => 1,
                22 => 1,
                24 => 0,
                25 => 1,
                26 => 2
            );

            if(array_key_exists($row[0]['race'], $factions))
            {
                return $factions[$row[0]['race']];
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return false;
        }
    }

    /**
    * Check if a character exists
    * @param Int $id
    * @return Boolean
    */
    public function characterExists($id)
    {
        $this->connect();
        
        $query = $this->db->query("SELECT COUNT(*) as `total` FROM ".table('characters', $this->realmId)." WHERE ".column("characters", "guid", false, $this->realmId)."=?", array($id));

        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            if($row[0]['total'] == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Move a character to a different account
     * @alive
     * @param $charGuid
     * @param $newAccount
     * @param $realmId
     */
    public function moveCharacterToAccount($charGuid, $newAccount, $realmId){
        $this->connect();

        if(empty($charGuid) || empty($newAccount) || empty($realmId)){
            return false;
        }

        $this->db->where('guid', $charGuid)
            ->update(table('characters', $this->realmId), array(
                "account" => $newAccount
            ));

        return true;
    }

    /**
    * Check if a character belongs to the specified account
    * @param Int $characterId
    * @param Int $accountId
    * @return Boolean
    */
    public function characterBelongsToAccount($characterId, $accountId)
    {
        $this->connect();
        
        $query = $this->db->query("SELECT COUNT(*) as `total` FROM ".table('characters', $this->realmId)." WHERE ".column("characters", "guid", false, $this->realmId)."=? AND ".column("characters", "account", false, $this->realmId)."=?", array($characterId, $accountId));
        
        if($this->db->_error_message())
        {
            die($this->db->_error_message());
        }

        if($query->num_rows() > 0)
        {
            $row = $query->result_array();

            if($row[0]['total'] == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Get the gold of a character
     * @param Int $account
     * @param Int $guid
     * @return Int
     */
    public function getGold($account, $guid)
    {
        $query = $this->db->query("SELECT ".column("characters", "money", true, $this->realmId)." FROM ".table("characters", $this->realmId)." WHERE ".column("characters", "account", false, $this->realmId)." = ? AND ".column("characters", "guid", false, $this->realmId)." = ?", array($account, $guid));
        if($query->num_rows() > 0)
        {
            $result = $query->result_array();
            return $result[0]["money"];
        }
        else
        {
            return false;
        }
    }

    /**
     * Finds the guild id of a character, if available
     * @alive
     * @param Integer $charGUID
     */
    public function getGuild($charGUID = -1){

        $this->connect();

        $query = $this->db->query(
            "SELECT ".column("guild_member", "guildid", true, $this->realmId)." FROM ".table("guild_member", $this->realmId)." WHERE ".column("guild_member", "guid", false, $this->realmId)."= ?",
            array($charGUID));

        if($this->db->_error_message()){
            die($this->db->_error_message());
        }

        if($query && $query->num_rows() > 0){
            $row = $query->result_array();

            return $row[0]['guildid'];
        }
        else{
            $query2 = $this->db->query(
                "SELECT ".column("guild", "guildid", true, $this->realmId)." FROM ".table("guild", $this->realmId)." WHERE ".column("guild", "leaderguid", false, $this->realmId)."= ?",
                array($charGUID));

            if($this->db->_error_message()){
                die($this->db->_error_message());
            }

            if($query2 && $query2->num_rows() > 0){

                $row2 = $query2->result_array();

                return $row2[0]['guildid'];
            }
            else{
                return false;
            }
        }
    }

    /**
     * Returns the guild name of a specified guild
     * @alive
     * @param Integer $guildId
     */
    public function getGuildName($guildId = -1){

        $this->connect();

        $query = $this->db->query("SELECT ".column("guild", "name", true, $this->realmId)." FROM ".table("guild", $this->realmId)." WHERE ".column("guild", "guildid", false, $this->realmId)."= ?", array($guildId));

        if($query && $query->num_rows() > 0){
            $row = $query->result_array();

            return $row[0]['name'];
        }
        else{
            return false;
        }
    }


    /**
     * Set the gold of a character
     * @param Int $account
     * @param Int $guid
     * @param Int $newGold
     * @return Boolean
     */
    public function setGold($account, $guid, $newGold)
    {
        $query = $this->db->query("UPDATE ".table("characters", $this->realmId)." SET ".column("characters", "money", false, $this->realmId)." = ? WHERE ".column("characters", "account", false, $this->realmId)." = ? AND ".column("characters", "guid", false, $this->realmId)." = ?", array($newGold, $account, $guid));
        
        if($query)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
}