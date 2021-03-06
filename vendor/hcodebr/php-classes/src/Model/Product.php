<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

/**
 *
 */
class Product extends Model {

	public static function listAll() {
		$sql = new Sql();
		$query = "SELECT * FROM tb_products ORDER BY idproduct";
		return $sql->select($query);
	}

	public static function checkList($list) {
		foreach ($list as &$row) {
			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}
		return $list;
	}

	public function save() {

		$sql = new Sql();

		$query = "CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)";

		$results = $sql->select($query, [
			":idproduct" => $this->getidproduct(),
			":desproduct" => $this->getdesproduct(),
			":vlprice" => $this->getvlprice(),
			":vlwidth" => $this->getvlwidth(),
			":vlheight" => $this->getvlheight(),
			":vllength" => $this->getvllength(),
			":vlweight" => $this->getvlweight(),
			":desurl" => $this->getdesurl(),
		]);
		//dump($results[0]);

		$this->setData($results[0]);
	}

	public function get($idproduct) {
		$sql = new Sql();
		$query = "SELECT * FROM tb_products WHERE idproduct = :idproduct";
		$results = $sql->select($query, [
			":idproduct" => $idproduct,
		]);
		$this->setData($results[0]);
	}

	public function delete() {
		$sql = new Sql();
		$query = "DELETE FROM tb_products WHERE idproduct = :idproduct";
		$results = $sql->query($query, [
			":idproduct" => $this->getidproduct(),
		]);
	}

	public function checkPhoto() {

		if (file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg"
		)) {

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";

		} else {

			$url = "/res/site/img/products/padrao.jpg";

		}

		return $this->setdesphoto($url);

	}

	public function getValues() {
		$this->checkPhoto();
		$values = parent::getValues();

		return $values;
	}

	public function setPhoto($file) {
		$extension = explode('.', $file['name']);
		//dump($extension);
		$extension = end($extension);
		switch ($extension) {
		case 'jpg':
		case 'jpeg':
			$image = imagecreatefromjpeg($file['tmp_name']);
			break;
		case 'gif':
			$image = imagecreatefromgif($file['tmp_name']);
			break;
		case 'png':
			$image = imagecreatefrompng($file['tmp_name']);
			break;
		}
		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
		"res" . DIRECTORY_SEPARATOR .
		"site" . DIRECTORY_SEPARATOR .
		"img" . DIRECTORY_SEPARATOR .
		"products" . DIRECTORY_SEPARATOR .
		$this->getidproduct() . ".jpg";
		imagejpeg($image, $dist);
		imagedestroy($image);
		$this->checkPhoto();
	}

	public function getFromURL($desurl) {
		$query = "SELECT * FROM tb_products WHERE desurl = :desurl";
		$sql = new Sql();
		$rows = $sql->select($query, [
			":desurl" => $desurl,
		]);
		$this->setData($rows[0]);
	}

	public function getCategory() {
		$query = "SELECT * FROM tb_categories a INNER JOIN tb_productscategories
		b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct";
		$sql = new Sql();
		return $sql->select($query, [
			":idproduct" => $this->getidproduct(),
		]);
	}

}

?>