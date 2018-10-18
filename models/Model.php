<?php 
namespace models;
use PDO;
class Model {

    protected $_db;
    protected $table;
    protected $data;

    public function __construct(){
        $this->_db = \libs\Db::make();
    }

   /* === 钩子函数 === */
    protected function _before_write(){}        // 添加之前
    protected function _after_write(){}         // 添加之后
    protected function _after_update(){}        // 修改之前
    protected function _before_delete(){}       // 修改之后
    protected function _after_delete(){}        // 删除之后
   /* === 钩子函数 === */

    

    public function insert(){

        $this->_before_write();
        // var_dump($this->data);die;
        $keys = [];
        $values = [];
        $token = [];
        foreach($this->data as $k => $v){
            $keys[] = $k;
            $values[] = $v;
            $token[] = '?';
        }
        $keys = implode(',', $keys);
        $token = implode(',', $token); 
        $sql = "INSERT INTO {$this->table}($keys) VALUES($token)";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($values);
        $this->data['id'] = $this->_db->lastInsertId();

        $this->_after_write();  
        // die;
    }

    // 接收表单中的数据
    public function fill($data)
{
        // 判断是否在白名单中
        foreach($data as $k => $v)
        {
            if(!in_array($k, $this->fillable)){
                unset($data[$k]);
            }
        }
        $this->data = $data;
        // var_dump($data);die;
    }

    public function update($id){
        
        $this->_before_write();

        $set = [];
        $token = [];

        foreach($this->data as $k => $v){
            $set[] = "$k=?";
            $values[] = $v;
            $token[] = '?';
        }

        $set = implode(',', $set);
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET $set WHERE id=?";
        // var_dump($sql);die;
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($values);
        $this->_after_update();
        $this->_after_write();
    }

    public function delete($id){
        $goods_id = $id;
        $this->_before_delete();
        $stmt = $this->_db->prepare("DELETE FROM {$this->table} WHERE id=?");
        $stmt->execute([$id]);
        $this->_after_delete();
        // var_dump($goods_id);die;
    }

    public function findAll($options = []){
        $_option = [
            'fields' => '*',
            'where' => 1,
            'order_by' => 'id',
            'order_way' => 'desc',
            'per_page'=>20,
            'join'=>'',
            'groupby'=>'',
        ];

        // 合并用户的配置
        if($options){
            $_option = array_merge($_option, $options);
        }

        // 分页
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page-1)*$_option['per_page'];
        
        $sql = "SELECT {$_option['fields']}
                FROM {$this->table}
                {$_option['join']}
                WHERE {$_option['where']} 
                {$_option['groupby']}
                ORDER BY {$_option['order_by']} {$_option['order_way']} 
                LIMIT $offset,{$_option['per_page']}";

        $stmt = $this->_db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll( PDO::FETCH_ASSOC );

  
        // 获取总的记录数
        $stmt = $this->_db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$_option['where']}");
        $stmt->execute();
        $count = $stmt->fetch( PDO::FETCH_COLUMN );
        $pageCount = ceil($count/$_option['per_page']);

        $page_str = '';
        if($pageCount>1){
            for($i=1;$i<=$pageCount;$i++){
                $page_str .= '<a href="?page='.$i.'">'.$i.'</a> ';
            }
        }

        return [
            'data' => $data,
            'page' => $page_str,
        ];
    }

    // 查询所有
    public function getAll($sql, $data=[]){
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($data);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    // 查询一条
    public function getRow($sql, $data=[]){
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($data);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // 查询一个
    public function getOne($sql, $data=[]){
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($data);
        return $stmt->fetch(\PDO::FETCH_COLUMN);
    }


    // 递归树形结构的数据
    public function tree(){
        // 取出所有权限
        $data = $this->findAll([
            'per_page'=>999999
        ]);
        // 递归重新排序
        $ret = $this->_tree($data['data']);
        return $ret;
    }
    // 递归
    protected function _tree($data,$parent_id=0,$level=0){
        static $_ret = [];
        foreach($data as $v){
            if($v['parent_id'] == $parent_id){
                $v['level'] = $level;
                $_ret[] = $v;
                $this->_tree($data,$v['id'],$level+1);
            }
        }
        return $_ret;
    }
 


}