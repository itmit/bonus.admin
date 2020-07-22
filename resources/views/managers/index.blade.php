@extends('layouts.mainApp')

@section('content')
<div class="col-sm-12 tabs-content">
    <div class="row justify-content-center cont-m">
        <div class="col-md-12">
            <h2>{{ $title }}</h2>
            <a class="btn btn-primary btn-lg" href="/managers/create">Создать менеджера</a>
            <table class="table policy-table">
                <thead>
                <tr>
                    <th scope="col">ФИО</th>
                    <th scope="col">Эл. почта</th>
                    <th scope="col">Город</th>
                    <th scope="col">Действие</th>
                </tr>
                </thead>
                <tbody>
                @foreach($managers as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->city }}</td>
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
    let isDelete = confirm("Удалить менеджера? Данное действие невозможно отменить!");

    if(isDelete)
    {
        let id = _self.data('id');
        $.ajax({
            headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: "json",
            data    : { id: id },
            url     : 'managers/delete',
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