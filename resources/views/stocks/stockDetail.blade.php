@extends('layouts.mainApp')

@section('content')
<form method="POST" action="{{route('auth.stocks.update',['id' => $stock->id])}}">
    {{ csrf_field() }}
    {{ method_field('PATCH') }}
    <div class="form-group row">
        <label for="inputName" class="col-sm-2 col-form-label">Нзвание</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="inputName" name="name" value="{{$stock->name}}">
        </div>
    </div>
    <div class="form-group row">
        <label for="inputCountry" class="col-sm-2 col-form-label">Страна</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="inputCountry" name="country" value="{{$stock->country}}">
        </div>
    </div>
    <div class="form-group row">
        <label for="inputCity" class="col-sm-2 col-form-label">Город</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="inputCity" name="city" value="{{$stock->city}}">
        </div>
    </div>
    <div class="form-group row">
        <label for="descriptionTextarea" class="col-sm-2 col-form-label">Описание</label>
        <div class="col-sm-10">
          <textarea class="form-control" id="descriptionTextarea" name="description" rows="4">{{$stock->description}}</textarea>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-10">
          <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </div>
</form>
@endsection