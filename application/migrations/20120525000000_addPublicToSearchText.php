<?php
class addPublicToSearchText extends Omeka_Db_Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `{$this->db->SearchText}` ADD `public` BOOLEAN NOT NULL AFTER `record_id`
SQL;
        $this->db->query($sql);
    }
}
