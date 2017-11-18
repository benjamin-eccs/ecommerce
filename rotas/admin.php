<?php

use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin', function () {
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("index");
});

$app->get('/admin/login', function () {
	$page = new PageAdmin([
		"header" => false,
		"footer" => false,
	]);
	$page->setTpl("login");
});

$app->post('/admin/login', function () {
	User::login($_POST['deslogin'], $_POST['despassword']);
	header("location: /admin");
	exit();
});

$app->get('/admin/logout', function () {
	User::logout();
	header("Location: /");
	exit;
});
?>