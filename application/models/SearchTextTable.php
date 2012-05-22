<?php
class SearchTextTable extends Omeka_Db_Table
{
    public function findByRecordNameAndRecordId($recordName, $recordId)
    {
        $select = $this->getSelect();
        $select->where('record_name = ?', $recordName);
        $select->where('record_id = ?', $recordId);
        return $this->fetchObject($select);
    }
    
    public function search($query)
    {
        $sql = "
        SELECT record_name, record_id, MATCH (text) AGAINST (?) AS relevance
        FROM {$this->getTableName()} 
        WHERE MATCH (text) AGAINST (?)";
        $results = $this->getDb()->fetchAll($sql, array($query, $query));
        foreach ($results as $key => $result) {
            $results[$key]['record'] = $this->getTable($result['record_name'])
                                            ->find($result['record_id']);
        }
        return $results;
    }
}
