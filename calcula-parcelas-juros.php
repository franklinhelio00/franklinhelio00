#!/usr/bin/php -q
<?php
/// Processo em que verifico um VALOR e aplico juros baseados em dias e quantidades de parcelas//
require('caminho para dados do servidor');
$agi = new AGI();
$VALOR = $argv[1];

$quantidade = 0;

function getJuros($qtdParcelas)
{
  $juros = 0;
  $qtdParcelas = 1 ? $juros = 0.10 : false;
  $qtdParcelas = 2 ? $juros = 0.20 : false;
  $qtdParcelas >= 3 ? $juros = 0.20 : false;
  return $juros;
}

function getValorParcelas($valorDivida, $valorEntrada, $qtdParcelas)
{
  $juros = getJuros($qtdParcelas);
  $saldoNegociado = $valorDivida - $valorEntrada;
  $valorParcela = $saldoNegociado * ((pow((1 + $juros), $qtdParcelas - 1) * $juros) / (pow((1 + $juros), $qtdParcelas - 1) - 1));
  return $valorParcela;
}

function getValorEntrada($valorDivida, $qtdParcelas)
{
  $juros = getJuros($qtdParcelas);
  $valorEntrada = $valorDivida * ((pow((1 + $juros), $qtdParcelas) * $juros) / (pow((1 + $juros), $qtdParcelas) - 1));
  return $valorEntrada;
}

function getValorDesconto($VALOR, $valorDesconto)
{
  $saldoDesconto = $VALOR;
  $valorDesconto = $saldoDesconto * 25 / 100;
  return $valorDesconto;
}


for ($i = 1; $i < 2; $i++) {
  if (($VALOR / $i) >= 100) {
    $quantidade = $i;
    $valorEntrada = getValorEntrada($VALOR, $quantidade);
    $valorParcelas = getValorParcelas($VALOR, $valorEntrada, $quantidade);
    $valorDesconto = getValorDesconto($VALOR, $valorEntrada);

    $valorDevedor = $VALOR - $valorDesconto;
    $Multa = ($VALOR - $valorDesconto) * 9 / 100 ;
    $jurosParc1 = ($valorDevedor + $Multa) / 2;
    $parcela1 = $jurosParc1 * 10 /100;
    $parcela2 = $jurosParc1 * 20 /100;
    $Parcela1 = $jurosParc1 + $parcela1;
    $Parcela2 = $jurosParc1 + $parcela2;
    $valoravista = $VALOR - ($VALOR * 30 / 100);
    $valor1proposta = $VALOR + ($VALOR * 10 / 100);
    $ValorProposta1 = $valor1proposta - $valorDesconto;
    $agi->set_variable("V_VALOR_DESCONTO_AVISTA", round($valoravista, 2));
    $agi->set_variable("V_VALOR_TOTAL_ENTRADA", round($valorDesconto,2));
    $agi->set_variable("V_VALOR_PROPOSTA_1" , round($ValorProposta1, 2));
    $agi->set_variable("V_VALOR_ATUALIZADO_PROPOSTA_2" , round($valorDevedor + $Multa,2));
    $agi->set_variable("V_VALOR_MULTA_PARCELA_PROPOSTA_2" , $Multa,2);
    $agi->set_variable("V_VALOR_TOTAL_CORRIGIDO" , round($Parcela1 + $Parcela2, 2));
    $agi->set_variable("V_TOTAL_PARCELA_PROPOSTA_2" , round(($Parcela1 + $Parcela2) / 2, 2));


  } else {
    break;
  }
}


exit(1);
