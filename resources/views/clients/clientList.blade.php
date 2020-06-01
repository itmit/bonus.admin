@extends('layouts.mainApp')

@section('content')

<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
Фильтры
</button>
<div class="collapse" id="collapseExample">
    <div class="card my-2">
        <form class="card-body">
          <div class="form-row">
            <div class="col-md-6 mb-3">
              <label for="name_field">ФИО</label>
              <input type="text" name="filter_name" class="form-control" id="name_field" value="{{ old('filter_name') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label for="email_field">Эл. почта</label>
              <input type="text" name="filter_email" class="form-control" id="email_field" value="{{ old('filter_email') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label for="phone_field">Телефон</label>
              <input type="text" name="filter_phone" class="form-control" id="phone_field" value="{{ old('filter_phone') }}">
            </div>
          </div>
          <button class="btn btn-primary" type="submit">Поиск</button>
          <a href="/customers" class="btn btn-primary">Сбросить</a>
        </form>
    </div>
</div>

<hr>

<div class="col-sm-12 tabs-content">
    <div class="row justify-content-center cont-m">
        <div class="col-md-12">
            <h2>{{ $title }}</h2>
            <table class="table policy-table">
                <thead>
                <tr>
                    <th scope="col">ФИО</th>
                    <th scope="col">Эл. почта</th>
                    <th scope="col">Телефон</th>
                    <th scope="col">Действие</th>
                </tr>
                </thead>
                <tbody>
                @foreach($clients as $item)
                    <tr>
                        <td><a href="/customers/{{ $item->id }}">{{ $item->name }}</a></td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td><button type="button" class="btn btn-danger delete-item" data-id="{{$item->id}}">Удалить</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> 

<script>

$(document).on('click', '.delete-item', function() {
    var _self = $(this);
    let isDelete = confirm("Удалить клиента? Данное действие невозможно отменить!");

    if(isDelete)
    {
        let id = _self.data('id');
        $.ajax({
            headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: "json",
            data    : { id: id },
            url     : 'clients/delete',
            method    : 'delete',
            success: function (response) {
                _self.closest('tr').remove();
                console.log('Удалено!');
            },
            error: function (xhr, err) { 
                console.log("Error: " + xhr + " " + err);
            }
        });
    }
});

</script>
@endsection