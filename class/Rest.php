<?php
  /**
  *created by vitas zhuo
  *user:myself
  *date:2019-4-24
  *Time:20:56
  */

  class Rest
  {
    /**
    *@var User
    */
    private $_user;

    /**
    *@var Article
    */
    private $_article;

    /**
    *请求方法
    *@var
    */
    private $_requestMethod;

    /**
    *请求资源
    *@var
    */
    private $_requestResource;

    /**
    *允许请求的资源
    */
    private $_allowResource=['users','articles'];
    /**
    *允许请求的方法
    */
    private $_allowMethod=['GET','POST','PUT','DELETE'];

    /**
    *版本号
    */
    private $_version;

    /**
    *资源标识
    */
    private $_requestUri;
    /**
    *常见状态码
    */
    private $_statusCode=[
      200=>'OK',
      204=>'No Content',
      400=>'Bad Request',
      403=>'Forbidden',
      404=>'Not Found',
      405=>'Method Not Allow',
      500=>'Server Interior Error'
    ];


    /**
    *Rest constructor
    *@param User $_user
    *@param Article  $_article
    */

    public function __construct(user $_user,Article $_article)
    {
      $this->_user = $_user;
      $this->_article = $_article;
    }

    /**
    *api启动方法
    */
    public function run()
    {

      try{
        $this->setMethod();
        $this->setResource();
        if($this->_requestResource=='users')
        {
          $this->sendUsers();
        }else if($this->_requestResource=='articles'){
          $this->sendArticles();
        }
      }catch(Exception $e)
      {
        $this->_json($e->getMessage(),$e->getCode());
      }

    }

    /**
    *处理用户逻辑
    */
    private function sendUsers()
    {
      if($this->_requestMethod!=="POST")
      {
        throw new Exception("请求方法不被允许",405);
      }
      if(empty($this->_requestUri[0]))
      {
        throw new Exception("请求参数缺失",400);
      }else if($this->_requestUri[0]=='login')
      {
        $this->dologin();
      }else if($this->_requestUri[0]=='register')
      {
        $this->doregister();
      }
      else{
        throw new Exception("请求资源不被允许",405);
      }
    }


    /**
    *用户注册接口
    */
    private function doregister()
    {
      $data = $this->getBody();
      if(empty($data['name']))
      {
        throw new Exception("用户名不能为空",400);
      }
      if(empty($data['password']))
      {
        throw new Exception("用户密码不能为空",400);
      }
      $user = $this->_user->register($data['name'],$data['password']);
      if($user)
      {
        $this->_json("注册成功",200);
      }
    }

    private function dologin()
    {
      $data = $this->getBody();
      if(empty($data['name']))
      {
        throw new Exception('用户名不能为空',400);
      }
      if(empty($data['password']))
      {
        throw new Exception('用户密码不能为空',400);
      }
      $user =  $this->_user->login($data['name'],$data['password']);
      $data = [
        'data'=>[
          'user_id'=>$user['id'],
          'name'=>$user['username'],
          'token'=> session_id()
        ],
        'message'=>'登录成功',
        'code'=>200
      ];
      $_SESSION['userInfo'] = $data['data'];
      echo json_encode($data);
    }

    private function getBody()
    {
      $data = file_get_contents("php://input");
      if(empty($data))
      {
        throw new Exception("请求参数错误",400);
      }
      return json_decode($data,true);
    }
    /**
    *处理文章逻辑
    */
    private function sendArticles()
    {
      switch($this->_requestMethod)
      {
        case "POST":
          return $this->articleCreate();
        case "PUT":
          return $this->articleEdit();
        case "DELETE":
          return $this->articleDel();
        case "GET":
          if($this->_requestUri[0] == 'list'){
            return $this->articleList();
          }else if($this->_requestUri[0] == 'view'&&$this->_requestUri[1]>0){
            return $this->articleView();
          }else{
            throw new Exception("请求资源不合法",405);
          }
        default:
          throw new Exception("请求方法不合法",405);
      }
    }

    /**
    *文章创建
    */
    private function articleCreate()
    {
      $data = $this->getBody();
      if(empty($data['title']))
      {
        throw new Exception('文章的标题不能为空',400);
      }
      if(empty($data['content']))
      {
        throw new Exception('文章的内容不能为空',400);
      }

      if(!$this->isLogin($data['token']))
      {
        throw new Exception("请重新登录",403);
      }
      $user_id = $_SESSION['userInfo']['user_id'];
      $return = $this->_article->create($data['title'],$data['content'],$user_id);
      if(!empty($return))
      {
        $this->_json("文章发表成功",200);
      }
    }

    /**
    *判断用户是否登录
    */
    private function isLogin($token)
    {
      $sessionID = session_id();
      if($sessionID!=$token)
      {
        return false;
      }
      return true;
    }

    /**
    *文章修改API
    */
    private function articleEdit()
    {

      $data = $this->getBody();

      if(!$this->isLogin($data['token']))
      {
        throw new Exception("请重新登录",403);
      }
      $article = $this->_article->view($this->_requestUri[1]);
      if($article['user_id']!=$_SESSION['userInfo']['user_id'])
      {
        throw new Exception("你无权修改此文章",403);
      }
      $return = $this->_article->edit($this->_requestUri[1],$data['title'],$data['content'],$_SESSION['userInfo']['user_id']);
      if($return)
      {
        $data = [
          'data'=>[
            'title'=>$data['title'],
            'content'=>$data['content'],
            'user_id'=> $_SESSION['userInfo']['user_id'],
            'create_time'=>$article['create_time']
          ],
          'message'=>'文章修改成功',
          'code'=>200
        ];
        echo json_encode($data);
        die;
      }
      $data = [
        'data'=>$article,
        'message'=>'文章修改失败',
        'code'=>500
      ];
      echo json_encode($data);
      die;
    }

    /**
    *文章删除API
    */
    private function articleDel()
    {
      if(!$article_id = $this->_requestUri[0])
      {
        throw new Exception('请求资源不被允许',405);
      }
      $data = $this->getBody();
      if(!$this->isLogin($data['token']))
      {
        throw new Exception("请重新登录",403);
      }
      $article = $this->_article->view($article_id);
      if($article['user_id']!=$_SESSION['userInfo']['user_id'])
      {
        throw new Exception("你无权修改此文章",403);
      }

      $return = $this->_article->delete($article_id,$_SESSION['userInfo']['user_id']);
      if($return)
      {
        $this->_json("删除文章成功",200);
      }
      $this->_json("删除文章失败",500);
    }

    /**
    *设置请求方法
    */
    private function setMethod()
    {
      $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
      if(!in_array($this->_requestMethod,$this->_allowMethod))
      {
        throw new Exception('请求方法不被允许',405);
      }
    }

    /**
    *处理资源
    */
    private function setResource()
    {
      if(!empty($_SERVER['PATH_INFO']))
      {
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/',$path);

        $this->_version = $params['1'];

        $this->_requestResource = $params['2'];
        if(!in_array($this->_requestResource,$this->_allowResource))
        {
          throw new Exception('请求资源不被允许',405);
        }
        if(!empty($params['3']))
        {
          $nums = count($params);
          for ($i=3;$i<$nums;$i++) {
            $this->_requestUri[] = $params[$i];
          }
        }

      }else{
        throw new Exception('请求资源不被允许',405);
      }
    }

    /**
    *数据输出
    *@param $message string 提示信息
    *@param $code int 状态码
    */
    private function _json($message,$code)
    {
      if($code!==200&&$code>200)
      {
        header('HTTP/1.1'.$code.' '.$this->_statusCode[$code]);
      }
      header("Content-Type:application/json;charset:utf-8");
      if(!empty($message))
      {
        echo json_encode(['message'=>$message,'code'=>$code]);
      }
      die;
    }

  }
