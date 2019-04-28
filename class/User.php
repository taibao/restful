<?php
  /**
  *created by vitas zhuo
  *user:myself
  *date:2019-4-24
  *Time:20:56
  */
require_once __DIR__."/DefError.php";
class User
{
    //保存数据连接对象
    private $_db;
    public function __construct(PDO $_db)
    {
      $this->_db=$_db;
    }
    /**
    *用户注册
    *@param $username string 用户名
    *@param $password string 用户密码
    *@return array
    *@throws Exception
    */
    public function register($username,$password)
    {
      if(empty($username))
      {
        throw new Exception("用户名不能为空",DefError::USERNAME_CONNOT_NULL);
      }
      if(empty($password))
      {
        throw new Exception("用户密码不能为空",DefError::USERPASS_CONNOT_NULL);
      }
      if($this->_isUsernameExists($username))
      {
        throw new Exception("用户名已存在",DefError::USERNAME_EXISTS);
      }

      $sql = " insert into `user`( `username`,`password`,`create_time`) values(:username,:password,:create_time)";
      $create_time = time();

      $sm = $this->_db->prepare($sql);

      $password = $this->_md5($password);
      $sm->bindParam(':username',$username);
      $sm->bindParam(':password',$password);
      $sm->bindParam(':create_time',$create_time);
      if(!$sm->execute())
      {
        var_dump($sm->DefErrorInfo());
        throw new Exception("注册失败",DefError::REGSITER_FAIL);
      }

      return [
        'username'=>$username,
        'user_id'=>$this->_db->lastInsertId(),
        'create_time'=>$create_time
      ];
    }

    /**
    *用户登录
    *@param $username string 用户名
    *@param $password string 用户密码
    *@return array
    *@throws Exception
    */
    public function login($username,$password)
    {
      if(empty($username))
      {
        throw new Exception("用户名不能为空",DefError::USERNAME_CONNOT_NULL);
      }
      if(empty($password))
      {
        throw new Exception("用户密码不能为空",DefError::USERPASS_CONNOT_NULL);
      }
      if(!$this->_isUsernameExists($username))
      {
        throw new Exception("用户不存在",DefError::USERNAME_EXISTS);
      }

      $sql = " select * from `user` where `username`= :username and  `password`=:password ";
      $sm = $this->_db->prepare($sql);

      $password = $this->_md5($password);
      $sm->bindParam(':username',$username);
      $sm->bindParam(':password',$password);
      if(!$sm->execute())
      {
        var_dump($sm->DefErrorInfo());
        throw new Exception("登录失败",DefError::LOGIN_FAIL);
      }
      $re = $sm->fetch(PDO::FETCH_ASSOC);
      if(!$re)
      {
        throw new Exception("用户密码错误",DefError::USERPASS_ERROR);
      }
      return $re;
    }


    private function _md5($pass)
    {
      return md5($pass.SALT);
    }

    private  function _isUsernameExists($username)
    {
      $sql="select * from `user` where `username`= :username ";
      $sm=$this->_db->prepare($sql);
      $sm->bindParam(":username",$username);
      $sm->execute();
      $re=$sm->fetch(PDO::FETCH_ASSOC);
      return  !empty($re);
    }


}
