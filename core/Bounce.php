<?php
/**
 * User: SaWey
 * Date: 21/12/13
 */

namespace phpList;

/**
 * Class Bounce
 * @package phpList
 */
class Bounce {
    public $id = 0;
    /**
     * @var \DateTime
     */
    public $date;
    public $header;
    public $data;
    public $status;
    public $comment;

    /**
     * Write bounce info to database
     */
    public function save()
    {
        if($this->id != 0){
            $this->update();
        }else{
            phpList::DB()->query(sprintf(
                    'INSERT INTO %s
                    (date, header, data, status, comment)
                    VALUES("%s", "%s", "%s", "%s", "%s")',
                    Config::getTableName('bounce'),
                    $this->date->format('Y-m-d H:i'),
                    addslashes($this->header),
                    addslashes($this->data),
                    addslashes($this->status),
                    addslashes($this->comment)
                ));
            $this->id = phpList::DB()->insertedId();
        }

    }

    /**
     * Update bounce info in database
     */
    public function update()
    {
        phpList::DB()->query(sprintf(
                'UPDATE %s SET
                date = "%s", header = "%s", data = "%s", status = "%s", comment = "%s"
                WHERE id = %d',
                Config::getTableName('bounce'),
                $this->date->format('Y-m-d H:i'),
                addslashes($this->header),
                addslashes($this->data),
                addslashes($this->status),
                addslashes($this->comment),
                $this->id
            ));
    }

    /**
     * Delete all unidentified bounces
     * only when ALLOW_DELETEBOUNCE is true
     */
    public static function deleteUnidentified()
    {
        if(Config::get('ALLOW_DELETEBOUNCE', false) !== false){
            phpList::DB()->query(sprintf(
                    'DELETE FROM %s
                    WHERE status = "unidentified bounce"
                    AND `date` < date_sub(now(),interval 2 month)',
                    Config::getTableName('bounce')
                ));
        }
    }

    /**
     * Delete all processed bounces
     * only when ALLOW_DELETEBOUNCE is true
     */
    public static function deleteProcessed()
    {
        if(Config::get('ALLOW_DELETEBOUNCE', false) !== false){
            phpList::DB()->query(sprintf(
                    'DELETE FROM %s
                    WHERE comment != "not processed"
                    AND `date` < date_sub(now(),interval 2 month)',
                    Config::getTableName('bounce')
                ));
        }
    }

    /**
     * Delete all bounces
     * only when ALLOW_DELETEBOUNCE is true
     */
    public static function deleteAll()
    {
        if(Config::get('ALLOW_DELETEBOUNCE', false) !== false){
            phpList::DB()->query(sprintf(
                    'DELETE FROM %s',
                    Config::getTableName('bounce')
                ));
        }
    }

    /**
     * Reset all bounces
     * only when ALLOW_DELETEBOUNCE is true
     */
    public static function resetAll()
    {
        if(Config::get('ALLOW_DELETEBOUNCE', false) !== false){
            phpList::DB()->query(sprintf(
                    'UPDATE %s, %s
                     SET bouncecount = 0',
                    Config::getTableName('user', true),
                    Config::getTableName('message')
                ));
            $tables = array(
                Config::getTableName('bounce') => '1',
                Config::getTableName('user_message_bounce') => '1'
            );
            phpList::DB()->deleteFromArray($tables, 1);
        }
    }

