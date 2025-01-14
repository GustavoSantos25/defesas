<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendamentoRequest;
use App\Models\Agendamento;
use App\Models\Docente;
use DateTime;
use Illuminate\Http\Request;
use Uspdev\Replicado\DB;
use Carbon\Carbon;
use App\Models\Banca;
use PhpParser\Node\Expr\Cast\Array_;

class ImportacaoController extends Controller
{
    public function bancas_aprovadas(){
        $this->authorize('admin');
        $query = "
        SELECT V.codpes, V.nompes, A.dtaaprbantrb, R.tittrb FROM DDTENTREGATRABALHO R, DDTDEPOSITOTRABALHO D, VINCULOPESSOAUSP V
        INNER JOIN AGPROGRAMA A ON V.codpes = A.codpes
        WHERE
        V.tipvin = 'ALUNOPOS'
        AND A.dtaaprbantrb IS NOT NULL
        AND A.dtadfapgm IS NULL
        AND V.sitatl = 'A'
        AND D.codpes = A.codpes
        AND D.codare = A.codare
        AND D.numseqpgm = A.numseqpgm
        AND D.dtahomdpotrb IS NOT NULL
        AND D.coddpodgttrb = R.coddpodgttrb
        AND V.codclg IN (
        ";

        $codundclgi = getenv('REPLICADO_CODUNDCLG');
        $codundclgi = array_map('intval', explode(",", $codundclgi));

        foreach($codundclgi as $c){
            $query .= "$c, ";
        }
        $query = rtrim($query, ", ");
        $query .= ")";

        $bancas_aprovadas =  DB::fetchAll($query);

        //MOSTRAR APENAS AS DEFESAS QUE AINDA NÃO FORAM SALVAS NO BANCO
        $agendamentos = Agendamento::all();
        foreach($agendamentos as $agendamento){
            for($i = 0; $i < sizeof($bancas_aprovadas); $i++){
                if($agendamento['codpes'] == $bancas_aprovadas[$i]['codpes'] && strcmp($agendamento['titulo'], $bancas_aprovadas[$i]['tittrb']) == 0){
                    unset($bancas_aprovadas[$i]);
                    $bancas_aprovadas = array_values($bancas_aprovadas);
                    break;
                }
            }
        }

        return view('importacao.bancas_aprovadas',[
            'bancas_aprovadas' => $bancas_aprovadas
        ]);
    }

    // Retorna os dados gerais do aluno com NUSP = $codpes
    private function get_dados_aluno($codpes){
        $query_dadosGerais = "
        SELECT DISTINCT P.codpes, P.nompes, P.sexpes, A.codare, A.nivpgm, N.nomare
        FROM AGPROGRAMA AS A, PESSOA AS P, NOMEAREA AS N
        WHERE A.codpes = $codpes
        AND A.codpes = P.codpes
        AND N.codare = A.codare
        AND A.nivpgm IS NOT NULL";

        $dadosGerais = DB::fetchAll($query_dadosGerais);
        return $dadosGerais;
    }

    // Retorna a banca da defesa do aluno com NUSP = $codpes
    private function get_dados_banca($codpes){
        $query_banca = "
        SELECT R.vinptpbantrb, R.codpesdct, P.nompes
        FROM
        R48PGMTRBDOC AS R, AGPROGRAMA AS A, PESSOA AS P
        WHERE
        R.codpes = $codpes    
        AND R.codpesdct = P.codpes
        AND A.codare = R.codare
        AND R.codpes = A.codpes
        AND A.numseqpgm = R.numseqpgm
        ORDER BY R.vinptpbantrb ASC, P.nompes ASC
        ";
        $banca_aluno = DB::fetchAll($query_banca);

        return $banca_aluno;
    }

    // Retorna os dados do trabalho escrito do aluno com NUSP = $codpes
    private function get_dados_trabalho($codpes){
        $query_trabalho = "
        SELECT A.codpes, R.tittrb, R.rsutrb, R.palcha
        FROM AGPROGRAMA AS A, DDTENTREGATRABALHO AS R, DDTDEPOSITOTRABALHO AS D
        WHERE A.codpes = $codpes
        AND D.codpes = A.codpes
        AND D.codare = A.codare
        AND D.numseqpgm = A.numseqpgm 
        AND D.coddpodgttrb = R.coddpodgttrb
        ";
        $trabalho = DB::fetchAll($query_trabalho);

        return $trabalho;
    }

    // Retorna os dados do orientador do aluno com NUSP = $codpes
    private function get_dados_orientador($codpes){
        $query_ori = "
        SELECT DISTINCT O.codpes, P.nompes, O.tiport
        FROM AGPROGRAMA AS A, PESSOA AS P, R39PGMORIDOC AS O
        WHERE A.codpes = $codpes
        AND O.codpespgm = A.codpes
        AND P.codpes = O.codpes
        AND O.codare = A.codare
        AND O.numseqpgm = A.numseqpgm
        AND O.dtafimort IS NULL
        ";
        $orientador = DB::fetchAll($query_ori);

        return $orientador;
    }


    // Salva os dados da defesa do aluno com NUSP = $codpes
    // Os dados são salvos na tabela 'agendamentos' e 'bancas'
    // Como aqui, a defesa ainda não foi agendada, alguns campos estão vazios ou inconsistentes,
    // e isso deve ser mudado futuramente, após o agendamento real dessas defesas
    // Estou supondo que como os dados vieram do Janus, eles são válidos.
    public function dados_defesa_aluno($codpes){
        $this->authorize('admin');
        $dadosGerais = $this->get_dados_aluno($codpes);
        $banca_aluno = $this->get_dados_banca($codpes);
        $trabalho = $this->get_dados_trabalho($codpes);
        $orientador = $this->get_dados_orientador($codpes);

        $agendamento = array(
            'codpes' => $dadosGerais[0]['codpes'],
            'nome' => $dadosGerais[0]['nompes'], 
            'regimento' => "Novo",
            'orientador_votante' => "Sim", 
            'sexo' => $dadosGerais[0]['sexpes'], 
            'nivel' => $dadosGerais[0]['nivpgm'], 
            'titulo' => $trabalho[0]['tittrb'], 
            'area_programa' => $dadosGerais[0]['codare'],
            'data_horario' => null, 
            'sala' => " ", 
            'orientador' => $orientador[0]['codpes'],
            'resumo' => $trabalho[0]['rsutrb'],
            'palavras_chave' => $trabalho[0]['palcha'],
            'nome_orientador' => $orientador[0]['nompes'],
        );

       
        $defesa_dadosGerais = Agendamento::create($agendamento);
        
        $newBanca = array();
        $newDocente = array();
        foreach($banca_aluno as $banca){
            $newBanca['codpes'] = $banca['codpesdct'];
            $newDocente['n_usp'] = $banca['codpesdct'];
            $newDocente['nome'] = $banca['nompes'];
            switch($banca['vinptpbantrb']){
                case 'PRE':
                    $newBanca['presidente'] = 'Sim';
                    $newBanca['tipo'] = 'Titular';
                    break;
                case 'TIT':
                    $newBanca['presidente'] = 'Não';
                    $newBanca['tipo'] = 'Titular';
                    break;
                case 'SUP':
                    $newBanca['presidente'] = 'Não';
                    $newBanca['tipo'] = 'Suplente';
                    break;
            }
            $newBanca['agendamento_id'] = $defesa_dadosGerais->id;
            Banca::create($newBanca);
            Docente::create($newDocente);
        }
        request()->session()->flash('alert-info', 'Dados importados com sucesso');
        return back();
    }
}
