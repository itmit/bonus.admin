@extends('layouts.mainApp')

@section('content')
<form method="POST" action="{{route('auth.news.update',['id' => $news->id])}}">
    {{ csrf_field() }}
    {{ method_field('PATCH') }}
    <input type="hidden" name="id" value="{{$news->id}}">
    <div class="form-group row">
        <label for="inputName" class="col-sm-2 col-form-label">Имя</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="inputName" name="name" value="{{$news->name}}">
        </div>
    </div>
    <div class="form-group row">
        <label for="descriptionTextarea" class="col-sm-2 col-form-label">Описание</label>
        <div class="col-sm-10">
          <textarea class="form-control" id="descriptionTextarea" name="description" rows="4">{{$news->description}}</textarea>
        </div>
    </div>
    <div class="form-group row">
        <label for="inputPreview" class="col-sm-2 col-form-label">Картинка</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="inputPreview" name="preview_image" value="{{$news->preview_image}}">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-10">
          <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </div>
</form>
@endsection