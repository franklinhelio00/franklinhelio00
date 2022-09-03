<?php
//Hélio Franklin
// API QUE FAZ CONSULTAR NO BANCO RELATORIOS DE ACORDO COM SEU FILTRO E FAZ EXPORT PARA EXCEL OU JSON

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('America/Sap_paulo');


ini_set('display_errors', 0);
ini_set('memory_limit', -1);
set_time_limit(30);

ob_start();
session_start();

$nome               = 'agi';
$diretorio_trabalho = "xxx/xxx";
$arquivoINI         = $diretorio_trabalho . DIRECTORY_SEPARATOR . 'teste.ini';

if (!file_exists($arquivoINI)) {
    die('ERRO: ' . $arquivoINI . ' não encontrado');
} else {
    $config = parse_ini_file($arquivoINI, true);
}

$url = $_SERVER['REQUEST_URI'];
$path = $_SERVER['PATH_INFO'];
//var_dump($_SERVER);

$url2 = explode('/', $path);
$campanha = $url2[1];
$lista = $url2[2];
$user = $url2[3];

//------------------------------------
//echo "Verificando Argumentos...\n";
//echo "Carregando classe : ".$diretorio_trabalho."/classes/master.class.php\n";
require($diretorio_trabalho . "/classes/master.class.php");
//echo "Carregando classe : ".$diretorio_trabalho."/classes/".$nome.".class.php\n";
require($diretorio_trabalho . "/classes/" . $nome . ".class.php");
$iogurte = new flamengoAGI($nome, $config);
$iogurte->gera_log("INFO", "Iniciando FRANKLIN api - Integração");

if($_SERVER['REQUEST_METHOD'] != "GET"){
	response(400,"Invalid request expected GET",NULL);
	exit(255);
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
  response(404,"Content type must be: application/json",NULL);
  exit(255);
}
$accept = isset($_SERVER["HTTP_ACCEPT"]) ? trim($_SERVER["HTTP_ACCEPT"]) : '';
if(strcasecmp($accept, 'application/json') != 0){
  response(404,"Accept type must be: application/json",NULL);
  exit(255);
}

if (!isset($campanha)) {
  response(404,"Parametro campanha not found",NULL);
exit(255);
}else{
$p_campanha = $campanha;
if (empty($p_campanha)){
    response(404,"Parametro campanha not found",NULL);
  exit(255);
}
}
if (!isset($lista)) {
	response(404,"Parametro lista not found",NULL);
  exit(255);
  }else{
  $p_lista = $lista;
  if (empty($p_lista)){
	  response(404,"Parametro lista not found",NULL);
	exit(255);
  }
  }
  if (!isset($user)) {
	response(404,"Parametro usuario not found",NULL);
  exit(255);
  }else{
  $p_user = $user;
  if (empty($p_user)){
	  response(404,"Parametro usuario not found",NULL);
	exit(255);
  }
  }
//============================================================================
//Query para pegar a ultima lista importada
//============================================================================
$query =  ("SELECT a.*,b.*,c.*,d.* FROM fla_campanhas AS a INNER JOIN fla_lista_master AS b on a.campanha = b.campanha 
INNER JOIN fla_fila_master as c on b.campanha=c.campanha 
INNER JOIN fla_usuario_contact_configuracoes_acessos_complementar as d on c.fila = d.valor
WHERE a.id = '$p_campanha' AND d.usuario = '$p_user' AND b.lista = '$p_lista' GROUP by 5");

$resultado = $iogurte->consultaDB($query);
if ($resultado != true){
	response(200,"Erro Interno - Historico",$query);
	exit(255);
}
$cp_campanha = array();


while($row = $resultado->fetch_array(MYSQL_ASSOC)) {
	
	$cp_campanha[] = $row;
}
if (!isset($cp_campanha)){
	response(404,"Not found",'Nao encontramos registros para essa solicitacao.');
	exit(255);
}
$campanha = $cp_campanha[0]["lista"];

//var_dump($campanha);
if($campanha == NULL) {
	response(404,"Not found",'Nao encontramos registros para essa solicitacao.');
exit(255);
	}
//============================================================================
//Finaliza a query e armazena a lista em uma variavel
//============================================================================

$sql =  ("SELECT nome_cliente AS 'NOME CLIENTE',cpf AS 'CPF',contrato AS 'CONTRATO',cod_1 AS 'CODIGO' FROM flamengAgi_$campanha ORDER BY 4");

$result = $iogurte->consultaDB($sql);
if ($result != true){
	response(200,"Erro Interno - Historico",$sql);
	exit(255);
}
$dados = array();
while($row = $result->fetch_array(MYSQL_ASSOC)) {
	$dados[] = $row;
}

if($campanha = NULL) {
	response(404,"Not found",'Nao encontramos registros para essa solicitacao.');
exit(255);
	}

//var_dump($dados);
//------------------------------------
$filename = "status_base"."_".date('d/m/Y').".csv";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
ExportFile($dados);
function ExportFile($dados) {
	$heading = false;
		if(empty($dados)){
			response(404,"Not found",'Nao encontramos registros para essa solicitacao.');
	    exit(255);	
		}
		  foreach($dados as $row) {
			if(!$heading) {
			  // display field/column names as a first row
			  echo implode(";", array_keys($row)) . "\n";
			  $heading = true;
			}
			echo implode(";", array_values($row)) . "\n";
		}
		exit;
	}
response(200,"OK",json_encode($dados));
	
function response($status,$status_message,$dados)
{
	
	header("HTTP/1.1 ".$status);	
	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']=$dados;
	
	$json_response = json_encode($response);
	echo $json_response;
	
}


