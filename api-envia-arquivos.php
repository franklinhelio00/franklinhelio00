<?php
//Hélio Franklin
//API QUE FAZ UPLOAD DE ARQUIVOS PARA O SERVIDOR APENAS USANDO CODIGOS CHAVES
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('America/Sao_paulo');
header("Acess-Control-Allow-Methods: POST");
header("Acess-Control-Allow-Headers: Acess-Control-Allow-Headers,Content-Type,Acess-Control-Allow-Methods, Authorization");


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

/* This is a way to get the request data from the POST. */
$url = $_SERVER['REQUEST_URI'];
$path = $_SERVER['PATH_INFO'];

$url2 = explode('/', $path);
$campanha = $url2[1];
$user = $url2[2];
$idcampanhaLista = $url2[3];


//------------------------------------
//echo "Verificando Argumentos...\n";
//echo "Carregando classe : ".$diretorio_trabalho."/classes/master.class.php\n";
require($diretorio_trabalho . "/classes/master.class.php");
//echo "Carregando classe : ".$diretorio_trabalho."/classes/".$nome.".class.php\n";
require($diretorio_trabalho . "/classes/" . $nome . ".class.php");
$iogurte = new flamengoAGI($nome, $config);
$iogurte->gera_log("INFO", "Iniciando FRANKLIN api - Integração");

if($_SERVER['REQUEST_METHOD'] != "POST"){
	response(400,"Invalid request expected POST",NULL);
	exit(255);
}

/* Esta é uma forma de obter os dados do pedido do POST. */
$data = json_decode(file_get_contents("php://input"), true); // collect input parameters and convert into readable format	
$fileName  =  $_FILES['file']['name'];
$tempPath  =  $_FILES['file']['tmp_name'];
$fileSize  =  $_FILES['file']['size'];

/* Isto é verificar se as variáveis estão vazias. Se elas estiverem vazias, isto irá ecoar uma mensagem de erro.. */
if(empty($campanha)){
    $errorMSG = json_encode(array("message" => "please insert campanha", "status" => false));	
	echo $errorMSG;
}
if(empty($user)){
    $errorMSG = json_encode(array("message" => "please insert user", "status" => false));	
	echo $errorMSG;
}
if(empty($idcampanhaLista)){
$errorMSG = json_encode(array("message" => "please insert ID CAMPANHA", "status" => false));	
	echo $errorMSG;
}

if(empty($fileName))
{
	$errorMSG = json_encode(array("message" => "please select image", "status" => false));	
	echo $errorMSG;
}
//query para validar se existe fila e usuario//
$query =  ("SELECT * FROM flamengoAGI
WHERE id = '$campanha' AND usuario = '$user'");

$resultado = $iogurte->consultaDB($query);
if ($resultado != true){
	response(200,"Erro Interno - Historico",$query);
	exit(255);
}
$fila = array();
$nomeCampanha = array();

while($row = $resultado->fetch_array(MYSQL_ASSOC)) {
	
	$fila[] = $row;
	$nomeCampanha[] = $row;
}
if (!isset($fila)){
	response(404,"Not found",'Nao encontramos registros para essa solicitacao.');
	exit(255);
}
$fila = $fila[0]["fila"];
$nomeCampanha = $nomeCampanha[0]["campanha"];
if($fila == NULL) {
    $errorMSG = json_encode(array("message" => "Nao encontramos registros para essa solicitacao", "status" => false));	
	echo $errorMSG;
	}
///

else
{
      
/* O código abaixo está fazendo o upload do arquivo para o servidor. */
	$upload_path = '/tmp/finaz/'; // set upload folder path 
	
	$fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
		
	// valid image extensions
	$valid_extensions = array('csv', 'CSV'); 
					
	// allow valid image file formats
	if(in_array($fileExt, $valid_extensions))
	{				
		//check file not exist our upload folder path
		if(!file_exists($upload_path . $fila.'_'.$nomeCampanha.'_'.$idcampanhaLista.'.csv'))
		{
			// check file size '5MB'
			if($fileSize < 30000000000){
				move_uploaded_file($tempPath, $upload_path . $fila.'_'.$nomeCampanha.'_'.$idcampanhaLista.'.csv'); // move file from system temporary path to our upload folder path 
			}
			else{		
				$errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 300 MB size", "status" => false));	
				echo $errorMSG;
			}
		}
		else
		{		
			$errorMSG = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));	
			echo $errorMSG;
		}
	}
	else
	{		
		$errorMSG = json_encode(array("message" => "Sorry, only CSV files are allowed", "status" => false));	
		echo $errorMSG;		
	}
}
if(!isset($errorMSG)){
    echo json_encode(array("message" => "Base Uploaded Successfully", "status" => true));
}
		

 -->
