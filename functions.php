<?php

function formatPrice(float $vlprice) {
	return 'R$ ' . number_format($vlprice, 2, ',', '.');
}

function dd($array) {
	var_dump($array);
	die();
}

?>