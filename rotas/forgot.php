<?php

use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin/forgot', function () {
	$page = new PageAdmin([
		"header" => false,
		"footer" => false,
	]);
	$page->setTpl("forgot");
});

$app->post("/admin/forgot", function () {

	User::getForgot($_POST["email"]);
	header("Location: /admin/forgot/sent");
	exit;
});

$app->get("/admin/forgot/sent", function () {
	$page = new PageAdmin([
		"header" => false,
		"footer" => false,
	]);
	$page->setTpl("forgot-sent");
});

$app->get("/admin/forgot/reset", function () {
	$user = User::validForgotDecrypt($_GET["code"]);
	$page = new PageAdmin([
		"header" => false,
		"footer" => false,
	]);

	$page->setTpl("forgot-reset", [
		"name" => $user["desperson"],
		"code" => $_GET["code"],
	]);
});

$app->post("/admin/forgot/reset", function () {
	$forgot = User::validForgotDecrypt($_POST["code"]);
	User::setForgotUser($forgot["idrecovery"]);
	$user = new User();
	$user->get((int) $forgot["iduser"]);
	$user->setPassword($_POST["password"]);
	$page = new PageAdmin([
		"header" => false,
		"footer" => false,
	]);
	$page->setTpl("forgot-reset-success");
});

?>