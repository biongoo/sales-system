<?php
	$data = 'regular';
	$client = 46;
	require_once 'include/header.php';
	$content .= '<div class="container px-4">';

		$products = $sql->query('SELECT `id`, `name` FROM `products` ORDER BY `name`')->fetchAll();

		$stmt = $sql->prepare('SELECT `products_regular`.*, `clients`.`nick` FROM `products_regular` INNER JOIN `clients` ON `id_client`=`clients`.`id` WHERE `id_client` = ?');
		$stmt->execute([$client]);
		$regular_list = $stmt->fetch();
		
		$content .= '<div class="card my-4">';

			$content .= '<h3 class="card-header text-center font-weight-bold text-uppercase py-4">Stała lista '.$regular_list['nick'].'</h3>';

			$content .= '<div class="card-body">';

				$content .= '<span class="table-add float-right mb-3 mr-2"><a href="" class="text-success" id="add_row"><i class="fas fa-plus fa-2x" aria-hidden="true"></i></a></span>';

				$content .= '<table class="table table-hover table-bordered">';
					$content .= '<thead class="thead-dark">';
						$content .= '<tr>';
							$content .= '<th scope="col">Lp.</th>';
							$content .= '<th scope="col" style="min-width:180px;">Produkt</th>';
							$content .= '<th scope="col">Ilość</th>';
							$content .= '<th scope="col">Cena</th>';
							$content .= '<th scope="col">Wartość</th>';
							$content .= '<th scope="col">Ostatnia Cena</th>';
							$content .= '<th scope="col">Sort</th>';
							$content .= '<th scope="col">Usuń</th>';
						$content .= '</tr>';
					$content .= '</thead>';
					$content .= '<tbody>';
						$content .= '<select id="default_select" hidden>';
							$content .= '<option value="">Brak</option>';
							foreach($products as $product) 
							{ 
								$content .= '<option value="'.$product['id'].'">'.$product['name'].'</option>';
							}
						$content .= '</select>';

						$i = 1;
						$last_products = json_decode($regular_list[2]);
						if($last_products)
						{
							foreach ($last_products as $row) 
							{
								$content .= '<tr id="row_'.$i.'">';
									$content .= '<th scope="col">'.$i.'</th>';
									$content .= '<td>';
										$content .= '<div class="input-group">';
											$content .= '<select class="custom-select">';
												$content .= '<option value="">Brak</option>';
												$selected = false;
												foreach($products as $product)
												{
													if($product[0] != $row[0])
														$content .= '<option value="'.$product['id'].'">'.$product['name'].'</option>';
													else
													{
														$content .= '<option value="'.$product['id'].'" selected>'.$product['name'].'</option>';
														$selected = 1;
													}
												}
											$content .= '</select>';
										$content .= '</div>';
									$content .= '</td>';
									$content .= '<td>';
										if($selected)
											$content .= '<div class="input-group"><input type="text" id="prod_'.$row[2].'" inputmode="numeric" pattern="[0-9]*[.,]?[0-9]{1,2}" class="form-control count"></div>';
										else
											$content .= '<div class="input-group"><input type="text" id="prod_'.$row[2].'" inputmode="numeric" pattern="[0-9]*[.,]?[0-9]{1,2}" class="form-control count" disabled></div>';										
									$content .= '</td>';
									$content .= '<td>';
										$content .= '<div class="input-group"><input type="text" id="price_'.$row[2].'" inputmode="numeric" pattern="[0-9]*[.,]?[0-9]{1,2}" class="form-control price" disabled></div>';
									$content .= '</td>';
									$content .= '<td>';
										$content .= '<span>0</span>';
									$content .= '</td>';
									$content .= '<td>';
										$content .= '<button type="button" class="btn btn-info last_price">'.$row[1].'</button>';
									$content .= '</td>';
									$content .= '<td>';
										$content .= '<span class="table-up"><a href="" class="indigo-text sort_up"><i class="fas fa-long-arrow-alt-up" aria-hidden="true"></i></a></span>';
										$content .= '<span class="table-down"><a href="" class="indigo-text sort_down"><i class="fas fa-long-arrow-alt-down" aria-hidden="true"></i></a></span>';
									$content .= '</td>';
									$content .= '<td>';
										$content .= '<button type="button" class="btn btn-danger btn-rounded btn-sm my-0 delete_row">Usuń</button>';
									$content .= '</td>';
								$content .= '</tr>';
								$i++;
							}
						}
					$content .= '</tbody>';
					
				$content .= '</table>';

				$content .= '<span class="float-left" style="font-size: 1.3em;" >Wartość: <b><span id="value">0</span> zł</b></span>';
				$content .= '<button id="finish" type="button" class="btn btn-outline-success float-right">Zakończ</button>';
				$content .= '<button id="print" type="button" class="btn btn-outline-info float-right mr-2">Drukuj</button>';

			$content .= '</div>';

		$content .= '</div>';

	$content .= '</div>'; // MAIN CONTAINER

	$script_content = '<script>var client = '.$client.'</script>';
				
	require_once 'include/footer.php';
?>