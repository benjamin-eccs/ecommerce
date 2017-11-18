<?php
session_start();
require_once "./vendor/autoload.php";

use Hcode\Model\Cart;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Page;
use \Slim\Slim;

//

$app = new Slim();

$app->config('debug', true);

require_once 'functions.php';
require_once 'rotas/admin.php';
require_once 'rotas/categories.php';
require_once 'rotas/forgot.php';
require_once 'rotas/products.php';
require_once 'rotas/user.php';

$app->get('/', function () {
	$products = Product::listAll();

	$page = new Page();
	$page->setTpl("index", [
		"products" => Product::checkList($products),
		"name" => "benjamin",
		"ano" => 2017,
	]);
});

$app->get('/categories/:idcategory', function ($idcategory) {
	$page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
	$category = new Category();
	$category->get((int) $idcategory);

	$pagination = $category->getProductsPage($page);
	$pages = [];
	for ($i = 1; $i <= $pagination['pages']; $i++) {
		array_push($pages, [
			//"link" => "/categories/{$category->getidcategory()}?page={$i}",
			'link' => '/categories/' . $category->getidcategory() . '?page=' . $i,
			//'link' => '/categories/' . $category->getidcategory() . '?page=' . $i,
			"page" => $i,
		]);
	}
	//dump($pages);

	$page = new Page();
	$page->setTpl("category", [
		"category" => $category->getValues(),
		"products" => $pagination["data"],
		"pages" => $pages,
	]);
});

$app->get("/products/:desurl", function ($desurl) {
	$product = new Product();
	$product->getFromURL($desurl);

	$page = new Page();
	$page->setTpl("product-detail", [
		'product' => $product->getValues(),
		'categories' => $product->getCategory(),
	]);
});

$app->get("/cart", function () {
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl("cart", [
		"cart" => $cart->getValues(),
		"products" => $cart->getProduct(),
		"error" => "",
	]);
});

$app->get("/cart/:idproduct/add", function ($idproduct) {
	$product = new Product();
	$product->get((int) $idproduct);
	//dd($product);
	$cart = Cart::getFromSession();
	$qtd = (isset($_GET['qtd'])) ? (int) $_GET['qtd'] : 1;
	for ($i = 1; $i <= $qtd; $i++) {
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/minus", function ($idproduct) {
	$product = new Product();
	$product->get((int) $idproduct);
	//dd($product);
	$cart = Cart::getFromSession();
	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/remove", function ($idproduct) {
	$product = new Product();
	$product->get((int) $idproduct);
	// dd($product);
	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
});

$app->run();

?>