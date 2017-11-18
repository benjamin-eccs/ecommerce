<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Model\Product;

/**
 *
 */
class Category extends Model {

	public static function listAll() {
		$sql = new Sql();
		$query = "SELECT * FROM tb_categories ORDER BY idcategory";
		return $sql->select($query);
	}

	public function save() {
		$sql = new Sql();

		$query = "CALL sp_categories_save(:idcategory, :descategory)";

		$results = $sql->select($query, [
			":idcategory" => $this->getidcategory(),
			":descategory" => $this->getdescategory(),
		]);
		$this->setData($results[0]);
		Category::updateFile();
	}

	public function get($idcategory) {
		$sql = new Sql();
		$query = "SELECT * FROM tb_categories WHERE idcategory = :idcategory";
		$results = $sql->select($query, [
			":idcategory" => $idcategory,
		]);
		$this->setData($results[0]);
	}

	public function delete() {
		$sql = new Sql();
		$query = "DELETE FROM tb_categories WHERE idcategory = :idcategory";
		$results = $sql->query($query, [
			":idcategory" => $this->getidcategory(),
		]);
		Category::updateFile();
	}

	public static function updateFile() {
		$category = Category::listAll();
		$html = [];

		foreach ($category as $row) {
			array_push($html, '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>');
		}
		$filename = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html";
		file_put_contents($filename, implode("", $html));
	}

	public function getProducts($related = true) {
		$sql = new Sql();
		if ($related === true) {
			return $sql->select("select * from tb_products where idproduct in(
					SELECT a.idproduct
				    FROM tb_products a
					inner join tb_productscategories b
					on a.idproduct = b.idproduct
					where b.idcategory= :idcategory
				    );", [
				":idcategory" => $this->getidcategory(),
			]);
		} else {
			return $sql->select("select * from tb_products where idproduct not in(
					SELECT a.idproduct
				    FROM tb_products a
					inner join tb_productscategories b
					on a.idproduct = b.idproduct
					where b.idcategory=:idcategory
				    );", [
				":idcategory" => $this->getidcategory(),
			]);
		}
	}

	public function getProductsPage($page = 1, $itemsPerPage = 4) {
		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();
		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * FROM tb_products a
			INNER JOIN tb_productscategories b ON
			a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON
			c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			 LIMIT $start, $itemsPerPage", [
			':idcategory' => $this->getidcategory(),
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");
		return [
			'data' => Product::checkList($results),
			'total' => (int) $resultTotal[0]["nrtotal"],
			'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage),
		];
	}

	public function addProduct(Product $product) {
		$sql = new Sql();
		$query = "INSERT INTO tb_productscategories(idcategory, idproduct) VALUES (:idcategory, :idproduct)";
		$sql->query($query, [
			':idcategory' => $this->getidcategory(),
			':idproduct' => $product->getidproduct(),
		]);
	}

	public function removeProduct($product) {
		$sql = new Sql();
		$query = "DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct";
		$sql->query($query, [
			':idcategory' => $this->getidcategory(),
			':idproduct' => $product->getidproduct(),
		]);
	}
}

?>