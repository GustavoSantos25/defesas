@extends('laravel-usp-theme::master')

@section('javascripts_head')
  <script src="{{asset('/js/app.js')}}"></script>
@endsection('javascript_head')

@section('content')
    @include('flash')

    <div class="row">
        <div class="col-sm">
            <a href="/docentes/create" class="btn btn-primary">Cadastrar Docente</a></br>
        </div>
        <div class="col-auto float-right">
            <form method="POST" action="/docentes/{{ $docente->id }}">
                @csrf 
                @method('delete')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Você tem certeza que deseja apagar?')">Apagar</button>
            </form>
        </div>
    </div>
    <br>

    <div class="card">
        <div class="card-header"><h4>Editar - Docente</h4></div>
        <div class="card-body">
            <form action="/docentes/{{$docente->id}}" method="POST">
                @csrf
                @method('PATCH')
                @include('docentes.form')
            </form>
        </div>
    </div>
@endsection('content')