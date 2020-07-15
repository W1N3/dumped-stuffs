<?php
include ("./includes/htmlActions.php");
include ("./_webtool/includes/funcoes.php");

$busca = filter_input(INPUT_GET, "busca", FILTER_SANITIZE_SPECIAL_CHARS);

$tt = 0;
//página atual
$pg = !isset($_REQUEST["pg"])?1:filter_input(INPUT_GET, "pg", FILTER_SANITIZE_NUMBER_INT);
//resultados por página
$flt_ttrpg = !isset($_REQUEST["flt_ttrpg"])?10:filter_input(INPUT_GET, "flt_ttrpg", FILTER_SANITIZE_NUMBER_INT);
$ttrlm = $flt_ttrpg-1;
//ordenamento do período
$flt_ord_p = !isset($_REQUEST["flt_ord_p"])?"DESC":filter_input(INPUT_GET, "flt_ord_p", FILTER_SANITIZE_SPECIAL_CHARS);
//coluna de ordenamento
$flt_ord = !isset($_REQUEST["flt_ord"])?"nome_projeto":filter_input(INPUT_GET, "flt_ord", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_nome = filter_input(INPUT_GET, "flt_nome", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_titulo  = filter_input(INPUT_GET, "flt_titulo", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_orientador = filter_input(INPUT_GET, "flt_orientador", FILTER_SANITIZE_NUMBER_INT);
$flt_ano_d = filter_input(INPUT_GET, "flt_ano_d", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_periodo_d = filter_input(INPUT_GET, "flt_periodo_d", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_ano_a = filter_input(INPUT_GET, "flt_ano_a", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_periodo_a = filter_input(INPUT_GET, "flt_periodo_a", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_resumo = filter_input(INPUT_GET, "flt_resumo", FILTER_SANITIZE_SPECIAL_CHARS);
$flt_premiacao = filter_input(INPUT_GET, "flt_premiacao", FILTER_SANITIZE_NUMBER_INT);
$flt_temas = filter_input(INPUT_GET, "flt_temas", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$flt_banca = filter_input(INPUT_GET, "flt_banca", FILTER_SANITIZE_NUMBER_INT);

if(!empty($busca) || !empty($flt_nome) || !empty($flt_titulo) || !empty($flt_orientador) || !empty($flt_banca) || !empty($flt_ano_d) || !empty($flt_ano_a) || !empty($flt_resumo) || !empty($flt_premiacao)|| !empty($flt_allpremiacao)|| isset($flt_temas) || isset($flt_pal_chaves)){
	
	$wherebs = array();

	if(!empty($busca)) {
		if(is_numeric($busca)){
			$wherebs = "ano_projeto='$busca'";
		} else {
			$wherebs[] = "nome_aluno LIKE '%$busca%' OR nome_projeto LIKE '%$busca%' OR nm_local LIKE '%$busca%' OR  '$busca' IN (SELECT temas.`nome_tema` FROM temas,projetos_temas WHERE projetos_temas.id_projeto=detalhes_projeto.id_projeto) OR professor_orientador LIKE '%$busca%' OR professor_coorientador LIKE '%$busca%'";

			$new_busca = split(" ", $busca);
			if(count($new_busca) > 1) {
				foreach($new_busca as $nb) {
					$wherebs[] = "nome_aluno LIKE '%$nb%' OR nome_projeto LIKE '%$nb%' OR nm_local LIKE '%$nb%' OR  '$nb' IN (SELECT temas.`nome_tema` FROM temas,projetos_temas WHERE projetos_temas.id_projeto=detalhes_projeto.id_projeto) OR professor_orientador LIKE '%$nb%' OR professor_coorientador LIKE '%$nb%'";
				}
			}
			//phonembr(nome_aluno) COLLATE utf8_unicode_ci  LIKE CONCAT('%',phonembr('$busca'),'%') COLLATE utf8_unicode_ci 
		}
	}

	if(!empty($flt_nome)){
		$wherebs[] ="nome_aluno LIKE '$flt_nome%' OR nome_aluno LIKE '% $flt_nome%'";//phonembr(nome_aluno) COLLATE utf8_unicode_ci LIKE CONCAT('%',phonembr('$flt_nome'),'%') COLLATE utf8_unicode_ci
		$new_flt_nome = split(" ", $flt_nome);
		if(count($new_flt_nome) > 1) {
			foreach($new_flt_nome as $nf) {
				$wherebs[] ="nome_aluno LIKE '$nf%' OR nome_aluno LIKE '% $nf%'";
			}
		}
	}
	if(!empty($flt_titulo)){
		$wherebs[] ="nome_projeto LIKE '%$flt_titulo%'";

		$new_flt_titulo = split(" ", $flt_titulo);
		if(count($new_flt_titulo) > 1) {
			foreach($new_flt_titulo as $nf) {
				$wherebs[] ="nome_projeto LIKE '%$nf%'";
			}
		}
	}
	if(!empty($flt_orientador)){
		$wherebs[] ="(id_professor_orientador=$flt_orientador OR id_professor_coorientador=$flt_orientador)";// OR id_projeto IN (SELECT id_projeto FROM convidado_banca_projeto WHERE id_professor=$flt_orientador))
	}
	if(!empty($flt_banca)){
		$wherebs[] ="(id_projeto IN (SELECT id_projeto FROM convidado_banca_projeto WHERE id_professor=$flt_banca))";//id_professor_orientador=$flt_banca OR id_professor_coorientador=$flt_banca OR
	}        
	if(!empty($flt_ano_d)){
		$tmp_ano_d = explode("|", $flt_ano_d);
		$flt_periodo_d = $tmp_ano_d[1];
		$flt_ano_d = $tmp_ano_d[0];

		if(empty($flt_ano_a)){
			$wherebs[] ="ano_projeto='$flt_ano_d'".(empty($flt_periodo_d)?"":" AND periodo_projeto=$flt_periodo_d");
		} else {
			$tmp_ano_a = explode("|", $flt_ano_a);
			$flt_periodo_a = $tmp_ano_a[1];
			$flt_ano_a = $tmp_ano_a[0];
			$wherean ="((ano_projeto>='$flt_ano_d'".(empty($flt_periodo_d)?"":" AND periodo_projeto>=$flt_periodo_d").") AND";
			$wherean .="(ano_projeto<='$flt_ano_a'".(empty($flt_periodo_a)?"":" AND periodo_projeto<=$flt_periodo_a")."))";
			$wherebs[] = $wherean;
		}
	}

	if(!empty($flt_resumo)){
		$wherebs[] ="sinopse_projeto LIKE '%$flt_resumo%'";

		$new_flt_resumo = split(" ", $flt_resumo);
		if(count($new_flt_resumo) > 1) {
			foreach($new_flt_resumo as $nf) {
				$wherebs[] ="sinopse_projeto LIKE '%$nf%'";
			}
		}
	}
	if(!empty($flt_premiacao)){
		$wherebs[] ="id_projeto IN (SELECT projetos_premiacoes.id_projeto FROM projetos_premiacoes WHERE id_premiacao=$flt_premiacao)";
	}

	if(!empty($flt_allpremiacao)){
		$wherebs[] ="id_projeto IN (SELECT projetos_premiacoes.id_projeto FROM projetos_premiacoes)";
	}

	if(isset($flt_temas) && count($flt_temas)){
		foreach ($flt_temas as $flt_tm) {
			$wherebs[] ="id_projeto IN (SELECT id_projeto FROM projetos_temas WHERE id_tema=$flt_tm)";
		}
	}

	if(isset($flt_pal_chaves) && count($flt_pal_chaves)){
		foreach ($flt_pal_chaves as $flt_pc) {
			$wherebs[] ="id_projeto IN (SELECT id_projeto FROM projeto_palavras_chaves WHERE id_pal_chave=$flt_pc)";
		}
	}

	$wherebsc = join(" OR ", $wherebs);//AND

} else {
	/*
	$adv = filter_input(INPUT_GET, "adv", FILTER_SANITIZE_NUMBER_INT);

	if($adv==1){
		setcookie("box-busca-itens", "block");        
	} else {
		setcookie("box-busca-itens", "none");                
	}*/
}

	$whereobj = empty($wherebsc)?"":"WHERE $wherebsc";
	//total de registros
	$sqlCnt = "SELECT count(*) as total FROM detalhes_projeto $whereobj;";
	//echo $sqlCnt;
	$resultCnt = mysqli_query($conn, $sqlCnt);
	//total de registros
	$tt = @mysqli_result($resultCnt, 0);
	//total de páginas existentes
	$ttpgs = ceil($tt/$flt_ttrpg);
	//correção para o caso da página ter um valor superior ao total de páginas (comum quando se troca o número de resultados por página
	$pg = $pg>$ttpgs?$ttpgs:$pg;
	//início da contagem no SQL
	$pg0 = ($pg -1) *$flt_ttrpg;
	$pg0 = $pg0<0?0:$pg0;
	//última página
	$lastpg = $ttpgs-$pg>5?$pg+5:$ttpgs;
	//primeira página
	$fsttpg = $ttpgs<$flt_ttrpg?1:$pg<6?1:$lastpg-10;
	//$fsttpg = $ttpgs<$flt_ttrpg?1:($lastpg-$pg<10?$lastpg-10:$pg);

	//a busca propriamente
	$ord_ano_proj = $flt_ord_p=="0"?"":(empty($flt_ord_p)?"ano_projeto DESC,periodo_projeto DESC,":"ano_projeto $flt_ord_p,periodo_projeto $flt_ord_p,");
	$sqlBusca = "SELECT id_projeto,nome_projeto,ano_projeto,periodo_projeto,nome_aluno,professor_orientador FROM detalhes_projeto $whereobj ORDER BY $ord_ano_proj $flt_ord LIMIT $pg0,$flt_ttrpg;";
	echo "<!--$sqlBusca-->";
	$resultBusca = mysqli_query($conn, $sqlBusca);
	


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
<?
	scriptsAndCSS();
?>
		<meta property="og:description" content="Pesquise pelos projetos desenvolvidos pelos alunos da FAU-UFRJ" />
		<meta name=”twitter:description” content="Pesquise pelos projetos desenvolvidos pelos alunos da FAU-UFRJ"/>
		<meta name="description" content="Pesquise pelos projetos desenvolvidos pelos alunos da FAU-UFRJ"/>

		<title>Midiateca FAU-UFRJ - Pesquisa</title>
	</head>
	<body>
<?
	headerPage();
?>
	<div class="bg-dark jumbotron-fluid">
		<div class="row m-1">
			<div class="col-md-3">
				<div class="mt-3" id="accordion">
					<div class="card">
						<div class="card-header" id="headingOne">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">Visualização</button>
							</h5>
						</div>

						<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="">
							<div class="card-body">
								<form>
									<div class="form-group">
										<label for="op">
											Ordenar por :
										</label>
										<select class="custom-select" name="flt_ord" id="flt_ord">
											<option value="nome_aluno" <?=($flt_ord=="nome_aluno")?"selected='selected'":""?>>aluno</option>
											<option value="nome_projeto" <?=($flt_ord=="nome_projeto")?"selected='selected'":""?>>titulo</option>
											<option value="professor_orientador" <?=($flt_ord=="professor_orientador")?"selected='selected'":""?>>orientador</option>
										</select>
									</div>
									<div class="form-group">
										<label for="periodo">
											Período :
										</label>
										<select class="custom-select" name="flt_ord_p"  id="flt_ord_p">
											<option value="ASC" <?=($flt_ord_p=="ASC")?"selected='selected'":""?>>crescente</option>
											<option value="DESC" <?=($flt_ord_p=="DESC")?"selected='selected'":""?>>decrescente</option>
											<option value="0" <?=($flt_ord_p=="0")?"selected='selected'":""?>>não ordena</option>
										</select>
									</div>
									<div class="form-group">
										<label for="ipp">
											Ítens por página :
										<label>
										<select class="custom-select" name="flt_ttrpg" id="flt_ttrpg">
											<option value="10" <?=($flt_ttrpg==10)?"selected='selected'":""?>>10</option>
											<option value="25" <?=($flt_ttrpg==25)?"selected='selected'":""?>>25</option>
											<option value="50" <?=($flt_ttrpg==50)?"selected='selected'":""?>>50</option>
										</select>
									</div>
									<button type="button" class="btn btn-outline-primary btn-block" id="add-order" value="ordenar"> Ordenar </button>
								</form>
							</div>
						</div>
					</div>

					<div class="card">
						<div class="card-header" id="headingTwo">
							<p class="form-check mb-0">
								<?=$tt?"$tt resultados encontrados":"defina a sua pesquisa";?>
							</p>
						</div>
					</div>

					<div class="card">
						<div class="card-header" id="headingThree">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">Busca Avançada</button>
							</h5>
						</div>
						<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="">
							<div class="card-body">
								<form name="pesquisa_adv" id="pesquisa_adv" method="get" action="pesquisa.php">
									<div id="flt-nm" class="form-group">
										<label> Busca por nome : </label>
										<input type="text" class="form-control" name="flt_nome" id="flt_nome" value="<?=$flt_nome?>" placeholder="digite o termo a ser pesquisado"/>
									</div>
									<div id="flt-tit" class="form-group">
										<label> Busca por título : </label>
										<input type="text" class="form-control" name="flt_titulo" id="flt_titulo" value="<?=$flt_titulo?>" placeholder="digite o termo a ser pesquisado"/>
									</div>
									<div id="flt-prof" class="form-group">
										<label> Orientador/Coorientador : </label>
										<select class="custom-select" name="flt_orientador" id="flt_orientador">
											<option value=""></option>
											<?criaSelect("nome_professor","id_professor", "professores","",$flt_orientador,"id_professor IN (SELECT DISTINCT id_professor_orientador AS id_professor FROM projetos  WHERE id_professor_orientador IS NOT NULL UNION SELECT DISTINCT id_professor_coorientador FROM projetos WHERE id_professor_coorientador IS NOT NULL)");?>
										</select>
									</div>
									<div class="form-group">
										<label> Banca : </label>
										<select class="custom-select" name="flt_banca" id="flt_banca">
											<option value=""></option>
											<?criaSelect("nome_professor","id_professor", "professores","",$flt_banca,"id_professor IN (SELECT DISTINCT id_professor FROM convidado_banca_projeto)");?>
										</select>
									</div>                            
									<div id="flt-per" class="form-group">
										<label> Período de </label>
										<select class="custom-select" name="flt_ano_d" id="flt_ano_d">
											<option value=""></option>
											<?
			$str_Sql = "SELECT DISTINCT ano_projeto FROM projetos ORDER BY ano_projeto";
			$SELcreate = mysqli_query($conn, $str_Sql);
			$dataCount = mysqli_num_rows($SELcreate);
			if ($dataCount){
			while($dataCreate = mysqli_fetch_array($SELcreate, MYSQLI_NUM)){
				$val0 = $dataCreate[0];
				for($x=1;$x<=2;$x++){
					$sel = ("$flt_ano_d|$flt_periodo_d"=="$val0|$x")?'selected="selected"':"";
					echo utf8_encode("<option value='$val0|$x' $sel>$val0/$x</option>\n");
				}
			}
			}
											?>
										</select>
										<labe> a </label>
										<select class="custom-select" name="flt_ano_a" id="flt_ano_a">
											<option value=""></option>
											<?
			$str_Sql = "SELECT DISTINCT ano_projeto FROM projetos ORDER BY ano_projeto";
			$SELcreate = mysqli_query($conn, $str_Sql);
			$dataCount = mysqli_num_rows($SELcreate);
			if ($dataCount){
			while($dataCreate = mysqli_fetch_array($SELcreate, MYSQLI_NUM)){
				$val0 = $dataCreate[0];
				for($x=1;$x<=2;$x++){
					$sel = ("$flt_ano_a|$flt_periodo_a"=="$val0|$x")?'selected="selected"':"";
					echo utf8_encode("<option value='$val0|$x' $sel>$val0/$x</option>\n");
				}
			}
			}
											?>
										</select>
									</div>
									<div id="flt-resum" class="form-group">
										<label> Resumo : </label>
										<input class="form-control" type="text" name="flt_resumo" id="flt_resumo" value="<?=$flt_resumo?>" placeholder="digite o termo a ser pesquisado"/>
									</div>
									<div id="flt-premi" class="form-group">
										<label> Premiações : </label>
										<select class="custom-select" name="flt_premiacao" id="flt_premiacao">
											<option value=""></option>
											<?criaSelect("nome_premiacao","id_premiacao", "premiacoes","", $flt_premiacao);?>
										</select>
									</div>
									<div id="flt-tema" class="form-group">
										<label> Temas : </label>
										<select class="custom-select" name="flt_temas[]" id="flt_temas" multiple size="6">
											<?criaSelect("IF(tm.id_tema_pai IS NULL, nome_tema, CONCAT((SELECT tm1.nome_tema FROM temas as tm1 WHERE tm1.id_tema=tm.id_tema_pai),' - ',tm.nome_tema)) as nm_tema","id_tema","temas as tm","nm_tema",$flt_temas);?>
										</select>
									</div>
									<div id="flt-tema" class="form-group">
										<label> Palavras-Chave : </label>
										<select class="custom-select" name="flt_pal_chaves[]" id="flt_pal_chaves" multiple size="6">
											<?criaSelect("palavra_chave","id_pal_chave","palavras_chaves","palavra_chave",$flt_pal_chaves);?>
										</select>
									</div>
									<div class="form-group">
										<button id="bt-adv-pesq" class="btn btn-outline-primary btn-block" value="pesquisar" type="submit"> Pesquisar </button>
										<button id="bt-adv-clear" type="button" class="btn btn-outline-primary btn-block" value="limpar"> Limpar </button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-9">
				<nav aria-label="search pagination">
					<ul id="pagination" class="mt-3 pagination justify-content-center">
						
<?
if(@$resultBusca){
	if($pg>1){
?>
					<li class="page-item"><a href="pesquisa.php?busca=<?=$busca.$lstfiltrs?>&pg=<?=($pg-1)?>" class="page-link"> Anterior </a></li>
<?
	}
	for ($index = $fsttpg; $index <= $lastpg; $index++) {
		if($index!=$pg){
			echo "<li class='page-item'><a class='page-link' href='pesquisa.php?busca=$busca$lstfiltrs&pg=$index'>$index</a></li>";
		} else {
			echo "<li class='page-item active'><a href='#' class='page-link'>$index</a></li>";
		}
	}
	if($pg!=$ttpgs){
?>
						<li class="page-item"><a class="page-link" href="pesquisa.php?busca=<?=$busca.$lstfiltrs?>&pg=<?=($pg+1)?>" class="search-pgn"> Próximo </a></li>
<?
	}
}
?>
					</ul>
				</nav>

<?
if(!empty($flt_allpremiacao)){
?>
				<div class="d-flex justify-content-center"><div class="alert alert-warning text-center" role="alert">Projetos Premiados</div></div>
<?
}
if(@$resultBusca && mysqli_num_rows($resultBusca)) {	
?>
			<div class="card-columns">
<?
	while ($dataBusca = mysqli_fetch_array($resultBusca, MYSQLI_ASSOC)) {
		$id_projeto = codify($dataBusca["id_projeto"]);
		$nome_projeto = codify($dataBusca["nome_projeto"]);
		$ano_projeto = $dataBusca["ano_projeto"];
		$periodo_projeto = $dataBusca["periodo_projeto"];
		$autor_projeto = codify($dataBusca["nome_aluno"]);
		$professor_orientador = codify($dataBusca["professor_orientador"]);
		$temas = array();
		$keywords = array();
		$imgs = array();

		//temas do projeto
		$selectTema = "SELECT tm.id_tema, IF(tm.id_tema_pai IS NULL, nome_tema, CONCAT((SELECT tm1.nome_tema FROM temas as tm1 WHERE tm1.id_tema=tm.id_tema_pai),' - ',tm.nome_tema)) as nm_tema FROM temas as tm WHERE tm.id_tema IN (SELECT id_tema FROM projetos_temas WHERE projetos_temas.id_projeto = $id_projeto)";
		$resultTema = mysqli_query($conn, $selectTema);
		while($dataTema = mysqli_fetch_array($resultTema, MYSQLI_ASSOC)){
			$temas[] = codify($dataTema["nm_tema"]);
		}
	//imagens do projeto
		$selectImgs = "SELECT imagens.id_imagem,imagens.nome_imagem,imagens.legenda_imagem FROM imagens JOIN projetos_imagens ON projetos_imagens.id_imagem = imagens.id_imagem  WHERE projetos_imagens.id_projeto=$id_projeto ORDER BY projetos_imagens.ordem_imagem, projetos_imagens.id_imagem LIMIT 1";
		$resultImgs = mysqli_query($conn, $selectImgs);
		while($dataImg = mysqli_fetch_array($resultImgs, MYSQLI_ASSOC)){
			$imgs[] = array($dataImg["id_imagem"],codify($dataImg["nome_imagem"]),codify($dataImg["legenda_imagem"]));
		}
?>
				<div class="card">
					<a href="projetos.php?id=<?=$id_projeto?>&busca=<?=$busca.$lstfiltrs?>&pg=<?=($pg)?>">
<?
	if (count($imgs)){
?>
					<?='<img class="card-img-top img-fluid" src="./returnImg.php?imagem='.$imgs[0][1].'&w=800&h=600&rnd='.rand().'" alt="'.$imgs[0][2].'"/>'?>
<?
	} else {
?>
					<img class="card-img-top img-fluid" src="imagens/md_no_foto.jpg" alt="'sem imagem"/>
<?
	}
?>
					</a>

					<div class="card-header" <?='id="headingCard'.$id_projeto.'"'?> >
						<h5 class="card-title">
							<a href="projetos.php?id=<?=$id_projeto?>&busca=<?=$busca.$lstfiltrs?>&pg=<?=($pg)?>">
								<?=$nome_projeto?>
							</a>
							<button type="button" class="btn btn-outline-primary btn-sm badge collapsed" data-toggle="collapse" <?='data-target="#collapseCard'.$id_projeto.'"'?> aria-expanded="false" <?='aria-controls="collapseCard'.$id_projeto.'"'?> > + </button>
						</h5>
					</div>

					<div <?='id="collapseCard'.$id_projeto.'"'?> class="collapse" <?='aria-labelledby="headingCard'.$id_projeto.'"'?> data-parent="">
						<ul class="list-group list-group-flush">
							<li class="list-group-item"> Autor : <?=$autor_projeto?> </li>
							<li class="list-group-item"> Orientador : <?=$professor_orientador?> </li>
							<li class="list-group-item"> Período : <?=$ano_projeto?>/<?=$periodo_projeto?> </li>
							<?if(count($temas)){?>
							<li class="list-group-item"> Tema(s) : <?=  join(", ", $temas)?> </li>
							<?}?>
							<?if(count($keywords)){?>
							<li class="list-group-item"> Palavras Chave : <?=  join(", ", $keywords)?> </li>
							<?}?>
						</ul>
					</div>

				</div>
<?
	}
} else {
?>
	<div class="d-flex justify-content-center"><div class="alert alert-danger text-center" role="alert"> Sem Resultados </div></div>
<?
}
?>
			</div>
			</div>
		</div>
	</div>
</div>
<?
	footerPage();
?>
	</body>
</html>

