<?php
class addTitleToSearchText extends Omeka_Db_Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE  `{$this->db->SearchText}` ADD  `title` TINYTEXT NULL DEFAULT NULL AFTER  `public`
SQL;
        $this->db->query($sql);
    }
}
