<?php
include('logicPHP/Auth/Captcha.php');
include('logicPHP/Auth/checkConfig.php');
include('logicPHP/Auth/checkBan.php');
include('logicPHP/Auth/AdminBtn.php');
include('logicPHP/Logs/Log.php');
include('logicPHP/Account/Register.php');
include('logicPHP/Account/Login.php');
include('logicPHP/Handle/Post.php');
include('logicPHP/Handle/deletePost.php');
include('logicPHP/Pagination/Pagination.php');
include('logicPHP/Pagination/PaginationBtn.php');
include('logicPHP/Account/Logout.php');
include('logicPHP/Auth/TitleName.php');
include('LogicPHP/Auth/Notify.php');
include('LogicPHP/Auth/GmailAuth.php');
include('LogicPHP/IFrame/RenderIFrame.php');
include($_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/admin/logicPHP/Check2FA.php');
include($_SERVER['DOCUMENT_ROOT'] . '/app/_USERS_LOGIC/view/logicPHP/Post/FileHandle.php');
?>