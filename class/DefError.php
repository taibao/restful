<?php
  /**
  *created by vitas zhuo
  *user:myself
  *date:2019-4-24
  *Time:20:56
  */
 class DefError
{
  //用户模块
  const USERNAME_CONNOT_NULL = 001;
  const USERPASS_CONNOT_NULL = 002;
  const USERNAME_EXISTS = 003;
  const USERNAME_NOT_EXISTS = 004;
  const REGSITER_FAIL = 005;
  const LOGIN_FAIL = 006;
  const USERPASS_ERROR = 006;

  //文章模块
  const ARTICLE_TITLE_CONNOT_NULL = 101;
  const ARTICLE_CONTENT_CONNOT_NULL = 102;
  const ARTICLE_CREATE_FAIL = 103;
  const ARTICLE_ID_CONNOT_NULL = 104;
  const ARTICLE_GET_FAIL = 105;
  const ARTICLE_NOT_EXISTS = 106;
  const PREMISSION_NOT_ALLOW = 107;
  const ARTICLE_UPDATE_FAIL = 108;


}
