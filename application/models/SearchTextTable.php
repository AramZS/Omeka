<?php
class SearchTextTable extends Omeka_Db_Table
{
    public function findByRecord($recordName, $recordId)
    {
        $select = $this->getSelect();
        $select->where('record_name = ?', $recordName);
        $select->where('record_id = ?', $recordId);
        return $this->fetchObject($select);
    }
    
    public function search($query)
    {
        $acl = Omeka_Context::getInstance()->acl;
        $showNotPublic = $acl->checkUserPermission('SearchTexts', 'showNotPublic');
        
        $sql = "
        SELECT record_name, record_id, title, MATCH (text) AGAINST (?) AS relevance
        FROM {$this->getTableName()} 
        WHERE MATCH (text) AGAINST (?)";
        if (!$showNotPublic) {
            $sql .= " AND public = 1";
        }
        $results = $this->getDb()->fetchAll($sql, array($query, $query));
        foreach ($results as $key => $result) {
            $results[$key]['record'] = $this->getTable($result['record_name'])
                                            ->find($result['record_id']);
        }
        return $results;
    }
}
