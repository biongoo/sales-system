<?php
	$data = 'history';
	require_once 'include/header.php';
	$content .= '<div class="container px-4 pb-5">';
	$today = date('Y-m-d');

	//////////////////// POSTS ////////////////////

	if(isset($_GET['date']))
		$day = $_GET['date'];

	if(isset($_GET['show_client']))
		$id_client = $_GET['show_client'];

	///////////////////////////////////////////////////////

	$content .= '<div class="card-group my-5">';

		$content .= '<div class="col-sm-6 mb-2">';
			$content .= '<div class="card">';
				$content .= '<h4 class="card-header">Historia z dnia:</h4>';
				$content .= '<div class="card-body">';
					$content .= '<h5 class="card-title">Data:</h5>';
					$content .= '<form>';
						$content .= '<div class="form-group">';
							$content .= '<input class="form-control" type="date" value="'.$today.'" name="date">';
						$content .= '</div>';
						$content .= '<button type="submit" class="btn btn-outline-primary">Pokaż</button>';
					$content .= '</form>';
				$content .= '</div>';
			$content .= '</div>';
		$content .= '</div>';
		$content .= '<div class="col-sm-6 mb-2">';
			$content .= '<div class="card">';
				$content .= '<h4 class="card-header">Zamówienia klienta:</h4>';
				$content .= '<div class="card-body">';
					$content .= '<h5 class="card-title">Klient:</h5>';
					$content .= '<form>';
						$content .= '<div class="form-group">';
							$content .= '<select class="custom-select" name="show_client" required>';
								$clients = clients();
								$content .= $clients;
							$content .= '</select>';
						$content .= '</div>';
						$content .= '<button type="submit" class="btn btn-outline-primary">Pokaż</button>';
					$content .= '</form>';
				$content .= '</div>';
			$content .= '</div>';
		$content .= '</div>';
	
	$content .= '</div>';

	$content .= '<hr>';

	if(!isset($day)) $day = $today;

	$content .= '<div>';
		$content .= '<div class="row orders_header pl-4">';
			if(!isset($id_client))
			{
				$stmt = $sql->prepare('SELECT `orders`.*, `clients`.`nick` FROM `orders` INNER JOIN `clients` ON `orders`.`id_client`=`clients`.`id` WHERE DATE(`date`)=? ORDER BY `date`');
				$stmt->execute([$day]);
				$orders = $stmt->fetchAll();
				($orders) ? $content .= '<h3>Zamówienia z dnia '.$day.':</h3>' : $content .= '<h3>Brak zamówień z dnia '.$day.'.</h3>';
			}
			else
			{
				$stmt = $sql->prepare('SELECT `orders`.*, `clients`.`nick` FROM `orders` INNER JOIN `clients` ON `orders`.`id_client`=`clients`.`id` WHERE `id_client`=? ORDER BY `date` LIMIT 50');
				$stmt->execute([$id_client]);
				$orders = $stmt->fetchAll();
				($orders) ? $content .= '<h3>Zamówienia klienta: '.$orders[0]['nick'].':</h3>' : $content .= '<h3>Brak zamówień dla danego klienta!</h3>';
				
			}
		$content .= '</div>';

		$content .= '<div class="row px-1">';
			$content .= '<div class="col-md-12">';
				$content .= '<div id="accordion">';
					$i = 1;
					foreach($orders as $order)
					{
						$xd = 0;
						$content .= '<div class="panel checkout-step">';
							$content .= '<div role="tab" class="accordion-toggle collapsed" id="heading'.$i.'" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$i.'">';
								$content .= '<div class="row">';
									$content .= '<div class="col-10">';
										$content .= '<span class="checkout-step-number">'.$i.'</span>';
										if(!isset($id_client))
											$content .= '<h4 class="checkout-step-title"> <a role="button"> '.$order['nick'].', '.$order['value'].'zł</a></h4>';
										else
											$content .= '<h4 class="checkout-step-title"> <a role="button"> '.$order['date'].'</a></h4>';
									$content .= '</div>';
									$content .= '<div class="col-2 text-right">';
										$content .= '<button id="nextBtn" name="nextBtn" class="btn btn-default btn-right"><i id="sec_i" class="fa fa-chevron-right"></i></button>';
									$content .= '</div>';
								$content .= '</div>';
							$content .= '</div>';

							$content .= '<div id="collapse'.$i.'" class="collapse in">';
								$content .= '<div class="checkout-step-body pb-5">';
									$content .= '<div class="row">';
										$content .= '<div class="col-lg-4">';
											$content .= '<div class="col-md-12">';
												$content .= 'Klient: '.$order['nick'];
											$content .= '</div>';
										$content .= '</div>';
									$content .= '</div>';
									$content .= '<div class="row">';
										$content .= '<div class="col-lg-4">';
											$content .= '<div class="col-md-12">';
												$content .= 'Wartość: '.$order['value'].'zł';
											$content .= '</div>';
										$content .= '</div>';
									$content .= '</div>';
									$content .= '<div class="row">';
										$content .= '<div class="col-lg-4">';
											$content .= '<div class="col-md-12">';
												$content .= 'Data: '.$order['date'];
											$content .= '</div>';
										$content .= '</div>';
									$content .= '</div>';

									if(!empty($order['date_edit']))
									{
										$content .= '<div class="row">';
											$content .= '<div class="col-lg-4">';
												$content .= '<div class="col-md-12">';
													$content .= 'Data modyfikacji: '.$order['date_edit'];
												$content .= '</div>';
											$content .= '</div>';
										$content .= '</div><br>';
									}
									else
									{
										$content .= '<br>';
									}
									$content .= '<table class="table table-striped table-bordered">';
										$content .= '<thead>';
											$content .= '<tr>';
												$content .= '<th scope="col" style="text-align: center; vertical-align: middle;">Produkt</th>';
												$content .= '<th scope="col" style="text-align: center; vertical-align: middle;">Ilość</th>';
												$content .= '<th scope="col" style="text-align: center; vertical-align: middle;">Cena</th>';
												$content .= '<th scope="col" style="text-align: center; vertical-align: middle;">Wart</th>';
											$content .= '</tr>';
										$content .= '</thead>';
										$content .= '<tbody>';
										$products = json_decode($order['products']);
										foreach($products as $product)
										{
											if(isset($product[7]))
												$xd = 1;
											if($product[5] == 1) $product[5] = 'op';
											elseif($product[5] == 2) $product[5] = 'szt';
											elseif($product[5] == 3) $product[5] = 'kg';

											$price_unit = $product[4];

											if($product[5] == 'op') $unit = 'kg/szt';
											elseif($product[5] == 'kg') { $unit = 'kg'; $price_unit = 1; $product[4] = $product[2];}
											elseif($product[5] == 'szt') { $unit = 'szt'; $product[4] = $product[2]; $price_unit = 1; }

											$content .= '<tr>';
												$content .= '<td>'.$product[0].'</td>';
												$content .= '<td>'.$product[2].' '.$product[5].'</td>';
												$content .= '<td>'.$product[3].'</td>';
												$content .= '<td><b>'.round($product[3] * $product[2], 2).'</b></td>';
											$content .= '</tr>';
										}
									$content .= '</table>';

									$content .= '<button type="button" class="btn btn-outline-danger float-left" onclick="delete_order('.$order['id'].')">Usuń</button>';
									if(!$xd)
									{
										$content .= '<button type="button" class="btn btn-outline-success float-right" onclick="window.location = \'main.php?edit='.$order['id'].'\';">Edytuj</button>';
										$content .= '<button type="button" class="btn btn-outline-primary float-right mr-2" onclick="if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest(); xmlhttp.open(\'GET\',\'include/actions.php?print='.$order['id'].'\',true); xmlhttp.send();">Drukuj</button>';	
									}
								$content .= '</div>';
							$content .= '</div>';
						$content .= '</div>';
						$i++;
					}
					$content .= '</div>';
				$content .= '</div>';
		$content .= '</div>';
	$content .= '</div>';

	$content .= '</div>'; // MAIN CONTAINER
				
	require_once 'include/footer.php';
?>