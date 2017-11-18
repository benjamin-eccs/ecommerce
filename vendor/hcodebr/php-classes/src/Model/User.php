<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Mailer;
use Hcode\Model;

/**
 *
 */
class User extends Model {
	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";

	public static function getFromSession() {
		$user = new User();
		if (isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]['iduser'] > 0) {
			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;
	}

	public static function checkLogin($inadmin = true) {
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int) $_SESSION[User::SESSION]["iduser"] > 0
		) {
			//não está logado
			return false;
		} else {
			if ($inadmin === true && (bool) $_SESSION[User::SESSION]['inadmin'] === true) {
				return true;
			} elseif ($inadmin === false) {
				return true;
			} else {
				return false;
			}
		}
	}

	public static function login($login, $password) {
		$sql = new Sql();
		$results = $sql->select("select * from tb_users where deslogin =:LOGIN", [
			":LOGIN" => $login,
		]);
		if (count($results) === 0) {
			throw new \Exception("Login ou senha incorretos");
		}
		$data = $results[0];
		if (password_verify($password, $data["despassword"])) {
			$user = new User();
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		} else {
			throw new \Exception("Login ou senha incorretos");
		}

	}

	public static function verifyLogin($inadmin = true) {
		if (!User::checkLogin($inadmin)) {
			header("Location: /admin/login");
			exit;
		}
	}

	public static function listAll() {
		$sql = new Sql();
		$query = "SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY a.iduser";
		return $sql->select($query);
	}

	public function save() {
		$sql = new Sql();

		$query = "CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)";
		$pass = password_hash($this->getdespassword(), PASSWORD_DEFAULT, [
			"cost" => 12,
		]);
		$results = $sql->select($query, [
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $pass,
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin(),
		]);
		$this->setData($results[0]);
	}

	public function get($iduser) {
		$sql = new Sql();
		$query = "SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser";
		$results = $sql->select($query, [
			":iduser" => $iduser,
		]);
		$this->setData($results[0]);
	}

	public function update() {
		$sql = new Sql();

		$query = "CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)";
		$results = $sql->select($query, [
			":iduser" => $this->getiduser(),
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $this->getdespassword(),
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin(),
		]);
		$this->setData($results[0]);
	}

	public function delete() {
		$sql = new Sql();
		$query = "CALL sp_users_delete(:iduser)";
		$sql->query($query, [
			":iduser" => $this->getiduser(),
		]);
	}

	public static function getForgot($email) {
		$sql = new Sql();
		$query = "SELECT * FROM tb_persons a
					inner join tb_users b using(idperson)
					where a.desemail = :email";
		$results = $sql->select($query, [
			":email" => $email,
		]);
		if (count($results) === 0) {
			throw new \Exception("Não foi possivel recuperar a senha.");
		} else {
			$data = $results[0];
			$eQuery = "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)";
			$results2 = $sql->select($eQuery, [
				":iduser" => $data["iduser"],
				":desip" => $_SERVER["REMOTE_ADDR"],
			]);
			if (count($results2) === 0) {
				throw new \Exception("Não foi possivel recuperar a senha.");
			} else {
				$dataRecovery = $results2[0];
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				//$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				$link = "http://ecommerce.dev/admin/forgot/reset?code=$code";

				$mailer = new Mailer($data["desemail"], $data["desperson"], "Recuperação de senha", "forgot", [
					"name" => $data["desperson"],
					"link" => $link,
				]);
				$mailer->send();
				return $data;

			}

		}

	}

	public static function validForgotDecrypt($code) {

		//$idRecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
		$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
		//echo $idRecovery . "--" . $idrecovery;
		$sql = new Sql();
		$query = "SELECT * FROM tb_userspasswordsrecoveries a
				INNER JOIN tb_users b USING(iduser)
				INNER JOIN tb_persons c USING(idperson)
				WHERE a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR)>= now()";
		$results = $sql->select($query, [
			":idrecovery" => $idrecovery,
		]);
		//dump($results);
		if (count($results) === 0) {
			throw new \Exception("Não foi possivel redefinir a senha.");
		} else {

			return $results[0];
		}
	}

	public static function setForgotUser($idrecovery) {
		$sql = new Sql();
		$query = "UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery";
		$sql->query($query, [
			":idrecovery" => $idrecovery,
		]);

	}

	public function setPassword($password) {
		$sql = new Sql();
		$query = "UPDATE tb_users SET despassword = :password WHERE iduser = :iduser";
		$pass = password_hash($password, PASSWORD_DEFAULT, [
			"cost" => 12,
		]);
		$sql->query($query, [
			":password" => $pass,
			":iduser" => $this->getiduser(),
		]);
	}

	public static function logout() {
		$_SESSION[User::SESSION] = NULL;
	}

}

?>