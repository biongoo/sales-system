<?php
	$data = 'products';
	require_once 'include/header.php';
	$content .= '<div class="container px-4">';

	//////////////////// POSTS ////////////////////
	if(isset($_POST['insert']))
	{
		$product = $_POST['product'];
		if(preg_match('/^.../', $product)) 
		{
			$sql->prepare('INSERT INTO `products` (`name`) VALUES (?)')->execute([$product]);
			isset($_POST['test']);
			alert('Dodano produkt pomyślnie!');
		}
		else
			alert('Podano błędne dane. Pseudonim musi zawierać więcej niz 3 znaki!');
	}

	if(isset($_POST['product_delete']))
	{
		$product_delete = $_POST['product_to_delete'];
		if ($sql->prepare('DELETE FROM `products` WHERE `id`=?')->execute([$product_delete])) 
			alert('Usunięto produkt pomyślnie!');
		else 
			alert('Ups! Coś poszło nie tak!');
	}

	///////////////////////////////////////////////////////

		$content .= '<div class="row pt-3">'; // Wiersz nr1
		$products = products();
			
			$content .= '<div class="pl-0 col-sm">'; // Zawartosc nr1
				$content .= '<h3 class="media-heading">Dodawanie nowego produktu:</h3>';
				$content .= '<form method="post">';

					$content .= '<div class="form-group">';
						$content .= '<label for="product">Nazwa produktu:</label>';
						$content .= '<input type="text" id="product" name="product" class="form-control" required placeholder="Np. Pomidor">';
					$content .= '</div>';

					$content .= '<button type="submit" class="btn btn-primary" name="insert">Dodaj</button>';
				$content .= '</form>';
			$content .= '</div>';

			$content .= '<div class="pl-0 col-sm">'; // Zawartosc nr2
				$content .= '<h3 class="media-heading">Usuwanie produktu:</h3>';
				$content .= '<form method="post">';
					$content .= '<div class="form-group">';
						$content .= '<label for="product_to_delete">Produkt:</label>';
						$content .= '<select class="custom-select" name="product_to_delete" id="product_to_delete" required>';
							$content .= $products;
						$content .= '</select>';
					$content .= '</div>';
					$content .= '<button type="submit" class="btn btn-primary" name="product_delete">Usuń</button>';
				$content .= '</form>';
			$content .= '</div>';

		$content .= '</div>'; // Koniec wiersza nr1
		
		$content .= '<div class="row border-top my-4">'; // Wiersz nr2

			$content .= '<table class="table table-hover">';
				$content .= '<thead class="thead-dark">';
					$content .= '<tr>';
						$content .= '<th scope="col">Nazwa</th>';
					$content .= '</tr>';
				$content .= '</thead>';
				$content .= '<tbody>';
					$stmt = $sql->query('SELECT * FROM `products` ORDER BY `name`')->fetchAll();
					foreach ($stmt as $row) 
					{
						$content .= '<tr>';
							$content .= '<td>'.$row['name'].'</td>';
						$content .= '</tr>';
					}
				$content .= '</tbody>';
				
			$content .= '</table>';

		$content .= '</div>';

	$content .= '</div>'; // MAIN CONTAINER
				
	require_once 'include/footer.php';
?>