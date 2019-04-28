<?php
  /**
  *created by vitas zhuo
  *user:myself
  *date:2019-4-24
  *Time:20:56
  */

require_once __DIR__.'/DefError.php';

class Article
{
  /**
  *数据库操作对象
  *@var PDO
  */
  private $_db;
  public function __construct(PDO $_db)
  {
    $this->_db = $_db;
  }

  /**
  *文章发表
  *@param $title string 文章标题
  *@param $content string 文章内容
  *@param $user_id string 用户ID
  *@return array 文章添加成功的返回信息
  *@throws Exception
  */

  public function create($title,$content,$user_id)
  {
    if(empty($title))
    {
      throw new Exception("文章的标题不能为空",DefError::ARTICLE_TITLE_CONNOT_NULL);
    }
    if(empty($content))
    {
      throw new Exception("文章的内容不能为空",DefError::ARTICLE_CONTENT_CONNOT_NULL);
    }
    $sql = " insert into `article`( `title`,`content`,`user_id`,`create_time`) values(:title,:content,:user_id,:create_time)";
    $create_time = time();
    $sm = $this->_db->prepare($sql);

    $sm->bindParam(':title',$title);
    $sm->bindParam(':content',$content);
    $sm->bindParam(':user_id',$user_id);
    $sm->bindParam(':create_time',$create_time);
    if(!$sm->execute())
    {
      var_dump($sm->errorInfo());
      throw new Exception("发表文章失败",DefError::ARTICLE_CREATE_FAIL);
    }

    return [
      'title'=>$title,
      'content'=>$content,
      'article_id'=>$this->_db->lastInsertId(),
      'create_time'=>$create_time,
      'user_id' => $user_id
    ];

  }

  /**
  *查看文章
  *@param $article_id int 文章编号
  *@return array 文章添加成功的返回信息
  *@throws Exception
  */
  public function view($article_id)
  {
    if(empty($article_id))
    {
      throw new Exception("文章的编号不能为空",DefError::ARTICLE_ID_CONNOT_NULL);
    }


    $sql = " select * from `article` where id =:id ";
    $sm = $this->_db->prepare($sql);

    $sm->bindParam(':id',$article_id);
    if(!$sm->execute())
    {
      var_dump($sm->errorInfo());
      throw new Exception("获取文章失败",DefError::ARTICLE_GET_FAIL);
    }

    $article = $sm->fetch(PDO::FETCH_ASSOC);
    if(empty($article))
    {
      throw new Exception("文章不存在",DefError::ARTICLE_NOT_EXISTS);
    }

    return $article;
  }

  /**
  *编辑文章
  *@param $article_id int 文章的ID
  *@param $title string 文章标题
  *@param $content string 用户内容
  *@throws Exception
  */
  public function edit($article_id,$title,$content,$user_id)
  {
    $article = $this->view($article_id);
    if((int)$user_id!==(int)$article['user_id'])
    {
      throw new Exception("你无权修改此文章",DefError::PREMISSION_NOT_ALLOW);
    }
    $title = empty($title)?$article['title']:$title;
    $content = empty($content)?$article['content']:$content;
    if($title==$article['title']&&$content==$article['content'])
    {
      return $article;
    }
    $sql = "update `article` set `title` = :title, `content`= :content where `id`= :id";
    $sm = $this->_db->prepare($sql);

    $sm->bindParam(':id',$article_id);
    $sm->bindParam(':title',$title);
    $sm->bindParam(':content',$content);
    if(!$sm->execute())
    {
      var_dump($sm->errorInfo());
      throw new Exception("修改文章失败",DefError::ARTICLE_UPDATE_FAIL);
    }

    return [
      'id'=>$article_id,
      'title'=>$title,
      'content'=>$content,
      'user_id' => $user_id
    ];
  }

  public function delete($article_id,$user_id)
  {
    $article = $this->view($article_id);
    if((int)$user_id!==(int)$article['user_id'])
    {
      throw new Exception("你无权删除此文章",DefError::PREMISSION_NOT_ALLOW);
    }

    $sql = "delete from `article` where id= :id ";
    $sm = $this->_db->prepare($sql);
    $sm->bindParam(':id',$article_id);
    if(!$re=$sm->execute())
    {
      var_dump($sm->errorInfo());
      throw new Exception("删除文章失败",DefError::ARTICLE_UPDATE_FAIL);
    }
    // echo "已删除".$sm->rowCount()."行";
    return $re;
  }

  public function _list()
  {

  }

}
