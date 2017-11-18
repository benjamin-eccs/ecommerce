SELECT * FROM tb_productscategories;

SELECT SQL_CALC_FOUND_ROWS * FROM tb_products a
INNER JOIN tb_productscategories b ON
a.idproduct = b.idproduct
INNER JOIN tb_categories c ON
c.idcategory = b.idcategory 
WHERE c.idcategory = 3 LIMIT 0,5;

SELECT FOUND_rows() AS nrtotal;

select * from tb_products where idproduct in(
	SELECT a.idproduct
    FROM tb_products a
	inner join tb_productscategories b
	on a.idproduct = b.idproduct
	where b.idcategory=6
    );
    
select * from tb_products where idproduct not in(
	SELECT a.idproduct
    FROM tb_products a
	inner join tb_productscategories b
	on a.idproduct = b.idproduct
	where b.idcategory=4
    );
    
SELECT * FROM tb_products WHERE desurl = 'smartphone-samsung-galaxy-j5';


