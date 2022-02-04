<?php
	$data = 'clients';
	require_once 'include/header.php';
	$content .= '<div class="container px-4">';

	//////////////////// POSTS ////////////////////
	if(isset($_POST['insert']))
	{
		$client = $_POST['client'];
		if(preg_match('/^.../', $client)) 
		{
			$sql->prepare('INSERT INTO `clients` (`nick`) VALUES (?)')->execute([$client]);
			alert('Dodano klienta pomyślnie!');
		}
		else
			alert('Podano błędne dane. Pseudonim musi zawierać więcej niz 3 znaki!');
	}
	
	if(isset($_POST['symbol_add']))
	{
		if($_POST['id_client'])
		{
			$id_client = $_POST['id_client'];
			if($id_client == 48)
				alert('Nie można dodać symbolu!');
			$symbol = $_POST['symbol'];
			if(preg_match('/^./', $symbol)) 
			{
				$stmt = $sql->prepare('SELECT `symbols` FROM `clients` WHERE `id`=?'); $stmt->execute([$id_client]); $symbols = $stmt->fetchColumn();
				$data_s = json_decode($symbols);
				$data_s[] = $symbol;
				$data_s = json_encode($data_s);
				$sql->prepare('UPDATE `clients` SET `symbols`=? WHERE `id`=?')->execute([$data_s,$id_client]);
				alert('Dodano symbol pomyślnie!');
			}
			else
				alert('Symbol musi zawierać przynajmniej 1 znak!');
		}
		else
			alert('Wybierz klienta!');
	}

	if(isset($_POST['client_delete']))
	{
		$client_to_delete = $_POST['client_to_delete'];
		if($client_to_delete == 48)
				alert('Nie można usunąć tego klienta!');
		if ($sql->prepare('DELETE FROM `clients` WHERE `id`=?')->execute([$client_to_delete])) 
			alert('Usunięto klienta pomyślnie!');
		else 
			alert('Ups! Coś poszło nie tak!');
	}

	if(isset($_POST['symbol_delete']))
	{
		if($_POST['id_client'] && $_POST['list2'])
		{
			$id_client = $_POST['id_client'];
			$symbol_to_delete = $_POST['list2'];
			$stmt = $sql->prepare('SELECT `symbols` FROM `clients` WHERE `id`=?'); $stmt->execute([$id_client]); $symbols = $stmt->fetchColumn();
			$data_s = json_decode($symbols);
			if (($key = array_search($symbol_to_delete, $data_s)) !== false) {
				unset($data_s[$key]);
			}
			$data_s = array_values($data_s);
			$data_s = json_encode($data_s);
			$sql->prepare('UPDATE `clients` SET `symbols`=? WHERE `id`=?')->execute([$data_s,$id_client]);
			alert('Usunięto symbol pomyślnie!');
		}
		else
			alert('Wybierz klienta oraz jego symbol!');
	}
	///////////////////////////////////////////////////////

		$content .= '<div class="row">'; // Wiersz nr1
		$clients = clients();
			
			$content .= '<div class="pt-3 pl-0 col-sm">'; // Zawartosc nr1
				$content .= '<h3 class="media-heading">Dodawanie nowego Klienta:</h3>';
				$content .= '<form method="post">';
					$content .= '<div class="form-group">';
						$content .= '<label for="client">Pseudonim:</label>';
						$content .= '<input type="text" id="client" name="client" class="form-control" required placeholder="Np. Żwirek">';
					$content .= '</div>';
					$content .= '<button type="submit" class="btn btn-primary" name="insert">Dodaj</button>';
				$content .= '</form>';
			$content .= '</div>';

			$content .= '<div class="pt-3 pl-0 col-sm">'; // Zawartosc nr2
				$content .= '<h3 class="media-heading">Usuwanie Klienta:</h3>';
				$content .= '<form method="post">';
					$content .= '<div class="form-group">';
						$content .= '<label for="client_to_delete">Klient:</label>';
						$content .= '<select class="custom-select" name="client_to_delete" id="client_to_delete" required>';
							$content .= $clients;
						$content .= '</select>';
					$content .= '</div>';
					$content .= '<button type="submit" class="btn btn-primary" name="client_delete">Usuń</button>';
				$content .= '</form>';
			$content .= '</div>';

		$content .= '</div>'; // Koniec wiersza nr1

		$content .= '<div class="row border-top my-4">';// Wiersz nr2

			$content .= '<div class="pt-3 pl-0 col-sm">'; // Zawartosc nr3
				$content .= '<h3>Dodawanie nowego symbolu:</h3>';
				$content .= '<form method="post">';
					$content .= '<div class="form-group">';
						$content .= '<label for="id_client">Klient:</label>';
						$content .= '<select class="custom-select" name="id_client" id="id_client" required>';
							$content .= $clients;
						$content .= '</select>';
					$content .= '</div>';
					$content .= '<div class="form-group">';
						$content .= '<label for="symbols">Symbol:</label>';
						$content .= '<input type="text" name="symbol" id="symbol" class="form-control" required placeholder="Np. S1/3">';
					$content .= '</div>';
					$content .= '<button type="submit" class="btn btn-primary" name="symbol_add">Dodaj</button>';
				$content .= '</form>';
			$content .= '</div>';

			$content .= '<div class="pt-3 pl-0 col-sm">'; // Zawartosc nr4
				$content .= '<h3 class="media-heading">Usuwanie symbolu:</h3>';
				$content .= '<form method="post">';
					$content .= '<div class="form-group">';
						$content .= '<label for="id_client">Klient:</label>';
						$content .= '<select class="custom-select" id="id_client" name="id_client" onchange="change(this.value)" required>';
							$content .= $clients;
						$content .= '</select>';
					$content .= '</div>';
					$content .= '<div class="form-group">';
						$content .= '<label for="list2">Symbol:</label>';
						$content .= '<select class="custom-select" id="list2" name="list2"></select>';
					$content .= '</div>';
					$content .= '<button type="submit" class="btn btn-primary" name="symbol_delete">Usuń</button>';
				$content .= '</form>';
			$content .= '</div>';


		$content .= '</div>'; // Koniec wiersza nr2
		
		$content .= '<div class="row border-top my-4">'; // Wiersz nr3

			$content .= '<table class="table table-hover">';
				$content .= '<thead class="thead-dark">';
					$content .= '<tr>';
						$content .= '<th scope="col">Klient</th>';
						$content .= '<th scope="col">Symbole</th>';
						$content .= '<th scope="col">Przelewy</th>';
					$content .= '</tr>';
				$content .= '</thead>';
				$content .= '<tbody>';
					$stmt = $sql->query('SELECT * FROM `clients` ORDER BY nick')->fetchAll();
					foreach ($stmt as $row) 
					{
						if($row['id'] == 48)
							continue;
						$symbols = '';
						if($row['symbols'] && $row['symbols'] != '[]')
						{
							$data_symb = json_decode($row['symbols']);
							$i = 0;
							$len = count($data_symb);
							foreach($data_symb as $symbol)
							{
								if ($i == $len - 1)
									$symbols .= $symbol;
								else
									$symbols .= $symbol.', ';
								$i++;
							}
						}

						if($row['transfer'] == 1)
							$transfer = '<input type="checkbox" class="big-checkbox" id="transfer'.$row['id'].'" onchange="transfer('.$row['id'].');" checked>';
						else
							$transfer = '<input type="checkbox" class="big-checkbox" id="transfer'.$row['id'].'" onchange="transfer('.$row['id'].');">';

						$content .= '<tr>';
							$content .= '<td>'.$row['nick'].'</td>';
							$content .= '<td>'.$symbols.'</td>';
							$content .= '<td>'.$transfer.'</td>';
						$content .= '</tr>';
					}
				$content .= '</tbody>';
				
			$content .= '</table>';

		$content .= '</div>';

	$content .= '</div>'; // MAIN CONTAINER
				
	require_once 'include/footer.php';
?>