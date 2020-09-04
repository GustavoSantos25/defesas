@inject('pessoa','Uspdev\Replicado\Pessoa')
@inject('replicado','App\Utils\ReplicadoUtils')

@extends('pdfs.fflch')
@section('styles_head')
<style type="text/css">
    #headerFFLCH {
        font-size: 14px; width: 17cm; text-align:center; font-weight:bold; font-style:italic;
    }
    .data_hoje{
        margin-left: 10cm; margin-bottom:0.8cm; 
    }
    .conteudo{ 
        margin: 1cm 
    }
    .boxSuplente {
        border: 1px solid; padding: 4px;
    }
    .boxPassagem {
        border: 1px solid; padding: 4px; text-align: justify;
    }
    .oficioSuplente{
        text-align: justify; 
    }
    .rodapeFFLCH{
        padding-top:3cm; text-align: center;
    }
    p.recuo {
        text-indent: 0.5cm;
    }
    .moremargin {
        margin-bottom: 0.15cm;
    }
    .importante {
        border:1px solid; margin-top:0.3cm; margin-bottom:0.3cm; width: 15cm; font-size:12px; margin-left:0.5cm;
    }
    .negrito {
        font-weight: bolder;
    }
    .justificar{
        text-align: justify;
    }
    table{
        border-collapse: collapse;
        border: 0px solid #000;
    }
    table th, table td {
        border: 0px solid #000;
    }
    tr, td {
        border: 1px #000 solid; padding: 1
    }
    body{
        margin-top: 0.2em; margin-left: 1.8em; font-family: DejaVu Sans, sans-serif; font-size: 12px;
    }
    #footer {
        position: fixed;
        bottom: -1cm;
        left: 0px;
        right: 0px;
        text-align: center;
        border-top: 1px solid gray;
        width: 18.5cm;
        height: 100px;
    }
    .page-break {
        page-break-after: always;
        margin-top:160px;
    }
</style>
@endsection('styles_head')

@section('content')
    <div id="headerFFLCH" style="text-align:center;">
		<table>
            <tr>
                <br>
                <td width="2cm"> <img src="images/fflch.gif" width="95%"/> </td> 
                <td width="14cm"> 
                    <p align="center" style="font-style:normal; font-size:17px"> 
                        Universidade de São Paulo<br> 
                        Faculdade de Filosofia, Letras e Ciências Humanas<br>
                    </p>
                </td>
            </tr>
 	    </table>
	</div>

    <div align="right">
        @php(setlocale(LC_TIME, 'pt_BR','pt_BR.utf-8','portuguese'))
        São Paulo, {{ strftime('%d de %B de %Y', strtotime('today')) }}    
    </div><br>

    <h1 align="center"> DECLARAÇÃO </h1>
    <br><br><br>

    <p class="recuo justificar" style="line-height: 190%;">  
        
        {!!$configs->declaracao!!}
    </p> <br><br>

    <table width="16cm" style="border='0'; margin-left:4cm; align-items: center; justify-content: center;">
        @foreach($professores as $componente)    
        <tr style="border='0'">
            <td><b>{{$componente->nome}}</b> </td>
            @if($pessoa::cracha($componente->codpes)['nomorg'] == null) 
                <td><b>{{$replicado::nomeOrganizacao($componente->codpes)['sglorg']}}</b></td>
            @else
                <td><b>{{$pessoa::cracha($componente->codpes)['nomorg']}}</b></td>
            @endif
        </tr>
        @endforeach
    </table>
	<div style="margin-top:2cm;" align="center"> 
        Atenciosamente,<br>  
        <b>
            {{Auth::user()->name}} - Defesas de Mestrado e Doutorado da {{$pessoa::cracha(Auth::user()->codpes)['nomorg']}}/USP 
        </b>
    </div> 
    <div id="footer">
        {!! $configs->rodape_oficios !!}
    </div>
@endsection('content')