    /**
     * Check if the bounce matches a rule
     * @param array $rules
     * @return bool
     */
    public function matchesBounceRule(&$rules)
    {
        if(($rule = BounceRule::matchedDBBounceRule($this->data)) !== false){
            $this->addToRule($rule);
            return true;
        }else{
            $lines = explode("\n",$this->data);
            set_time_limit(100);
            foreach ($lines as $line) {
                if (preg_match('/ (55\d) (.*)/',$line,$regs)) {
                    $code = $regs[1];
                    $info = $regs[2];
                    $rule = preg_replace('/[^\s\<]+@[^\s\>]+/','.*',$info);
                    $rule = preg_replace('/\{.*\}/U','.*',$rule);
                    $rule = preg_replace('/\(.*\)/U','.*',$rule);
                    $rule = preg_replace('/\<.*\>/U','.*',$rule);
                    $rule = preg_replace('/\[.*\]/U','.*',$rule);
                    $rule = str_replace('?','.',$rule);
                    $rule = str_replace('/','.',$rule);
                    $rule = str_replace('"','.',$rule);
                    $rule = str_replace('(','.',$rule);
                    $rule = str_replace(')','.',$rule);

                    if (stripos($rule,'Unknown local user') !== false) {
                        $rule = 'Unknown local user';
                    } elseif (preg_match('/Unknown local part (.*) in/iU',$rule,$regs)) {
                        $rule = preg_replace('/'.preg_quote($regs[1]).'/','.*',$rule);
                    } elseif (preg_match('/mta(.*)\.mail\.yahoo\.com/iU',$rule)) {
                        $rule = preg_replace('/mta[\d]+/i','mta[\\d]+',$rule);
                    }

                    $rule = trim($rule);
                    if (!in_array($rule,$rules) && strlen($rule) > 25) {# && $code != 554 && $code != 552) {
                        if (Config::VERBOSE) {
                            Output::output('Rule:'.htmlspecialchars($rule));
                        }
                        array_push($rules,$rule);

                        #}
                        switch ($code) {
                            case 554:
                            case 552:
                                $action = 'unconfirmuseranddeletebounce';break;
                            case 550:
                                $action = 'blacklistuseranddeletebounce';break;
                            default:
                                $action = 'unconfirmuseranddeletebounce';break;
                        }
                        $new_rule = new BounceRule();
                        $new_rule->regex = trim($rule);
                        $new_rule->action = $action;
                        $new_rule->comment = 'Auto Created from bounce ' . $this->id;
                        $new_rule->status = 'candidate';
                        $new_rule->save();
                        $this->addToRule($new_rule);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    public static function deleteBounce($bounce_id = 0)
    {
        if (!$bounce_id) return;
        phpList::DB()->query(sprintf(
                'DELETE FROM %s
                WHERE id = %d',
                Config::getTableName('bounce'),
                $bounce_id
            ));
        $tables = array(
            Config::getTableName('user_message_bounce') => 'bounce',
            Config::getTableName('bounceregex_bounce') => 'bounce'
        );
        phpList::DB()->deleteFromArray($tables, $bounce_id);
    }


    /**
     * Add this bounce to a user and message
     * @param User $user
     * @param int $message_id
     */
    public function connectMeToUserAndMessage($user, $message_id)
    {
        ## check if we already have this um as a bounce
        ## so that we don't double count "delayed" like bounces
        $exists = phpList::DB()->fetchRowQuery(sprintf(
                'SELECT COUNT(*) FROM %s
                WHERE user = %d
                AND message = %d',
                Config::getTableName('user_message_bounce'),
                $user->id,
                $message_id
            ));

        phpList::DB()->query(sprintf(
                'INSERT INTO %s
                 SET user = %d, message = %d, bounce = %d',
                Config::getTableName('user_message_bounce'),
                $user->id,
                $message_id,
                $this->id
        ));
        $this->status = 'bounced list message ' . $message_id;
        $this->comment = $user->id . 'bouncecount increased';
        $this->save();

        ## if the relation did not exist yet, increment the counters
        if(empty($exists[0])){
            phpList::DB()->query(sprintf(
                    'UPDATE %s
                     SET bouncecount = bouncecount + 1
                     WHERE id = %d',
                    Config::getTableName('message'),
                    $message_id
                ));
            $user->bouncecount ++;
            $user->update();
        }
    }

    /**
     * Add a BounceRule to this bounce
     * @param BounceRule $rule
     */
    private function addToRule($rule)
    {
        phpList::DB()->query(sprintf(
                'INSERT INTO %s
                (regex,bounce)
                VALUES(%d,%d)',
                $rule->id,
                $this->id
            ));
    }

    /**
     * Create a Bounce from an array
     * @param array $array
     * @return Bounce
     */
    private static function bounceFromArray($array)
    {
        $bounce = new Bounce();
        $bounce->id = $array['id'];
        $bounce->date = new \DateTime($array['date']);
        $bounce->header = $array['header'];
        $bounce->data = $array['data'];
        $bounce->status = $array['status'];
        $bounce->comment = $array['comment'];
        return $bounce;
    }

}

/**
 * Class BounceRule
 * @package phpList
 */
class BounceRule {
    public $id = 0;
    public $regex;
    public $action;
    public $listorder = 0;
    public $admin;
    public $comment;
    public $status;
    public $count;

    function __construct(){}

    /**
     * Get bounce rule by id
     * @param int $bounce_rule_id
     * @return BounceRule
     */
    public static function getBounceRule($bounce_rule_id)
    {
        $result = phpList::DB()->fetchAssocQuery(sprintf(
                'SELECT * FROM %s
                WHERE id = %d',
                Config::getTableName('bounceregex'),
                $bounce_rule_id
            ));
        return BounceRule::bounceRuleFromArray($result);
    }

    /**
     * Get available bounce rules
     * @return array
     */
    public static function getAllBounceRules()
    {
        $rules = array();
        $result = phpList::DB()->query(sprintf(
                'SELECT * FROM %s
                ORDER BY listorder',
                Config::getTableName('bounceregex')
            ));
        while($row = phpList::DB()->fetchAssoc($result)){
            $rules[] = BounceRule::bounceRuleFromArray($row);
        }
        return $rules;
    }

    /**
     * Get bounce by status
     * @param string $status
     * @return array
     */
    public static function getBounceRulesByStatus($status)
    {
        $rules = array();
        $result = phpList::DB()->query(sprintf(
                'SELECT * FROM %s
                WHERE status = "%s"
                ORDER BY listorder,regex',
                Config::getTableName('bounceregex'),
                phpList::DB()->sqlEscape($status)
            ));
        while($row = phpList::DB()->fetchAssoc($result)){
            $rules[] = BounceRule::bounceRuleFromArray($row);
        }
        return $rules;
    }

    /**
     * Create a BounceRule from an array
     * @param array $array
     * @return BounceRule
     */
    private static function bounceRuleFromArray($array)
    {
        $rule = new BounceRule();
        $rule->id = $array['id'];
        $rule->regex = $array['regex'];
        $rule->action = $array['action'];
        $rule->listorder = $array['listorder'];
        $rule->admin = $array['admin'];
        $rule->comment = $array['comment'];
        $rule->status = $array['status'];
        $rule->count = $array['count'];
        return $rule;
    }

    /**
     * Remove a bouncerule from the database
     * @param int $bounce_rule_id
     */
    public static function delete($bounce_rule_id)
    {
        phpList::DB()->query(sprintf(
            'DELETE FROM %s
            WHERE id = %d',
            Config::getTableName('bounceregex'),
            $bounce_rule_id
        ));
    }

    /**
     * Save this instance to the database
     * will call update when it already has an id
     */
    public function save()
    {
        if($this->id != 0){
            $this->update();
        }else{
            phpList::DB()->query(sprintf(
                    'INSERT INTO %s (regex, action, listorder, admin, comment, status)
                    VALUES ("%s", "%s", %d, %d, "%s", "%s")',
                    Config::getTableName('bounceregex'),
                    phpList::DB()->sqlEscape($this->regex),
                    $this->action,
                    $this->listorder,
                    $this->admin,
                    phpList::DB()->sqlEscape($this->comment),
                    phpList::DB()->sqlEscape($this->status)
                ));
            $this->id = phpList::DB()->insertedId();
        }
    }

    /**
     * Update a bounce rule in the database
     */
    public function update()
    {
        phpList::DB()->query(sprintf(
                'UPDATE %s SET
                regex = "%s", action = "%s", listorder = %d, admin = %d, comment = "%s", status = "%s")
                WHERE id = %d',
                Config::getTableName('bounceregex'),
                phpList::DB()->sqlEscape($this->regex),
                $this->action,
                $this->listorder,
                $this->admin,
                phpList::DB()->sqlEscape($this->comment),
                phpList::DB()->sqlEscape($this->status),
                $this->id
            ));
    }

    /**
     * Find a matching bounce rule from database
     * returns false if none found
     * @param string $text
     * @param bool $activeonly
     * @return bool|BounceRule
     */
    public static function matchedDBBounceRule($text, $activeonly = false) {
        if ($activeonly) {
            $rules = BounceRule::getBounceRulesByStatus('active');
            if(empty($rules)){
                return false;
            }
        } else {
            $rules = BounceRule::getAllBounceRules();
        }

        return BounceRule::matchedBounceRules($text, $rules);
    }

    /**
     * Find a matching bounce rule from given set of rules
     * returns false if none found
     * @param string $text
     * @param array $rules
     * @return bool|BounceRule
     */
    public static function matchedBounceRules($text, $rules = array()) {
        if (empty($rules)) {
            $rules = BounceRule::getAllBounceRules();
        }

        /**
         * @var BounceRule $rule
         */
        foreach ($rules as $rule) {
            $pattern = str_replace(' ','\s+',$rule->regex);
            if (@preg_match('/'.preg_quote($pattern).'/iUm',$text)
                || @preg_match('/'.$pattern.'/iUm',$text)
            ) {
                return $rule;
            }
        }
        return false;
    }
}