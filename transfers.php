<?php
	ini_set( 'display_errors', 'On' ); 
	error_reporting( E_ALL );
	$data = 'transfers';
	require_once 'include/header.php';

	/// POSTS

	if(isset($_POST['add_invoice']))
	{
		$client = intval($_POST['client']);
		$invoice = intval($_POST['invoice']);
		$value = floatval(str_replace(',', '.', $_POST['value']));
		$date = $_POST["date"];
		$symbol = $_POST["symbols"];

		

		if(empty($date)) 
			$date = date('Y.m.d');
			
		$stmt = $sql->prepare('SELECT `id` FROM `transfers` WHERE `id_invoice`=? AND YEAR(CURDATE()) = YEAR(`date`)'); 
		$stmt->execute([$invoice]); 
		$row = $stmt->fetchColumn();

		if(!$row)
		{
			if($sql->prepare('INSERT INTO `transfers` (`id_invoice`, `id_client`, `symbol`, `value`, `date`, `is_settled`) VALUES (?, ?, ?, ?, ?, 0)')->execute([$invoice, $client, $symbol, $value, $date]))
				alert('Dodano fakturę pomyślnie!');
			else
				alert('Wystąpił błąd!');
		}
		else
			alert('Faktura o podanym numerze juz istnieje!');
	}

	elseif(isset($_POST['back']))
	{
		$id_tran = $_POST['back'];
		$stmt = $sql->prepare('SELECT `ids_of_transfers` FROM `transfers_history` WHERE `id`=?');
		$stmt->execute([$id_tran]);
		$ids = json_decode($stmt->fetchColumn());

		$len = count($ids);

		if($len < 1)
			alert('Wystąpił błąd!');

		$del = '(';

		foreach($ids as $key=>$id)
		{
			if($key+1 == $len)
				$del .= intval($id);
			else
				$del .= intval($id) . ', ';
		}

		$del .= ')';
		
		if($sql->prepare('UPDATE `transfers` SET `is_settled` = 0 WHERE `id_invoice` IN '.$del)->execute([]))
		{
			if($sql->prepare('DELETE FROM `transfers_history` WHERE `id`=?')->execute([$id_tran]))
				alert('Usunięto pomyślnie!');
			else
				alert('Wystąpił błąd!');
		}
		else
			alert('Wystąpił błąd!');
	}

	elseif(isset($_POST['delete_whole']))
	{
		$id_tran = $_POST['delete_whole'];
		$stmt = $sql->prepare('SELECT `ids_of_transfers` FROM `transfers_history` WHERE `id`=?');
		$stmt->execute([$id_tran]);
		$ids = json_decode($stmt->fetchColumn());

		$len = count($ids);

		if($len < 1)
			alert('Wystąpił błąd!');

		$del = '(';

		foreach($ids as $key=>$id)
		{
			if($key+1 == $len)
				$del .= intval($id);
			else
				$del .= intval($id) . ', ';
		}

		$del .= ')';
		
		if($sql->prepare('DELETE FROM `transfers` WHERE `id_invoice` IN '.$del)->execute([]))
		{
			if($sql->prepare('DELETE FROM `transfers_history` WHERE `id`=?')->execute([$id_tran]))
				alert('Usunięto pomyślnie!');
			else
				alert('Wystąpił błąd!');
		}
		else
			alert('Wystąpił błąd!');
	}

	elseif(isset($_POST['ids_del']))
	{
		$ids = json_decode($_POST['ids_del']);
		$len = count($ids);

		if($len < 1)
			alert('Wystąpił błąd!');

		$del = '(';

		foreach($ids as $key=>$id)
		{
			if($key+1 == $len)
				$del .= intval($id);
			else
				$del .= intval($id) . ', ';
		}

		$del .= ')';
		
		if($sql->prepare('DELETE FROM `transfers` WHERE `id_invoice` IN '.$del)->execute([]))
			alert('Usunięto pomyślnie!');
		else
			alert('Wystąpił błąd!');
	}

	elseif(isset($_POST['ids_sett']))
	{
		$basket = json_decode($_POST['ids_sett']);
		$client = $basket[0];
		$ids = $basket[1];
		$symbol = $basket[2];
		$value = $basket[3];
		$len = count($ids);

		if($len < 1)
			alert('Wystąpił błąd!');

		$set = '(';

		foreach($ids as $key=>$id)
		{
			if($key+1 == $len)
				$set .= intval($id);
			else
				$set .= intval($id) . ', ';
		}

		$set .= ')';
		
		if($sql->prepare('UPDATE `transfers` SET `is_settled` = 1 WHERE `id_invoice` IN '.$set)->execute([]))
		{
			if($sql->prepare('INSERT INTO `transfers_history` (`client`, `symbol`, `value`, `ids_of_transfers`, `date`) VALUES (?, ?, ?, ?, CURDATE())')->execute([$client, $symbol, $value, json_encode($ids)]))
				alert('Rozliczono pomyślnie!');
			else
				alert('Wystąpił błąd!');
		}
		else
			alert('Wystąpił błąd!');
	}

	///

	$content .= '<div class="container-fluid px-3 pb-5">';

		$content .= '<div class="card-columns my-5">';

			$content .= '<div class="card add_invoice mx-2 border-primary">';
				$content .= '<h4 class="card-header">Nowa faktura:</h4>';
				$content .= '<div class="card-body">';
					$content .= '<form method="post">';

						$content .= '<div class="form-group" id="client_form">';
							$stmt = $sql->query('SELECT * FROM `clients` WHERE `transfer`="1" ORDER BY `nick`')->fetchAll();
							$content .= '<label for="client">Klient:</label>';
							$content .= '<select class="form-control" id="client" name="client">';
							$index = 0;
								foreach($stmt as $key=>$row) 
								{
									if(!$row['nick'] == 'PALEO') 
										$content .= '<option value="'.$row['id'].'">'.$row['nick'].'</option>';
									else
									{
										$content .= '<option value="'.$row['id'].'" selected>'.$row['nick'].'</option>';
										$index = $key;
									}
								}
							$symbols = json_decode($stmt[$index]['symbols']);
							unset($index);
							$content .= '</select>';
						$content .= '</div>';
						
						if(count($symbols) > 0)
						{
							$content .= '<div class="form-group" id="symbols">';
								$content .= '<h6>Symbol:</h6>';
								$content .= '<div class="d-flex justify-content-center">';
									$content .= '<div class="btn-group-toggle" data-toggle="buttons">';
										$content .= '<div class="btn-group btn-group-justified" style="display:block;">';
										$i = 1;
										foreach($symbols as $symbol)
										{
											$content .= '<label class="btn btn-primary mb-1">';
												$content .= '<input type="radio" name="symbols" id="option'.$i.'" value="'.$symbol.'" autocomplete="off" required> '.$symbol;
											$content .= '</label>';
											$i++;
										}
										$content .= '</div>';
									$content .= '</div>';
								$content .= '</div>';
							$content .= '</div>';
						}

						$content .= '<div class="form-group">';
							$content .= '<label for="invoice">Nr Faktury:</label>';
							$content .= '<input type="number" class="form-control" id="invoice" name="invoice" required>';
						$content .= '</div>';

						$content .= '<div class="form-group">';
							$content .= '<label for="value">Wartość:</label>';
							$content .= '<input type="number" step="0.01" class="form-control" id="value" name="value" required>';
						$content .= '</div>';

						$content .= '<div class="form-group">';
							$content .= '<label for="date">Data:</label>';
							$content .= '<input type="date" class="form-control" id="date" name="date" value="0">';
						$content .= '</div>';

						$content .= '<button type="submit" class="btn btn-outline-primary" name="add_invoice">Dodaj</button>';
					$content .= '</form>';
				$content .= '</div>';
			$content .= '</div>';

			$content .= '<div class="card transfers mx-2 border-primary">';
						$stmt = $sql->query('SELECT `transfers`.*, `clients`.`nick` FROM `transfers` INNER JOIN `clients` ON `transfers`.`id_client` = `clients`.`id` WHERE `is_settled`="0" ORDER BY `nick`, `symbol`, `id_invoice`')->fetchAll();
						$i = 1;
						$j = count($stmt);
				$content .= '<h4 class="card-header">Przelewy: <b><span id="value_of_all"></span> zł ('.$j.' przelewy)</b></h4>';
				$content .= '<div class="card-body p-0">';
					$content .= '<div id="accordion">';

						
						$k = 1;
						$value = 0;
						foreach($stmt as $row)
						{
							$client = $row['nick'].$row['symbol'];
							(isset($stmt[$i])) ? $next_client = $stmt[$i]['nick'].$stmt[$i]['symbol'] : $next_client = '';

							if($k)
							{
								$content .= '<div class="panel">';
									$content .= '<div class="card-header" id="heading'.$i.'" data-toggle="collapse" data-target="#collapse'.$i.'" aria-controls="collapse'.$i.'">';
										$content .= '<h5 class="mb-0"><button class="btn btn-link"><span>'.$row['nick'].'</span> <span>'.$row['symbol'].'</span></button></h5>';
									$content .= '</div>';

									$content .= '<div id="collapse'.$i.'" class="collapse" aria-labelledby="heading'.$i.'" data-parent="#accordion">';
										$content .= '<div class="card-body px-2 py-4">';
											$content .= '<div class="table-responsive">';
												$content .= '<table class="table table-bordered m-0" style="text-align:center;">';
													$content .= '<thead class="thead-dark">';
														$content .= '<tr>';
															$content .= '<th scope="col">#</th>';
															$content .= '<th scope="col">Data</th>';
															$content .= '<th scope="col">Nr FS</th>';
															$content .= '<th scope="col">Wartość</th>';
														$content .= '</tr>';
													$content .= '</thead>';
													$content .= '<tbody>';
								}
														($client != $next_client) ? $k = 1 : $k = 0;
														$content .= '<tr>';
															$content .= '<th scope="row">'.$i.'</th>';
															$content .= '<td>'.$row['date'].'</td>';
															$content .= '<td class="bg-success">FS '.$row['id_invoice'].'</td>';
															$content .= '<td>'.round($row['value'], 2).'</td>';
															$value += $row['value'];
														$content .= '</tr>';
								

								if($k)
								{
													$content .= '</tbody>';
												$content .= '</table>';
											$content .= '</div>';
										$content .= '</div>';
									$content .= '</div>';
								$content .= '</div>';
							}
							$i++;		
						}
					$content .= '</div>';
				$content .= '</div>';
			$content .= '</div>';

			$content .= '<div class="card mx-2 border-primary">';
				$content .= '<h4 class="card-header">Historia:</h4>';
				$content .= '<div class="card-body">';

					$stmt = $sql->prepare('SELECT * FROM `transfers_history` ORDER BY `id` DESC LIMIT 30');
					$stmt->execute([]);
					$transfers = $stmt->fetchAll();

					$content .= '<div id="accordionp">';
						$i = 1;
						foreach($transfers as $tran)
						{
							$xd = 0;
							$content .= '<div class="panel checkout-step">';
								$content .= '<div role="tab" class="accordion-toggle collapsed" id="headingp'.$i.'" data-toggle="collapse" data-parent="#accordionp" href="#collapsep'.$i.'">';
									$content .= '<div class="row">';
										$content .= '<div class="col-10">';
											$content .= '<span class="checkout-step-number">'.$i.'</span>';
												$content .= '<h4 class="checkout-step-title"> <a role="button">'.$tran['date'].' <i class="fas fa-arrow-right"></i> '.$tran['client'].' '.$tran['symbol'].'  <i class="fas fa-arrow-right"></i> '.$tran['value'].' zł</a></h4>';
										$content .= '</div>';
										$content .= '<div class="col-2 text-right">';
											$content .= '<button id="nextBtn" name="nextBtn" class="btn btn-default btn-right"><i id="sec_i" class="fa fa-chevron-right"></i></button>';
										$content .= '</div>';
									$content .= '</div>';
								$content .= '</div>';

								$content .= '<div id="collapsep'.$i.'" class="collapse in">';
									$content .= '<div class="checkout-step-body pb-5">';

										$content .= '<button type="button" class="btn btn-outline-danger float-left delete_whole">Usuń</button>';
											$content .= '<form method="post" id="delete_whole_form"><input type="hidden" name="delete_whole"></form>';
										$content .= '<button type="button" class="btn btn-outline-warning float-right back">Cofnij rozliczenie</button>';
											$content .= '<form method="post" id="back_form"><input type="hidden" name="back"></form>';

										$content .= '<input type="hidden" value="'.$tran['id'].'">';
									$content .= '</div>';
								$content .= '</div>';
							$content .= '</div>';
							$i++;
						}
						$content .= '</div>';
					$content .= '</div>';
				$content .= '</div>';
			$content .= '</div>';

		$content .= '</div>';

	$content .= '</div>'; // MAIN CONTAINER

	$content .= '<footer class="navbar fixed-bottom table-primary mx-2 border border-primary rounded" style="display: none;">';

		$content .= '<div class="container-fluid px-1">';

			$content .= '<div class="col-sm px-1" style="text-align: center;">';
				$content .= '<button class="btn btn-danger" id="delete">Usuń zaznaczone</button>';
				$content .= '<form method="post" id="delete_tran"><input type="hidden" id="ids_del" name="ids_del"></form>';
			$content .= '</div>';

			$content .= '<div class="col-sm px-1" style="text-align: center;">';
				$content .= '<b>Wartość: <span id="value-tran">0</span> zł</b>';
			$content .= '</div>';

			$content .= '<div class="col-sm px-1" style="text-align: center;">';
				$content .= '<button class="btn btn-success ml-1" id="settle">Rozlicz zaznaczone</button>';
				$content .= '<form method="post" id="settle_tran"><input type="hidden" id="ids_sett" name="ids_sett"></form>';
			$content .= '</div>';

        $content .= '</div>';

	$content .= '</footer>';
	
	$content .= '<script>var value_of_all = '.$value.'</script>';
				
	require_once 'include/footer.php';
?>