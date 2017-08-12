<?php
namespace app\ims\model;

use think\Model;

class BaseModel extends Model
{
    public $baseHidden = ['create_time','modify_time'];

    public function __construct($data = [])
    {
        $this->hidden = array_merge($this->hidden, $this->baseHidden);
        parent::__construct($data);
    }

    public function formatInput($data,$hiddenData = [])
    {
        if(empty($data)){
            return false;
        }
        if(isset($this->mapFields)){
            foreach ($data as $index => $value) {
                if($key = array_search($index,$this->mapFields)){
                    $this->$key = $value;
                }else{
                    if (!in_array($index,$hiddenData)){
                        $this->$index = $value;
                    }
                }
            }
        }
        return $this;
    }

    public function formatOutPut($data = array())
    {
        if(empty($data)){
            $data = $this->toArray();
        }
        if(isset($this->mapFields)){
            foreach ($this->mapFields as $index => $value) {
                if(isset($data[$index])){
                    $data[$value] = $data[$index];
                    unset($data[$index]);
                }
            }
        }
        return $data;
    }

    public function formatData($data, $return = array())
    {
        if(is_string($data)){
            $data = $this->$data;
        }
        if(isset($this->mapFields)){
            foreach ($data as $item) {
                if ($key = array_search($item, $this->mapFields)) {
                    $value =  $this->getAttr($key);
                }else{
                    $value =  $this->getAttr($item);
                }
                $return[$item] = $value;
            }
        }
        return $return;
    }

    public function replace($data)
    {
        $sql =  $this->fetchSql()->insert($data);
        $sql = str_replace('INSERT INTO','REPLACE INTO',$sql);
        return $this->execute($sql);
    }

    /**
     * 保存多个数据到当前数据对象
     * @access public
     * @param array   $dataSet 数据
     * @param boolean $replace 是否自动识别更新和写入
     * @return array|false
     * @throws \Exception
     */
    public function replaceAll($dataSet, $replace = true)
    {
        if ($this->validate) {
            // 数据批量验证
            $validate = $this->validate;
            foreach ($dataSet as $data) {
                if (!$this->validateData($data, $validate)) {
                    return false;
                }
            }
        }

        $result = [];
        $db     = $this->db();
        $db->startTrans();
        try {
            foreach ($dataSet as $key => $data) {
                $sql =  $this->fetchSql()->insert($data);
                $sql = str_replace('INSERT INTO','REPLACE INTO',$sql);
                $result[$key] = $this->execute($sql);
            }
            $db->commit();
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function getGroupColumn($field,$where)
    {
        return $this->where($where)->group($field)->column($field);
    }

    public function setUniqueIndex()
    {
        if (isset($this->uniqueIndex)){
            $this->pk($this->uniqueIndex);
        }
    }

    public function getUniqueWhere($data, $where = [])
    {
        if (count($where) === 0 && isset($this->uniqueIndex)){
            foreach ($this->uniqueIndex as $uniqueIndex) {
                if (isset($data[$uniqueIndex])){
                    $where[$uniqueIndex] = $data[$uniqueIndex];
                }
            }
            if (count($where) !== count($this->uniqueIndex)){
                $where = [];
            }
        }
        return $where;
    }

    public function modify($data, $where = [])
    {
        $this->getUniqueWhere($data,$where);
        $result = $this->where($where)->find();
        if (!$result){
            $return = $this->create($data);
        }else{
            $return = $this->update($data);
        }
        return $return;
    }

    public function modifyAll($dataSet)
    {
        $resultSet = [];
        $db = $this->db();
        $db->startTrans();
        try {
            foreach ($dataSet as $key => $data) {
                dump($data);
                $where = $this->getUniqueWhere($data);
                $result = $this->where($where)->find();
                if (!$result) {
                    $resultSet[$key] = $this->create($data);
                } else {
                    $resultSet[$key] = $this->update($data);
                }
            }
            $db->commit();
            return $resultSet;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}